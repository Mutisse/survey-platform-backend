<?php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class PaymentService
{
    protected string $mpesaApiUrl;
    protected string $mpesaApiKey;
    protected string $mpesaPublicKey;
    protected string $mpesaServiceProviderCode;
    protected string $mpesaInitiatorIdentifier;
    protected string $mpesaSecurityCredential;
    protected Client $httpClient;

    public function __construct()
    {
        // Configurações do M-Pesa (devem estar no .env)
        $this->mpesaApiUrl = env('MPESA_API_URL', 'https://api.vm.co.mz');
        $this->mpesaApiKey = env('MPESA_API_KEY');
        $this->mpesaPublicKey = env('MPESA_PUBLIC_KEY');
        $this->mpesaServiceProviderCode = env('MPESA_SERVICE_PROVIDER_CODE');
        $this->mpesaInitiatorIdentifier = env('MPESA_INITIATOR_IDENTIFIER');
        $this->mpesaSecurityCredential = env('MPESA_SECURITY_CREDENTIAL');

        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false, // Em produção, mude para true e use certificados válidos
            'http_errors' => false
        ]);
    }

    /**
     * Criar uma nova intenção de pagamento
     */
    public function createPaymentIntent(array $data): array
    {
        try {
            // Validar dados obrigatórios
            if (empty($data['amount']) || empty($data['customer_phone'])) {
                return [
                    'success' => false,
                    'message' => 'Dados incompletos para criar pagamento',
                    'code' => 'INVALID_DATA'
                ];
            }

            // Verificar chave de idempotência
            if (!empty($data['idempotency_key'])) {
                $existingPayment = Payment::where('idempotency_key', $data['idempotency_key'])->first();
                if ($existingPayment) {
                    return [
                        'success' => true,
                        'message' => 'Pagamento já existe',
                        'payment' => $existingPayment,
                        'code' => 'DUPLICATE'
                    ];
                }
            }

            // Iniciar transação M-Pesa
            $mpesaResult = $this->initiateMpesaPayment($data);

            if (!$mpesaResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Erro ao iniciar pagamento M-Pesa: ' . $mpesaResult['message'],
                    'code' => $mpesaResult['code'] ?? 'MPESA_ERROR'
                ];
            }

            // Criar payment intent
            $paymentIntentId = 'pi_' . Str::random(24);
            $clientSecret = $paymentIntentId . '_secret_' . Str::random(32);

            // Criar pagamento
            $payment = Payment::create([
                'payment_intent_id' => $paymentIntentId,
                'client_secret' => $clientSecret,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'MZN',
                'customer_phone' => $data['customer_phone'],
                'payment_method' => $data['payment_method'] ?? 'mpesa',
                'provider' => 'mpesa',
                'status' => 'processing',
                'mpesa_reference' => $mpesaResult['conversation_id'] ?? $mpesaResult['reference'],
                'mpesa_response_code' => $mpesaResult['code'] ?? null,
                'mpesa_response_message' => $mpesaResult['message'] ?? null,
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'mpesa_request' => $mpesaResult['response'],
                    'initiated_at' => now()->toISOString()
                ]),
                'idempotency_key' => $data['idempotency_key'] ?? null
            ]);

            Log::info('Pagamento M-Pesa iniciado', [
                'payment_id' => $payment->id,
                'phone' => $data['customer_phone'],
                'amount' => $data['amount'],
                'conversation_id' => $mpesaResult['conversation_id'] ?? null,
                'reference' => $mpesaResult['reference'] ?? null
            ]);

            return [
                'success' => true,
                'message' => 'Pagamento iniciado com sucesso. Por favor, insira o PIN M-Pesa.',
                'payment' => $payment,
                'code' => 'PENDING'
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Erro interno ao processar pagamento',
                'code' => 'INTERNAL_ERROR',
                'error' => $e->getMessage()
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
                    'code' => 'NOT_FOUND'
                ];
            }

            // Se já estiver concluído ou falhou, retornar status atual
            if (in_array($payment->status, ['success', 'failed'])) {
                return [
                    'success' => true,
                    'message' => 'Status verificado',
                    'payment' => $payment,
                    'code' => strtoupper($payment->status)
                ];
            }

            // Consultar status na API do M-Pesa
            $mpesaStatus = $this->queryMpesaStatus($payment);

            if ($mpesaStatus['success']) {
                $payment->update([
                    'status' => $mpesaStatus['status'],
                    'mpesa_response_code' => $mpesaStatus['code'],
                    'mpesa_response_message' => $mpesaStatus['message'],
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'status_check' => $mpesaStatus['response'],
                        'checked_at' => now()->toISOString()
                    ])
                ]);

                Log::info('Status M-Pesa atualizado', [
                    'payment_id' => $payment->id,
                    'status' => $mpesaStatus['status'],
                    'code' => $mpesaStatus['code']
                ]);
            }

            return [
                'success' => true,
                'message' => 'Status verificado com sucesso',
                'payment' => $payment,
                'code' => strtoupper($payment->status)
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao verificar status',
                'code' => 'CHECK_ERROR',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Processar webhook do provedor de pagamento
     */
    public function processWebhook(array $payload): array
    {
        try {
            Log::info('Webhook M-Pesa recebido', ['payload' => $payload]);

            // Validar assinatura do webhook
            if (!$this->validateWebhookSignature($payload)) {
                Log::warning('Assinatura inválida no webhook', ['payload' => $payload]);
                return [
                    'success' => false,
                    'message' => 'Assinatura inválida',
                    'code' => 'INVALID_SIGNATURE'
                ];
            }

            // Extrair dados do payload baseado no tipo de notificação
            $transactionId = $payload['TransactionID'] ??
                           $payload['TransID'] ??
                           $payload['output_TransactionID'] ?? null;

            $conversationId = $payload['ConversationID'] ??
                             $payload['ConvID'] ??
                             $payload['output_ConversationID'] ?? null;

            $resultCode = $payload['ResultCode'] ??
                         $payload['output_ResponseCode'] ?? '0';

            $resultDesc = $payload['ResultDesc'] ??
                         $payload['output_ResponseDesc'] ?? 'Processado';

            if (!$transactionId && !$conversationId) {
                return [
                    'success' => false,
                    'message' => 'Payload inválido - sem identificador',
                    'code' => 'INVALID_PAYLOAD'
                ];
            }

            // Buscar pagamento
            $payment = null;

            if ($transactionId) {
                $payment = Payment::where('mpesa_reference', $transactionId)->first();
            }

            if (!$payment && $conversationId) {
                $payment = Payment::where('metadata->mpesa_request->output_ConversationID', $conversationId)
                    ->orWhere('metadata->mpesa_request->ConversationID', $conversationId)
                    ->first();
            }

            if (!$payment) {
                Log::warning('Pagamento não encontrado para webhook', [
                    'transaction_id' => $transactionId,
                    'conversation_id' => $conversationId
                ]);

                // Salvar webhook órfão para análise
                $this->saveOrphanWebhook($payload);

                return [
                    'success' => false,
                    'message' => 'Pagamento não encontrado',
                    'code' => 'PAYMENT_NOT_FOUND'
                ];
            }

            // Determinar status
            $status = $this->mapMpesaCodeToStatus($resultCode);

            // Atualizar pagamento
            $payment->update([
                'status' => $status,
                'mpesa_reference' => $transactionId ?? $payment->mpesa_reference,
                'mpesa_response_code' => (string) $resultCode,
                'mpesa_response_message' => $resultDesc,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'webhook' => $payload,
                    'webhook_processed_at' => now()->toISOString(),
                    'webhook_type' => $this->determineWebhookType($payload)
                ])
            ]);

            Log::info('Webhook M-Pesa processado com sucesso', [
                'payment_id' => $payment->id,
                'status' => $status,
                'code' => $resultCode,
                'transaction_id' => $transactionId
            ]);

            return [
                'success' => true,
                'message' => 'Webhook processado com sucesso',
                'payment' => $payment,
                'code' => 'PROCESSED'
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao processar webhook',
                'code' => 'PROCESSING_ERROR',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Iniciar pagamento M-Pesa
     */
    protected function initiateMpesaPayment(array $data): array
    {
        try {
            // Formatar número de telefone
            $phone = $this->formatPhoneNumber($data['customer_phone']);

            // Gerar referências únicas
            $transactionReference = 'TXN' . date('YmdHis') . Str::random(6);
            $thirdPartyReference = 'TPR' . date('YmdHis') . Str::random(6);

            // Obter token
            $token = $this->getMpesaToken();

            // Dados da requisição C2B
            $requestData = [
                'input_ServiceProviderCode' => $this->mpesaServiceProviderCode,
                'input_CustomerMSISDN' => $phone,
                'input_Amount' => (string) $data['amount'],
                'input_TransactionReference' => $transactionReference,
                'input_ThirdPartyReference' => $thirdPartyReference,
            ];

            Log::info('Enviando requisição C2B M-Pesa', $requestData);

            // Fazer requisição HTTP com Guzzle
            $response = $this->httpClient->post($this->mpesaApiUrl . '/ipg/v1/c2bPayment/singleStage/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Origin' => config('app.url'),
                ],
                'json' => $requestData
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            // Tentar decodificar JSON
            $responseData = json_decode($body, true) ?? [];

            // Verificar se a requisição foi bem sucedida
            if ($statusCode < 200 || $statusCode >= 300) {
                Log::error('Resposta HTTP erro M-Pesa', [
                    'status' => $statusCode,
                    'body' => $body
                ]);

                return [
                    'success' => false,
                    'message' => "Erro HTTP $statusCode na comunicação com M-Pesa",
                    'code' => "HTTP_$statusCode",
                    'response' => $responseData
                ];
            }

            Log::info('Resposta C2B M-Pesa recebida', $responseData);

            // Verificar código de resposta
            $responseCode = $responseData['output_ResponseCode'] ?? '500';

            if ($responseCode === 'INS-0' || $responseCode === '0') {
                return [
                    'success' => true,
                    'reference' => $transactionReference,
                    'conversation_id' => $responseData['output_ConversationID'] ?? null,
                    'response' => $responseData,
                    'message' => 'Pagamento iniciado com sucesso',
                    'code' => 'SUCCESS'
                ];
            }

            // Mapear erro específico
            $errorMessage = $this->mapMpesaErrorCode(
                $responseCode,
                $responseData['output_ResponseDesc'] ?? 'Erro desconhecido'
            );

            return [
                'success' => false,
                'message' => $errorMessage,
                'code' => $responseCode,
                'response' => $responseData
            ];

        } catch (ConnectException $e) {
            Log::error('ConnectException M-Pesa: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erro de conexão com M-Pesa: servidor não respondeu',
                'code' => 'CONNECTION_ERROR'
            ];
        } catch (RequestException $e) {
            Log::error('RequestException M-Pesa: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erro na requisição M-Pesa: ' . $e->getMessage(),
                'code' => 'REQUEST_ERROR'
            ];
        } catch (\Exception $e) {
            Log::error('Erro na requisição M-Pesa: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao conectar com M-Pesa: ' . $e->getMessage(),
                'code' => 'SYSTEM_ERROR'
            ];
        }
    }

    /**
     * Consultar status da transação M-Pesa
     */
    protected function queryMpesaStatus(Payment $payment): array
    {
        try {
            if (!$payment->mpesa_reference) {
                return [
                    'success' => false,
                    'status' => $payment->status,
                    'message' => 'Sem referência para consulta',
                    'code' => 'NO_REFERENCE'
                ];
            }

            $token = $this->getMpesaToken();

            $queryData = [
                'input_ServiceProviderCode' => $this->mpesaServiceProviderCode,
                'input_TransactionReference' => $payment->mpesa_reference,
                'input_ThirdPartyReference' => $payment->mpesa_reference,
            ];

            $response = $this->httpClient->post($this->mpesaApiUrl . '/ipg/v1/queryTransactionStatus/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $queryData
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode >= 300) {
                return [
                    'success' => false,
                    'status' => $payment->status,
                    'message' => 'Erro na consulta HTTP ' . $statusCode,
                    'code' => 'QUERY_ERROR'
                ];
            }

            $body = $response->getBody()->getContents();
            $responseData = json_decode($body, true) ?? [];

            $code = $responseData['output_ResponseCode'] ?? '0';
            $status = $this->mapMpesaCodeToStatus($code);

            return [
                'success' => true,
                'status' => $status,
                'code' => $code,
                'message' => $responseData['output_ResponseDesc'] ?? 'Consulta realizada',
                'response' => $responseData
            ];

        } catch (\Exception $e) {
            Log::error('Erro ao consultar M-Pesa: ' . $e->getMessage(), [
                'payment_id' => $payment->id
            ]);

            return [
                'success' => false,
                'status' => $payment->status,
                'message' => 'Erro na consulta: ' . $e->getMessage(),
                'code' => 'QUERY_EXCEPTION'
            ];
        }
    }

    /**
     * Obter token de autenticação M-Pesa
     */
    protected function getMpesaToken(): string
    {
        try {
            // Verificar cache
            if (Cache::has('mpesa_access_token')) {
                $cached = Cache::get('mpesa_access_token');
                if (isset($cached['token']) && isset($cached['expires_at']) && now()->lt($cached['expires_at'])) {
                    return $cached['token'];
                }
            }

            // Gerar novo token
            $credentials = base64_encode($this->mpesaApiKey . ':' . $this->mpesaPublicKey);

            $response = $this->httpClient->post($this->mpesaApiUrl . '/ipg/v1/security/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new \Exception('Falha na autenticação M-Pesa: HTTP ' . $statusCode);
            }

            $body = $response->getBody()->getContents();
            $tokenData = json_decode($body, true) ?? [];

            if (empty($tokenData['output_AccessToken'])) {
                throw new \Exception('Token não recebido da M-Pesa');
            }

            $token = $tokenData['output_AccessToken'];
            $expiresIn = $tokenData['output_ExpiresIn'] ?? 3500; // ~58 minutos

            // Guardar em cache (expira em 50 minutos para segurança)
            Cache::put('mpesa_access_token', [
                'token' => $token,
                'expires_at' => now()->addSeconds(min($expiresIn - 300, 3300))
            ], now()->addSeconds(min($expiresIn - 300, 3300)));

            return $token;

        } catch (\Exception $e) {
            Log::error('Erro crítico ao obter token M-Pesa: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Formatar número de telefone para padrão M-Pesa
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remover tudo que não é número
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Se começar com 84, 85, 86, 87, adicionar 258
        if (strlen($phone) === 9 && preg_match('/^8[4-7]/', $phone)) {
            $phone = '258' . $phone;
        }

        // Se já tiver 258 no início, manter
        if (strlen($phone) === 12 && substr($phone, 0, 3) === '258') {
            return $phone;
        }

        throw new \InvalidArgumentException('Número de telefone inválido para M-Pesa');
    }

    /**
     * Validar assinatura do webhook
     */
    protected function validateWebhookSignature(array $payload): bool
    {
        // Implementar validação conforme documentação M-Pesa
        $trustedIps = config('mpesa.trusted_ips', []);
        $requestIp = request()->ip();

        if (!empty($trustedIps) && !in_array($requestIp, $trustedIps)) {
            Log::warning('Webhook de IP não confiável', ['ip' => $requestIp]);
            return false;
        }

        return true;
    }

    /**
     * Mapear código M-Pesa para status interno
     */
    protected function mapMpesaCodeToStatus(string $code): string
    {
        $code = (string) $code;

        // Códigos de sucesso
        if ($code === '0' || $code === 'INS-0' || $code === '00') {
            return 'success';
        }

        // Códigos de pendência (processando)
        if (in_array($code, ['INS-1', '1', 'PENDING', 'processing'])) {
            return 'processing';
        }

        // Códigos de falha
        if (in_array($code, [
            'INS-2', '2', 'INS-3', '3', 'INS-4', '4', 'INS-5', '5',
            'FAILED', 'failed', 'error', 'ERROR', 'TIMEOUT'
        ])) {
            return 'failed';
        }

        // Qualquer outro código, considerar falha
        return 'failed';
    }

    /**
     * Mapear código de erro M-Pesa para mensagem amigável
     */
    protected function mapMpesaErrorCode(string $code, string $defaultMessage): string
    {
        $errors = [
            'INS-1' => 'Saldo insuficiente',
            'INS-2' => 'Limite diário excedido',
            'INS-3' => 'Número inválido',
            'INS-4' => 'Timeout na transação',
            'INS-5' => 'Transação cancelada pelo usuário',
            'INS-6' => 'Serviço temporariamente indisponível',
            'INS-7' => 'PIN inválido',
            'INS-8' => 'Conta não ativa',
            'INS-9' => 'Limite por transação excedido',
            '2001' => 'Autenticação falhou',
            '2002' => 'Token inválido',
            '2003' => 'Requisição inválida',
            '2004' => 'Serviço indisponível',
            '2005' => 'Timeout na comunicação',
        ];

        return $errors[$code] ?? $defaultMessage;
    }

    /**
     * Determinar tipo de webhook
     */
    protected function determineWebhookType(array $payload): string
    {
        if (isset($payload['TransactionType'])) {
            return $payload['TransactionType'];
        }

        if (isset($payload['output_ResponseCode'])) {
            return 'RESULT';
        }

        if (isset($payload['ResultCode'])) {
            return 'CALLBACK';
        }

        return 'UNKNOWN';
    }

    /**
     * Salvar webhook órfão para análise
     */
    protected function saveOrphanWebhook(array $payload): void
    {
        try {
            $filename = storage_path('logs/orphan_webhooks_' . date('Y-m-d') . '.log');
            $content = json_encode([
                'timestamp' => now()->toISOString(),
                'payload' => $payload
            ], JSON_PRETTY_PRINT) . PHP_EOL;

            file_put_contents($filename, $content, FILE_APPEND);
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}
