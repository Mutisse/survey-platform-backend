<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ParticipantDashboardController extends Controller
{
    /**
     * Obter dados completos do dashboard do participante
     */
    public function getDashboardData(Request $request)
    {
        try {
            Log::info('[Dashboard] Request received', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            // Estatísticas do participante
            $stats = $this->getParticipantStats($user);

            // Pesquisas disponíveis para o participante
            $availableSurveys = $this->getAvailableSurveys($user);

            // Transações recentes
            $recentTransactions = $this->getRecentTransactions($user);

            // Ranking dos participantes
            $rankings = $this->getParticipantRankings($user);

            // Notificações (COM CORREÇÃO)
            $notifications = $this->getNotifications($user);

            $responseData = [
                'success' => true,
                'message' => 'Dashboard do participante carregado com sucesso',
                'data' => [
                    'stats' => $stats,
                    'available_surveys' => $availableSurveys,
                    'recent_transactions' => $recentTransactions,
                    'rankings' => $rankings,
                    'notifications' => $notifications,
                    'last_updated' => now()->toDateTimeString(),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'balance' => (float) $user->balance,
                        'verification_status' => $user->verification_status,
                        'created_at' => $user->created_at->toDateTimeString(),
                    ]
                ]
            ];

            Log::info('[Dashboard] Response prepared', [
                'user_id' => $user->id,
                'stats_count' => count($stats),
                'surveys_count' => count($availableSurveys),
                'transactions_count' => count($recentTransactions),
                'notifications_count' => count($notifications)
            ]);

            // CORREÇÃO: Sempre retornar JSON válido
            return response()->json($responseData, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            Log::error('[Dashboard] Error loading dashboard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // CORREÇÃO: Retornar JSON mesmo no erro
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
                'timestamp' => now()->toDateTimeString(),
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obter estatísticas do participante
     */
    private function getParticipantStats($user)
    {
        try {
            // Saldo atual do participante
            $currentBalance = $user->balance ?? 0;

            // Total ganho (de todas as transações de ganhos)
            $totalEarned = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->sum('amount');

            $totalEarned = $totalEarned ?? 0;

            // Total sacado
            $totalWithdrawn = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount');

            $totalWithdrawn = $totalWithdrawn ?? 0;

            // Pesquisas respondidas
            $completedSurveys = SurveyResponse::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();

            // Total de pesquisas respondidas (incluindo pendentes)
            $totalSurveysResponded = SurveyResponse::where('user_id', $user->id)->count();

            // Ranking (baseado no total ganho)
            $ranking = 1;
            try {
                $rankingQuery = User::where('role', 'participant')
                    ->where('verification_status', 'approved')
                    ->select('users.*')
                    ->selectSub(function ($query) {
                        $query->from('transactions')
                            ->selectRaw('COALESCE(SUM(amount), 0)')
                            ->whereColumn('user_id', 'users.id')
                            ->where('type', 'survey_earnings')
                            ->where('status', 'completed');
                    }, 'total_earned')
                    ->orderByDesc('total_earned')
                    ->get();

                foreach ($rankingQuery as $index => $rankedUser) {
                    if ($rankedUser->id == $user->id) {
                        $ranking = $index + 1;
                        break;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[Dashboard] Error calculating ranking', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                $ranking = 0;
            }

            // Ganhos mensais (últimos 6 meses)
            $monthlyEarnings = [];
            try {
                $monthlyEarnings = Transaction::where('user_id', $user->id)
                    ->where('type', 'survey_earnings')
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                        DB::raw('SUM(amount) as amount')
                    )
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'month' => $item->month,
                            'amount' => (float) ($item->amount ?? 0),
                        ];
                    })->toArray();
            } catch (\Exception $e) {
                Log::warning('[Dashboard] Error getting monthly earnings', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Pesquisas disponíveis
            $availableSurveysCount = 0;
            try {
                $availableSurveysCount = Survey::where('status', 'approved')
                    ->where('target_responses', '>', function ($query) {
                        $query->from('survey_responses')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('survey_id', 'surveys.id')
                            ->where('status', 'completed');
                    })
                    ->count();
            } catch (\Exception $e) {
                Log::warning('[Dashboard] Error counting available surveys', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return [
                'current_balance' => (float) $currentBalance,
                'total_earned' => (float) $totalEarned,
                'ranking' => (int) $ranking,
                'completed_surveys' => (int) $completedSurveys,
                'total_surveys_responded' => (int) $totalSurveysResponded,
                'average_rating' => 4.5, // Placeholder - você pode implementar avaliações
                'total_withdrawn' => (float) $totalWithdrawn,
                'monthly_earnings' => $monthlyEarnings,
                'available_surveys_count' => (int) $availableSurveysCount,
            ];

        } catch (\Exception $e) {
            Log::error('[Dashboard] Error getting participant stats', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            // Retorna estatísticas padrão em caso de erro
            return [
                'current_balance' => 0.00,
                'total_earned' => 0.00,
                'ranking' => 0,
                'completed_surveys' => 0,
                'total_surveys_responded' => 0,
                'average_rating' => 0.0,
                'total_withdrawn' => 0.00,
                'monthly_earnings' => [],
                'available_surveys_count' => 0,
            ];
        }
    }

    /**
     * Obter pesquisas disponíveis para o participante
     */
    private function getAvailableSurveys($user)
    {
        try {
            // Obter IDs das pesquisas que o participante já respondeu
            $answeredSurveyIds = SurveyResponse::where('user_id', $user->id)
                ->pluck('survey_id')
                ->toArray();

            // Pesquisas disponíveis (não respondidas, aprovadas, e dentro do prazo)
            $surveys = Survey::where('status', 'approved')
                ->whereNotIn('id', $answeredSurveyIds)
                ->where(function ($query) {
                    $query->whereNull('deadline')
                        ->orWhere('deadline', '>=', now());
                })
                ->where('target_responses', '>', function ($query) {
                    $query->from('survey_responses')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('survey_id', 'surveys.id')
                        ->where('status', 'completed');
                })
                ->with(['user' => function ($query) {
                    $query->select('id', 'name', 'university as institution');
                }])
                ->select([
                    'id',
                    'title',
                    'description',
                    'category',
                    'estimated_time',
                    'reward_per_response as reward',
                    'target_responses',
                    'user_id',
                    'created_at',
                    'deadline',
                ])
                ->get();

            return $surveys->map(function ($survey) {
                // Contar respostas atuais
                $currentResponses = SurveyResponse::where('survey_id', $survey->id)
                    ->where('status', 'completed')
                    ->count();

                // Garantir valores padrão
                $estimatedTime = $survey->estimated_time ?? 0;
                $reward = $survey->reward ?? 0;
                $targetResponses = $survey->target_responses ?? 0;

                return [
                    'id' => (string) $survey->id,
                    'title' => $survey->title ?? 'Título não disponível',
                    'description' => $survey->description ?? 'Descrição não disponível',
                    'category' => $survey->category ?? 'Geral',
                    'estimated_time' => (int) $estimatedTime,
                    'reward' => (float) $reward,
                    'target_responses' => (int) $targetResponses,
                    'current_responses' => (int) $currentResponses,
                    'researcher' => [
                        'id' => (string) ($survey->user->id ?? ''),
                        'name' => $survey->user->name ?? 'Pesquisador',
                        'institution' => $survey->user->institution ?? 'Instituição não informada',
                    ],
                    'created_at' => $survey->created_at ? $survey->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'deadline' => $survey->deadline ? $survey->deadline->toDateTimeString() : null,
                    'questions_count' => $survey->questions()->count(),
                    'progress_percentage' => $targetResponses > 0
                        ? round(($currentResponses / $targetResponses) * 100, 2)
                        : 0,
                    'days_remaining' => $survey->deadline ?
                        max(0, now()->diffInDays($survey->deadline, false)) : null,
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('[Dashboard] Error getting available surveys', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Obter transações recentes do participante
     */
    private function getRecentTransactions($user)
    {
        try {
            $transactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return $transactions->map(function ($transaction) {
                $surveyTitle = null;
                if ($transaction->survey_id) {
                    $survey = Survey::find($transaction->survey_id);
                    $surveyTitle = $survey ? $survey->title : null;
                }

                // Formatando o tipo para português
                $typeLabels = [
                    'survey_earnings' => 'Ganho de Pesquisa',
                    'withdrawal' => 'Saque',
                    'deposit' => 'Depósito',
                    'bonus' => 'Bônus',
                    'refund' => 'Reembolso'
                ];

                $statusLabels = [
                    'pending' => 'Pendente',
                    'completed' => 'Concluído',
                    'failed' => 'Falhou',
                    'cancelled' => 'Cancelado'
                ];

                return [
                    'id' => (string) $transaction->id,
                    'type' => $transaction->type,
                    'type_label' => $typeLabels[$transaction->type] ?? $transaction->type,
                    'amount' => (float) ($transaction->amount ?? 0),
                    'status' => $transaction->status,
                    'status_label' => $statusLabels[$transaction->status] ?? $transaction->status,
                    'description' => $transaction->description ?? 'Transação',
                    'survey_title' => $surveyTitle,
                    'survey_id' => $transaction->survey_id ? (string) $transaction->survey_id : null,
                    'payment_method' => $transaction->payment_method,
                    'account_details' => $transaction->account_details,
                    'created_at' => $transaction->created_at ? $transaction->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'completed_at' => $transaction->completed_at ? $transaction->completed_at->toDateTimeString() : null,
                    'is_positive' => in_array($transaction->type, ['survey_earnings', 'deposit', 'bonus', 'refund']),
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('[Dashboard] Error getting recent transactions', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Obter ranking dos participantes
     */
    private function getParticipantRankings($currentUser)
    {
        try {
            $rankings = User::where('role', 'participant')
                ->where('verification_status', 'approved')
                ->where('id', '!=', $currentUser->id) // Excluir o usuário atual da lista geral
                ->select('users.*')
                ->selectSub(function ($query) {
                    $query->from('transactions')
                        ->selectRaw('COALESCE(SUM(amount), 0)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('type', 'survey_earnings')
                        ->where('status', 'completed');
                }, 'total_earned')
                ->selectSub(function ($query) {
                    $query->from('survey_responses')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'completed');
                }, 'completed_surveys')
                ->orderByDesc('total_earned')
                ->limit(10) // Top 10
                ->get();

            $rankingsData = $rankings->map(function ($user, $index) {
                return [
                    'user_id' => (string) $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'position' => $index + 1,
                    'total_earned' => (float) ($user->total_earned ?? 0),
                    'completed_surveys' => (int) ($user->completed_surveys ?? 0),
                    'average_rating' => 4.5, // Placeholder
                    'avatar_color' => $this->generateAvatarColor($user->id),
                ];
            })->toArray();

            // Adicionar o usuário atual no ranking se não estiver no top 10
            $currentUserPosition = 0;
            $currentUserTotalEarned = Transaction::where('user_id', $currentUser->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->sum('amount') ?? 0;

            $currentUserCompletedSurveys = SurveyResponse::where('user_id', $currentUser->id)
                ->where('status', 'completed')
                ->count();

            // Calcular posição real
            $allUsersCount = User::where('role', 'participant')
                ->where('verification_status', 'approved')
                ->whereHas('transactions', function ($query) {
                    $query->where('type', 'survey_earnings')
                        ->where('status', 'completed');
                }, '>', 0)
                ->count();

            $currentUserPosition = $allUsersCount > 0 ? $allUsersCount : 1;

            $currentUserData = [
                'user_id' => (string) $currentUser->id,
                'user_name' => $currentUser->name,
                'user_email' => $currentUser->email,
                'position' => $currentUserPosition,
                'total_earned' => (float) $currentUserTotalEarned,
                'completed_surveys' => (int) $currentUserCompletedSurveys,
                'average_rating' => 4.5,
                'avatar_color' => $this->generateAvatarColor($currentUser->id),
                'is_current_user' => true,
            ];

            // Adicionar no início do array
            array_unshift($rankingsData, $currentUserData);

            return $rankingsData;

        } catch (\Exception $e) {
            Log::error('[Dashboard] Error getting rankings', [
                'user_id' => $currentUser->id,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Obter notificações do participante - CORRIGIDO
     */
    private function getNotifications($user)
    {
        try {
            $notifications = Notification::where('user_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    $query->whereNull('user_id')
                        ->where('role', 'participant');
                })
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return $notifications->map(function ($notification) {
                // CORREÇÃO: Tratar corretamente o campo data
                $data = $notification->data;

                // Se data é string, tentar decodificar JSON
                if (is_string($data) && !empty($data)) {
                    $decoded = json_decode($data, true);
                    $data = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $data;
                }
                // Se data já é array ou objeto, usar como está
                // Se for null, manter null

                // Determinar ícone baseado no tipo
                $icon = 'info';
                $iconColors = [
                    'survey_started' => ['icon' => 'assignment', 'color' => 'primary'],
                    'survey_completed' => ['icon' => 'check_circle', 'color' => 'success'],
                    'withdrawal_requested' => ['icon' => 'account_balance_wallet', 'color' => 'warning'],
                    'withdrawal_approved' => ['icon' => 'attach_money', 'color' => 'success'],
                    'withdrawal_rejected' => ['icon' => 'cancel', 'color' => 'negative'],
                    'bonus_received' => ['icon' => 'card_giftcard', 'color' => 'positive'],
                    'system' => ['icon' => 'notifications', 'color' => 'info'],
                    'warning' => ['icon' => 'warning', 'color' => 'warning'],
                    'success' => ['icon' => 'check_circle', 'color' => 'positive'],
                ];

                $notificationType = $notification->type ?? 'system';
                $iconConfig = $iconColors[$notificationType] ?? $iconColors['system'];

                return [
                    'id' => $notification->id,
                    'type' => $notificationType,
                    'title' => $notification->title ?? 'Notificação',
                    'message' => $notification->message ?? '',
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => $notification->created_at ? $notification->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'read_at' => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
                    'data' => $data,
                    'icon' => $iconConfig['icon'],
                    'color' => $iconConfig['color'],
                    'time_ago' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Agora',
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('[Dashboard] Error getting notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Gerar cor de avatar baseada no ID do usuário
     */
    private function generateAvatarColor($userId)
    {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
        ];

        $index = $userId % count($colors);
        return $colors[$index];
    }

    /**
     * Solicitar saque
     */
    public function requestWithdrawal(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:50|max:100000',
                'payment_method' => 'required|in:mpesa,bank_transfer,cash',
                'account_details' => 'required_if:payment_method,mpesa,bank_transfer|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $amount = $request->amount;

            if ($user->balance < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente para saque. Saldo disponível: ' . number_format($user->balance, 2) . ' MZN',
                    'current_balance' => (float) $user->balance,
                    'requested_amount' => (float) $amount,
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            // Verificar se há saques pendentes
            $pendingWithdrawals = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->count();

            if ($pendingWithdrawals > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você já tem um saque pendente. Aguarde a aprovação antes de solicitar outro.',
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            DB::beginTransaction();

            try {
                // Criar transação de saque
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $amount,
                    'status' => 'pending',
                    'description' => 'Solicitação de saque via ' . $request->payment_method,
                    'payment_method' => $request->payment_method,
                    'account_details' => $request->account_details,
                    'notes' => $request->notes,
                    'requested_at' => now(),
                ]);

                // Atualizar saldo do usuário (bloquear o valor)
                $user->balance -= $amount;
                $user->save();

                // Criar notificação
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal_requested',
                    'title' => 'Solicitação de Saque',
                    'message' => 'Sua solicitação de saque de ' . number_format($amount, 2) . ' MZN foi recebida e está em análise.',
                    'data' => json_encode([
                        'transaction_id' => $transaction->id,
                        'amount' => $amount,
                        'payment_method' => $request->payment_method,
                        'account_details' => $request->account_details,
                    ]),
                ]);

                DB::commit();

                Log::info('[Withdrawal] Request successful', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'new_balance' => $user->balance,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Saque solicitado com sucesso! O valor será processado em até 48 horas.',
                    'data' => [
                        'id' => (string) $transaction->id,
                        'type' => $transaction->type,
                        'amount' => (float) $transaction->amount,
                        'status' => $transaction->status,
                        'description' => $transaction->description,
                        'payment_method' => $transaction->payment_method,
                        'account_details' => $transaction->account_details,
                        'created_at' => $transaction->created_at->toDateTimeString(),
                        'new_balance' => (float) $user->balance,
                    ]
                ], 200, [], JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('[Withdrawal] Request error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao solicitar saque',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obter histórico de transações com paginação
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $perPage = $request->get('limit', 15);
            $page = $request->get('page', 1);
            $type = $request->get('type', 'all');
            $status = $request->get('status', 'all');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Transaction::where('user_id', $user->id);

            if ($type !== 'all') {
                $query->where('type', $type);
            }

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $transactionsData = $transactions->map(function ($transaction) {
                $surveyTitle = null;
                if ($transaction->survey_id) {
                    $survey = Survey::find($transaction->survey_id);
                    $surveyTitle = $survey ? $survey->title : null;
                }

                return [
                    'id' => (string) $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) ($transaction->amount ?? 0),
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'survey_title' => $surveyTitle,
                    'survey_id' => $transaction->survey_id ? (string) $transaction->survey_id : null,
                    'payment_method' => $transaction->payment_method,
                    'account_details' => $transaction->account_details,
                    'created_at' => $transaction->created_at ? $transaction->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'completed_at' => $transaction->completed_at ? $transaction->completed_at->toDateTimeString() : null,
                    'is_positive' => in_array($transaction->type, ['survey_earnings', 'deposit', 'bonus', 'refund']),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Transações carregadas com sucesso',
                'data' => $transactionsData,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'total_pages' => $transactions->lastPage(),
                    'total_items' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'has_more' => $transactions->hasMorePages(),
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Transactions] Get transactions error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar transações',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obter ranking detalhado
     */
    public function getRankings(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $limit = $request->get('limit', 20);
            $page = $request->get('page', 1);

            $rankings = User::where('role', 'participant')
                ->where('verification_status', 'approved')
                ->select('users.*')
                ->selectSub(function ($query) {
                    $query->from('transactions')
                        ->selectRaw('COALESCE(SUM(amount), 0)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('type', 'survey_earnings')
                        ->where('status', 'completed');
                }, 'total_earned')
                ->selectSub(function ($query) {
                    $query->from('survey_responses')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'completed');
                }, 'completed_surveys')
                ->orderByDesc('total_earned')
                ->paginate($limit, ['*'], 'page', $page);

            $rankingsData = $rankings->map(function ($user, $index) use ($rankings) {
                // Calcular posição considerando paginação
                $position = (($rankings->currentPage() - 1) * $rankings->perPage()) + $index + 1;

                return [
                    'user_id' => (string) $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'position' => $position,
                    'total_earned' => (float) ($user->total_earned ?? 0),
                    'completed_surveys' => (int) ($user->completed_surveys ?? 0),
                    'average_rating' => 4.5,
                    'avatar_color' => $this->generateAvatarColor($user->id),
                    'member_since' => $user->created_at ? $user->created_at->diffForHumans() : 'Recente',
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Ranking carregado com sucesso',
                'data' => $rankingsData,
                'meta' => [
                    'current_page' => $rankings->currentPage(),
                    'total_pages' => $rankings->lastPage(),
                    'total_items' => $rankings->total(),
                    'per_page' => $rankings->perPage(),
                    'has_more' => $rankings->hasMorePages(),
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Rankings] Get rankings error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar ranking',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Marcar notificação como lida
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $notification = Notification::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere(function ($q) use ($user) {
                            $q->whereNull('user_id')
                                ->where('role', 'participant');
                        });
                })
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada',
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }

            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            Log::info('[Notifications] Marked as read', [
                'user_id' => $user->id,
                'notification_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
                'data' => [
                    'id' => $notification->id,
                    'is_read' => true,
                    'read_at' => $notification->read_at->toDateTimeString(),
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Notifications] Mark as read error', [
                'user_id' => Auth::id(),
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificação como lida',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $updated = Notification::where('user_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    $query->whereNull('user_id')
                        ->where('role', 'participant');
                })
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            Log::info('[Notifications] All marked as read', [
                'user_id' => $user->id,
                'updated_count' => $updated,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Todas as notificações foram marcadas como lidas',
                'data' => [
                    'updated_count' => $updated,
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Notifications] Mark all as read error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificações como lidas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Participar de uma Pesquisa
     */
    public function respondToSurvey(Request $request, $surveyId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $survey = Survey::where('id', $surveyId)
                ->where('status', 'approved')
                ->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesquisa não encontrada ou não disponível',
                ], 404, [], JSON_UNESCAPED_UNICODE);
            }

            // Verificar se já respondeu
            $existingResponse = SurveyResponse::where('user_id', $user->id)
                ->where('survey_id', $surveyId)
                ->first();

            if ($existingResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você já respondeu a esta pesquisa',
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            // Verificar se pesquisa ainda tem vagas
            $currentResponses = SurveyResponse::where('survey_id', $surveyId)
                ->where('status', 'completed')
                ->count();

            if ($currentResponses >= $survey->target_responses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta pesquisa já atingiu o número máximo de respostas',
                ], 400, [], JSON_UNESCAPED_UNICODE);
            }

            DB::beginTransaction();

            try {
                // Criar resposta
                $response = SurveyResponse::create([
                    'user_id' => $user->id,
                    'survey_id' => $surveyId,
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);

                // Criar transação pendente
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'survey_id' => $surveyId,
                    'type' => 'survey_earnings',
                    'amount' => $survey->reward_per_response,
                    'status' => 'pending',
                    'description' => 'Participação na pesquisa: ' . $survey->title,
                ]);

                // Criar notificação
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'survey_started',
                    'title' => 'Pesquisa Iniciada',
                    'message' => 'Você iniciou a pesquisa "' . $survey->title . '". Complete para ganhar ' . number_format($survey->reward_per_response, 2) . ' MZN.',
                    'data' => json_encode([
                        'survey_id' => $survey->id,
                        'survey_title' => $survey->title,
                        'transaction_id' => $transaction->id,
                        'reward' => $survey->reward_per_response,
                        'response_id' => $response->id,
                    ]),
                ]);

                DB::commit();

                Log::info('[Survey] Started successfully', [
                    'user_id' => $user->id,
                    'survey_id' => $surveyId,
                    'response_id' => $response->id,
                    'transaction_id' => $transaction->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pesquisa iniciada com sucesso!',
                    'data' => [
                        'survey_response_id' => (string) $response->id,
                        'transaction_id' => (string) $transaction->id,
                        'survey' => [
                            'id' => (string) $survey->id,
                            'title' => $survey->title,
                            'description' => $survey->description,
                            'reward' => (float) $survey->reward_per_response,
                            'estimated_time' => $survey->estimated_time,
                            'questions_count' => $survey->questions()->count(),
                        ],
                        'started_at' => $response->started_at->toDateTimeString(),
                        'deadline' => $survey->deadline ? $survey->deadline->toDateTimeString() : null,
                    ]
                ], 200, [], JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('[Survey] Respond error', [
                'user_id' => Auth::id(),
                'survey_id' => $surveyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar pesquisa',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obter notificações (método público) - CORRIGIDO
     */
    public function getNotificationsList(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $perPage = $request->get('limit', 15);
            $page = $request->get('page', 1);
            $unreadOnly = $request->get('unread_only', false);

            $query = Notification::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->whereNull('user_id')
                            ->where('role', 'participant');
                    });
            });

            if ($unreadOnly) {
                $query->where('is_read', false);
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $notificationsData = $notifications->map(function ($notification) {
                // CORREÇÃO: Tratar corretamente o campo data
                $data = $notification->data;

                if (is_string($data) && !empty($data)) {
                    $decoded = json_decode($data, true);
                    $data = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $data;
                }

                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'is_read' => (bool) $notification->is_read,
                    'created_at' => $notification->created_at->toDateTimeString(),
                    'read_at' => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
                    'data' => $data,
                    'time_ago' => $notification->created_at->diffForHumans(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Notificações carregadas com sucesso',
                'data' => $notificationsData,
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'total_pages' => $notifications->lastPage(),
                    'total_items' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'unread_count' => $notifications->where('is_read', false)->count(),
                    'has_more' => $notifications->hasMorePages(),
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Notifications] Get list error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar notificações',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obter perfil do participante
     */
    public function getProfile(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            // Estatísticas do perfil
            $totalCompletedSurveys = SurveyResponse::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();

            $totalEarned = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->sum('amount') ?? 0;

            $totalWithdrawn = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount') ?? 0;

            $firstSurveyDate = SurveyResponse::where('user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->value('created_at');

            $profile = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth,
                'education_level' => $user->education_level,
                'occupation' => $user->occupation,
                'province' => $user->province,
                'district' => $user->district,
                'address' => $user->address,
                'verification_status' => $user->verification_status,
                'verification_status_label' => $this->getVerificationStatusLabel($user->verification_status),
                'balance' => (float) $user->balance,
                'created_at' => $user->created_at->toDateTimeString(),
                'updated_at' => $user->updated_at->toDateTimeString(),
                'stats' => [
                    'total_completed_surveys' => $totalCompletedSurveys,
                    'total_earned' => (float) $totalEarned,
                    'total_withdrawn' => (float) $totalWithdrawn,
                    'active_since' => $firstSurveyDate ? $firstSurveyDate->diffForHumans() : 'Nunca participou',
                    'member_since' => $user->created_at->diffForHumans(),
                    'response_rate' => $totalCompletedSurveys > 0 ?
                        round(($totalCompletedSurveys / SurveyResponse::where('user_id', $user->id)->count()) * 100, 2) : 0,
                ],
                'preferences' => [
                    'email_notifications' => $user->email_notifications ?? true,
                    'push_notifications' => $user->push_notifications ?? true,
                    'survey_notifications' => $user->survey_notifications ?? true,
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Perfil carregado com sucesso',
                'data' => $profile
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Profile] Get error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar perfil',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Atualizar perfil do participante
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'gender' => 'sometimes|in:male,female,other',
                'date_of_birth' => 'sometimes|date|before:today',
                'education_level' => 'sometimes|string|max:100',
                'occupation' => 'sometimes|string|max:100',
                'province' => 'sometimes|string|max:100',
                'district' => 'sometimes|string|max:100',
                'address' => 'sometimes|string|max:500',
                'email_notifications' => 'sometimes|boolean',
                'push_notifications' => 'sometimes|boolean',
                'survey_notifications' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors(),
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }

            $user->update($request->only([
                'name', 'phone', 'gender', 'date_of_birth', 'education_level',
                'occupation', 'province', 'district', 'address',
                'email_notifications', 'push_notifications', 'survey_notifications'
            ]));

            Log::info('[Profile] Updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($request->all()),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'updated_at' => $user->updated_at->toDateTimeString(),
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[Profile] Update error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Obter rótulo do status de verificação
     */
    private function getVerificationStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'under_review' => 'Em análise',
        ];

        return $labels[$status] ?? 'Desconhecido';
    }

    /**
     * Obter estatísticas resumidas (para widgets)
     */
    public function getQuickStats(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado',
                ], 401, [], JSON_UNESCAPED_UNICODE);
            }

            if ($user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403, [], JSON_UNESCAPED_UNICODE);
            }

            // Estatísticas rápidas
            $todayEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('amount') ?? 0;

            $weekEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfWeek())
                ->sum('amount') ?? 0;

            $monthEarnings = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount') ?? 0;

            $pendingSurveys = SurveyResponse::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->count();

            $pendingWithdrawals = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->count();

            $unreadNotifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas rápidas carregadas',
                'data' => [
                    'balance' => (float) $user->balance,
                    'today_earnings' => (float) $todayEarnings,
                    'week_earnings' => (float) $weekEarnings,
                    'month_earnings' => (float) $monthEarnings,
                    'pending_surveys' => $pendingSurveys,
                    'pending_withdrawals' => $pendingWithdrawals,
                    'unread_notifications' => $unreadNotifications,
                    'available_surveys' => Survey::where('status', 'approved')
                        ->where('target_responses', '>', function ($query) {
                            $query->from('survey_responses')
                                ->selectRaw('COUNT(*)')
                                ->whereColumn('survey_id', 'surveys.id')
                                ->where('status', 'completed');
                        })
                        ->count(),
                    'last_updated' => now()->toDateTimeString(),
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('[QuickStats] Get error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor',
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }
}
