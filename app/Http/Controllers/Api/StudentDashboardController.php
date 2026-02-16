<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentDashboardController extends Controller
{
    /**
     * Obter dados completos do dashboard do estudante
     */
    public function getDashboardData(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            Log::info('📊 Buscando dados do dashboard para estudante', ['user_id' => $user->id]);

            // Verificar se é estudante
            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            // Buscar estatísticas
            $stats = $this->getDashboardStatsInternal($user);

            // Pesquisas recentes do estudante
            $recentSurveys = $user->surveys()
                ->withCount('responses')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($survey) {
                    return [
                        'id' => $survey->id,
                        'title' => $survey->title,
                        'description' => $survey->description,
                        'category' => $survey->category,
                        'status' => $survey->status,
                        'target_responses' => $survey->target_responses,
                        'current_responses' => $survey->responses_count,
                        'reward_per_response' => $survey->reward_per_response,
                        'total_cost' => $survey->total_cost,
                        'created_at' => $survey->created_at->toISOString(),
                        'updated_at' => $survey->updated_at->toISOString(),
                    ];
                });

            // Pesquisas disponíveis para participação
            $availableSurveys = Survey::where('status', 'active')
                ->where('user_id', '!=', $user->id)
                ->with('user:id,name')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($survey) {
                    return [
                        'id' => $survey->id,
                        'title' => $survey->title,
                        'description' => $survey->description,
                        'category' => $survey->category,
                        'estimated_time' => $survey->estimated_time ?? 10,
                        'reward' => $survey->reward_per_response,
                        'researcher' => [
                            'id' => $survey->user->id,
                            'name' => $survey->user->name,
                            'institution' => 'Universidade'
                        ],
                        'created_at' => $survey->created_at->toISOString(),
                    ];
                });

            $dashboardData = [
                'stats' => $stats,
                'recent_surveys' => $recentSurveys,
                'available_surveys' => $availableSurveys,
                'recent_earnings' => [],
                'pending_withdrawals' => [],
                'notifications' => [],
                'last_updated' => now()->toISOString(),
            ];

            Log::info('✅ Dados do dashboard retornados', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Dados do dashboard carregados com sucesso',
                'data' => $dashboardData
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar dados do dashboard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Função interna para obter estatísticas
     */
    private function getDashboardStatsInternal(User $user)
    {
        // Carrega todas as pesquisas com contagem de respostas
        $surveys = $user->surveys()->withCount('responses')->get();

        // Cálculos básicos
        $totalResponses = $surveys->sum('responses_count');
        $totalSurveys = $surveys->count();
        $completedSurveys = $surveys->where('status', 'completed')->count();
        $activeSurveys = $surveys->where('status', 'active')->count();
        $totalSpent = (float) $surveys->sum('total_cost');
        $totalTargetResponses = $surveys->sum('target_responses');

        // Cálculo de porcentagens
        $completionRate = $totalSurveys > 0
            ? round(($completedSurveys / $totalSurveys) * 100, 1)
            : 0;

        $responseRate = $totalTargetResponses > 0
            ? round(($totalResponses / $totalTargetResponses) * 100, 1)
            : 0;

        return [
            'total_surveys_created' => $totalSurveys,
            'active_surveys' => $activeSurveys,
            'completed_surveys' => $completedSurveys,
            'total_responses' => $totalResponses,
            'total_spent' => $totalSpent,
            'total_earned' => (float) $user->balance,
            'average_completion_rate' => $completionRate,
            'response_rate' => $responseRate,
        ];
    }

    /**
     * Obter estatísticas do dashboard
     */
    public function getDashboardStats(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            $stats = $this->getDashboardStatsInternal($user);

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas carregadas com sucesso',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar estatísticas do dashboard', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter pesquisas do estudante
     */
    public function getStudentSurveys(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            $query = $user->surveys()->withCount('responses');

            // Filtrar por status se fornecido
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Paginação
            $perPage = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $surveys = $query->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            $formattedSurveys = $surveys->map(function ($survey) {
                return [
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'category' => $survey->category,
                    'status' => $survey->status,
                    'target_responses' => $survey->target_responses,
                    'current_responses' => $survey->responses_count,
                    'reward_per_response' => $survey->reward_per_response,
                    'total_cost' => $survey->total_cost,
                    'created_at' => $survey->created_at->toISOString(),
                    'updated_at' => $survey->updated_at->toISOString(),
                    'deadline' => $survey->deadline?->toISOString(),
                    'questions_count' => $survey->questions()->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pesquisas carregadas com sucesso',
                'data' => $formattedSurveys,
                'meta' => [
                    'current_page' => $surveys->currentPage(),
                    'total_pages' => $surveys->lastPage(),
                    'total_items' => $surveys->total(),
                    'per_page' => $surveys->perPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar pesquisas do estudante', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar pesquisas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter ganhos do estudante
     */
    public function getEarnings(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            // Retorna vazio por enquanto
            return response()->json([
                'success' => true,
                'message' => 'Ganhos carregados com sucesso',
                'data' => []
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar ganhos do estudante', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar ganhos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter saques pendentes
     */
    public function getWithdrawals(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            // Retorna vazio por enquanto
            return response()->json([
                'success' => true,
                'message' => 'Saques carregados com sucesso',
                'data' => []
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar saques do estudante', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar saques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Solicitar saque (versão simplificada)
     */
    public function requestWithdrawal(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:50|max:' . $user->balance,
                'payment_method' => 'required|in:mpesa,bank_transfer,cash',
                'account_details' => 'required_if:payment_method,mpesa,bank_transfer|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Verificar saldo suficiente
            if ($user->balance < $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente para realizar o saque'
                ], 400);
            }

            // Simular dedução do saldo (sem criar modelos extras)
            $user->update([
                'balance' => $user->balance - $validated['amount']
            ]);

            Log::info('💰 Solicitação de saque simulada', [
                'user_id' => $user->id,
                'amount' => $validated['amount']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitação de saque processada com sucesso',
                'data' => [
                    'amount' => (float) $validated['amount'],
                    'payment_method' => $validated['payment_method'],
                    'requested_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao solicitar saque', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao solicitar saque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter notificações
     */
    public function getNotifications(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            // Retorna vazio por enquanto
            return response()->json([
                'success' => true,
                'message' => 'Notificações carregadas com sucesso',
                'data' => []
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar notificações', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar notificações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar notificação como lida
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            // Simular sucesso
            return response()->json([
                'success' => true,
                'message' => 'Operação simulada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao marcar notificação como lida', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na operação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar todas as notificações
     */
    public function clearAllNotifications(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para estudantes'
                ], 403);
            }

            // Simular sucesso
            return response()->json([
                'success' => true,
                'message' => 'Operação simulada com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao limpar notificações', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro na operação',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
