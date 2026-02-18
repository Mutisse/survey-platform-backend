<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Survey;

class SystemMonitorController extends Controller
{
    /**
     * Construtor - apenas admin pode acessar
     */
    public function __construct()
    {
        // Middleware aplicado nas rotas
    }

    /**
     * Dashboard principal do sistema
     */
    public function dashboard()
    {
        try {
            $data = [
                'system' => $this->getSystemInfo(),
                'database' => $this->getDatabaseStats(),
                'users' => $this->getUserStats(),
                'surveys' => $this->getSurveyStats(),
                'performance' => $this->getPerformanceMetrics(),
                'recent_activity' => $this->getRecentActivity(),
                'alerts' => $this->getSystemAlerts(),
                'log_stats' => $this->getLogStats(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Dashboard carregado com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no dashboard do sistema: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estatísticas de logs - DIRETO DO BANCO
     */
    private function getLogStats()
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('activity_logs')) {
                return [
                    'total_logs' => 0,
                    'by_level' => [],
                    'by_action' => [],
                    'by_log_name' => [],
                    'by_device' => [],
                    'by_country' => [],
                    'today' => 0,
                    'this_week' => 0,
                    'errors_today' => 0,
                ];
            }

            return [
                'total_logs' => DB::table('activity_logs')->count(),
                'by_level' => DB::table('activity_logs')
                    ->select('level_system', DB::raw('count(*) as total'))
                    ->groupBy('level_system')
                    ->get(),
                'by_action' => DB::table('activity_logs')
                    ->select('action', DB::raw('count(*) as total'))
                    ->groupBy('action')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                'by_log_name' => DB::table('activity_logs')
                    ->select('log_name', DB::raw('count(*) as total'))
                    ->groupBy('log_name')
                    ->get(),
                'by_device' => DB::table('activity_logs')
                    ->select('device_type', DB::raw('count(*) as total'))
                    ->whereNotNull('device_type')
                    ->groupBy('device_type')
                    ->get(),
                'by_country' => DB::table('activity_logs')
                    ->select('country', DB::raw('count(*) as total'))
                    ->whereNotNull('country')
                    ->groupBy('country')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                'today' => DB::table('activity_logs')
                    ->whereDate('created_at', today())
                    ->count(),
                'this_week' => DB::table('activity_logs')
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'errors_today' => DB::table('activity_logs')
                    ->whereDate('created_at', today())
                    ->whereIn('level_system', ['error', 'critical'])
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas de logs: ' . $e->getMessage());
            return [
                'total_logs' => 0,
                'by_level' => [],
                'by_action' => [],
                'by_log_name' => [],
                'by_device' => [],
                'by_country' => [],
                'today' => 0,
                'this_week' => 0,
                'errors_today' => 0,
            ];
        }
    }

    /**
     * Informações do sistema
     */
    private function getSystemInfo()
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'environment' => app()->environment(),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => app()->getLocale(),
            'debug_mode' => config('app.debug'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'server_name' => gethostname(),
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
            'disk_usage' => $this->getDiskUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'uptime' => $this->getServerUptime(),
            'last_cron_run' => $this->getLastCronRun(),
        ];
    }

    /**
     * Estatísticas do banco de dados - VERSÃO CORRIGIDA PARA TiDB
     */
    private function getDatabaseStats()
    {
        try {
            $connection = DB::connection();
            $databaseName = $connection->getDatabaseName();

            // 1. Tamanho do banco (funciona no TiDB)
            $size = DB::select("
                SELECT
                    SUM(data_length + index_length) / 1024 / 1024 as size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$databaseName]);

            // 2. Listar tabelas (sem table_rows que não existe no TiDB)
            $tables = DB::select("
                SELECT
                    table_name
                FROM information_schema.tables
                WHERE table_schema = ?
                ORDER BY table_name
            ", [$databaseName]);

            // 3. Contar registros de cada tabela diretamente
            $tablesWithRows = [];
            foreach ($tables as $table) {
                try {
                    // Pular tabelas do sistema que podem causar erro
                    if (in_array($table->table_name, ['cache', 'cache_locks', 'sessions', 'jobs', 'failed_jobs', 'migrations'])) {
                        $tablesWithRows[] = (object)[
                            'table_name' => $table->table_name,
                            'rows' => 0,
                            'size_mb' => 0
                        ];
                        continue;
                    }

                    $count = DB::table($table->table_name)->count();
                    $tablesWithRows[] = (object)[
                        'table_name' => $table->table_name,
                        'rows' => $count,
                        'size_mb' => 0 // TiDB não fornece tamanho por tabela
                    ];
                } catch (\Exception $e) {
                    // Tabela pode não ter contagem permitida
                    $tablesWithRows[] = (object)[
                        'table_name' => $table->table_name,
                        'rows' => 0,
                        'size_mb' => 0
                    ];
                }
            }

            return [
                'connection' => $connection->getName(),
                'database' => $databaseName,
                'size_mb' => round(($size[0]->size_mb ?? 0), 2),
                'active_connections' => 0,
                'slow_queries' => 0,
                'tables_count' => count($tablesWithRows),
                'tables' => $tablesWithRows,
                'status' => 'healthy',
            ];

        } catch (\Exception $e) {
            Log::error('Erro no banco TiDB: ' . $e->getMessage());

            return [
                'error' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage(),
                'status' => 'error',
                'size_mb' => 0,
                'active_connections' => 0,
                'slow_queries' => 0,
                'tables_count' => $this->getFallbackTablesCount(),
                'tables' => $this->getFallbackTables(),
            ];
        }
    }

    /**
     * Função auxiliar para fallback - lista tabelas principais com contagem
     */
    private function getFallbackTables()
    {
        $tables = [];
        $tableNames = [
            'users', 'surveys', 'survey_responses', 'activity_logs',
            'payments', 'student_documents', 'notifications', 'universities',
            'survey_categories', 'transactions', 'withdrawal_requests',
            'academic_configurations', 'survey_questions', 'survey_images',
            'survey_exports', 'survey_stats', 'student_stats', 'participant_stats',
            'report_histories', 'report_templates', 'personal_access_tokens'
        ];

        foreach ($tableNames as $tableName) {
            try {
                if (DB::getSchemaBuilder()->hasTable($tableName)) {
                    $count = DB::table($tableName)->count();
                    $tables[] = (object)[
                        'table_name' => $tableName,
                        'rows' => $count,
                        'size_mb' => 0
                    ];
                }
            } catch (\Exception $e) {
                // Ignorar tabelas com erro
            }
        }

        return $tables;
    }

    /**
     * Contar tabelas no fallback
     */
    private function getFallbackTablesCount()
    {
        return count($this->getFallbackTables());
    }

    /**
     * Estatísticas de usuários - APENAS DADOS REAIS
     */
    private function getUserStats()
    {
        try {
            $total = User::count();

            $active = User::whereNotNull('email_verified_at')->count();
            $inactive = User::whereNull('email_verified_at')->count();
            $blocked = User::onlyTrashed()->count();

            $byRole = User::select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->get()
                ->pluck('total', 'role')
                ->toArray();

            $newToday = User::whereDate('created_at', today())->count();
            $newThisWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
            $newThisMonth = User::whereMonth('created_at', now()->month)->count();

            $verified = User::whereNotNull('email_verified_at')->count();

            $withDocuments = 0;
            if (DB::getSchemaBuilder()->hasTable('student_documents')) {
                $withDocuments = DB::table('student_documents')->distinct('user_id')->count('user_id');
            }

            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'blocked' => $blocked,
                'by_role' => $byRole,
                'new_today' => $newToday,
                'new_this_week' => $newThisWeek,
                'new_this_month' => $newThisMonth,
                'verified' => $verified,
                'with_documents' => $withDocuments,
                'verification_rate' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas de usuários: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'blocked' => 0,
                'by_role' => [],
                'new_today' => 0,
                'new_this_week' => 0,
                'new_this_month' => 0,
                'verified' => 0,
                'with_documents' => 0,
                'verification_rate' => 0,
            ];
        }
    }

    /**
     * Estatísticas de pesquisas - APENAS DADOS REAIS
     */
    private function getSurveyStats()
    {
        try {
            $total = Survey::count();
            $active = Survey::where('status', 'active')->count();
            $draft = Survey::where('status', 'draft')->count();
            $completed = Survey::where('status', 'completed')->count();
            $archived = Survey::where('status', 'archived')->count();

            $totalResponses = 0;
            $completedResponses = 0;
            $inProgress = 0;
            $avgCompletionTime = 0;

            if (DB::getSchemaBuilder()->hasTable('survey_responses')) {
                $totalResponses = DB::table('survey_responses')->count();
                $completedResponses = DB::table('survey_responses')->where('status', 'completed')->count();
                $inProgress = DB::table('survey_responses')->where('status', 'in_progress')->count();

                $avgCompletionTime = DB::table('survey_responses')
                    ->whereNotNull('completion_time')
                    ->avg('completion_time');
            }

            $totalRewards = Survey::sum('reward') ?? 0;

            $totalPaid = 0;
            if (DB::getSchemaBuilder()->hasTable('payments')) {
                $totalPaid = DB::table('payments')->where('status', 'completed')->sum('amount') ?? 0;
            }

            return [
                'total_surveys' => $total,
                'active_surveys' => $active,
                'draft_surveys' => $draft,
                'completed_surveys' => $completed,
                'archived_surveys' => $archived,
                'total_responses' => $totalResponses,
                'completed_responses' => $completedResponses,
                'in_progress_responses' => $inProgress,
                'completion_rate' => $totalResponses > 0 ? round(($completedResponses / $totalResponses) * 100, 2) : 0,
                'avg_completion_time_seconds' => round($avgCompletionTime ?? 0, 2),
                'total_rewards_mzn' => round($totalRewards, 2),
                'total_paid_mzn' => round($totalPaid, 2),
                'pending_payments' => round($totalRewards - $totalPaid, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas de pesquisas: ' . $e->getMessage());
            return [
                'total_surveys' => 0,
                'active_surveys' => 0,
                'draft_surveys' => 0,
                'completed_surveys' => 0,
                'archived_surveys' => 0,
                'total_responses' => 0,
                'completed_responses' => 0,
                'in_progress_responses' => 0,
                'completion_rate' => 0,
                'avg_completion_time_seconds' => 0,
                'total_rewards_mzn' => 0,
                'total_paid_mzn' => 0,
                'pending_payments' => 0,
            ];
        }
    }

    /**
     * Métricas de performance - APENAS DADOS REAIS
     */
    private function getPerformanceMetrics()
    {
        try {
            $cacheHits = Cache::get('cache_hits', 0);
            $cacheMisses = Cache::get('cache_misses', 0);
            $cacheTotal = $cacheHits + $cacheMisses;

            $responseTimes = 0;
            $requestsLastHour = 0;

            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                $responseTimes = DB::table('activity_logs')
                    ->whereNotNull('duration_ms')
                    ->where('created_at', '>=', now()->subDay())
                    ->avg('duration_ms');

                $requestsLastHour = DB::table('activity_logs')
                    ->where('created_at', '>=', now()->subHour())
                    ->count();
            }

            return [
                'cache' => [
                    'hits' => $cacheHits,
                    'misses' => $cacheMisses,
                    'hit_rate' => $cacheTotal > 0 ? round(($cacheHits / $cacheTotal) * 100, 2) : 0,
                ],
                'avg_response_time_ms' => round($responseTimes ?? 0, 2),
                'requests_per_minute' => round($requestsLastHour / 60, 2),
                'requests_last_hour' => $requestsLastHour,
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
                'current_memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter métricas de performance: ' . $e->getMessage());
            return [
                'cache' => [
                    'hits' => 0,
                    'misses' => 0,
                    'hit_rate' => 0,
                ],
                'avg_response_time_ms' => 0,
                'requests_per_minute' => 0,
                'requests_last_hour' => 0,
                'peak_memory_mb' => 0,
                'current_memory_mb' => 0,
            ];
        }
    }

    /**
     * Atividade recente - APENAS DADOS REAIS
     */
    private function getRecentActivity()
    {
        try {
            $logs = [];
            $errors = [];

            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                $logs = DB::table('activity_logs')
                    ->select('id', 'user_id', 'action', 'level_system', 'description', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();

                $errors = DB::table('activity_logs')
                    ->whereIn('level_system', ['error', 'critical'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            $newUsers = User::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'email', 'role', 'created_at']);
            $newSurveys = Survey::orderBy('created_at', 'desc')->limit(5)->get(['id', 'title', 'user_id', 'status', 'created_at']);

            return [
                'logs' => $logs,
                'errors' => $errors,
                'new_users' => $newUsers,
                'new_surveys' => $newSurveys,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter atividade recente: ' . $e->getMessage());
            return [
                'logs' => [],
                'errors' => [],
                'new_users' => [],
                'new_surveys' => [],
            ];
        }
    }

    /**
     * Alertas do sistema - BASEADO EM DADOS REAIS
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        try {
            $diskUsage = $this->getDiskUsage();
            if ($diskUsage['free_gb'] < 5) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Espaço em disco baixo',
                    'message' => "Apenas {$diskUsage['free_gb']}GB disponíveis",
                    'action' => 'Liberar espaço'
                ];
            }

            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                $recentErrors = DB::table('activity_logs')
                    ->whereIn('level_system', ['error', 'critical'])
                    ->where('created_at', '>=', now()->subHour())
                    ->count();

                if ($recentErrors > 10) {
                    $alerts[] = [
                        'type' => 'critical',
                        'title' => 'Muitos erros no sistema',
                        'message' => "{$recentErrors} erros na última hora",
                        'action' => 'Verificar logs'
                    ];
                }
            }

            if (!$this->checkDatabaseConnection()) {
                $alerts[] = [
                    'type' => 'critical',
                    'title' => 'Problema na conexão com banco',
                    'message' => 'Banco de dados inacessível',
                    'action' => 'Verificar serviço MySQL'
                ];
            }

        } catch (\Exception $e) {
            Log::warning('Erro ao gerar alertas: ' . $e->getMessage());
        }

        return $alerts;
    }

    /**
     * Verificar saúde do banco de dados
     */
    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obter uso de disco
     */
    private function getDiskUsage()
    {
        try {
            $path = storage_path();
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;

            return [
                'total_gb' => round($total / 1024 / 1024 / 1024, 2),
                'used_gb' => round($used / 1024 / 1024 / 1024, 2),
                'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                'used_percent' => round(($used / $total) * 100, 2),
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter uso de disco: ' . $e->getMessage());
            return [
                'total_gb' => 0,
                'used_gb' => 0,
                'free_gb' => 0,
                'used_percent' => 0,
            ];
        }
    }

    /**
     * Obter uso de memória
     */
    private function getMemoryUsage()
    {
        try {
            return [
                'total_mb' => 0,
                'used_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'free_mb' => 0,
                'used_percent' => 0,
            ];
        } catch (\Exception $e) {
            return [
                'total_mb' => 0,
                'used_mb' => 0,
                'free_mb' => 0,
                'used_percent' => 0,
            ];
        }
    }

    /**
     * Obter tempo de atividade do servidor
     */
    private function getServerUptime()
    {
        return 'N/A';
    }

    /**
     * Obter última execução do cron
     */
    private function getLastCronRun()
    {
        try {
            $lastRun = Cache::get('last_cron_run');
            if ($lastRun) {
                return [
                    'time' => $lastRun,
                    'ago' => now()->diffForHumans($lastRun),
                ];
            }
        } catch (\Exception $e) {
            // Ignorar
        }
        return null;
    }

    /**
     * Testar todas as conexões
     */
    public function healthCheck()
    {
        try {
            $checks = [
                'database' => $this->checkDatabaseConnection(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'mail' => $this->checkMail(),
                'queue' => $this->checkQueue(),
            ];

            $allHealthy = !in_array(false, $checks, true);

            return response()->json([
                'success' => true,
                'status' => $allHealthy ? 'healthy' : 'degraded',
                'checks' => $checks,
                'timestamp' => now(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no health check',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar cache
     */
    private function checkCache()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            return Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar storage
     */
    private function checkStorage()
    {
        try {
            $testFile = 'health_check_' . uniqid() . '.txt';
            Storage::disk('local')->put($testFile, 'ok');
            $exists = Storage::disk('local')->exists($testFile);
            Storage::disk('local')->delete($testFile);
            return $exists;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar mail
     */
    private function checkMail()
    {
        return config('mail.default') !== 'log';
    }

    /**
     * Verificar queue
     */
    private function checkQueue()
    {
        return config('queue.default') !== 'sync';
    }
}
