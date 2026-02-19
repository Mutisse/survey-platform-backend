<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyCategory;
use App\Models\SurveyInstitution;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SurveyController extends Controller
{
    // ==============================================
    // MÉTODOS PÚBLICOS
    // ==============================================

    // Listar todas as pesquisas (com filtros) - PÚBLICO
    public function index(Request $request)
    {
        $query = Survey::with(['questions', 'user:id,name,email']);

        // Aplicar filtros
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_available')) {
            if ($request->boolean('is_available')) {
                $query->available();
            } else {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'active')
                        ->orWhereColumn('current_responses', '>=', 'target_responses');
                });
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('institution', 'like', "%{$search}%");
            });
        }

        if ($request->has('min_reward')) {
            $query->where('reward', '>=', $request->min_reward);
        }

        if ($request->has('max_reward')) {
            $query->where('reward', '<=', $request->max_reward);
        }

        // Ordenação
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginação
        $perPage = $request->get('per_page', 15);
        $surveys = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Pesquisas listadas com sucesso',
            'data' => $surveys->items(),
            'meta' => [
                'current_page' => $surveys->currentPage(),
                'last_page' => $surveys->lastPage(),
                'per_page' => $surveys->perPage(),
                'total' => $surveys->total(),
            ]
        ]);
    }

    // Listar pesquisas disponíveis para participantes - PROTEGIDO
    public function available(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = Survey::with(['questions'])
            ->available()
            ->where('user_id', '!=', $userId);

        // Aplicar filtros
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('min_reward')) {
            $query->where('reward', '>=', $request->min_reward);
        }

        if ($request->has('max_reward')) {
            $query->where('reward', '<=', $request->max_reward);
        }

        // Excluir pesquisas já respondidas
        $completedSurveyIds = SurveyResponse::where('user_id', $userId)
            ->where('status', 'completed')
            ->pluck('survey_id');

        if ($completedSurveyIds->isNotEmpty()) {
            $query->whereNotIn('id', $completedSurveyIds);
        }

        $surveys = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Pesquisas disponíveis listadas com sucesso',
            'data' => $surveys
        ]);
    }

    // Mostrar detalhes de uma pesquisa - PÚBLICO
    public function show($id)
    {
        $survey = Survey::with(['questions', 'user:id,name,email'])
            ->findOrFail($id);

        $userId = Auth::id();
        $hasResponded = false;

        if ($userId) {
            $hasResponded = SurveyResponse::where('survey_id', $id)
                ->where('user_id', $userId)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'message' => 'Detalhes da pesquisa',
            'data' => array_merge($survey->toArray(), [
                'has_responded' => $hasResponded,
                'is_available' => $survey->isAvailable(),
                'completion_rate' => $survey->getCompletionRate(),
            ])
        ]);
    }

    // ==============================================
    // MÉTODOS PROTEGIDOS (CRIADOR/ADMIN)
    // ==============================================
    public function store(Request $request)
    {
        // ✅ ADICIONE ESTE LOG PARA DEBUG (opcional, mas útil)
        error_log('🎯 Método store() chamado - Dados recebidos: ' . json_encode([
            'titulo' => $request->title,
            'total_perguntas' => count($request->questions ?? []),
        ]));

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'institution' => 'required|string|max:100',
            'duration' => 'required|integer|min:1|max:180',
            'reward' => 'required|numeric|min:1|max:1000',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:100',
            'target_responses' => 'required|integer|min:1|max:10000',
            'questions' => 'required|array|min:1',

            // ✅ ADICIONADO: Validação do campo 'title' da pergunta
            'questions.*.title' => 'required|string|max:255',
            'questions.*.question' => 'required|string|max:500',
            'questions.*.type' => 'required|in:text,paragraph,multiple_choice,checkboxes,dropdown,linear_scale,date,time',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*' => 'string|max:100',
            'questions.*.min_value' => 'nullable|integer|min:1',
            'questions.*.max_value' => 'nullable|integer|min:1|gt:questions.*.min_value',
            'questions.*.low_label' => 'nullable|string|max:50',
            'questions.*.high_label' => 'nullable|string|max:50',
            'questions.*.required' => 'boolean',
            'questions.*.order' => 'integer',
            'questions.*.image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $user = Auth::user();

            // Criar a pesquisa
            $survey = Survey::create([
                'user_id' => $userId,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'institution' => $request->institution,
                'duration' => $request->duration,
                'reward' => $request->reward,
                'requirements' => $request->requirements ?? [],
                'target_responses' => $request->target_responses,
                'status' => 'pending',
            ]);

            // ✅ CRIAR AS PERGUNTAS CORRETAMENTE (COM TITLE!)
            foreach ($request->questions as $index => $questionData) {
                // ✅ LOG DE DEBUG PARA CADA PERGUNTA (opcional)
                error_log("📝 Criando pergunta {$index}: " . ($questionData['title'] ?? 'SEM TÍTULO'));

                $survey->questions()->create([
                    // ✅ OBRIGATÓRIO: Campo 'title' para a tabela survey_questions
                    'title' => $questionData['title'] ?? $questionData['question'],

                    // Campo 'question' (texto completo da pergunta)
                    'question' => $questionData['question'],

                    // Campos básicos
                    'type' => $questionData['type'],
                    'options' => $questionData['options'] ?? null,
                    'min_value' => $questionData['min_value'] ?? null,
                    'max_value' => $questionData['max_value'] ?? null,
                    'low_label' => $questionData['low_label'] ?? null,
                    'high_label' => $questionData['high_label'] ?? null,
                    'required' => $questionData['required'] ?? false,
                    'order' => $questionData['order'] ?? $index,
                    'image_url' => $questionData['image_url'] ?? null,
                ]);
            }

            // Atualizar contadores
            $this->updateCategoryCount($request->category);
            $this->updateInstitutionCount($request->institution);

            DB::commit();

            // ============ NOTIFICAR ADMINS SOBRE NOVA PESQUISA ============
            try {
                $notificationController = new NotificationController();
                $admins = User::where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    $notificationController->sendToUser(
                        $admin->id,
                        'new_survey_from_student',
                        [
                            'student_name' => $user->name,
                            'survey_title' => $survey->title,
                            'survey_id' => $survey->id
                        ]
                    );
                }

                Log::info('🔔 Notificações enviadas para admins sobre nova pesquisa', [
                    'survey_id' => $survey->id,
                    'admins_count' => $admins->count()
                ]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erro ao enviar notificações para admins', [
                    'error' => $e->getMessage(),
                    'survey_id' => $survey->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa criada com sucesso',
                'data' => $survey->load('questions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            // ✅ LOG SIMPLES DO ERRO
            error_log('❌ ERRO ao criar pesquisa: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar pesquisa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Atualizar pesquisa - PROTEGIDO (apenas criador)
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $survey = Survey::where('user_id', $userId)->findOrFail($id);

        // Só pode atualizar se estiver em rascunho
        if ($survey->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Só é possível atualizar pesquisas em rascunho'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:100',
            'institution' => 'sometimes|string|max:100',
            'duration' => 'sometimes|integer|min:1|max:180',
            'reward' => 'sometimes|numeric|min:1|max:1000',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:100',
            'target_responses' => 'sometimes|integer|min:1|max:10000',
            'status' => 'sometimes|in:draft,active,paused,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $survey->update($request->only([
            'title',
            'description',
            'category',
            'institution',
            'duration',
            'reward',
            'requirements',
            'target_responses',
            'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Pesquisa atualizada com sucesso',
            'data' => $survey->load('questions')
        ]);
    }

    // Publicar pesquisa - PROTEGIDO
    public function publish($id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $survey = Survey::where('user_id', $userId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $survey->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Pesquisa publicada com sucesso',
            'data' => $survey
        ]);
    }

    // Duplicar pesquisa - PROTEGIDO
    public function duplicate($id)
    {
        try {
            $survey = Survey::findOrFail($id);
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar permissão - CORRIGIDO
            $user = Auth::user();
            // Verifique se sua coluna é 'role' ou 'user_type' e ajuste conforme necessário
            $isAdmin = isset($user->role) && $user->role === 'admin';

            if ($survey->user_id !== $userId && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para duplicar esta pesquisa.'
                ], 403);
            }

            // Criar cópia
            $newSurvey = $survey->replicate();
            $newSurvey->title = $survey->title . ' (Cópia)';
            $newSurvey->status = 'draft';
            $newSurvey->published_at = null;
            $newSurvey->responses_count = 0;
            $newSurvey->save();

            // Duplicar perguntas
            foreach ($survey->questions as $question) {
                $newQuestion = $question->replicate();
                $newQuestion->survey_id = $newSurvey->id;
                $newQuestion->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa duplicada com sucesso',
                'data' => $newSurvey->load('questions')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar pesquisa: ' . $e->getMessage()
            ], 500);
        }
    }

    // Arquivar pesquisa - PROTEGIDO
    public function archive($id)
    {
        try {
            $survey = Survey::findOrFail($id);
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar permissão - CORRIGIDO
            $user = Auth::user();
            $isAdmin = isset($user->role) && $user->role === 'admin';

            if ($survey->user_id !== $userId && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para arquivar esta pesquisa.'
                ], 403);
            }

            $survey->update(['status' => 'archived']);

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa arquivada com sucesso',
                'data' => $survey
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao arquivar pesquisa: ' . $e->getMessage()
            ], 500);
        }
    }

    // Excluir pesquisa - PROTEGIDO
    public function destroy($id)
    {
        try {
            $survey = Survey::findOrFail($id);
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar permissão - CORRIGIDO
            $user = Auth::user();
            $isAdmin = isset($user->role) && $user->role === 'admin';

            if ($survey->user_id !== $userId && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para excluir esta pesquisa.'
                ], 403);
            }

            // Verificar se há respostas (apenas para não-admin)
            if ($survey->responses_count > 0 && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir uma pesquisa com respostas.'
                ], 400);
            }

            $survey->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir pesquisa: ' . $e->getMessage()
            ], 500);
        }
    }

    // Estatísticas da pesquisa - PROTEGIDO
    public function stats($id)
    {
        try {
            $survey = Survey::withCount('responses')->findOrFail($id);
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar permissão - CORRIGIDO
            $user = Auth::user();
            $isAdmin = isset($user->role) && $user->role === 'admin';

            if ($survey->user_id !== $userId && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para ver estatísticas desta pesquisa.'
                ], 403);
            }

            $responses = SurveyResponse::where('survey_id', $id)->get();

            // Calcular taxa de conclusão
            $completionRate = $responses->count() > 0
                ? ($responses->where('status', 'completed')->count() / $responses->count()) * 100
                : 0;

            // Tempo médio de resposta
            $avgTime = $responses->whereNotNull('completion_time')->avg('completion_time') ?? 0;

            // Distribuição por dispositivo
            $deviceStats = $responses->groupBy('device_type')->map->count();

            // Estatísticas por localização
            $locationStats = $responses->whereNotNull('province')
                ->groupBy('province')
                ->map(function ($group) {
                    return $group->count();
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_responses' => $survey->responses_count,
                    'completion_rate' => round($completionRate, 2),
                    'average_time' => round($avgTime, 2),
                    'response_distribution' => $this->getResponseDistribution($survey),
                    'device_stats' => $deviceStats,
                    'location_stats' => $locationStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    // Exportar respostas - PROTEGIDO
    public function export($id, Request $request)
    {
        try {
            $survey = Survey::findOrFail($id);
            $userId = Auth::id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar permissão - CORRIGIDO
            $user = Auth::user();
            $isAdmin = isset($user->role) && $user->role === 'admin';

            if ($survey->user_id !== $userId && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para exportar respostas desta pesquisa.'
                ], 403);
            }

            $format = $request->query('format', 'csv');
            $filename = "survey_{$id}_responses_" . date('Y-m-d_H-i-s') . ".{$format}";
            $path = "exports/{$filename}";

            // Aqui você implementaria a lógica real de exportação
            // Por enquanto, retornamos uma URL simulada
            $url = url("storage/{$path}");

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $url,
                    'filename' => $filename,
                    'expires_at' => now()->addHours(24)->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar respostas: ' . $e->getMessage()
            ], 500);
        }
    }

    // Upload de imagem - PROTEGIDO
    public function uploadImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('image');
            $filename = 'survey_images/' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Salvar no storage
            Storage::disk('public')->put($filename, file_get_contents($file));

            $url = Storage::url($filename);

            return response()->json([
                'success' => true,
                'message' => 'Imagem enviada com sucesso',
                'data' => [
                    'url' => url($url)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar imagem: ' . $e->getMessage()
            ], 500);
        }
    }

    // No SurveyController, procure o método respond() e atualize:

    public function respond(Request $request, $id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $survey = Survey::available()->findOrFail($id);
        $user = Auth::user();

        // Verificar se já existe resposta
        $existingResponse = SurveyResponse::where('survey_id', $id)
            ->where('user_id', $userId)
            ->first();

        // Se já completou
        if ($existingResponse && $existingResponse->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Você já respondeu esta pesquisa'
            ], 403);
        }

        // Se já existe mas não completou
        if ($existingResponse) {
            $response = $existingResponse;
        } else {
            // Criar NOVA resposta
            $response = SurveyResponse::create([
                'survey_id' => $id,
                'user_id' => $userId,
                'status' => SurveyResponse::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'device_type' => $this->detectDeviceType($request),
                'browser' => $this->detectBrowser($request),
                'ip_address' => $request->ip(),
                'is_paid' => false,
                'payment_amount' => $survey->reward,
            ]);
        }

        // Se o request tem respostas, está finalizando
        if ($request->has('responses') && !empty($request->responses)) {
            // Validar respostas
            $validator = $this->validateResponses($request->responses, $survey);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação nas respostas',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Atualizar resposta
            $response->update([
                'answers' => $request->responses,
                'status' => SurveyResponse::STATUS_COMPLETED,
                'completed_at' => now(),
                'completion_time' => $response->started_at ? now()->diffInSeconds($response->started_at) : null,
            ]);

            // ============ NOTIFICAR ESTUDANTE SOBRE NOVA RESPOSTA ============
            try {
                $notificationController = new NotificationController();
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
                Log::warning('⚠️ Erro ao enviar notificação de resposta', [
                    'error' => $e->getMessage(),
                    'survey_id' => $survey->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resposta enviada com sucesso',
                'data' => [
                    'response' => $response,
                    'reward_earned' => $survey->reward,
                    'payment_pending' => true,
                ]
            ]);
        } else {
            // Apenas iniciando
            return response()->json([
                'success' => true,
                'message' => 'Pesquisa iniciada com sucesso',
                'data' => $response,
                'redirect_url' => "/survey/{$id}/answer?response_id={$response->id}",
            ]);
        }
    }

    // Adicione estes métodos auxiliares no SurveyController:
    private function detectDeviceType(Request $request): string
    {
        $userAgent = $request->header('User-Agent');
        if (strpos($userAgent, 'Mobile') !== false) return 'mobile';
        if (strpos($userAgent, 'Tablet') !== false) return 'tablet';
        return 'desktop';
    }

    private function detectBrowser(Request $request): string
    {
        $userAgent = $request->header('User-Agent');
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        return 'Unknown';
    }

    // Iniciar resposta a uma pesquisa - PROTEGIDO (versão simplificada)
    public function startResponse(Request $request, $id)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $survey = Survey::available()->findOrFail($id);

            // Verificar se já existe resposta
            $existingResponse = SurveyResponse::where('survey_id', $id)
                ->where('user_id', $userId)
                ->first();

            if ($existingResponse) {
                // Se já existe uma resposta COMPLETADA
                if ($existingResponse->status === 'completed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você já respondeu esta pesquisa',
                        'data' => [
                            'has_completed' => true,
                            'response_id' => $existingResponse->id,
                        ]
                    ], 403);
                }

                // Se existe uma resposta em progresso
                return response()->json([
                    'success' => true,
                    'message' => 'Você já iniciou esta pesquisa',
                    'data' => [
                        'response' => $existingResponse,
                        'survey' => $survey,
                        'redirect_url' => "/app/survey/{$id}/answer?response_id={$existingResponse->id}",
                    ]
                ]);
            }

            // Criar nova resposta
            $response = SurveyResponse::create([
                'survey_id' => $id,
                'user_id' => $userId,
                'status' => 'in_progress',
                'started_at' => now(),
                'response_code' => 'RESP_' . strtoupper(Str::random(8)),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa iniciada com sucesso',
                'data' => [
                    'response' => $response,
                    'survey' => $survey,
                    'redirect_url' => "/app/survey/{$id}/answer?response_id={$response->id}",
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar pesquisa: ' . $e->getMessage()
            ], 500);
        }
    }

    // Minhas pesquisas - PROTEGIDO
    public function mySurveys(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = Survey::with(['questions'])
            ->where('user_id', $userId);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $surveys = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Minhas pesquisas',
            'data' => $surveys
        ]);
    }

    // Minhas respostas - PROTEGIDO
    public function myResponses(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        $query = SurveyResponse::with(['survey'])
            ->where('user_id', $userId)
            ->where('status', 'completed');

        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        $responses = $query->orderBy('completed_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Minhas respostas',
            'data' => $responses
        ]);
    }

    // ==============================================
    // MÉTODOS DE ADMINISTRAÇÃO
    // ==============================================

    // Listar todas as pesquisas para admin
    public function adminIndex(Request $request)
    {
        $query = Survey::with(['user:id,name,email', 'questions']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('institution', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $surveys = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Pesquisas listadas para administração',
            'data' => $surveys
        ]);
    }

    // Atualizar status da pesquisa (admin)
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,active,paused,completed,archived,rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $survey = Survey::findOrFail($id);
        $oldStatus = $survey->status;

        $survey->update(['status' => $request->status]);

        // ============ NOTIFICAÇÕES QUANDO STATUS MUDA ============
        try {
            $notificationController = new NotificationController();

            if ($request->status === 'approved' && $oldStatus !== 'approved') {
                // Notificar estudante que pesquisa foi aprovada
                $notificationController->sendToUser(
                    $survey->user_id,
                    'survey_approved_for_payment',
                    [
                        'survey_title' => $survey->title,
                        'survey_id' => $survey->id
                    ]
                );

                Log::info('💰 Notificação de pesquisa aprovada enviada', [
                    'survey_id' => $survey->id,
                    'student_id' => $survey->user_id
                ]);
            } elseif ($request->status === 'rejected' && $oldStatus !== 'rejected') {
                // Notificar estudante que pesquisa foi rejeitada
                $notificationController->sendToUser(
                    $survey->user_id,
                    'survey_rejected',
                    [
                        'survey_title' => $survey->title,
                        'survey_id' => $survey->id
                    ]
                );

                Log::info('❌ Notificação de pesquisa rejeitada enviada', [
                    'survey_id' => $survey->id,
                    'student_id' => $survey->user_id
                ]);
            } elseif ($request->status === 'active' && $oldStatus !== 'active') {
                // Notificar todos os participantes sobre nova pesquisa
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
                            'survey_id' => $survey->id
                        ]
                    );
                }

                Log::info('📊 Notificações de nova pesquisa enviadas para participantes', [
                    'survey_id' => $survey->id,
                    'participants_count' => $participants->count()
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ Erro ao enviar notificações de pesquisa', [
                'error' => $e->getMessage(),
                'survey_id' => $survey->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status da pesquisa atualizado',
            'data' => $survey
        ]);
    }

    // Obter respostas de uma pesquisa (admin)
    public function getSurveyResponses($id)
    {
        $survey = Survey::with(['responses.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Respostas da pesquisa',
            'data' => [
                'survey' => $survey,
                'responses' => $survey->responses()->paginate(20)
            ]
        ]);
    }

    // Análises da pesquisa (admin)
    public function getSurveyAnalytics($id)
    {
        $survey = Survey::withCount('responses')->findOrFail($id);
        $responses = SurveyResponse::where('survey_id', $id)->get();

        $completionRate = $responses->count() > 0
            ? ($responses->where('status', 'completed')->count() / $responses->count()) * 100
            : 0;

        $analytics = [
            'total_responses' => $survey->responses_count,
            'completion_rate' => round($completionRate, 2),
            'unique_participants' => $responses->unique('user_id')->count(),
            'response_rate' => $survey->target_responses > 0
                ? ($survey->responses_count / $survey->target_responses) * 100
                : 0,
            'total_rewards_paid' => $responses->where('is_paid', true)->sum('payment_amount'),
            'rewards_pending' => $responses->where('status', 'completed')->where('is_paid', false)->sum('payment_amount'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Análises da pesquisa',
            'data' => $analytics
        ]);
    }

    // ==============================================
    // MÉTODOS PÚBLICOS COMPLEMENTARES
    // ==============================================

    // Estatísticas gerais do sistema - PÚBLICO
    public function globalStats()
    {
        $stats = [
            'total_surveys' => Survey::count(),
            'active_surveys' => Survey::active()->count(),
            'total_responses' => SurveyResponse::where('status', 'completed')->count(),
            'total_rewards_paid' => SurveyResponse::where('is_paid', true)->sum('payment_amount'),
            'average_completion_time' => SurveyResponse::where('status', 'completed')
                ->whereNotNull('completion_time')
                ->avg('completion_time'),
            'total_participants' => SurveyResponse::distinct('user_id')->count('user_id'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Estatísticas do sistema',
            'data' => $stats
        ]);
    }

    // Listar categorias - PÚBLICO
    public function categories()
    {
        $categories = SurveyCategory::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categorias listadas',
            'data' => $categories
        ]);
    }

    // Listar instituições - PÚBLICO
    public function institutions()
    {
        $institutions = SurveyInstitution::where('is_verified', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Instituições listadas',
            'data' => $institutions
        ]);
    }

    // ==============================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ==============================================

    private function validateResponses($responses, $survey)
    {
        $rules = [];
        $messages = [];

        foreach ($survey->questions as $question) {
            $fieldName = "question_{$question->id}";
            $rules[$fieldName] = $question->getValidationRules();

            if (in_array('required', $rules[$fieldName])) {
                $messages["{$fieldName}.required"] = "A pergunta '{$question->question}' é obrigatória";
            }
        }

        $dataToValidate = [];
        foreach ($responses as $questionId => $answer) {
            $dataToValidate["question_{$questionId}"] = $answer;
        }

        return Validator::make($dataToValidate, $rules, $messages);
    }

    private function updateCategoryCount($categoryName)
    {
        $category = SurveyCategory::firstOrCreate(
            ['name' => $categoryName],
            ['slug' => Str::slug($categoryName)]
        );
        $category->incrementSurveyCount();
    }

    private function updateInstitutionCount($institutionName)
    {
        $institution = SurveyInstitution::firstOrCreate(
            ['name' => $institutionName],
            [
                'abbreviation' => strtoupper(substr($institutionName, 0, 3)),
                'type' => 'other'
            ]
        );
        $institution->incrementSurveyCount();
    }

    private function getResponseDistribution(Survey $survey)
    {
        $distribution = [];

        foreach ($survey->questions as $question) {
            if (in_array($question->type, ['multiple_choice', 'checkboxes', 'dropdown'])) {
                $options = $question->options ?? [];
                $counts = array_fill_keys($options, 0);

                foreach ($survey->responses as $response) {
                    $answers = json_decode($response->answers, true);
                    if (isset($answers[$question->id])) {
                        $answer = $answers[$question->id];
                        if (is_array($answer)) {
                            foreach ($answer as $item) {
                                if (isset($counts[$item])) {
                                    $counts[$item]++;
                                }
                            }
                        } else {
                            if (isset($counts[$answer])) {
                                $counts[$answer]++;
                            }
                        }
                    }
                }

                $distribution[$question->id] = $counts;
            }
        }

        return $distribution;
    }
}
