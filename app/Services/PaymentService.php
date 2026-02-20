<?php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Models\Survey;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    protected string $klcApiKey;
    protected string $klcApiUrl = 'https://api.klc.com/v1';

    public function __construct()
    {
        $this->klcApiKey = env('KLC_API_KEY');
    }

    /**
     * Criar intenção de pagamento
     */
    public function createPaymentIntent(array $data): array
    {
        try {
            $idempotencyKey = $data['idempotency_key'] ?? 'intent-' . time() . '-' . Str::random(8);

            $headers = [
                'x-api-key' => $this->klcApiKey,
                'Idempotency-Key' => $idempotencyKey,
                'Content-Type' => 'application/json',
            ];

            $metadata = array_merge($data['metadata'] ?? [], [
                'mode' => 'live',
            ]);

            $payload = [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'MZN',
                'customer_phone' => $data['customer_phone'],
                'payment_method' => $data['payment_method'] ?? 'mpesa',
                'metadata' => $metadata,
            ];

            Log::info('📤 Enviando requisição para KLC', [
                'url' => $this->klcApiUrl . '/payments',
                'mode' => 'live',
                'amount' => $data['amount'],
                'phone' => $data['customer_phone'],
            ]);

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders($headers)
                ->post($this->klcApiUrl . '/payments', $payload);

            if ($response->ok()) {
                /** @var array $responseData */
                $responseData = $response->json();

                $payment = Payment::create([
                    'user_id' => $metadata['user_id'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'MZN',
                    'customer_phone' => $data['customer_phone'],
                    'payment_method' => $data['payment_method'] ?? 'mpesa',
                    'status' => 'pending',
                    'metadata' => $metadata,
                    'idempotency_key' => $idempotencyKey,
                    'client_secret' => $responseData['client_secret'] ?? null,
                    'mpesa_reference' => $responseData['reference'] ?? null,
                ]);

                Log::info('✅ Pagamento criado com sucesso', [
                    'payment_id' => $payment->id,
                    'reference' => $responseData['reference'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Pagamento iniciado com sucesso',
                    'payment' => $payment,
                ];
            }

            Log::error('❌ Erro na API KLC', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar pagamento: ' . ($response->json()['message'] ?? 'Erro desconhecido'),
                'code' => 'API_ERROR',
            ];

        } catch (\Exception $e) {
            Log::error('❌ Exceção no createPaymentIntent: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage(),
                'code' => 'EXCEPTION',
            ];
        }
    }

    /**
     * Verificar status do pagamento
     */
    public function checkPaymentStatus(int $paymentId): array
    {
        try {
            $payment = Payment::find($paymentId);

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pagamento não encontrado',
                ];
            }

            $headers = [
                'x-api-key' => $this->klcApiKey,
                'Content-Type' => 'application/json',
            ];

            Log::info('🔍 Verificando status do pagamento', [
                'payment_id' => $paymentId,
                'reference' => $payment->mpesa_reference,
            ]);

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders($headers)
                ->get($this->klcApiUrl . '/payments/' . ($payment->mpesa_reference ?? $paymentId));

            if ($response->ok()) {
                /** @var array $responseData */
                $responseData = $response->json();

                if (isset($responseData['status'])) {
                    $payment->status = $responseData['status'];
                    $payment->save();
                }

                return [
                    'success' => true,
                    'payment' => $payment,
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao verificar status',
                'payment' => $payment,
            ];

        } catch (\Exception $e) {
            Log::error('❌ Erro ao verificar status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erro ao verificar status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Processar webhook
     */
    public function processWebhook(array $payload): array
    {
        try {
            Log::info('🔄 Processando webhook', ['payload' => $payload]);

            $payment = null;

            if (isset($payload['payment_id'])) {
                $payment = Payment::find($payload['payment_id']);
            } elseif (isset($payload['reference'])) {
                $payment = Payment::where('mpesa_reference', $payload['reference'])->first();
            }

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Pagamento não encontrado',
                ];
            }

            if (isset($payload['status'])) {
                $payment->status = $payload['status'];
                $payment->save();

                Log::info('✅ Status do pagamento atualizado', [
                    'payment_id' => $payment->id,
                    'status' => $payment->status,
                ]);
            }

            return [
                'success' => true,
                'payment' => $payment,
            ];

        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar webhook: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Enviar notificações de pagamento bem-sucedido
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
}
