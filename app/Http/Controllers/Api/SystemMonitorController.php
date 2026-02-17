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
     * Informações do sistema - VERSÃO ADAPTADA PARA RENDER
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
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Render.com',
            'server_name' => gethostname() ?: 'Render',
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
                'status' => $this->checkDatabaseConnection() ? 'healthy' : 'error',
            ];
        } catch (\Exception $e) {
            Log::warning('Erro ao obter estatísticas do banco: ' . $e->getMessage());
            return [
                'error' => 'Erro ao obter estatísticas do banco',
                'status' => 'error',
                'size_mb' => 0,
                'active_connections' => 0,
                'slow_queries' => 0,
                'tables_count' => 0,
                'tables' => []
            ];
        }
    }

    /**
     * Estatísticas de usuários
     */
    private function getUserStats()
    {
        try {
            $total = User::count();

            // Usar email_verified_at como indicador de usuário ativo
            $active = User::whereNotNull('email_verified_at')->count();
            $inactive = User::whereNull('email_verified_at')->count();

            // Usar deleted_at para bloqueados (soft deletes)
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

            // Verificar se tabela student_documents existe
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
            Log::warning('Erro ao obter estatísticas de usuários: ' . $e->getMessage());
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
     * Estatísticas de pesquisas
     */
    private function getSurveyStats()
    {
        try {
            $total = Survey::count();
            $active = Survey::where('status', 'active')->count();
            $draft = Survey::where('status', 'draft')->count();
            $completed = Survey::where('status', 'completed')->count();
            $archived = Survey::where('status', 'archived')->count();

            // Verificar se a tabela survey_responses existe
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

            // Verificar se a tabela payments existe
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
            Log::warning('Erro ao obter estatísticas de pesquisas: ' . $e->getMessage());
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
     * Métricas de performance
     */
    private function getPerformanceMetrics()
    {
        try {
            // Cache stats
            $cacheHits = Cache::get('cache_hits', 0);
            $cacheMisses = Cache::get('cache_misses', 0);
            $cacheTotal = $cacheHits + $cacheMisses;

            // Response times (se a tabela activity_logs existir)
            $responseTimes = 0;
            $requestsLastHour = 0;

            if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
                $responseTimes = DB::table('activity_logs')
                    ->whereNotNull('duration_ms')
                    ->where('created_at', '>=', now()->subDay())
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
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
            Log::warning('Erro ao obter métricas de performance: ' . $e->getMessage());
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
     * Atividade recente
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

            return [
                'logs' => $logs,
                'errors' => $errors,
                'new_users' => User::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'email', 'role', 'created_at']),
                'new_surveys' => Survey::orderBy('created_at', 'desc')->limit(5)->get(['id', 'title', 'user_id', 'status', 'created_at']),
            ];
        } catch (\Exception $e) {
            Log::warning('Erro ao obter atividade recente: ' . $e->getMessage());
            return [
                'logs' => [],
                'errors' => [],
                'new_users' => [],
                'new_surveys' => [],
            ];
        }
    }

    /**
     * Alertas do sistema
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        try {
            // Verificar espaço em disco (valores simulados para Render)
            $diskUsage = $this->getDiskUsage();
            if ($diskUsage['free_gb'] < 1) {
                $alerts[] = [
                    'type' => 'critical',
                    'title' => 'Espaço em disco baixo',
                    'message' => "Apenas {$diskUsage['free_gb']}GB disponíveis",
                    'action' => 'Liberar espaço imediatamente'
                ];
            } elseif ($diskUsage['free_gb'] < 2) {
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

            // Verificar usuários não verificados
            $unverifiedUsers = User::whereNull('email_verified_at')->count();
            if ($unverifiedUsers > 100) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Muitos usuários não verificados',
                    'message' => "{$unverifiedUsers} usuários aguardando verificação",
                    'action' => 'Revisar processo de verificação'
                ];
            }

            // Verificar pesquisas sem respostas
            $emptySurveys = Survey::doesntHave('responses')->count();
            if ($emptySurveys > 10) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Pesquisas sem respostas',
                    'message' => "{$emptySurveys} pesquisas não tiveram respostas",
                    'action' => 'Revisar distribuição das pesquisas'
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
     * Obter uso de disco - VERSÃO ADAPTADA PARA RENDER
     */
    private function getDiskUsage()
    {
        try {
            // No Render.com, o disco é efêmero e limitado
            // Vamos retornar valores simulados baseados em fatos reais do Render

            // Verificar se conseguimos obter informações reais
            $path = storage_path();
            if (function_exists('disk_total_space') && function_exists('disk_free_space')) {
                $total = @disk_total_space($path);
                $free = @disk_free_space($path);

                if ($total !== false && $free !== false && $total > 0) {
                    $used = $total - $free;
                    return [
                        'total_gb' => round($total / 1024 / 1024 / 1024, 2),
                        'used_gb' => round($used / 1024 / 1024 / 1024, 2),
                        'free_gb' => round($free / 1024 / 1024 / 1024, 2),
                        'used_percent' => round(($used / $total) * 100, 2),
                    ];
                }
            }

            // Fallback - valores aproximados para Render (10GB de disco)
            return [
                'total_gb' => 10,
                'used_gb' => 3,
                'free_gb' => 7,
                'used_percent' => 30,
            ];
        } catch (\Exception $e) {
            Log::warning('Erro ao obter uso de disco: ' . $e->getMessage());
            return [
                'total_gb' => 10,
                'used_gb' => 3,
                'free_gb' => 7,
                'used_percent' => 30,
            ];
        }
    }

    /**
     * Obter uso de memória - VERSÃO ADAPTADA PARA RENDER
     */
    private function getMemoryUsage()
    {
        try {
            // No Render.com, comandos shell são bloqueados
            // Usar apenas memory_get_usage()

            $used = memory_get_usage(true) / 1024 / 1024;

            // Render geralmente tem 512MB-1GB de RAM
            return [
                'total_mb' => 512, // Valor aproximado
                'used_mb' => round($used, 2),
                'free_mb' => round(512 - $used, 2),
                'used_percent' => round(($used / 512) * 100, 2),
            ];
        } catch (\Exception $e) {
            Log::warning('Erro ao obter uso de memória: ' . $e->getMessage());
            return [
                'total_mb' => 512,
                'used_mb' => 128,
                'free_mb' => 384,
                'used_percent' => 25,
            ];
        }
    }

    /**
     * Obter tempo de atividade do servidor - VERSÃO ADAPTADA PARA RENDER
     */
    private function getServerUptime()
    {
        try {
            // No Render.com, não temos acesso ao uptime
            // Retornar informação amigável
            return 'Serviço gerenciado pelo Render.com';
        } catch (\Exception $e) {
            return 'N/A';
        }
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
            Log::warning('Erro ao obter último cron: ' . $e->getMessage());
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
            Log::error('Erro no health check: ' . $e->getMessage());
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
