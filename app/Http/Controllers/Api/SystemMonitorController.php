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
     * Estatísticas do banco de dados
     */
    private function getDatabaseStats()
    {
        try {
            $connection = DB::connection();
            $databaseName = $connection->getDatabaseName();

            // Tamanho do banco
            $size = DB::select("
                SELECT
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$databaseName]);

            // Conexões ativas
            $connections = DB::select("SHOW STATUS LIKE 'Threads_connected'");

            // Queries lentas
            $slowQueries = DB::select("SHOW GLOBAL STATUS LIKE 'Slow_queries'");

            // Tabelas e registros
            $tables = DB::select("
                SELECT
                    table_name,
                    table_rows as rows,
                    ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
                ORDER BY size_mb DESC
            ", [$databaseName]);

            return [
                'connection' => $connection->getName(),
                'database' => $databaseName,
                'size_mb' => $size[0]->size_mb ?? 0,
                'active_connections' => $connections[0]->Value ?? 0,
                'slow_queries' => $slowQueries[0]->Value ?? 0,
                'tables_count' => count($tables),
                'tables' => $tables,
                'status' => $this->checkDatabaseConnection(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Erro ao obter estatísticas do banco: ' . $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Estatísticas de usuários
     */
    private function getUserStats()
    {
        $total = User::count();
        $active = User::where('status', 'active')->count();
        $inactive = User::where('status', 'inactive')->count();
        $blocked = User::where('status', 'blocked')->count();

        $byRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get()
            ->pluck('total', 'role')
            ->toArray();

        $newToday = User::whereDate('created_at', today())->count();
        $newThisWeek = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $newThisMonth = User::whereMonth('created_at', now()->month)->count();

        $verified = User::whereNotNull('email_verified_at')->count();
        $withDocuments = DB::table('student_documents')->distinct('user_id')->count('user_id');

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
    }

    /**
     * Estatísticas de pesquisas
     */
    private function getSurveyStats()
    {
        $total = Survey::count();
        $active = Survey::where('status', 'active')->count();
        $draft = Survey::where('status', 'draft')->count();
        $completed = Survey::where('status', 'completed')->count();
        $archived = Survey::where('status', 'archived')->count();

        $totalResponses = DB::table('survey_responses')->count();
        $completedResponses = DB::table('survey_responses')->where('status', 'completed')->count();
        $inProgress = DB::table('survey_responses')->where('status', 'in_progress')->count();

        $avgCompletionTime = DB::table('survey_responses')
            ->whereNotNull('completion_time')
            ->avg('completion_time');

        $totalRewards = Survey::sum('reward');
        $totalPaid = DB::table('payments')->where('status', 'completed')->sum('amount');

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
    }

    /**
     * Métricas de performance
     */
    private function getPerformanceMetrics()
    {
        // Cache stats
        $cacheHits = Cache::get('cache_hits', 0);
        $cacheMisses = Cache::get('cache_misses', 0);
        $cacheTotal = $cacheHits + $cacheMisses;

        // Response times (últimas 100 requisições dos logs)
        $responseTimes = DB::table('activity_logs')
            ->whereNotNull('duration_ms')
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->avg('duration_ms');

        // Requests por minuto (estimado)
        $requestsLastHour = DB::table('activity_logs')
            ->where('created_at', '>=', now()->subHour())
            ->count();

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
    }

    /**
     * Atividade recente
     */
    private function getRecentActivity()
    {
        return [
            'logs' => DB::table('activity_logs')
                ->select('id', 'user_id', 'action', 'level_system', 'description', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(),
            'errors' => DB::table('activity_logs')
                ->whereIn('level_system', ['error', 'critical'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'new_users' => User::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'email', 'role', 'created_at']),
            'new_surveys' => Survey::orderBy('created_at', 'desc')->limit(5)->get(['id', 'title', 'user_id', 'status', 'created_at']),
        ];
    }

    /**
     * Alertas do sistema
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        // Verificar espaço em disco
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage['free_gb'] < 5) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Espaço em disco baixo',
                'message' => "Apenas {$diskUsage['free_gb']}GB disponíveis",
                'action' => 'Liberar espaço imediatamente'
            ];
        } elseif ($diskUsage['free_gb'] < 10) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Espaço em disco reduzido',
                'message' => "{$diskUsage['free_gb']}GB disponíveis",
                'action' => 'Considerar limpeza de arquivos'
            ];
        }

        // Verificar conexão com banco
        if (!$this->checkDatabaseConnection()) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Problema na conexão com banco',
                'message' => 'Banco de dados pode estar inacessível',
                'action' => 'Verificar serviço do MySQL'
            ];
        }

        // Verificar fila de jobs
        $pendingJobs = DB::table('jobs')->count();
        if ($pendingJobs > 100) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Fila de jobs acumulada',
                'message' => "{$pendingJobs} jobs pendentes",
                'action' => 'Verificar worker queue'
            ];
        }

        // Verificar erros recentes
        $recentErrors = DB::table('activity_logs')
            ->where('level_system', 'critical')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentErrors > 5) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Muitos erros críticos',
                'message' => "{$recentErrors} erros na última hora",
                'action' => 'Revisar logs de erro'
            ];
        }

        // Verificar cache
        if (Cache::get('cache_test') !== 'ok') {
            Cache::put('cache_test', 'ok', 60);
            if (Cache::get('cache_test') !== 'ok') {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Cache não está funcionando',
                    'message' => 'Sistema de cache pode estar inoperante',
                    'action' => 'Verificar configuração do cache'
                ];
            }
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
    }

    /**
     * Obter uso de memória
     */
    private function getMemoryUsage()
    {
        $memInfo = file_get_contents('/proc/meminfo');
        if ($memInfo) {
            preg_match('/MemTotal:\s+(\d+)/', $memInfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $available);
            preg_match('/MemFree:\s+(\d+)/', $memInfo, $free);

            if (isset($total[1])) {
                $totalMem = $total[1] / 1024;
                $availableMem = $available[1] / 1024 ?? 0;
                $usedMem = $totalMem - $availableMem;

                return [
                    'total_mb' => round($totalMem, 2),
                    'used_mb' => round($usedMem, 2),
                    'free_mb' => round($availableMem, 2),
                    'used_percent' => round(($usedMem / $totalMem) * 100, 2),
                ];
            }
        }

        return ['error' => 'Não foi possível obter informações de memória'];
    }

    /**
     * Obter tempo de atividade do servidor
     */
    private function getServerUptime()
    {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime');
            if ($uptime) {
                return trim($uptime);
            }
        }
        return 'N/A';
    }

    /**
     * Obter última execução do cron
     */
    private function getLastCronRun()
    {
        $lastRun = Cache::get('last_cron_run');
        if ($lastRun) {
            return [
                'time' => $lastRun,
                'ago' => now()->diffForHumans($lastRun),
            ];
        }
        return null;
    }

    /**
     * Testar todas as conexões
     */
    public function healthCheck()
    {
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
