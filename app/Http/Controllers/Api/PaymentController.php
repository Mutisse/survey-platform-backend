<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;


class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Criar novo pagamento
     * POST /api/payments
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        try {
            $result = $this->paymentService->createPaymentIntent([
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'MZN',
                'customer_phone' => $request->customer_phone,
                'payment_method' => $request->payment_method ?? 'mpesa',
                'metadata' => $request->metadata ?? ['source' => 'api'],
                'idempotency_key' => $request->header('Idempotency-Key')
            ]);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                    'data' => [
                        'payment' => new PaymentResource($result['payment']),
                        'client_secret' => $result['payment']->client_secret
                    ]
                ], 201);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
                'code' => $result['code'] ?? 'ERROR'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erro no controller store: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno ao processar pagamento'
            ], 500);
        }
    }

    /**
     * Listar todos os pagamentos
     * GET /api/payments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payments = Payment::query()
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($request->phone, function ($query, $phone) {
                    return $query->where('customer_phone', 'like', "%$phone%");
                })
                ->when($request->mpesa_reference, function ($query, $ref) {
                    return $query->where('mpesa_reference', 'like', "%$ref%");
                })
                ->when($request->date_from, function ($query, $date) {
                    return $query->whereDate('created_at', '>=', $date);
                })
                ->when($request->date_to, function ($query, $date) {
                    return $query->whereDate('created_at', '<=', $date);
                })
                ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_page' => $payments->currentPage(),
                    'data' => PaymentResource::collection($payments),
                    'first_page_url' => $payments->url(1),
                    'from' => $payments->firstItem(),
                    'last_page' => $payments->lastPage(),
                    'last_page_url' => $payments->url($payments->lastPage()),
                    'links' => $payments->linkCollection(),
                    'next_page_url' => $payments->nextPageUrl(),
                    'path' => $payments->path(),
                    'per_page' => $payments->perPage(),
                    'prev_page_url' => $payments->previousPageUrl(),
                    'to' => $payments->lastItem(),
                    'total' => $payments->total()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar pagamentos: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao listar pagamentos'
            ], 500);
        }
    }

    /**
     * Ver detalhes de um pagamento
     * GET /api/payments/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $payment = Payment::with('user')->find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pagamento não encontrado'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => new PaymentResource($payment)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar pagamento: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao buscar pagamento'
            ], 500);
        }
    }

    /**
     * Verificar status de um pagamento
     * GET /api/payments/{id}/status
     */
    public function status(int $id): JsonResponse
    {
        try {
            $result = $this->paymentService->checkPaymentStatus($id);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'payment_status' => $result['payment']->status,
                        'payment' => new PaymentResource($result['payment'])
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 404);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao verificar status'
            ], 500);
        }
    }

    /**
     * Webhook para receber atualizações de pagamento
     * POST /api/payments/webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();

            Log::info('Webhook recebido', ['payload' => $payload]);

            $result = $this->paymentService->processWebhook($payload);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook processado com sucesso'
                ]);
            }

            return response()->json([
                'status' => 'received',
                'message' => 'Webhook recebido mas não processado'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no webhook: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar webhook'
            ], 500);
        }
    }

    /**
     * Estatísticas de pagamentos
     * GET /api/payments/stats/summary
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $query = Payment::query();

            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $totalAmount = (float) $query->sum('amount');
            $successAmount = (float) (clone $query)->where('status', 'success')->sum('amount');
            $pendingAmount = (float) (clone $query)->where('status', 'pending')->sum('amount');
            $failedAmount = (float) (clone $query)->where('status', 'failed')->sum('amount');
            $processingAmount = (float) (clone $query)->where('status', 'processing')->sum('amount');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'totalAmount' => $totalAmount,
                    'successAmount' => $successAmount,
                    'pendingAmount' => $pendingAmount,
                    'failedAmount' => $failedAmount,
                    'processingAmount' => $processingAmount,
                    'totalCount' => $query->count(),
                    'successCount' => (clone $query)->where('status', 'success')->count(),
                    'pendingCount' => (clone $query)->where('status', 'pending')->count(),
                    'failedCount' => (clone $query)->where('status', 'failed')->count(),
                    'processingCount' => (clone $query)->where('status', 'processing')->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar resumo: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao gerar resumo'
            ], 500);
        }
    }
}
