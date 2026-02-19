<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationConfig;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationConfigController extends Controller
{
    // ==============================================
    // CONFIGURAÇÕES GLOBAIS (APENAS ADMIN)
    // ==============================================

    /**
     * Listar todas as configurações de notificação
     * GET /api/notification-configs
     */
    public function index(Request $request): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $query = NotificationConfig::query();

            // Filtros
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('is_system')) {
                $query->where('is_system', $request->boolean('is_system'));
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->has('role')) {
                $role = $request->role;
                $query->whereJsonContains('allowed_roles', $role);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'type');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 20);
            $configs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Configurações carregadas',
                'data' => $configs->items(),
                'meta' => [
                    'current_page' => $configs->currentPage(),
                    'last_page' => $configs->lastPage(),
                    'per_page' => $configs->perPage(),
                    'total' => $configs->total(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar configurações: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações'
            ], 500);
        }
    }

    /**
     * Buscar configuração por ID
     * GET /api/notification-configs/{id}
     */
    public function show(int $id): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $config = NotificationConfig::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $config
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar configuração: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar configuração'
            ], 500);
        }
    }

    /**
     * Criar nova configuração
     * POST /api/notification-configs
     */
    public function store(Request $request): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|unique:notification_configs,type',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'icon' => 'nullable|string|max:50',
                'action_label' => 'nullable|string|max:100',
                'action_url' => 'nullable|string|max:500',
                'priority' => 'nullable|integer|min:1|max:3',
                'expires_in_days' => 'nullable|integer|min:1',
                'allowed_roles' => 'nullable|array',
                'allowed_roles.*' => 'in:admin,student,participant',
                'is_system' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = NotificationConfig::create([
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'icon' => $request->icon,
                'action_label' => $request->action_label,
                'action_url' => $request->action_url,
                'priority' => $request->priority ?? 2,
                'expires_in_days' => $request->expires_in_days,
                'allowed_roles' => $request->allowed_roles ?? ['admin', 'student', 'participant'],
                'is_system' => $request->boolean('is_system', false),
                'is_active' => $request->boolean('is_active', true),
            ]);

            Log::info('Nova configuração criada', [
                'user_id' => Auth::id(),
                'config_id' => $config->id,
                'type' => $config->type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuração criada com sucesso',
                'data' => $config
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar configuração: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar configuração'
            ], 500);
        }
    }

    /**
     * Atualizar configuração
     * PUT /api/notification-configs/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $config = NotificationConfig::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'message' => 'sometimes|string',
                'icon' => 'nullable|string|max:50',
                'action_label' => 'nullable|string|max:100',
                'action_url' => 'nullable|string|max:500',
                'priority' => 'nullable|integer|min:1|max:3',
                'expires_in_days' => 'nullable|integer|min:1',
                'allowed_roles' => 'nullable|array',
                'allowed_roles.*' => 'in:admin,student,participant',
                'is_system' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config->update($request->only([
                'title',
                'message',
                'icon',
                'action_label',
                'action_url',
                'priority',
                'expires_in_days',
                'allowed_roles',
                'is_system',
                'is_active'
            ]));

            Log::info('Configuração atualizada', [
                'user_id' => Auth::id(),
                'config_id' => $config->id,
                'type' => $config->type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuração atualizada com sucesso',
                'data' => $config
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configuração: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configuração'
            ], 500);
        }
    }

    /**
     * Excluir configuração
     * DELETE /api/notification-configs/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $config = NotificationConfig::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            // Impedir exclusão de tipos críticos do sistema
            if ($config->is_system && $config->type === 'system_alert') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir esta configuração de sistema'
                ], 403);
            }

            $config->delete();

            Log::info('Configuração excluída', [
                'user_id' => Auth::id(),
                'config_id' => $id,
                'type' => $config->type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuração excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir configuração: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir configuração'
            ], 500);
        }
    }

    /**
     * Duplicar configuração
     * POST /api/notification-configs/{id}/duplicate
     */
    public function duplicate(int $id): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $config = NotificationConfig::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            $newConfig = $config->replicate();
            $newConfig->type = $config->type . '_copy_' . uniqid();
            $newConfig->title = $config->title . ' (cópia)';
            $newConfig->is_active = false; // Cópia começa inativa
            $newConfig->save();

            Log::info('Configuração duplicada', [
                'user_id' => Auth::id(),
                'original_id' => $id,
                'new_id' => $newConfig->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configuração duplicada com sucesso',
                'data' => $newConfig
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao duplicar configuração: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar configuração'
            ], 500);
        }
    }

    /**
     * Ativar/Desativar configuração
     * PATCH /api/notification-configs/{id}/toggle
     */
    public function toggle(int $id): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $config = NotificationConfig::find($id);

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }

            $config->is_active = !$config->is_active;
            $config->save();

            return response()->json([
                'success' => true,
                'message' => $config->is_active ? 'Configuração ativada' : 'Configuração desativada',
                'data' => ['is_active' => $config->is_active]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status'
            ], 500);
        }
    }

    /**
     * Listar tipos disponíveis
     * GET /api/notification-configs/types
     */
    public function getTypes(): JsonResponse
    {
        // Verificação de autenticação
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado'
            ], 401);
        }

        try {
            $types = NotificationConfig::select('type')
                ->orderBy('type')
                ->pluck('type')
                ->toArray();

            // Adicionar categorização
            $categorized = [
                'user' => array_values(array_filter($types, fn($t) => str_contains($t, 'user') || str_contains($t, 'pending') || str_contains($t, 'approved'))),
                'survey' => array_values(array_filter($types, fn($t) => str_contains($t, 'survey'))),
                'payment' => array_values(array_filter($types, fn($t) => str_contains($t, 'payment') || str_contains($t, 'withdrawal'))),
                'system' => array_values(array_filter($types, fn($t) => str_contains($t, 'system') || str_contains($t, 'maintenance'))),
                'other' => array_values(array_filter($types, fn($t) => !str_contains($t, 'user') && !str_contains($t, 'survey') && !str_contains($t, 'payment') && !str_contains($t, 'system'))),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'all' => $types,
                    'categorized' => $categorized,
                    'count' => count($types)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar tipos'
            ], 500);
        }
    }

    /**
     * Obter estatísticas das configurações
     * GET /api/notification-configs/stats
     */
    public function getStats(): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $stats = [
                'total' => NotificationConfig::count(),
                'active' => NotificationConfig::where('is_active', true)->count(),
                'inactive' => NotificationConfig::where('is_active', false)->count(),
                'system' => NotificationConfig::where('is_system', true)->count(),
                'by_priority' => [
                    'high' => NotificationConfig::where('priority', 3)->count(),
                    'medium' => NotificationConfig::where('priority', 2)->count(),
                    'low' => NotificationConfig::where('priority', 1)->count(),
                ],
                'by_role' => [
                    'admin' => NotificationConfig::whereJsonContains('allowed_roles', 'admin')->count(),
                    'student' => NotificationConfig::whereJsonContains('allowed_roles', 'student')->count(),
                    'participant' => NotificationConfig::whereJsonContains('allowed_roles', 'participant')->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas'
            ], 500);
        }
    }

    // ==============================================
    // PREFERÊNCIAS DO USUÁRIO (CADA PERFIL)
    // ==============================================

    /**
     * Obter preferências do usuário atual
     * GET /api/notification-configs/user-preferences
     */
    public function getUserPreferences(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Buscar do profile_info
            $profileInfo = $user->profile_info ?? [];
            $preferences = $profileInfo['notification_preferences'] ?? $this->getDefaultPreferencesByRole($user->role);

            return response()->json([
                'success' => true,
                'data' => $preferences
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar preferências: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar preferências'
            ], 500);
        }
    }

    /**
     * Atualizar preferências do usuário atual
     * PUT /api/notification-configs/user-preferences
     */
    public function updateUserPreferences(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'nullable|boolean',
                'whatsapp' => 'nullable|boolean',
                'push' => 'nullable|boolean',
                'sms' => 'nullable|boolean',
                'selected_types' => 'nullable|array',
                'selected_types.*' => 'string',
                'frequency' => 'nullable|in:all,daily,weekly',
                'mute_until' => 'nullable|date|after:now',
                'muted_types' => 'nullable|array',
                'muted_types.*' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar profile_info atual
            $profileInfo = $user->profile_info ?? [];

            // Mesclar preferências
            $currentPreferences = $profileInfo['notification_preferences'] ?? $this->getDefaultPreferencesByRole($user->role);
            $newPreferences = array_merge($currentPreferences, $request->all());

            // Atualizar profile_info
            $profileInfo['notification_preferences'] = $newPreferences;
            $user->profile_info = $profileInfo;
            $user->save();

            Log::info('Preferências atualizadas', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Preferências atualizadas com sucesso',
                'data' => $newPreferences
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar preferências: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar preferências'
            ], 500);
        }
    }

    /**
     * Obter preferências padrão por role
     */
    private function getDefaultPreferencesByRole(string $role): array
    {
        $base = [
            'email' => true,
            'whatsapp' => false,
            'push' => true,
            'sms' => false,
            'selected_types' => [],
            'frequency' => 'all',
            'mute_until' => null,
            'muted_types' => [],
        ];

        // Ajustes por role
        if ($role === 'participant') {
            $base['whatsapp'] = true;
            $base['sms'] = false;
        }

        return $base;
    }

    /**
     * Obter configurações gerais (idioma, formato data, etc)
     * GET /api/notification-configs/general-settings
     */
    public function getGeneralSettings(): JsonResponse
    {
        try {
            // Buscar do banco ou usar padrões
            $settings = Cache::remember('notification_general_settings', 3600, function () {
                return DB::table('system_settings')
                    ->whereIn('key', [
                        'notification_language',
                        'notification_date_format',
                        'notification_batch_enabled',
                        'notification_batch_interval',
                        'notification_silent_start',
                        'notification_silent_end',
                        'notification_retention_days',
                        'notification_cleanup_policy'
                    ])
                    ->pluck('value', 'key')
                    ->toArray();
            });

            $defaults = [
                'language' => 'pt',
                'date_format' => 'pt',
                'batch_enabled' => false,
                'batch_interval' => 30,
                'silent_start' => '22:00',
                'silent_end' => '07:00',
                'retention_days' => 30,
                'cleanup_policy' => 'auto',
            ];

            $result = [];
            foreach ($defaults as $key => $default) {
                $dbKey = 'notification_' . $key;
                $result[$key] = $settings[$dbKey] ?? $default;
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar configurações gerais: ' . $e->getMessage());

            // Fallback para valores padrão
            return response()->json([
                'success' => true,
                'data' => [
                    'language' => 'pt',
                    'date_format' => 'pt',
                    'batch_enabled' => false,
                    'batch_interval' => 30,
                    'silent_start' => '22:00',
                    'silent_end' => '07:00',
                    'retention_days' => 30,
                    'cleanup_policy' => 'auto',
                ]
            ]);
        }
    }

    /**
     * Atualizar configurações gerais
     * PUT /api/notification-configs/general-settings
     */
    public function updateGeneralSettings(Request $request): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $validator = Validator::make($request->all(), [
                'language' => 'nullable|in:pt,en,fr',
                'date_format' => 'nullable|in:pt,en,iso',
                'batch_enabled' => 'nullable|boolean',
                'batch_interval' => 'nullable|integer|min:5|max:1440',
                'silent_start' => 'nullable|date_format:H:i',
                'silent_end' => 'nullable|date_format:H:i',
                'retention_days' => 'nullable|integer|min:1|max:365',
                'cleanup_policy' => 'nullable|in:auto,manual,never',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            foreach ($request->all() as $key => $value) {
                $dbKey = 'notification_' . $key;

                DB::table('system_settings')->updateOrInsert(
                    ['key' => $dbKey],
                    ['value' => is_bool($value) ? ($value ? '1' : '0') : $value, 'updated_at' => now()]
                );
            }

            // Limpar cache
            Cache::forget('notification_general_settings');

            DB::commit();

            Log::info('Configurações gerais atualizadas', [
                'user_id' => Auth::id(),
                'settings' => $request->all()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Configurações gerais atualizadas com sucesso',
                'data' => $request->all()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar configurações gerais: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configurações'
            ], 500);
        }
    }

    /**
     * Executar limpeza manual de notificações antigas
     * POST /api/notification-configs/cleanup
     */
    public function cleanup(Request $request): JsonResponse
    {
        // Verificação de admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado'
            ], 403);
        }

        try {
            $days = $request->get('days', 30);

            $deleted = DB::table('notifications')
                ->where('created_at', '<', now()->subDays($days))
                ->where('is_read', true)
                ->delete();

            Log::info('Limpeza manual executada', [
                'user_id' => Auth::id(),
                'days' => $days,
                'deleted' => $deleted
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} notificações antigas removidas",
                'data' => ['deleted' => $deleted]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro na limpeza: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar limpeza'
            ], 500);
        }
    }
}
