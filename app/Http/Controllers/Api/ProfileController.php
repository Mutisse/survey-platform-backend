<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NotificationConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * ProfileController - Gerencia perfis de todos os níveis de acesso
 *
 * @package App\Http\Controllers\Api
 */
class ProfileController extends Controller
{
    // ==============================================
    // PERFIL BASE - COMUM A TODOS OS USUÁRIOS
    // ==============================================

    /**
     * GET /api/profile - Perfil completo (base)
     */
    public function getCompleteProfile(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }

            // Verificar se perfil está completo e enviar notificação se necessário
            $this->checkProfileCompletionAndNotify($user);

            $basicInfo = [
                'id' => $user->id ?? null,
                'name' => $user->name ?? 'Nome não definido',
                'email' => $user->email ?? 'Email não definido',
            ];

            if (property_exists($user, 'phone')) $basicInfo['phone'] = $user->phone;
            if (property_exists($user, 'avatar')) $basicInfo['avatar'] = $user->avatar;
            if (property_exists($user, 'role')) $basicInfo['role'] = $user->role;
            if (property_exists($user, 'verification_status')) $basicInfo['verification_status'] = $user->verification_status;
            if (property_exists($user, 'created_at')) $basicInfo['created_at'] = $user->created_at;
            if (property_exists($user, 'last_login_at')) $basicInfo['last_login_at'] = $user->last_login_at;
            if (property_exists($user, 'balance')) $basicInfo['balance'] = (float) $user->balance;
            if (property_exists($user, 'email_notifications')) $basicInfo['email_notifications'] = (bool) $user->email_notifications;
            if (property_exists($user, 'whatsapp_notifications')) $basicInfo['whatsapp_notifications'] = (bool) $user->whatsapp_notifications;

            return response()->json([
                'success' => true,
                'message' => 'Perfil carregado com sucesso',
                'data' => [
                    'basic_info' => $basicInfo,
                    'permissions' => $this->getUserPermissions($user),
                    'allowed_sections' => $this->getAllowedSections($user),
                    'specific_data' => $this->getSpecificData($user),
                    'stats' => $this->getUserStats($user),
                    'profile_completion' => $this->calculateProfileCompletion($user),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getCompleteProfile: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * PUT /api/profile/update - Atualizar perfil básico
     */
    public function updateProfile(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?? null,
                'updated_at' => now(),
            ];

            DB::table('users')->where('id', $userId)->update($updateData);
            $user = DB::table('users')->where('id', $userId)->first();

            // Verificar se perfil ficou completo após atualização
            $this->checkProfileCompletionAndNotify($user);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no updateProfile: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar perfil'], 500);
        }
    }

    /**
     * PUT /api/profile/change-password - Alterar senha
     */
    public function changePassword(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Senha atual incorreta'], 401);
            }

            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true, 'message' => 'Senha alterada com sucesso!']);
        } catch (\Exception $e) {
            Log::error('Erro no changePassword: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao alterar senha'], 500);
        }
    }

    /**
     * GET /api/profile/stats - Obter estatísticas do usuário atual
     */
    public function getStats(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->getUserStats($user)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getStats: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar estatísticas'], 500);
        }
    }

    /**
     * GET /api/profile/notification-settings - Obter configurações de notificação
     */
    public function getNotificationSettings(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'email_notifications' => property_exists($user, 'email_notifications') ? (bool) $user->email_notifications : true,
                    'whatsapp_notifications' => property_exists($user, 'whatsapp_notifications') ? (bool) $user->whatsapp_notifications : false,
                    'push_notifications' => true,
                    'sms_notifications' => false,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getNotificationSettings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar configurações'], 500);
        }
    }

    /**
     * PUT /api/profile/notification-settings - Atualizar configurações de notificação
     */
    public function updateNotificationSettings(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $validator = Validator::make($request->all(), [
                'email_notifications' => 'nullable|boolean',
                'whatsapp_notifications' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            $updateData = ['updated_at' => now()];

            if ($request->has('email_notifications')) {
                $updateData['email_notifications'] = (bool) $request->email_notifications;
            }

            if ($request->has('whatsapp_notifications')) {
                $updateData['whatsapp_notifications'] = (bool) $request->whatsapp_notifications;
            }

            DB::table('users')->where('id', $userId)->update($updateData);

            return response()->json(['success' => true, 'message' => 'Configurações de notificação atualizadas com sucesso!']);
        } catch (\Exception $e) {
            Log::error('Erro no updateNotificationSettings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar configurações'], 500);
        }
    }

    /**
     * GET /api/profile/activity - Obter histórico de atividades
     */
    public function getActivity(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            $activities = [];

            if ($user && property_exists($user, 'last_login_at') && $user->last_login_at) {
                $activities[] = [
                    'id' => 1,
                    'type' => 'login',
                    'title' => 'Login no sistema',
                    'description' => 'Você acessou sua conta',
                    'created_at' => $user->last_login_at,
                    'metadata' => [
                        'ip' => property_exists($user, 'last_login_ip') ? $user->last_login_ip : null
                    ]
                ];
            }

            if ($user && property_exists($user, 'created_at') && $user->created_at) {
                $activities[] = [
                    'id' => 2,
                    'type' => 'account_created',
                    'title' => 'Conta criada',
                    'description' => 'Bem-vindo à plataforma!',
                    'created_at' => $user->created_at,
                    'metadata' => null
                ];
            }

            return response()->json(['success' => true, 'data' => $activities]);
        } catch (\Exception $e) {
            Log::error('Erro no getActivity: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar atividades'], 500);
        }
    }

    /**
     * GET /api/profile/export - Exportar dados do perfil
     */
    public function exportProfileData(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuário não encontrado'], 404);
            }

            $exportData = [
                'basic_info' => $this->getBasicInfoForExport($user),
                'specific_data' => $this->getSpecificData($user),
                'stats' => $this->getUserStats($user),
                'export_date' => now()->toISOString(),
            ];

            return response()->json(['success' => true, 'data' => $exportData]);
        } catch (\Exception $e) {
            Log::error('Erro no exportProfileData: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao exportar dados'], 500);
        }
    }

    // ==============================================
    // PERFIL DO PARTICIPANTE
    // ==============================================

    /**
     * GET /api/profile/participant/completed-surveys
     */
    public function getParticipantCompletedSurveys(Request $request)
    {
        try {
            $userId = Auth::id();

            $completedSurveys = DB::table('survey_responses')
                ->join('surveys', 'survey_responses.survey_id', '=', 'surveys.id')
                ->where('survey_responses.user_id', $userId)
                ->where('survey_responses.status', 'completed')
                ->select(
                    'surveys.id',
                    'surveys.title',
                    'surveys.description',
                    'survey_responses.completed_at',
                    'survey_responses.status',
                    'surveys.duration',
                    'survey_responses.payment_amount as reward',
                    'survey_responses.rating as survey_rating',
                    'survey_responses.feedback as researcher_feedback',
                    DB::raw('NULL as researcher_rating')
                )
                ->orderBy('survey_responses.completed_at', 'desc')
                ->get();

            $formattedSurveys = $completedSurveys->map(function ($survey) {
                return [
                    'id' => $survey->id,
                    'title' => $survey->title ?? 'Sem título',
                    'description' => $survey->description ?? '',
                    'completed_at' => $survey->completed_at ?? now(),
                    'status' => $survey->status ?? 'completed',
                    'duration' => (int) ($survey->duration ?? 0),
                    'reward' => (float) ($survey->reward ?? 0),
                    'survey_rating' => $survey->survey_rating ? (float) $survey->survey_rating : null,
                    'researcher_feedback' => $survey->researcher_feedback,
                    'researcher_rating' => null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedSurveys
            ]);
        } catch (\Exception $e) {
            Log::error('Erro em getParticipantCompletedSurveys: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Nenhum questionário encontrado'
            ]);
        }
    }

    /**
     * GET /api/profile/participant/reward-history
     */
    public function getParticipantRewardHistory(Request $request)
    {
        try {
            $userId = Auth::id();

            $rewards = DB::table('transactions')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($transaction) {
                    $type = 'survey';
                    if ($transaction->type === 'withdrawal') $type = 'withdrawal';
                    if ($transaction->description === 'Bónus') $type = 'bonus';
                    if (strpos($transaction->description, 'Indicação') !== false) $type = 'referral';

                    return [
                        'id' => $transaction->id,
                        'type' => $type,
                        'amount' => (float) $transaction->amount,
                        'description' => $transaction->description,
                        'status' => $transaction->status,
                        'created_at' => $transaction->created_at
                    ];
                });

            return response()->json(['success' => true, 'data' => $rewards]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar histórico de recompensas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar histórico'], 500);
        }
    }

    /**
     * GET /api/profile/participant/metrics
     */
    public function getParticipantMetrics(Request $request)
    {
        try {
            $userId = Auth::id();

            $surveysCompleted = DB::table('survey_responses')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->count();

            $totalEarned = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->sum('amount') ?? 0;

            $averageRating = DB::table('survey_responses')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->whereNotNull('rating')
                ->avg('rating') ?? 0;

            $totalStarted = DB::table('survey_responses')
                ->where('user_id', $userId)
                ->count();

            $completionRate = $totalStarted > 0
                ? round(($surveysCompleted / $totalStarted) * 100, 1)
                : 0;

            $streakDays = $this->calculateStreak($userId);

            $totalWithdrawn = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount') ?? 0;

            $pendingWithdrawal = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('type', 'withdrawal')
                ->where('status', 'pending')
                ->sum('amount') ?? 0;

            $metrics = [
                'total_surveys_completed' => (int) $surveysCompleted,
                'total_earned' => (float) $totalEarned,
                'average_rating' => round((float) $averageRating, 1),
                'response_time_average' => 0,
                'completion_rate' => (float) $completionRate,
                'streak_days' => (int) $streakDays,
                'total_withdrawn' => (float) $totalWithdrawn,
                'pending_withdrawal' => (float) $pendingWithdrawal
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Erro em getParticipantMetrics: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'data' => [
                    'total_surveys_completed' => 0,
                    'total_earned' => 0,
                    'average_rating' => 0,
                    'response_time_average' => 0,
                    'completion_rate' => 0,
                    'streak_days' => 0,
                    'total_withdrawn' => 0,
                    'pending_withdrawal' => 0
                ]
            ]);
        }
    }

    /**
     * POST /api/profile/participant/withdraw - Solicitar saque
     */
    public function participantWithdraw(Request $request)
    {
        try {
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuário não autenticado'], 401);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:50',
                'method' => 'required|in:bank_transfer,mobile_money',
                'account_details' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $validator->errors()], 422);
            }

            $user = DB::table('users')->where('id', $userId)->first();
            $balance = property_exists($user, 'balance') ? (float) $user->balance : 0;

            if ($balance < $request->amount) {
                return response()->json(['success' => false, 'message' => 'Saldo insuficiente'], 400);
            }

            DB::beginTransaction();

            $transactionId = DB::table('transactions')->insertGetId([
                'user_id' => $userId,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'status' => 'pending',
                'description' => 'Saque via ' . ($request->method === 'bank_transfer' ? 'Transferência Bancária' : 'Mobile Money'),
                'metadata' => json_encode($request->account_details),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('users')->where('id', $userId)->update([
                'balance' => $balance - $request->amount,
                'updated_at' => now()
            ]);

            DB::commit();

            // ============ NOTIFICAR ADMINS SOBRE SAQUE PENDENTE ============
            try {
                $notificationController = new NotificationController();
                $admins = DB::table('users')->where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    $notificationController->sendToUser(
                        $admin->id,
                        'withdrawal_pending',
                        [
                            'participant_name' => $user->name,
                            'amount' => $request->amount,
                            'withdrawal_id' => $transactionId
                        ]
                    );
                }

                Log::info('💰 Notificações de saque pendente enviadas para admins', [
                    'user_id' => $userId,
                    'amount' => $request->amount,
                    'transaction_id' => $transactionId
                ]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erro ao enviar notificações de saque para admins', [
                    'error' => $e->getMessage(),
                    'user_id' => $userId
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Solicitação de saque enviada com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao solicitar saque: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao solicitar saque'], 500);
        }
    }

    // ==============================================
    // PERFIL DO ESTUDANTE
    // ==============================================

    /**
     * GET /api/profile/student/dashboard
     */
    public function getStudentDashboard(Request $request)
    {
        try {
            $userId = Auth::id();
            $academicInfo = $this->getStudentAcademicInfo($userId);
            $surveys = $this->getStudentSurveys($userId);
            $totalSurveys = count($surveys);
            $totalEarnings = DB::table('transactions')->where('user_id', $userId)->where('type', 'credit')->where('status', 'completed')->sum('amount');
            $pendingWithdrawals = DB::table('transactions')->where('user_id', $userId)->where('type', 'withdrawal')->where('status', 'pending')->sum('amount');
            $recentActivity = $this->getStudentRecentActivity($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'academic_info' => $academicInfo,
                    'total_surveys' => (int) $totalSurveys,
                    'total_earnings' => (float) $totalEarnings,
                    'pending_withdrawals' => (float) $pendingWithdrawals,
                    'surveys' => $surveys,
                    'recent_activity' => $recentActivity
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dashboard do estudante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar dashboard'], 500);
        }
    }

    /**
     * GET /api/profile/student/surveys
     */
    public function getStudentSurveysList(Request $request)
    {
        try {
            $userId = Auth::id();
            $surveys = DB::table('surveys')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $surveys]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar pesquisas do estudante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar pesquisas'], 500);
        }
    }

    /**
     * GET /api/profile/student/earnings
     */
    public function getStudentEarnings(Request $request)
    {
        try {
            $userId = Auth::id();
            $earnings = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('type', 'credit')
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $earnings]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar ganhos do estudante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar ganhos'], 500);
        }
    }

    /**
     * GET /api/profile/student/withdrawals
     */
    public function getStudentWithdrawals(Request $request)
    {
        try {
            $userId = Auth::id();
            $withdrawals = DB::table('transactions')
                ->where('user_id', $userId)
                ->where('type', 'withdrawal')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $withdrawals]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar saques do estudante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar saques'], 500);
        }
    }

    /**
     * POST /api/profile/student/withdraw
     */
    public function studentWithdraw(Request $request)
    {
        return $this->participantWithdraw($request);
    }

    // ==============================================
    // PERFIL DO ADMIN
    // ==============================================

    /**
     * GET /api/profile/admin/stats - Estatísticas do sistema
     */
    public function getAdminSystemStats(Request $request)
    {
        try {
            $stats = [
                'total_users' => DB::table('users')->count(),
                'new_users_today' => DB::table('users')->whereDate('created_at', now()->toDateString())->count(),
                'pending_verifications' => DB::table('users')->where('role', 'student')->where('verification_status', 'pending')->count(),
                'total_surveys' => DB::table('surveys')->count(),
                'active_surveys' => DB::table('surveys')->where('status', 'published')->count(),
                'pending_surveys' => DB::table('surveys')->where('status', 'pending')->count(),
                'total_earnings_distributed' => (float) DB::table('transactions')->where('type', 'credit')->where('status', 'completed')->sum('amount'),
            ];

            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar estatísticas do admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar estatísticas'], 500);
        }
    }

    /**
     * GET /api/profile/admin/activity - Atividade do admin
     */
    public function getAdminActivity(Request $request)
    {
        try {
            $userId = Auth::id();
            $activities = [];

            $user = DB::table('users')->where('id', $userId)->first();
            if ($user && property_exists($user, 'last_login_at') && $user->last_login_at) {
                $activities[] = [
                    'id' => 1,
                    'type' => 'login',
                    'title' => 'Login no sistema',
                    'description' => 'Você acessou sua conta',
                    'created_at' => $user->last_login_at,
                    'metadata' => ['ip' => property_exists($user, 'last_login_ip') ? $user->last_login_ip : null]
                ];
            }

            if ($user && property_exists($user, 'created_at') && $user->created_at) {
                $activities[] = [
                    'id' => 2,
                    'type' => 'account_created',
                    'title' => 'Conta criada',
                    'description' => 'Bem-vindo à plataforma!',
                    'created_at' => $user->created_at,
                    'metadata' => null
                ];
            }

            return response()->json(['success' => true, 'data' => $activities]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar atividades do admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar atividades'], 500);
        }
    }

    // ==============================================
    // FUNÇÕES AUXILIARES
    // ==============================================

    /**
     * Calcular sequência de dias
     */
    private function calculateStreak($userId)
    {
        try {
            $lastResponse = DB::table('survey_responses')
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->first();

            if (!$lastResponse || !$lastResponse->completed_at) {
                return 0;
            }

            $lastDate = Carbon::parse($lastResponse->completed_at);
            $today = now();

            if ($lastDate->isToday()) {
                return 1;
            }

            return 0;
        } catch (\Exception $e) {
            Log::error('Erro ao calcular streak: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obter informações acadêmicas do estudante
     */
    private function getStudentAcademicInfo($userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        $profileInfo = $this->decodeProfileInfo($user->profile_info ?? null);

        return [
            'university' => $profileInfo['university'] ?? null,
            'course' => $profileInfo['course'] ?? null,
            'year' => $profileInfo['year'] ?? null,
            'semester' => $profileInfo['semester'] ?? null,
            'faculty' => $profileInfo['faculty'] ?? null,
            'student_id' => $profileInfo['student_id'] ?? null,
        ];
    }

    /**
     * Obter pesquisas do estudante
     */
    private function getStudentSurveys($userId)
    {
        return DB::table('surveys')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obter atividade recente do estudante
     */
    private function getStudentRecentActivity($userId)
    {
        $activities = [];

        $recentSurveys = DB::table('surveys')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentSurveys as $survey) {
            $activities[] = [
                'id' => $survey->id,
                'type' => 'survey_created',
                'title' => 'Pesquisa criada',
                'description' => 'Você criou a pesquisa: ' . $survey->title,
                'created_at' => $survey->created_at,
                'metadata' => null
            ];
        }

        return $activities;
    }

    /**
     * Obter informações básicas para exportação
     */
    private function getBasicInfoForExport($user)
    {
        $info = [
            'id' => $user->id ?? null,
            'name' => $user->name ?? 'Nome não definido',
            'email' => $user->email ?? 'Email não definido',
        ];

        if (property_exists($user, 'phone')) $info['phone'] = $user->phone;
        if (property_exists($user, 'avatar')) $info['avatar'] = $user->avatar;
        if (property_exists($user, 'role')) $info['role'] = $user->role;
        if (property_exists($user, 'verification_status')) $info['verification_status'] = $user->verification_status;
        if (property_exists($user, 'created_at')) $info['created_at'] = $user->created_at;
        if (property_exists($user, 'last_login_at')) $info['last_login_at'] = $user->last_login_at;

        return $info;
    }

    /**
     * Obter dados específicos do perfil
     */
    private function getSpecificData($user)
    {
        if (!property_exists($user, 'profile_info')) {
            return [];
        }

        $profileInfo = $this->decodeProfileInfo($user->profile_info);
        $role = property_exists($user, 'role') ? $user->role : 'participant';

        switch ($role) {
            case 'student':
                $data = $profileInfo['student_data'] ?? [];
                if (property_exists($user, 'university_id')) $data['university_id'] = $user->university_id;
                if (property_exists($user, 'course')) $data['course'] = $user->course;
                return $data;
            case 'participant':
                return $profileInfo['participant_data'] ?? [];
            case 'admin':
                return $profileInfo['admin_data'] ?? [];
            default:
                return [];
        }
    }

    /**
     * Decodificar informações do perfil
     */
    private function decodeProfileInfo($profileInfo)
    {
        if (is_string($profileInfo)) {
            return json_decode($profileInfo, true) ?? [];
        }
        return is_array($profileInfo) ? $profileInfo : [];
    }

    /**
     * Obter estatísticas do usuário
     */
    private function getUserStats($user)
    {
        try {
            $userId = $user->id;
            $role = property_exists($user, 'role') ? $user->role : 'participant';
            $balance = property_exists($user, 'balance') ? (float) $user->balance : 0;

            switch ($role) {
                case 'student':
                    $surveysCreated = DB::table('surveys')->where('user_id', $userId)->count();
                    $surveysPublished = DB::table('surveys')->where('user_id', $userId)->where('status', 'published')->count();
                    $totalResponses = DB::table('survey_responses')
                        ->join('surveys', 'survey_responses.survey_id', '=', 'surveys.id')
                        ->where('surveys.user_id', $userId)
                        ->count();
                    $totalEarnings = DB::table('transactions')
                        ->where('user_id', $userId)
                        ->where('type', 'credit')
                        ->where('status', 'completed')
                        ->sum('amount');

                    return [
                        'surveys_created' => (int) $surveysCreated,
                        'surveys_published' => (int) $surveysPublished,
                        'total_responses' => (int) $totalResponses,
                        'total_earnings' => (float) $totalEarnings,
                        'available_balance' => $balance,
                    ];

                case 'participant':
                    $surveysCompleted = DB::table('survey_responses')
                        ->where('user_id', $userId)
                        ->where('status', 'completed')
                        ->count();

                    $totalEarnings = DB::table('transactions')
                        ->where('user_id', $userId)
                        ->where('type', 'credit')
                        ->where('status', 'completed')
                        ->sum('amount');

                    $ranking = 0;
                    try {
                        $ranking = DB::table('users')
                            ->select('id')
                            ->where('role', 'participant')
                            ->orderByDesc('balance')
                            ->pluck('id')
                            ->search($userId) + 1;
                    } catch (\Exception $e) {
                        Log::warning('Erro ao calcular ranking: ' . $e->getMessage());
                    }

                    return [
                        'surveys_completed' => (int) $surveysCompleted,
                        'total_earnings' => (float) $totalEarnings,
                        'available_balance' => $balance,
                        'current_ranking' => $ranking,
                    ];

                case 'admin':
                    return [
                        'total_users' => DB::table('users')->count(),
                        'new_users_today' => DB::table('users')->whereDate('created_at', now()->toDateString())->count(),
                        'pending_verifications' => DB::table('users')->where('role', 'student')->where('verification_status', 'pending')->count(),
                        'total_earnings_distributed' => (float) DB::table('transactions')->where('type', 'credit')->where('status', 'completed')->sum('amount'),
                        'total_surveys' => DB::table('surveys')->count(),
                        'active_surveys' => DB::table('surveys')->where('status', 'published')->count(),
                        'pending_surveys' => DB::table('surveys')->where('status', 'pending')->count(),
                    ];

                default:
                    return [
                        'surveys_created' => 0,
                        'surveys_published' => 0,
                        'total_responses' => 0,
                        'total_earnings' => $balance,
                        'available_balance' => $balance,
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas do usuário: ' . $e->getMessage());
            return [
                'surveys_created' => 0,
                'surveys_published' => 0,
                'total_responses' => 0,
                'total_earnings' => 0,
                'available_balance' => 0,
                'surveys_completed' => 0,
                'current_ranking' => 0,
            ];
        }
    }

    /**
     * Obter permissões do usuário
     */
    private function getUserPermissions($user)
    {
        $role = property_exists($user, 'role') ? $user->role : 'participant';

        return [
            'can_edit_profile' => true,
            'can_change_password' => true,
            'can_upload_avatar' => true,
            'can_request_withdrawal' => in_array($role, ['student', 'participant']),
            'can_view_admin_panel' => $role === 'admin',
            'can_request_verification' => $role === 'student',
        ];
    }

    /**
     * Obter seções permitidas
     */
    private function getAllowedSections($user)
    {
        $role = property_exists($user, 'role') ? $user->role : 'participant';

        return [
            'stats' => true,
            'profile_info' => true,
            'activity' => true,
            'actions' => true,
            'admin_panel' => $role === 'admin',
            'student_documents' => $role === 'student',
            'student_verification' => $role === 'student',
            'participant_preferences' => $role === 'participant',
        ];
    }

    /**
     * Calcular porcentagem de conclusão do perfil
     */
    private function calculateProfileCompletion($user)
    {
        $completedFields = 0;
        $totalFields = 6;

        $fieldsToCheck = [
            'name' => !empty($user->name),
            'email' => !empty($user->email),
            'phone' => !empty($user->phone),
            'avatar' => !empty($user->avatar),
            'profile_info' => !empty($user->profile_info),
            'verification_status' => $user->verification_status === 'approved',
        ];

        foreach ($fieldsToCheck as $field => $isCompleted) {
            if ($isCompleted) $completedFields++;
        }

        return round(($completedFields / $totalFields) * 100);
    }

    /**
     * Verificar se perfil está completo e enviar notificação se necessário
     */
    private function checkProfileCompletionAndNotify($user)
    {
        try {
            $completion = $this->calculateProfileCompletion($user);

            // Se perfil está abaixo de 50% completo, notificar o usuário
            if ($completion < 50 && $user->role !== 'admin') {
                $userId = $user->id;

                // Verificar se já não notificamos recentemente (evitar spam)
                $lastNotification = DB::table('notifications')
                    ->where('user_id', $userId)
                    ->where('type', 'profile_incomplete')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->first();

                if (!$lastNotification) {
                    $notificationController = new NotificationController();
                    $notificationController->sendToUser(
                        $userId,
                        'profile_incomplete',
                        [
                            'completion' => $completion,
                            'missing_fields' => $this->getMissingProfileFields($user)
                        ]
                    );

                    Log::info('🔔 Notificação de perfil incompleto enviada', [
                        'user_id' => $userId,
                        'completion' => $completion
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao verificar conclusão do perfil: ' . $e->getMessage());
        }
    }

    /**
     * Obter campos do perfil que estão faltando
     */
    private function getMissingProfileFields($user)
    {
        $missing = [];

        if (empty($user->name)) $missing[] = 'name';
        if (empty($user->email)) $missing[] = 'email';
        if (empty($user->phone)) $missing[] = 'phone';
        if (empty($user->avatar)) $missing[] = 'avatar';
        if (empty($user->profile_info)) $missing[] = 'profile_info';
        if ($user->verification_status !== 'approved') $missing[] = 'verification';

        return $missing;
    }

    // ==============================================
    // MÉTODOS PENDENTES (Avatar, Verificação, Dados Específicos)
    // ==============================================

    /**
     * POST /api/profile/upload-avatar - Upload de avatar
     */
    public function uploadAvatar(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Método não implementado'], 501);
    }

    /**
     * DELETE /api/profile/remove-avatar - Remover avatar
     */
    public function removeAvatar(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Método não implementado'], 501);
    }

    /**
     * PUT /api/profile/specific-data - Atualizar dados específicos
     */
    public function updateSpecificData(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Método não implementado'], 501);
    }

    /**
     * POST /api/profile/request-verification - Solicitar verificação
     */
    public function requestVerification(Request $request)
    {
        return response()->json(['success' => false, 'message' => 'Método não implementado'], 501);
    }
}
