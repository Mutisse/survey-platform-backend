<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Survey;
use App\Services\PaymentService;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            Log::info('📝 Iniciando criação de pagamento', [
                'amount' => $request->amount,
                'phone' => $request->customer_phone
            ]);

            $result = $this->paymentService->createPaymentIntent([
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'MZN',
                'customer_phone' => $request->customer_phone,
                'payment_method' => $request->payment_method ?? 'mpesa',
                'metadata' => $request->metadata ?? ['source' => 'api'],
                'idempotency_key' => $request->header('Idempotency-Key')
            ]);

            if ($result['success']) {
                Log::info('✅ Pagamento criado com sucesso', [
                    'payment_id' => $result['payment']->id
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                    'data' => [
                        'payment' => new PaymentResource($result['payment']),
                        'client_secret' => $result['payment']->client_secret
                    ]
                ], 201);
            }

            Log::warning('⚠️ Falha ao criar pagamento', [
                'message' => $result['message']
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
                'code' => $result['code'] ?? 'ERROR'
            ], 400);
        } catch (\Exception $e) {
            Log::error('❌ Erro no controller store: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

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
            Log::info('📊 Iniciando listagem de pagamentos', [
                'filters' => $request->all()
            ]);

            $query = Payment::query()->with('user'); // ✅ Carregar relacionamento

            // Filtros
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('phone')) {
                $query->where('customer_phone', 'like', "%{$request->phone}%");
            }

            if ($request->has('mpesa_reference')) {
                $query->where('mpesa_reference', 'like', "%{$request->mpesa_reference}%");
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('created_at', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);

            Log::info('📊 Pagamentos encontrados', [
                'total' => $payments->total(),
                'current_page' => $payments->currentPage()
            ]);

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
            Log::error('❌ Erro ao listar pagamentos: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao listar pagamentos: ' . $e->getMessage()
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
            Log::info('🔍 Buscando pagamento', ['id' => $id]);

            $payment = Payment::with('user')->find($id);

            if (!$payment) {
                Log::warning('⚠️ Pagamento não encontrado', ['id' => $id]);
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
            Log::error('❌ Erro ao buscar pagamento: ' . $e->getMessage());
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
            Log::error('❌ Erro ao verificar status: ' . $e->getMessage());
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

            Log::info('🔄 Webhook recebido', ['payload' => $payload]);

            $result = $this->paymentService->processWebhook($payload);

            if ($result['success'] && isset($result['payment'])) {
                $payment = $result['payment'];

                if ($payment->status === 'success') {
                    $this->sendPaymentSuccessNotifications($payment);
                }
            }

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
            Log::error('❌ Erro no webhook: ' . $e->getMessage());
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
            Log::error('❌ Erro ao gerar resumo: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao gerar resumo'
            ], 500);
        }
    }

    /**
     * Processar confirmação de pagamento manual
     * POST /api/payments/{id}/confirm
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        try {
            $payment = Payment::find($id);

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pagamento não encontrado'
                ], 404);
            }

            $payment->status = 'success';
            $payment->save();

            $this->sendPaymentSuccessNotifications($payment);

            return response()->json([
                'status' => 'success',
                'message' => 'Pagamento confirmado com sucesso',
                'data' => new PaymentResource($payment)
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao confirmar pagamento: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao confirmar pagamento'
            ], 500);
        }
    }

    /**
     * Método auxiliar para enviar notificações de pagamento bem-sucedido
     */
    private function sendPaymentSuccessNotifications(Payment $payment): void
    {
        try {
            $notificationController = new NotificationController();

            $metadata = $payment->metadata ?? [];
            $surveyId = $metadata['survey_id'] ?? null;

            if ($payment->user_id) {
                $notificationController->sendToUser(
                    $payment->user_id,
                    'payment_confirmed',
                    [
                        'amount' => $payment->amount,
                        'payment_id' => $payment->id,
                        'survey_id' => $surveyId,
                        'survey_title' => $metadata['survey_title'] ?? 'Pesquisa'
                    ]
                );

                Log::info('💰 Notificação de pagamento enviada', [
                    'user_id' => $payment->user_id,
                    'payment_id' => $payment->id,
                ]);
            }

            if ($surveyId) {
                $survey = Survey::with('user')->find($surveyId);

                if ($survey) {
                    $survey->status = 'active';
                    $survey->save();

                    $participants = User::where('role', 'participant')
                        ->where('verification_status', 'approved')
                        ->get();

                    foreach ($participants as $participant) {
                        $notificationController->sendToUser(
                            $participant->id,
                            'new_survey_available',
                            [
                                'student_name' => $survey->user->name,
                                'survey_title' => $survey->title,
                                'survey_id' => $survey->id,
                                'reward' => $survey->reward
                            ]
                        );
                    }
                }
            }

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $notificationController->sendToUser(
                    $admin->id,
                    'payment_received',
                    [
                        'amount' => $payment->amount,
                        'user_id' => $payment->user_id,
                        'payment_id' => $payment->id,
                        'survey_id' => $surveyId
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ Erro ao enviar notificações', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);
        }
    }

    /**
     * Método para processar pagamento de pesquisa
     * POST /api/payments/survey/{surveyId}
     */
    public function payForSurvey(Request $request, int $surveyId): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $survey = Survey::find($surveyId);

            if (!$survey) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pesquisa não encontrada'
                ], 404);
            }

            $result = $this->paymentService->createPaymentIntent([
                'amount' => $survey->price ?? 100,
                'currency' => 'MZN',
                'customer_phone' => $user->phone,
                'payment_method' => 'mpesa',
                'metadata' => [
                    'source' => 'survey_payment',
                    'survey_id' => $surveyId,
                    'survey_title' => $survey->title,
                    'user_id' => $user->id
                ],
                'idempotency_key' => 'survey_' . $surveyId . '_' . $user->id
            ]);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pagamento iniciado com sucesso',
                    'data' => [
                        'payment' => new PaymentResource($result['payment']),
                        'client_secret' => $result['payment']->client_secret
                    ]
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 400);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao pagar pesquisa: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar pagamento'
            ], 500);
        }
    }
}
