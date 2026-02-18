<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the logs.
     */
    public function index(Request $request)
    {
        try {
            $query = DB::table('activity_logs');

            // Filtros
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('action')) {
                $query->where('action', 'like', '%' . $request->action . '%');
            }

            if ($request->has('level_system')) {
                $query->where('level_system', $request->level_system);
            }

            if ($request->has('log_name')) {
                $query->where('log_name', $request->log_name);
            }

            if ($request->has('subject_type')) {
                $query->where('subject_type', $request->subject_type);
            }

            if ($request->has('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->has('country')) {
                $query->where('country', $request->country);
            }

            if ($request->has('device_type')) {
                $query->where('device_type', $request->device_type);
            }

            // Filtro por data
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Ordenação
            $orderBy = $request->get('order_by', 'id');
            $orderDir = $request->get('order_dir', 'desc');
            $query->orderBy($orderBy, $orderDir);

            // Paginação
            $perPage = $request->get('per_page', 50);
            $logs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'Logs recuperados com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOVO MÉTODO: Dados para gráficos de análise de logs
     */
    public function chartData(Request $request)
    {
        try {
            $period = $request->get('period', '30d'); // 7d, 30d, 90d, 1y

            // Definir intervalo baseado no período
            $interval = match($period) {
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
                '90d' => now()->subDays(90),
                '1y' => now()->subYear(),
                default => now()->subDays(30),
            };

            // 1. Gráfico de linhas: Logs por dia
            $logsByDay = DB::table('activity_logs')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $interval)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            // 2. Gráfico de pizza: Distribuição por nível
            $logsByLevel = DB::table('activity_logs')
                ->select('level_system', DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $interval)
                ->groupBy('level_system')
                ->orderBy('total', 'desc')
                ->get();

            // 3. Gráfico de barras: Top 10 ações
            $topActions = DB::table('activity_logs')
                ->select('action', DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $interval)
                ->groupBy('action')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // 4. Gráfico de barras: Logs por hora do dia (últimos 7 dias)
            $logsByHour = DB::table('activity_logs')
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy(DB::raw('HOUR(created_at)'))
                ->orderBy('hour', 'asc')
                ->get();

            // 5. Gráfico de pizza: Dispositivos
            $logsByDevice = DB::table('activity_logs')
                ->select('device_type', DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $interval)
                ->whereNotNull('device_type')
                ->groupBy('device_type')
                ->orderBy('total', 'desc')
                ->get();

            // 6. Gráfico de barras: Top 10 países
            $topCountries = DB::table('activity_logs')
                ->select('country', DB::raw('COUNT(*) as total'))
                ->where('created_at', '>=', $interval)
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // 7. Gráfico de linhas: Erros vs Info ao longo do tempo
            $errorsVsInfo = DB::table('activity_logs')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw("SUM(CASE WHEN level_system IN ('error', 'critical') THEN 1 ELSE 0 END) as errors"),
                    DB::raw("SUM(CASE WHEN level_system = 'info' THEN 1 ELSE 0 END) as info"),
                    DB::raw("SUM(CASE WHEN level_system = 'warning' THEN 1 ELSE 0 END) as warnings")
                )
                ->where('created_at', '>=', $interval)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'logs_by_day' => $logsByDay,
                    'logs_by_level' => $logsByLevel,
                    'top_actions' => $topActions,
                    'logs_by_hour' => $logsByHour,
                    'logs_by_device' => $logsByDevice,
                    'top_countries' => $topCountries,
                    'errors_vs_info' => $errorsVsInfo,
                    'period' => $period,
                    'total_logs' => DB::table('activity_logs')->where('created_at', '>=', $interval)->count(),
                ],
                'message' => 'Dados para gráficos recuperados com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar dados para gráficos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar dados para gráficos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOVO MÉTODO: Estatísticas detalhadas por período
     */
    public function detailedStats(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            $stats = [
                'summary' => [
                    'total' => DB::table('activity_logs')
                        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->count(),
                    'errors' => DB::table('activity_logs')
                        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->whereIn('level_system', ['error', 'critical'])
                        ->count(),
                    'warnings' => DB::table('activity_logs')
                        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->where('level_system', 'warning')
                        ->count(),
                    'info' => DB::table('activity_logs')
                        ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                        ->where('level_system', 'info')
                        ->count(),
                ],
                'by_user' => DB::table('activity_logs')
                    ->select('user_id', DB::raw('COUNT(*) as total'))
                    ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                    ->whereNotNull('user_id')
                    ->groupBy('user_id')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                'peak_hours' => DB::table('activity_logs')
                    ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as total'))
                    ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
                    ->groupBy(DB::raw('HOUR(created_at)'))
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estatísticas detalhadas recuperadas com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar estatísticas detalhadas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar estatísticas detalhadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified log.
     */
    public function show($id)
    {
        try {
            $log = DB::table('activity_logs')->find($id);

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Log não encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $log,
                'message' => 'Log recuperado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics.
     */
    public function stats()
    {
        try {
            $stats = [
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

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estatísticas recuperadas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar estatísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get error logs.
     */
    public function errors(Request $request)
    {
        try {
            $query = DB::table('activity_logs')
                ->whereIn('level_system', ['warning', 'error', 'critical']);

            if ($request->has('level')) {
                $query->where('level_system', $request->level);
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'Logs de erro recuperados'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar logs de erro',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get logs by user.
     */
    public function userLogs($userId, Request $request)
    {
        try {
            $logs = DB::table('activity_logs')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'Logs do usuário recuperados'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar logs do usuário',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get logs by subject.
     */
    public function subjectLogs($subjectType, $subjectId, Request $request)
    {
        try {
            $logs = DB::table('activity_logs')
                ->where('subject_type', $subjectType)
                ->where('subject_id', $subjectId)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'Logs da entidade recuperados'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar logs da entidade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean old logs.
     */
    public function clean(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $date = now()->subDays($days);

            $deleted = DB::table('activity_logs')
                ->where('created_at', '<', $date)
                ->where('level_system', '!=', 'critical')
                ->delete();

            return response()->json([
                'success' => true,
                'data' => ['deleted' => $deleted],
                'message' => "{$deleted} logs antigos removidos"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export logs.
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'json');
            $query = DB::table('activity_logs')->orderBy('created_at', 'desc');

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->limit($request->get('limit', 1000))->get();

            if ($format === 'csv') {
                return $this->exportToCsv($logs);
            }

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'Logs exportados com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to CSV.
     */
    private function exportToCsv($logs)
    {
        $filename = 'logs_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'w');

        fputcsv($handle, ['ID', 'Usuário', 'Ação', 'Nível', 'Categoria', 'Descrição', 'IP', 'País', 'Data']);

        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->user_id,
                $log->action,
                $log->level_system,
                $log->log_name,
                substr($log->description, 0, 100),
                $log->ip_address,
                $log->country,
                $log->created_at
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
