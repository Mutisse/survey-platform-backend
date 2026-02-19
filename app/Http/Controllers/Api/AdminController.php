<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Survey;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\NotificationConfig; // Adicionado
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Obter dados do dashboard administrativo
     */
    public function getDashboardData(Request $request)
    {
        try {
            // Estatísticas gerais
            $totalUsers = User::count();
            $activeUsers = User::where('verification_status', 'approved')->count();
            $pendingUsers = User::where('verification_status', 'pending')->count();
            $recentUsers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();

            $totalSurveys = Survey::count();
            $activeSurveys = Survey::where('status', 'published')->count();
            $completedSurveys = Survey::where('status', 'closed')->count();

            $totalTransactions = Transaction::count();
            $transactionVolume = Transaction::where('status', 'completed')->sum('amount');
            $pendingTransactions = Transaction::where('status', 'pending')->count();

            // Estatísticas de crescimento mensal
            $currentMonth = Carbon::now()->month;
            $lastMonth = Carbon::now()->subMonth()->month;

            $currentMonthUsers = User::whereMonth('created_at', $currentMonth)->count();
            $lastMonthUsers = User::whereMonth('created_at', $lastMonth)->count();

            $monthlyGrowth = $lastMonthUsers > 0
                ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 2)
                : 100;

            // Estatísticas de satisfação
            $averageSatisfaction = 4.5;
            $completionRate = $totalSurveys > 0
                ? round(($completedSurveys / $totalSurveys) * 100, 2)
                : 0;

            // Estatísticas mensais (últimos 6 meses)
            $monthlyStats = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();

                $monthStats = [
                    'month' => $month->format('M/Y'),
                    'users' => User::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'surveys' => Survey::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'transactions' => Transaction::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'revenue' => Transaction::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->where('status', 'completed')
                        ->sum('amount'),
                ];

                $monthlyStats[] = $monthStats;
            }

            return response()->json([
                'success' => true,
                'message' => 'Dados do dashboard carregados com sucesso',
                'data' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'pending_users' => $pendingUsers,
                    'recent_users' => $recentUsers,
                    'total_surveys' => $totalSurveys,
                    'active_surveys' => $activeSurveys,
                    'completed_surveys' => $completedSurveys,
                    'total_transactions' => $totalTransactions,
                    'transaction_volume' => $transactionVolume,
                    'pending_transactions' => $pendingTransactions,
                    'monthly_growth' => $monthlyGrowth,
                    'average_satisfaction' => $averageSatisfaction,
                    'completion_rate' => $completionRate,
                    'monthly_stats' => $monthlyStats,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas resumidas
     */
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('verification_status', 'approved')->count(),
                    'pending' => User::where('verification_status', 'pending')->count(),
                    'today' => User::whereDate('created_at', Carbon::today())->count(),
                ],
                'surveys' => [
                    'total' => Survey::count(),
                    'active' => Survey::where('status', 'published')->count(),
                    'draft' => Survey::where('status', 'draft')->count(),
                    'completed' => Survey::where('status', 'closed')->count(),
                ],
                'transactions' => [
                    'total' => Transaction::count(),
                    'completed' => Transaction::where('status', 'completed')->count(),
                    'pending' => Transaction::where('status', 'pending')->count(),
                    'volume' => Transaction::where('status', 'completed')->sum('amount'),
                ],
                'activity' => [
                    'today' => ActivityLog::whereDate('created_at', Carbon::today())->count(),
                    'week' => ActivityLog::where('created_at', '>=', Carbon::now()->subWeek())->count(),
                    'month' => ActivityLog::where('created_at', '>=', Carbon::now()->subMonth())->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas carregadas',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar usuários
     */
    public function getUsers(Request $request)
    {
        try {
            $query = User::query();

            if ($request->has('role') && !empty($request->role)) {
                $query->where('role', $request->role);
            }

            if ($request->has('status') && !empty($request->status)) {
                $statusMapping = [
                    'active' => 'approved',
                    'pending' => 'pending',
                    'suspended' => 'rejected',
                    'inactive' => 'rejected'
                ];

                $frontendStatus = $request->status;
                $verificationStatus = $statusMapping[$frontendStatus] ?? 'pending';

                $query->where('verification_status', $verificationStatus);
            }

            if ($request->has('verification_status') && !empty($request->verification_status)) {
                $query->where('verification_status', $request->verification_status);
            }

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $users = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedUsers = $users->map(function ($user) {
                $profileInfo = $user->profile_info ?? [];

                $statusMap = [
                    'approved' => 'active',
                    'pending' => 'pending',
                    'rejected' => 'suspended',
                ];

                $status = $statusMap[$user->verification_status] ?? 'pending';

                $institution = null;
                if ($user->role === 'student' && isset($profileInfo['student_data']['university'])) {
                    $institution = $profileInfo['student_data']['university'];
                } elseif (isset($profileInfo['institution'])) {
                    $institution = $profileInfo['institution'];
                }

                $statsFromProfile = $profileInfo['stats'] ?? [];

                $surveysCreated = $statsFromProfile['surveys_created'] ?? 0;
                $surveysCompleted = $statsFromProfile['surveys_completed'] ?? 0;
                $totalEarnings = $statsFromProfile['total_earnings'] ?? 0;
                $totalWithdrawals = $statsFromProfile['total_withdrawals'] ?? 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'status' => $status,
                    'verification_status' => $user->verification_status,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                    'last_login' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
                    'institution' => $institution,
                    'balance' => $user->balance,
                    'email_notifications' => $profileInfo['email_notifications'] ?? true,
                    'whatsapp_notifications' => $profileInfo['whatsapp_notifications'] ?? false,

                    'profile' => [
                        'avatar' => $profileInfo['avatar'] ?? null,
                        'bio' => $profileInfo['bio'] ?? null,
                        'location' => $profileInfo['location'] ?? null,
                    ],

                    'student_data' => $user->role === 'student' ? [
                        'university' => $profileInfo['student_data']['university'] ?? null,
                        'course' => $profileInfo['student_data']['course'] ?? null,
                        'year_of_study' => $profileInfo['student_data']['year_of_study'] ?? null,
                        'student_id' => $profileInfo['student_data']['student_id'] ?? null,
                    ] : null,

                    'participant_data' => $user->role === 'participant' ? [
                        'occupation' => $profileInfo['participant_data']['occupation'] ?? null,
                        'education_level' => $profileInfo['participant_data']['education_level'] ?? null,
                        'income_range' => $profileInfo['participant_data']['income_range'] ?? null,
                    ] : null,

                    'stats' => [
                        'surveys_created' => $surveysCreated,
                        'surveys_completed' => $surveysCompleted,
                        'total_earnings' => $totalEarnings,
                        'total_withdrawals' => $totalWithdrawals,
                        'balance' => $user->balance,
                    ],

                    'verification_notes' => $profileInfo['verification_notes'] ?? null,
                    'verified_at' => $profileInfo['verified_at'] ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Usuários carregados com sucesso',
                'data' => $formattedUsers,
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'total_pages' => $users->lastPage(),
                    'total_items' => $users->total(),
                    'per_page' => $users->perPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro em getUsers: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar usuários',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter detalhes de um usuário
     */
    public function getUser($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado'
                ], 404);
            }

            $profileInfo = $user->profile_info ?? [];

            $statusMap = [
                'approved' => 'active',
                'pending' => 'pending',
                'rejected' => 'suspended',
            ];

            $status = $statusMap[$user->verification_status] ?? 'pending';

            $institution = null;
            if ($user->role === 'student' && isset($profileInfo['student_data']['university'])) {
                $institution = $profileInfo['student_data']['university'];
            } elseif (isset($profileInfo['institution'])) {
                $institution = $profileInfo['institution'];
            }

            $statsFromProfile = $profileInfo['stats'] ?? [];
            $surveysCreated = $statsFromProfile['surveys_created'] ?? 0;
            $surveysCompleted = $statsFromProfile['surveys_completed'] ?? 0;
            $totalEarnings = $statsFromProfile['total_earnings'] ?? 0;
            $totalWithdrawals = $statsFromProfile['total_withdrawals'] ?? 0;

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $status,
                'verification_status' => $user->verification_status,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
                'last_login' => $user->last_login_at ? $user->last_login_at->toISOString() : null,
                'institution' => $institution,
                'balance' => $user->balance,
                'email_notifications' => $profileInfo['email_notifications'] ?? true,
                'whatsapp_notifications' => $profileInfo['whatsapp_notifications'] ?? false,

                'profile' => [
                    'avatar' => $profileInfo['avatar'] ?? null,
                    'bio' => $profileInfo['bio'] ?? null,
                    'location' => $profileInfo['location'] ?? null,
                ],

                'student_data' => $user->role === 'student' ? [
                    'university' => $profileInfo['student_data']['university'] ?? null,
                    'course' => $profileInfo['student_data']['course'] ?? null,
                    'year_of_study' => $profileInfo['student_data']['year_of_study'] ?? null,
                    'student_id' => $profileInfo['student_data']['student_id'] ?? null,
                ] : null,

                'participant_data' => $user->role === 'participant' ? [
                    'occupation' => $profileInfo['participant_data']['occupation'] ?? null,
                    'education_level' => $profileInfo['participant_data']['education_level'] ?? null,
                    'income_range' => $profileInfo['participant_data']['income_range'] ?? null,
                ] : null,

                'stats' => [
                    'surveys_created' => $surveysCreated,
                    'surveys_completed' => $surveysCompleted,
                    'total_earnings' => $totalEarnings,
                    'total_withdrawals' => $totalWithdrawals,
                    'balance' => $user->balance,
                ],

                'verification_notes' => $profileInfo['verification_notes'] ?? null,
                'verified_at' => $profileInfo['verified_at'] ?? null,
                'profile_info' => $profileInfo,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Usuário encontrado',
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            Log::error('AdminController@getUser - Erro: ' . $e->getMessage(), [
                'user_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar usuário',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Criar novo usuário
     */
    public function createUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'required|in:student,participant,admin',
                'phone' => 'required|string|max:20',
                'status' => 'nullable|in:active,pending,suspended,inactive',
            ]);

            $statusMapping = [
                'active' => 'approved',
                'pending' => 'pending',
                'suspended' => 'rejected',
                'inactive' => 'rejected'
            ];

            $verificationStatus = 'pending';
            if (isset($validated['status']) && !empty($validated['status'])) {
                $verificationStatus = $statusMapping[$validated['status']] ?? 'pending';
            } elseif ($validated['role'] === 'admin') {
                $verificationStatus = 'approved';
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => $validated['role'],
                'phone' => $validated['phone'],
                'verification_status' => $verificationStatus,
            ]);

            // Registrar atividade
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'user_created',
                'description' => "Usuário {$user->name} criado pelo administrador",
                'metadata' => json_encode(['user_id' => $user->id, 'role' => $user->role]),
            ]);

            $frontendStatusMapping = [
                'approved' => 'active',
                'pending' => 'pending',
                'rejected' => 'suspended',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $frontendStatusMapping[$user->verification_status] ?? 'pending',
                    'verification_status' => $user->verification_status,
                    'phone' => $user->phone,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualizar usuário
     */
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $oldStatus = $user->verification_status;

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'role' => 'nullable|in:student,participant,admin',
                'phone' => 'nullable|string|max:20',
                'status' => 'nullable|in:active,pending,suspended,inactive',
                'verification_status' => 'nullable|in:pending,approved,rejected',
                'balance' => 'nullable|numeric|min:0',
                'email_notifications' => 'nullable|boolean',
                'whatsapp_notifications' => 'nullable|boolean',
                'profile_info' => 'nullable|array',
            ]);

            // ============ NOTIFICAÇÃO QUANDO STATUS MUDA ============
            $statusChanged = false;
            $newStatus = null;

            if (isset($validated['status'])) {
                $statusMapping = [
                    'active' => 'approved',
                    'pending' => 'pending',
                    'suspended' => 'rejected',
                    'inactive' => 'rejected'
                ];
                $newStatus = $statusMapping[$validated['status']];
                if ($user->verification_status !== $newStatus) {
                    $statusChanged = true;
                }
                $user->verification_status = $newStatus;
            } elseif (isset($validated['verification_status'])) {
                if ($user->verification_status !== $validated['verification_status']) {
                    $statusChanged = true;
                }
                $user->verification_status = $validated['verification_status'];
                $newStatus = $validated['verification_status'];
            }

            if (isset($validated['name'])) $user->name = $validated['name'];
            if (isset($validated['email'])) $user->email = $validated['email'];
            if (isset($validated['role'])) $user->role = $validated['role'];
            if (isset($validated['phone'])) $user->phone = $validated['phone'];
            if (isset($validated['balance'])) $user->balance = $validated['balance'];
            if (isset($validated['email_notifications'])) $user->email_notifications = $validated['email_notifications'];
            if (isset($validated['whatsapp_notifications'])) $user->whatsapp_notifications = $validated['whatsapp_notifications'];
            if (isset($validated['profile_info'])) $user->profile_info = $validated['profile_info'];

            if ($request->has('password') && $request->password) {
                $user->password = bcrypt($request->password);
            }

            $user->save();

            // ============ DISPARAR NOTIFICAÇÃO SE STATUS MUDOU ============
            if ($statusChanged && $newStatus === 'approved') {
                try {
                    $notificationController = new NotificationController();
                    $notificationController->sendToUser(
                        $user->id,
                        'user_approved',
                        []
                    );

                    Log::info('🔔 Notificação de aprovação enviada para usuário', [
                        'user_id' => $user->id,
                        'user_name' => $user->name
                    ]);
                } catch (\Exception $e) {
                    Log::warning('⚠️ Erro ao enviar notificação de aprovação', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                }
            }

            // Registrar atividade
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'user_updated',
                'description' => "Usuário {$user->name} atualizado pelo administrador",
                'metadata' => json_encode(['user_id' => $user->id, 'changes' => $validated]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $validated['status'] ?? $this->getStatusFromVerification($user->verification_status),
                    'verification_status' => $user->verification_status,
                    'balance' => $user->balance,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método auxiliar para converter verification_status para status
     */
    private function getStatusFromVerification($verificationStatus)
    {
        $mapping = [
            'approved' => 'active',
            'pending' => 'pending',
            'rejected' => 'suspended',
        ];

        return $mapping[$verificationStatus] ?? 'pending';
    }

    /**
     * Excluir usuário
     */
    public function deleteUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não pode excluir sua própria conta'
                ], 403);
            }

            $userName = $user->name;
            $user->delete();

            // Registrar atividade
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'user_deleted',
                'description' => "Usuário {$userName} excluído pelo administrador",
                'metadata' => json_encode(['user_id' => $id]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ações em massa para usuários
     */
    public function bulkUserActions(Request $request)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:activate,suspend,verify,reject,delete',
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'reason' => 'nullable|string|max:500',
            ]);

            $userIds = $validated['user_ids'];
            $action = $validated['action'];
            $reason = $validated['reason'] ?? null;

            $successCount = 0;
            $failedCount = 0;
            $notificationController = new NotificationController();

            foreach ($userIds as $userId) {
                try {
                    $user = User::find($userId);

                    if ($user->id === $request->user()->id) {
                        $failedCount++;
                        continue;
                    }

                    switch ($action) {
                        case 'delete':
                            $user->delete();
                            break;
                        case 'activate':
                        case 'verify':
                            $oldStatus = $user->verification_status;
                            $user->verification_status = 'approved';
                            $user->verified_at = now();
                            $user->save();

                            // Notificar usuário se foi aprovado
                            if ($oldStatus !== 'approved') {
                                $notificationController->sendToUser(
                                    $user->id,
                                    'user_approved',
                                    []
                                );
                            }
                            break;
                        case 'suspend':
                        case 'reject':
                            $user->verification_status = 'rejected';
                            $user->save();
                            break;
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            // Registrar atividade
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'bulk_user_action',
                'description' => "Ação em massa '{$action}' executada em {$successCount} usuários",
                'metadata' => json_encode([
                    'action' => $action,
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'reason' => $reason
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Ação executada em {$successCount} usuários",
                'data' => [
                    'success' => $successCount,
                    'failed' => $failedCount
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar ação em massa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar usuários
     */
    public function exportUsers(Request $request)
    {
        try {
            $query = User::query();

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            if ($request->has('verification_status')) {
                $query->where('verification_status', $request->verification_status);
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('created_at', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            $users = $query->get();

            $csvData = "ID,Nome,Email,Telefone,Role,Verificação,Saldo,Email Notif,WhatsApp Notif,Data Cadastro\n";

            foreach ($users as $user) {
                $csvData .= sprintf(
                    '%s,"%s","%s","%s","%s","%s",%s,%s,%s,%s' . "\n",
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '',
                    $user->role,
                    $user->verification_status,
                    $user->balance,
                    $user->email_notifications ? 'Sim' : 'Não',
                    $user->whatsapp_notifications ? 'Sim' : 'Não',
                    $user->created_at->format('Y-m-d H:i:s')
                );
            }

            $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/exports/' . $filename);

            if (!file_exists(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0777, true);
            }

            file_put_contents($filepath, $csvData);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'users_exported',
                'description' => "Exportação de {$users->count()} usuários realizada",
                'metadata' => json_encode(['file' => $filename, 'count' => $users->count()]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exportação realizada com sucesso',
                'data' => [
                    'url' => url('/storage/exports/' . $filename),
                    'filename' => $filename,
                    'count' => $users->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar usuários',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar transações
     */
    public function getTransactions(Request $request)
    {
        try {
            $query = Transaction::with('user');

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
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

            if ($request->has('min_amount')) {
                $query->where('amount', '>=', $request->min_amount);
            }

            if ($request->has('max_amount')) {
                $query->where('amount', '<=', $request->max_amount);
            }

            $perPage = $request->get('limit', 15);
            $page = $request->get('page', 1);

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $transactions = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Transações carregadas',
                'data' => $transactions,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'total_pages' => $transactions->lastPage(),
                    'total_items' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar transações',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transações recentes
     */
    public function getRecentTransactions(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $transactions = Transaction::with('user')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Transações recentes carregadas',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar transações recentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar status da transação
     */
    public function updateTransactionStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,processing,completed,failed,cancelled',
                'notes' => 'nullable|string|max:500',
            ]);

            $transaction = Transaction::findOrFail($id);
            $oldStatus = $transaction->status;

            $transaction->status = $validated['status'];

            if (isset($validated['notes'])) {
                $transaction->admin_notes = $validated['notes'];
            }

            if ($validated['status'] === 'completed') {
                $transaction->completed_at = now();

                // ============ NOTIFICAR USUÁRIO SOBRE SAQUE COMPLETO ============
                try {
                    if ($transaction->type === 'withdrawal') {
                        $notificationController = new NotificationController();
                        $notificationController->sendToUser(
                            $transaction->user_id,
                            'withdrawal_completed',
                            [
                                'amount' => $transaction->amount,
                                'withdrawal_id' => $transaction->id
                            ]
                        );

                        Log::info('💰 Notificação de saque completo enviada', [
                            'user_id' => $transaction->user_id,
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('⚠️ Erro ao enviar notificação de saque completo', [
                        'error' => $e->getMessage(),
                        'transaction_id' => $transaction->id
                    ]);
                }
            }

            $transaction->save();

            // Registrar atividade
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'transaction_status_updated',
                'description' => "Status da transação #{$transaction->id} alterado de '{$oldStatus}' para '{$transaction->status}'",
                'metadata' => json_encode([
                    'transaction_id' => $transaction->id,
                    'old_status' => $oldStatus,
                    'new_status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'user_id' => $transaction->user_id
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status da transação atualizado',
                'data' => $transaction
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status da transação',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Processar pagamentos pendentes
     */
    public function processPayments(Request $request)
    {
        try {
            $pendingTransactions = Transaction::where('status', 'pending')
                ->where('type', 'withdrawal')
                ->get();

            $processed = [];
            $notificationController = new NotificationController();

            foreach ($pendingTransactions as $transaction) {
                try {
                    $transaction->status = 'completed';
                    $transaction->completed_at = now();
                    $transaction->save();

                    // Notificar usuário
                    $notificationController->sendToUser(
                        $transaction->user_id,
                        'withdrawal_completed',
                        [
                            'amount' => $transaction->amount,
                            'withdrawal_id' => $transaction->id
                        ]
                    );

                    $processed[] = $transaction;

                    ActivityLog::create([
                        'user_id' => $request->user()->id,
                        'action' => 'payment_processed',
                        'description' => "Pagamento #{$transaction->id} processado para usuário {$transaction->user->name}",
                        'metadata' => json_encode([
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount,
                            'user_id' => $transaction->user_id,
                            'method' => $transaction->payment_method
                        ]),
                    ]);
                } catch (\Exception $e) {
                    $transaction->status = 'failed';
                    $transaction->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($processed) . ' pagamentos processados',
                'data' => $processed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar pagamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar pesquisas
     */
    public function getSurveys(Request $request)
    {
        try {
            $query = Survey::query();

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('researcher_id')) {
                $query->where('user_id', $request->researcher_id);
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('limit', 15);
            $page = $request->get('page', 1);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $surveys = $query->paginate($perPage, ['*'], 'page', $page);

            $formattedSurveys = $surveys->map(function ($survey) {
                $user = User::find($survey->user_id);

                return [
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'category' => $survey->category,
                    'status' => $survey->status,
                    'researcher_id' => $survey->user_id,
                    'researcher_name' => $user ? $user->name : null,
                    'researcher_email' => $user ? $user->email : null,
                    'target_responses' => $survey->target_responses,
                    'current_responses' => $survey->current_responses,
                    'reward' => $survey->reward,
                    'created_at' => $survey->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pesquisas carregadas',
                'data' => $formattedSurveys,
                'meta' => [
                    'current_page' => $surveys->currentPage(),
                    'total_pages' => $surveys->lastPage(),
                    'total_items' => $surveys->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro em getSurveys: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar pesquisas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar status da pesquisa
     */
    public function updateSurveyStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:draft,published,closed,rejected',
                'reason' => 'nullable|string|max:500',
            ]);

            $survey = Survey::with('user')->findOrFail($id);
            $oldStatus = $survey->status;

            $survey->status = $validated['status'];

            if ($validated['status'] === 'rejected' && isset($validated['reason'])) {
                $survey->rejection_reason = $validated['reason'];
            }

            if ($validated['status'] === 'published' && !$survey->published_at) {
                $survey->published_at = now();
            }

            $survey->save();

            // ============ NOTIFICAÇÕES QUANDO STATUS DA PESQUISA MUDA ============
            try {
                $notificationController = new NotificationController();

                if ($validated['status'] === 'published') {
                    // Notificar estudantes que a pesquisa foi publicada
                    $notificationController->sendToUser(
                        $survey->user_id,
                        'payment_confirmed',
                        [
                            'survey_title' => $survey->title,
                            'survey_id' => $survey->id
                        ]
                    );

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

                    Log::info('📊 Notificações de nova pesquisa enviadas', [
                        'survey_id' => $survey->id,
                        'participants_count' => $participants->count()
                    ]);
                } elseif ($validated['status'] === 'approved') {
                    // Notificar estudante que pesquisa foi aprovada para pagamento
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
                } elseif ($validated['status'] === 'closed') {
                    // Notificar estudante que pesquisa foi encerrada
                    $notificationController->sendToUser(
                        $survey->user_id,
                        'survey_closed',
                        [
                            'survey_title' => $survey->title,
                            'survey_id' => $survey->id
                        ]
                    );

                    Log::info('🔚 Notificação de pesquisa encerrada enviada', [
                        'survey_id' => $survey->id,
                        'student_id' => $survey->user_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('⚠️ Erro ao enviar notificações de pesquisa', [
                    'error' => $e->getMessage(),
                    'survey_id' => $survey->id
                ]);
            }

            // Registrar atividade
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'survey_status_updated',
                'description' => "Status da pesquisa '{$survey->title}' alterado de '{$oldStatus}' para '{$survey->status}'",
                'metadata' => json_encode([
                    'survey_id' => $survey->id,
                    'old_status' => $oldStatus,
                    'new_status' => $survey->status,
                    'researcher_id' => $survey->user_id
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status da pesquisa atualizado',
                'data' => $survey
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status da pesquisa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter atividade recente
     */
    public function getActivity(Request $request)
    {
        try {
            $limit = $request->get('limit', 20);

            $activity = ActivityLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'user_id' => $log->user_id,
                        'user_name' => $log->user->name ?? 'Sistema',
                        'action' => $log->action,
                        'description' => $log->description,
                        'metadata' => $log->metadata ? json_decode($log->metadata, true) : null,
                        'created_at' => $log->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Atividade carregada',
                'data' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar atividade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar relatório
     */
    public function generateReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:users,surveys,transactions,financial,activity',
                'format' => 'required|in:pdf,csv,json',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'filters' => 'nullable|array',
            ]);

            $reportData = [
                'type' => $validated['type'],
                'period' => [
                    'from' => $validated['date_from'],
                    'to' => $validated['date_to'],
                ],
                'generated_at' => now()->toISOString(),
                'generated_by' => $request->user()->name,
                'filters' => $validated['filters'] ?? [],
                'data' => $this->generateReportData($validated['type'], $validated['date_from'], $validated['date_to'], $validated['filters'] ?? [])
            ];

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'report_generated',
                'description' => "Relatório do tipo '{$validated['type']}' gerado",
                'metadata' => json_encode([
                    'type' => $validated['type'],
                    'format' => $validated['format'],
                    'period' => [$validated['date_from'], $validated['date_to']]
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Relatório gerado com sucesso',
                'data' => [
                    'url' => '#',
                    'data' => $reportData
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter relatório específico
     */
    public function getReport($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Funcionalidade em desenvolvimento'
        ], 501);
    }

    /**
     * Gerar dados do relatório
     */
    private function generateReportData($type, $dateFrom, $dateTo, $filters = [])
    {
        switch ($type) {
            case 'users':
                return $this->generateUsersReport($dateFrom, $dateTo, $filters);
            case 'surveys':
                return $this->generateSurveysReport($dateFrom, $dateTo, $filters);
            case 'transactions':
                return $this->generateTransactionsReport($dateFrom, $dateTo, $filters);
            case 'financial':
                return $this->generateFinancialReport($dateFrom, $dateTo, $filters);
            case 'activity':
                return $this->generateActivityReport($dateFrom, $dateTo, $filters);
            default:
                return [];
        }
    }

    private function generateUsersReport($dateFrom, $dateTo, $filters)
    {
        $query = User::whereBetween('created_at', [$dateFrom, $dateTo]);

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        return [
            'total' => $query->count(),
            'by_role' => $query->select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->toArray(),
            'by_verification_status' => $query->select('verification_status', DB::raw('count(*) as count'))
                ->groupBy('verification_status')
                ->get()
                ->toArray(),
            'daily_registrations' => $this->getDailyStats(User::class, $dateFrom, $dateTo),
        ];
    }

    private function generateSurveysReport($dateFrom, $dateTo, $filters)
    {
        $query = Survey::whereBetween('created_at', [$dateFrom, $dateTo]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return [
            'total' => $query->count(),
            'by_status' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->toArray(),
            'by_category' => $query->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get()
                ->toArray(),
            'total_responses' => $query->sum('current_responses'),
            'total_rewards' => $query->sum(DB::raw('reward * current_responses')),
        ];
    }

    private function generateTransactionsReport($dateFrom, $dateTo, $filters)
    {
        $query = Transaction::whereBetween('created_at', [$dateFrom, $dateTo]);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return [
            'total' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'by_type' => $query->select('type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
                ->groupBy('type')
                ->get()
                ->toArray(),
            'by_status' => $query->select('status', DB::raw('count(*) as count'), DB::raw('sum(amount) as total_amount'))
                ->groupBy('status')
                ->get()
                ->toArray(),
        ];
    }

    private function generateFinancialReport($dateFrom, $dateTo, $filters)
    {
        $earnings = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('type', 'earning')
            ->sum('amount');

        $withdrawals = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('type', 'withdrawal')
            ->sum('amount');

        return [
            'earnings' => $earnings,
            'withdrawals' => $withdrawals,
            'net_profit' => $earnings - $withdrawals,
            'balance_summary' => [
                'total_user_balance' => User::sum('balance'),
                'average_user_balance' => User::avg('balance'),
            ],
        ];
    }

    private function generateActivityReport($dateFrom, $dateTo, $filters)
    {
        $query = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo]);

        return [
            'total' => $query->count(),
            'by_action' => $query->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->get()
                ->toArray(),
            'by_user' => $query->select('user_id', DB::raw('count(*) as count'))
                ->groupBy('user_id')
                ->with('user')
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'user_name' => $item->user->name ?? 'Sistema',
                        'count' => $item->count
                    ];
                })
                ->toArray(),
        ];
    }

    private function getDailyStats($model, $dateFrom, $dateTo)
    {
        $stats = $model::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $stats;
    }
}
