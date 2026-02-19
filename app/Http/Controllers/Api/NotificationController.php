<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\NotificationConfig; // <-- IMPORT ADICIONADO
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Listar notificações do usuário com filtros avançados
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Validação dos parâmetros
            $validator = Validator::make($request->all(), [
                'type' => 'nullable|in:student,participant,admin,all',
                'notification_type' => 'nullable|string',
                'priority' => 'nullable|integer|min:1|max:3',
                'unread_only' => 'nullable|boolean',
                'expired' => 'nullable|boolean',
                'limit' => 'nullable|integer|min:1|max:100',
                'per_page' => 'nullable|integer|min:1|max:50',
                'page' => 'nullable|integer|min:1',
                'sort_by' => 'nullable|in:created_at,priority,type',
                'sort_order' => 'nullable|in:asc,desc'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parâmetros inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Query base - sempre filtrar pelo usuário logado
            $query = Notification::forUser($user->id)
                ->notExpired();

            // Aplicar filtros
            if ($request->filled('type') && $request->type !== 'all') {
                $query->byUserType($request->type);
            }

            if ($request->filled('notification_type')) {
                $query->where('type', $request->notification_type);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->boolean('unread_only')) {
                $query->where('is_read', false);
            }

            if ($request->boolean('expired')) {
                $query->where('expires_at', '<=', now());
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder)
                ->orderBy('id', 'desc');

            // Paginação
            $perPage = $request->get('per_page', 20);
            $notifications = $query->paginate($perPage);

            // Estatísticas
            $stats = $this->getUserNotificationStats($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Notificações carregadas com sucesso',
                'data' => [
                    'notifications' => $notifications->items(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'total_pages' => $notifications->lastPage(),
                        'total_items' => $notifications->total(),
                        'per_page' => $notifications->perPage(),
                        'has_more_pages' => $notifications->hasMorePages()
                    ],
                    'stats' => $stats,
                    'filters_applied' => $request->all()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar notificações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas detalhadas das notificações
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();

            $stats = $this->getUserNotificationStats($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Estatísticas de notificações',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter tipos de notificações disponíveis
     */
    public function types(): JsonResponse
    {
        try {
            // Consultar diretamente do banco usando Schema
            $columnType = DB::select("
                SELECT COLUMN_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'notifications'
                AND COLUMN_NAME = 'type'
            ");

            if (empty($columnType)) {
                // Fallback: usar array fixo com os tipos conhecidos
                $enumValues = $this->getKnownNotificationTypes();
            } else {
                // Extrair valores do ENUM
                $typeDefinition = $columnType[0]->COLUMN_TYPE;
                $typeDefinition = trim($typeDefinition, "enum()");
                $enumValues = array_map(function ($value) {
                    return trim($value, "'");
                }, explode(',', $typeDefinition));
            }

            // Agrupar por categoria para melhor organização
            $categorizedTypes = $this->getCategorizedTypes();

            return response()->json([
                'success' => true,
                'data' => [
                    'all_types' => $enumValues,
                    'categorized_types' => $categorizedTypes,
                    'count' => count($enumValues)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter tipos de notificações: ' . $e->getMessage());

            // Fallback para tipos conhecidos
            return response()->json([
                'success' => true,
                'data' => [
                    'all_types' => $this->getKnownNotificationTypes(),
                    'categorized_types' => $this->getCategorizedTypes(),
                    'count' => count($this->getKnownNotificationTypes())
                ]
            ]);
        }
    }

    /**
     * Obter tipos conhecidos de notificações (fallback)
     * VERSÃO COMPLETA COM TODAS AS NOTIFICAÇÕES
     */
    private function getKnownNotificationTypes(): array
    {
        return [
            // =============================================
            // NOTIFICAÇÕES EXISTENTES (ORIGINAIS)
            // =============================================
            'survey_response',
            'survey_approved',
            'survey_rejected',
            'survey_expiring',
            'survey_completed',
            'survey_published',
            'payment_received',
            'withdrawal_processed',
            'withdrawal_rejected',
            'low_balance',
            'research_reminder',
            'deadline_alert',
            'survey_available',
            'survey_invitation',
            'response_completed',
            'payment_credited',
            'profile_update',
            'qualification_approved',
            'bonus_received',
            'rank_improved',
            'weekly_summary',
            'referral_bonus',
            'new_user_registered',
            'survey_pending_review',
            'withdrawal_requested',
            'user_verification_pending',
            'system_alert',
            'batch_payment_processed',
            'low_system_funds',
            'abuse_reported',
            'high_activity',
            'system_maintenance',
            'new_feature',
            'policy_update',
            'security_alert',
            'holiday_schedule',
            'app_update',
            'general_announcement',
            'important_reminder',

            // =============================================
            // NOTIFICAÇÕES NOVAS - FLUXO DE USUÁRIO
            // =============================================
            'new_user_pending_approval',     // Admin: Novo usuário aguarda aprovação
            'user_approved',                  // Usuário: Cadastro aprovado

            // =============================================
            // NOTIFICAÇÕES NOVAS - FLUXO DE PESQUISA (ESTUDANTE)
            // =============================================
            'new_survey_from_student',        // Admin: Estudante criou nova pesquisa
            'survey_approved_for_payment',     // Estudante: Pesquisa aprovada (link pagamento)
            'payment_confirmed',               // Estudante: Pagamento confirmado
            'survey_response_received',        // Estudante: Participante respondeu
            'survey_goal_reached',             // Estudante: Atingiu meta de respostas
            'survey_closed',                    // Estudante: Pesquisa encerrada
            'survey_results_available',         // Estudante: Resultados disponíveis

            // =============================================
            // NOTIFICAÇÕES NOVAS - FLUXO DE PARTICIPAÇÃO (PARTICIPANTE)
            // =============================================
            'reward_received',                  // Participante: Ganhou recompensa
            'withdrawal_requested',              // Participante: Solicitou saque
            'withdrawal_completed',              // Participante: Saque processado
            'profile_incomplete',                 // Participante: Lembrete completar perfil
            'survey_reminder',                    // Participante: Lembrete de pesquisa

            // =============================================
            // NOTIFICAÇÕES NOVAS - FLUXO ADMINISTRATIVO
            // =============================================
            'withdrawal_pending',                // Admin: Saque aguardando aprovação
            'report_submitted',                   // Admin: Denúncia recebida
            'low_participants_alert',             // Admin: Poucos participantes ativos

            // =============================================
            // NOTIFICAÇÕES NOVAS - SISTEMA GERAL
            // =============================================
            'system_maintenance_scheduled',       // Todos: Manutenção programada
            'new_feature_available',               // Todos: Nova funcionalidade
            'terms_updated'                         // Todos: Termos atualizados
        ];
    }

    /**
     * Obter tipos categorizados por perfil de usuário
     * VERSÃO COMPLETA COM TODAS AS NOTIFICAÇÕES
     */
    private function getCategorizedTypes(): array
    {
        return [
            'student' => [
                // Originais
                'survey_response',
                'survey_approved',
                'survey_rejected',
                'survey_expiring',
                'survey_completed',
                'survey_published',
                'payment_received',
                'withdrawal_processed',
                'withdrawal_rejected',
                'low_balance',
                'research_reminder',
                'deadline_alert',

                // Novas
                'user_approved',
                'survey_approved_for_payment',
                'payment_confirmed',
                'survey_response_received',
                'survey_goal_reached',
                'survey_closed',
                'survey_results_available',
                'system_maintenance_scheduled',
                'new_feature_available',
                'terms_updated'
            ],

            'participant' => [
                // Originais
                'survey_available',
                'survey_invitation',
                'response_completed',
                'payment_credited',
                'profile_update',
                'qualification_approved',
                'bonus_received',
                'rank_improved',
                'weekly_summary',
                'referral_bonus',

                // Novas
                'user_approved',
                'reward_received',
                'withdrawal_requested',
                'withdrawal_completed',
                'profile_incomplete',
                'survey_reminder',
                'system_maintenance_scheduled',
                'new_feature_available',
                'terms_updated'
            ],

            'admin' => [
                // Originais
                'new_user_registered',
                'survey_pending_review',
                'withdrawal_requested',
                'user_verification_pending',
                'system_alert',
                'batch_payment_processed',
                'low_system_funds',
                'abuse_reported',
                'high_activity',

                // Novas
                'new_user_pending_approval',
                'new_survey_from_student',
                'payment_confirmed',
                'withdrawal_pending',
                'report_submitted',
                'low_participants_alert',
                'system_maintenance_scheduled',
                'new_feature_available',
                'terms_updated'
            ],

            'system' => [
                'system_maintenance',
                'new_feature',
                'policy_update',
                'security_alert',
                'holiday_schedule',
                'app_update',
                'general_announcement',
                'important_reminder'
            ]
        ];
    }

    /**
     * Criar nova notificação (para uso interno do sistema)
     */
    public function create(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Verificar se é admin
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso não autorizado. Apenas administradores podem criar notificações.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'type' => 'required|string',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'icon' => 'nullable|string',
                'action_url' => 'nullable|url',
                'action_label' => 'nullable|string|max:50',
                'data' => 'nullable|json',
                'priority' => 'nullable|integer|min:1|max:3',
                'expires_in_days' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Usar o método helper que verifica configurações
            $notification = $this->createNotificationForUser(
                $request->user_id,
                [
                    'type' => $request->type,
                    'title' => $request->title,
                    'message' => $request->message,
                    'icon' => $request->icon,
                    'action_url' => $request->action_url,
                    'action_label' => $request->action_label,
                    'data' => $request->data ? json_decode($request->data, true) : null,
                    'priority' => $request->priority ?? 1,
                    'expires_in_days' => $request->expires_in_days
                ]
            );

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível criar notificação. Usuário pode ter notificações desativadas.'
                ], 400);
            }

            // Enviar notificação em tempo real
            $this->sendRealTimeNotification($notification);

            return response()->json([
                'success' => true,
                'message' => 'Notificação criada com sucesso',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar notificação: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar notificação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notificação marcada como lida',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar notificação como lida: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificação como lida: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar múltiplas notificações como lidas
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:notifications,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IDs inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = Notification::whereIn('id', $request->ids)
                ->where('user_id', $user->id)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Notificações marcadas como lidas',
                'data' => ['updated_count' => $updated]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar múltiplas notificações como lidas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar notificações como lidas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Notification::where('user_id', $user->id)
                ->where('is_read', false);

            if ($request->filled('type')) {
                $query->byUserType($request->type);
            }

            $updated = $query->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Todas as notificações foram marcadas como lidas',
                'data' => ['updated_count' => $updated]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar todas as notificações como lidas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar todas as notificações como lidas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir notificação
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificação não encontrada'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificação excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir notificação: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir múltiplas notificações
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:notifications,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IDs inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deleted = Notification::whereIn('id', $request->ids)
                ->where('user_id', $user->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificações excluídas com sucesso',
                'data' => ['deleted_count' => $deleted]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir múltiplas notificações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar todas as notificações do usuário
     */
    public function clearAll(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Notification::where('user_id', $user->id);

            if ($request->filled('type')) {
                $query->byUserType($request->type);
            }

            if ($request->filled('read_status')) {
                if ($request->read_status === 'read') {
                    $query->where('is_read', true);
                } elseif ($request->read_status === 'unread') {
                    $query->where('is_read', false);
                }
            }

            $deleted = $query->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificações limpas com sucesso',
                'data' => ['deleted_count' => $deleted]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao limpar notificações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar notificações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpar notificações expiradas
     */
    public function clearExpired(): JsonResponse
    {
        try {
            $user = Auth::user();

            $deleted = Notification::where('user_id', $user->id)
                ->where('expires_at', '<=', now())
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificações expiradas limpas',
                'data' => ['deleted_count' => $deleted]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao limpar notificações expiradas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar notificações expiradas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar notificações não lidas (para polling/badge count)
     */
    public function unread(): JsonResponse
    {
        try {
            $user = Auth::user();

            $unreadNotifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->notExpired()
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->notExpired()
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Notificações não lidas',
                'data' => [
                    'notifications' => $unreadNotifications,
                    'count' => $unreadCount,
                    'high_priority_count' => $unreadNotifications->where('priority', 3)->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar notificações não lidas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar notificações não lidas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter configurações de notificações do usuário
     */
    public function getSettings(): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $profileInfo = $user->profile_info ?? [];
            $notificationSettings = $profileInfo['notification_settings'] ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Configurações de notificações',
                'data' => [
                    'email_notifications' => $user->email_notifications ?? true,
                    'whatsapp_notifications' => $user->whatsapp_notifications ?? false,
                    'push_notifications' => $notificationSettings['push_notifications'] ?? true,
                    'desktop_notifications' => $notificationSettings['desktop_notifications'] ?? true,
                    'app_notifications' => $notificationSettings['app_notifications'] ?? true,
                    'system_notifications' => $notificationSettings['system_notifications'] ?? true,
                    'mute_until' => $notificationSettings['mute_until'] ?? null,
                    'muted_types' => $notificationSettings['muted_types'] ?? [],
                    'profile_info' => $user->profile_info
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter configurações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar configurações de notificações do usuário
     */
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'email_notifications' => 'nullable|boolean',
                'whatsapp_notifications' => 'nullable|boolean',
                'push_notifications' => 'nullable|boolean',
                'desktop_notifications' => 'nullable|boolean',
                'app_notifications' => 'nullable|boolean',
                'system_notifications' => 'nullable|boolean',
                'mute_until' => 'nullable|date|after:now',
                'muted_types' => 'nullable|array',
                'muted_types.*' => 'string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configurações inválidas',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Atualizar campos diretos que existem no seu modelo
            $updates = [];

            if ($request->has('email_notifications')) {
                $updates['email_notifications'] = $request->email_notifications;
            }

            if ($request->has('whatsapp_notifications')) {
                $updates['whatsapp_notifications'] = $request->whatsapp_notifications;
            }

            // Outras configurações serão salvas no profile_info
            $profileInfo = $user->profile_info ?? [];

            // Configurações de notificações no profile_info
            $notificationSettings = $profileInfo['notification_settings'] ?? [];

            // Atualizar configurações extras
            if ($request->has('push_notifications')) {
                $notificationSettings['push_notifications'] = $request->push_notifications;
            }

            if ($request->has('desktop_notifications')) {
                $notificationSettings['desktop_notifications'] = $request->desktop_notifications;
            }

            if ($request->has('app_notifications')) {
                $notificationSettings['app_notifications'] = $request->app_notifications;
            }

            if ($request->has('system_notifications')) {
                $notificationSettings['system_notifications'] = $request->system_notifications;
            }

            if ($request->has('mute_until')) {
                $notificationSettings['mute_until'] = $request->mute_until;
            }

            if ($request->has('muted_types')) {
                $notificationSettings['muted_types'] = $request->muted_types;
            }

            // Salvar no profile_info
            if (!empty($notificationSettings)) {
                $profileInfo['notification_settings'] = $notificationSettings;
                $updates['profile_info'] = $profileInfo;
            }

            // Atualizar o usuário
            if (!empty($updates)) {
                $user->update($updates);
            }

            return response()->json([
                'success' => true,
                'message' => 'Configurações atualizadas com sucesso',
                'data' => [
                    'email_notifications' => $user->email_notifications,
                    'whatsapp_notifications' => $user->whatsapp_notifications,
                    'notification_settings' => $user->profile_info['notification_settings'] ?? []
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configurações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Obter estatísticas do usuário
     */
    private function getUserNotificationStats($userId): array
    {
        $total = Notification::where('user_id', $userId)->count();
        $unread = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->notExpired()
            ->count();

        $byType = Notification::where('user_id', $userId)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $byPriority = Notification::where('user_id', $userId)
            ->selectRaw('priority, count(*) as count')
            ->groupBy('priority')
            ->orderBy('priority', 'desc')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();

        $todayCount = Notification::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        $unreadHighPriority = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->where('priority', 3)
            ->notExpired()
            ->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'unread_percentage' => $total > 0 ? round(($unread / $total) * 100, 2) : 0,
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'today_count' => $todayCount,
            'unread_high_priority' => $unreadHighPriority,
            'last_7_days' => $this->getLast7DaysStats($userId)
        ];
    }

    /**
     * Helper: Estatísticas dos últimos 7 dias
     */
    private function getLast7DaysStats($userId): array
    {
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = Notification::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->count();
            $stats[$date] = $count;
        }

        return $stats;
    }

    /**
     * Helper: Enviar notificação em tempo real (WebSocket/Push)
     */
    private function sendRealTimeNotification(Notification $notification): void
    {
        try {
            // Implementar envio via WebSocket (Pusher, Socket.io, etc.)
            // ou Push Notifications (Firebase, APNS, etc.)
            // Esta é apenas uma estrutura de exemplo

            // Exemplo com Laravel Echo/Pusher
            // broadcast(new NewNotificationEvent($notification))->toOthers();

            // Exemplo com Firebase Cloud Messaging
            // if ($notification->user->fcm_token) {
            //     $this->sendPushNotification($notification);
            // }

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação em tempo real: ' . $e->getMessage());
        }
    }

    /**
     * Criar notificação verificando configurações do usuário
     * VERSÃO ATUALIZADA COM SUPORTE A notification_configs
     */
    private function createNotificationForUser($userId, $data): ?Notification
    {
        $user = User::find($userId);

        if (!$user) {
            return null;
        }

        // =============================================
        // BUSCAR CONFIGURAÇÃO DO TIPO (CORRIGIDO)
        // =============================================
        $config = null;
        if (isset($data['type'])) {
            $config = NotificationConfig::where('type', $data['type']) // <-- SEM O \
                ->where('is_active', true)
                ->first();
        }

        // Se existir configuração, usa os valores padrão
        if ($config) {
            // Se tiver dados para formatar, aplica as variáveis
            if (!empty($data['data'])) {
                $data['title'] = $config->formatTitle($data['data']);
                $data['message'] = $config->formatMessage($data['data']);
                $data['action_url'] = $config->formatUrl($data['data']);
                $data['icon'] = $config->icon;
                $data['action_label'] = $config->action_label;
                $data['priority'] = $config->priority;
            } else {
                // Usa os valores padrão sem formatação
                $data['title'] = $data['title'] ?? $config->title;
                $data['message'] = $data['message'] ?? $config->message;
                $data['icon'] = $data['icon'] ?? $config->icon;
                $data['action_label'] = $data['action_label'] ?? $config->action_label;
                $data['action_url'] = $data['action_url'] ?? $config->action_url;
                $data['priority'] = $data['priority'] ?? $config->priority;
            }

            // Verificar se o usuário pode receber este tipo
            $allowedRoles = $config->allowed_roles ?? [];
            if (!empty($allowedRoles) && !in_array($user->role, $allowedRoles)) {
                Log::info('Usuário não tem permissão para este tipo: ' . $user->role);
                return null;
            }

            // Definir data de expiração se configurada
            if ($config->expires_in_days && !isset($data['expires_in_days'])) {
                $data['expires_in_days'] = $config->expires_in_days;
            }
        }

        // =============================================
        // CONTINUA IGUAL (validações existentes)
        // =============================================

        // Verificar se notificação está silenciada
        if ($this->isNotificationMuted($user, $data['type'] ?? 'general_announcement')) {
            return null;
        }

        // Verificar se o usuário tem notificações da aplicação ativadas
        $profileInfo = $user->profile_info ?? [];
        $notificationSettings = $profileInfo['notification_settings'] ?? [];

        if (
            isset($notificationSettings['app_notifications']) &&
            !$notificationSettings['app_notifications'] &&
            !in_array($data['type'] ?? '', ['system_alert', 'security_alert', 'system_maintenance'])
        ) {
            return null;
        }

        // Verificar se é notificação do sistema (sempre permitida)
        $systemNotifications = ['system_alert', 'security_alert', 'system_maintenance', 'policy_update'];
        $isSystemNotification = in_array($data['type'] ?? '', $systemNotifications);

        if (!$isSystemNotification) {
            // Verificar configurações específicas por tipo
            switch ($data['type']) {
                case 'survey_response':
                case 'survey_approved':
                case 'survey_rejected':
                    if (!$user->email_notifications && !$user->whatsapp_notifications) {
                        return null;
                    }
                    break;

                case 'survey_available':
                case 'payment_credited':
                    if (!$notificationSettings['app_notifications'] ?? true) {
                        return null;
                    }
                    break;
            }
        }

        // =============================================
        // CRIAR A NOTIFICAÇÃO (igual)
        // =============================================
        return Notification::create([
            'user_id' => $userId,
            'type' => $data['type'] ?? 'general_announcement',
            'title' => $data['title'] ?? 'Notificação',
            'message' => $data['message'] ?? '',
            'icon' => $data['icon'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? null,
            'data' => $data['data'] ?? null,
            'priority' => $data['priority'] ?? 1,
            'expires_at' => isset($data['expires_in_days']) ? now()->addDays($data['expires_in_days']) : null
        ]);
    }

    /**
     * Verificar se notificação está silenciada para o usuário
     */
    private function isNotificationMuted($user, $notificationType): bool
    {
        $profileInfo = $user->profile_info ?? [];
        $notificationSettings = $profileInfo['notification_settings'] ?? [];
        $mutedTypes = $notificationSettings['muted_types'] ?? [];

        // Verificar mute_until
        if (isset($notificationSettings['mute_until'])) {
            $muteUntil = \Carbon\Carbon::parse($notificationSettings['mute_until']);
            if ($muteUntil->isFuture()) {
                return true; // Todas as notificações estão silenciadas
            }
        }

        // Verificar tipo específico silenciado
        if (in_array($notificationType, $mutedTypes)) {
            return true;
        }

        // Verificar se as notificações da aplicação estão desativadas
        if (
            isset($notificationSettings['app_notifications']) &&
            !$notificationSettings['app_notifications'] &&
            !in_array($notificationType, ['system_alert', 'security_alert', 'system_maintenance'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Enviar notificação para um usuário (método público para usar em outros controllers)
     */
    public function sendToUser($userId, $type, $data = [])
    {
        return $this->createNotificationForUser($userId, [
            'type' => $type,
            'data' => $data
        ]);
    }

    /**
     * Enviar notificação para múltiplos usuários
     */
    public function sendToMany($userIds, $type, $data = [])
    {
        $results = [];
        foreach ($userIds as $userId) {
            $results[] = $this->sendToUser($userId, $type, $data);
        }
        return $results;
    }

    /**
     * Enviar notificação para todos de uma role
     */
    public function sendToRole($role, $type, $data = [])
    {
        $users = User::where('role', $role)
            ->where('status', 'active')
            ->get();

        return $this->sendToMany($users->pluck('id')->toArray(), $type, $data);
    }
}
