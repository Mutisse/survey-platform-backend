<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use App\Models\Survey;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SurveyResponseController extends Controller
{
    /**
     * ✅ CORRIGIDO: Mostrar resposta específica COM CONVERSÃO DE OPTIONS
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $response = SurveyResponse::with([
                'survey' => function ($query) {
                    $query->with(['questions' => function ($q) {
                        $q->orderBy('order');
                    }]);
                }
            ])->find($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta não encontrada'
                ], 404);
            }

            if ($response->user_id !== $userId) {
                Log::warning('⚠️ Usuário tentou acessar resposta de outro usuário', [
                    'user_id' => $userId,
                    'response_user_id' => $response->user_id,
                    'response_id' => $id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Esta resposta não pertence ao seu usuário'
                ], 403);
            }

            // ✅ FORÇAR A CONVERSÃO DE OPTIONS PARA ARRAY
            if ($response->survey && $response->survey->questions) {
                foreach ($response->survey->questions as $question) {
                    // Garantir que options seja array
                    if (isset($question->options) && is_string($question->options)) {
                        $question->options = json_decode($question->options, true);
                    }
                    // Garantir que validation_rules seja array
                    if (isset($question->validation_rules) && is_string($question->validation_rules)) {
                        $question->validation_rules = json_decode($question->validation_rules, true);
                    }
                    // Garantir que metadata seja array
                    if (isset($question->metadata) && is_string($question->metadata)) {
                        $question->metadata = json_decode($question->metadata, true);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar resposta:', [
                'error' => $e->getMessage(),
                'response_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar resposta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ CORRIGIDO: Criar nova resposta
     */
    public function store(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'survey_id' => 'required|exists:surveys,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $surveyId = $request->survey_id;

            // ✅ Carregar survey com perguntas
            $survey = Survey::with(['questions' => function ($q) {
                $q->orderBy('order');
            }])
                ->available()
                ->findOrFail($surveyId);

            // ✅ Garantir que options das perguntas sejam array
            if ($survey->questions) {
                foreach ($survey->questions as $question) {
                    if (isset($question->options) && is_string($question->options)) {
                        $question->options = json_decode($question->options, true);
                    }
                }
            }

            // Verificar se já existe resposta EM PROGRESSO
            $existingResponse = SurveyResponse::where('survey_id', $surveyId)
                ->where('user_id', $userId)
                ->whereIn('status', ['in_progress', 'pending', 'started'])
                ->first();

            if ($existingResponse) {
                return response()->json([
                    'success' => true,
                    'message' => 'Resposta existente encontrada',
                    'data' => [
                        'response' => $existingResponse,
                        'survey' => $survey,
                        'redirect_url' => "/participant/survey/{$surveyId}/answer?response_id={$existingResponse->id}"
                    ]
                ]);
            }

            // Verificar se já completou
            $completedResponse = SurveyResponse::where('survey_id', $surveyId)
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->first();

            if ($completedResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você já respondeu esta pesquisa'
                ], 403);
            }

            // Informações do dispositivo
            $deviceInfo = $this->getDeviceInfo($request);

            // ✅ Criar nova resposta
            $response = SurveyResponse::create([
                'survey_id' => $surveyId,
                'user_id' => $userId,
                'status' => 'in_progress',
                'started_at' => now(),
                'device_type' => $deviceInfo['device_type'] ?? 'desktop',
                'browser' => $deviceInfo['browser'] ?? 'unknown',
                'ip_address' => $request->ip(),
                'is_paid' => false,
                'payment_amount' => $survey->reward,
            ]);

            Log::info('✅ Nova resposta criada', [
                'response_id' => $response->id,
                'user_id' => $userId,
                'survey_id' => $surveyId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resposta criada com sucesso',
                'data' => [
                    'response' => $response,
                    'survey' => $survey,
                    'redirect_url' => "/participant/survey/{$surveyId}/answer?response_id={$response->id}"
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao criar resposta:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'survey_id' => $request->survey_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar resposta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Atualizar progresso
     */
    public function updateProgress(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'answers' => 'nullable|array',
                'completion_time' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $response = SurveyResponse::find($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta não encontrada'
                ], 404);
            }

            if ($response->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta resposta não pertence ao seu usuário'
                ], 403);
            }

            // ✅ Aceitar múltiplos status para progresso
            $allowedStatuses = ['in_progress', 'pending', 'started'];
            if (!in_array($response->status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta não está em progresso. Status atual: ' . $response->status
                ], 400);
            }

            $updates = [];

            if ($request->has('answers')) {
                $updates['answers'] = $request->answers;
            }

            if ($request->has('completion_time')) {
                $updates['completion_time'] = $request->completion_time;
            }

            $updates['status'] = 'in_progress';
            $response->update($updates);

            return response()->json([
                'success' => true,
                'message' => 'Progresso salvo com sucesso',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao salvar progresso:', [
                'error' => $e->getMessage(),
                'response_id' => $id,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar progresso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Completar resposta
     */
    public function complete(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();
            if (!$userId) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $user = Auth::user();
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'completion_time' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $response = SurveyResponse::where('user_id', $userId)
                ->whereIn('status', ['in_progress', 'pending', 'started'])
                ->find($id);

            if (!$response) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta não encontrada ou não está em progresso'
                ], 404);
            }

            $survey = Survey::with('user')->findOrFail($response->survey_id);

            if (!$survey->isAvailable()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Esta pesquisa não está mais disponível'
                ], 400);
            }

            $response->update([
                'answers' => $request->answers,
                'status' => 'completed',
                'completed_at' => now(),
                'completion_time' => $request->completion_time,
            ]);

            $survey->increment('current_responses');

            if ($survey->current_responses >= $survey->target_responses) {
                $survey->update(['status' => 'completed']);
            }

            // Processar recompensa e criar transação
            $transaction = $this->processReward($userId, $survey->reward, $response->id, $survey->id);

            // ============ NOTIFICAR ESTUDANTE SOBRE NOVA RESPOSTA ============
            try {
                $notificationController = new NotificationController();

                // Notificar o ESTUDANTE (dono da pesquisa)
                $notificationController->sendToUser(
                    $survey->user_id,
                    'survey_response_received',
                    [
                        'participant_name' => $user->name,
                        'survey_title' => $survey->title,
                        'survey_id' => $survey->id
                    ]
                );

                Log::info('🔔 Notificação de nova resposta enviada para estudante', [
                    'survey_id' => $survey->id,
                    'student_id' => $survey->user_id,
                    'participant_id' => $userId
                ]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erro ao enviar notificação de resposta para estudante', [
                    'error' => $e->getMessage(),
                    'survey_id' => $survey->id
                ]);
            }

            // ============ NOTIFICAR PARTICIPANTE SOBRE RECOMPENSA ============
            try {
                $notificationController = new NotificationController();

                // Notificar o PARTICIPANTE que ganhou a recompensa
                $notificationController->sendToUser(
                    $userId,
                    'reward_received',
                    [
                        'amount' => $survey->reward,
                        'survey_title' => $survey->title,
                        'survey_id' => $survey->id
                    ]
                );

                Log::info('💰 Notificação de recompensa enviada para participante', [
                    'user_id' => $userId,
                    'amount' => $survey->reward,
                    'survey_id' => $survey->id
                ]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erro ao enviar notificação de recompensa para participante', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resposta completada com sucesso',
                'data' => [
                    'response' => $response,
                    'reward_earned' => $survey->reward,
                    'transaction' => $transaction
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Erro ao completar resposta:', [
                'error' => $e->getMessage(),
                'response_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao completar resposta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Cancelar resposta
     */
    public function cancel($id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $response = SurveyResponse::where('user_id', $userId)
                ->whereIn('status', ['in_progress', 'pending', 'started'])
                ->find($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resposta não encontrada ou não pode ser cancelada'
                ], 404);
            }

            $response->update([
                'status' => 'abandoned',
                'completed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Resposta cancelada com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao cancelar resposta:', [
                'error' => $e->getMessage(),
                'response_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar resposta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Processar recompensa e criar transação
     */
    private function processReward($userId, $amount, $responseId, $surveyId)
    {
        try {
            $response = SurveyResponse::find($responseId);
            if ($response) {
                $response->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                    'payment_amount' => $amount
                ]);
            }

            $user = User::find($userId);
            if ($user) {
                $user->increment('balance', $amount);
            }

            // Criar transação
            $transaction = Transaction::create([
                'user_id' => $userId,
                'survey_id' => $surveyId,
                'response_id' => $responseId,
                'type' => 'earning',
                'amount' => $amount,
                'status' => 'completed',
                'description' => 'Recompensa por responder pesquisa',
                'metadata' => json_encode([
                    'response_id' => $responseId,
                    'completed_at' => now()->toDateTimeString()
                ])
            ]);

            Log::info('💰 Recompensa processada', [
                'user_id' => $userId,
                'amount' => $amount,
                'response_id' => $responseId,
                'transaction_id' => $transaction->id
            ]);

            return $transaction;
        } catch (\Exception $e) {
            Log::error('❌ Erro ao processar recompensa:', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'amount' => $amount,
                'response_id' => $responseId
            ]);
            return null;
        }
    }

    /**
     * ✅ ADICIONAR ESTE MÉTODO
     * Listar todas as respostas (com filtros)
     */
    public function index(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $query = SurveyResponse::with(['user', 'survey']);

            // Filtrar por survey_id
            if ($request->has('survey_id')) {
                $query->where('survey_id', $request->survey_id);
            }

            // Filtrar por status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filtrar por usuário (admin pode ver todos, usuário normal só os seus)
            $user = Auth::user();
            $isAdmin = $user && $user->role === 'admin';

            if (!$isAdmin && !$request->has('user_id')) {
                $query->where('user_id', $userId);
            } elseif ($request->has('user_id') && $isAdmin) {
                $query->where('user_id', $request->user_id);
            }

            // Filtrar por pago/não pago
            if ($request->has('is_paid')) {
                $query->where('is_paid', $request->boolean('is_paid'));
            }

            // Filtrar por período
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $responses = $query->paginate($perPage);

            // ✅ Garantir que answers seja array
            foreach ($responses as $response) {
                if (is_string($response->answers)) {
                    $response->answers = json_decode($response->answers, true);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Respostas listadas com sucesso',
                'data' => $responses->items(),
                'meta' => [
                    'current_page' => $responses->currentPage(),
                    'last_page' => $responses->lastPage(),
                    'per_page' => $responses->perPage(),
                    'total' => $responses->total(),
                    'from' => $responses->firstItem(),
                    'to' => $responses->lastItem(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao listar respostas:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar respostas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Obter informações do dispositivo
     */
    private function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->header('User-Agent');
        $deviceType = 'desktop';
        $browser = 'unknown';

        if (strpos($userAgent, 'Mobile') !== false) {
            $deviceType = 'mobile';
        } elseif (strpos($userAgent, 'Tablet') !== false) {
            $deviceType = 'tablet';
        }

        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
        ];
    }
}
