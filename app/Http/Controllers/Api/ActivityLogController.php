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
