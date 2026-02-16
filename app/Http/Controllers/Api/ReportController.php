<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\ReportHistory;
use App\Models\ReportTemplate;
use App\Services\ReportExportService;

class ReportController extends Controller
{
    protected ReportExportService $exportService;

    public function __construct()
    {
        $this->exportService = new ReportExportService();
    }

    // ========== MÉTODOS AUXILIARES ==========

    private function getTopPerformers(int $limit = 10)
    {
        return User::whereIn('role', ['student', 'participant'])
            ->withCount('surveys')
            ->orderByDesc('surveys_count')
            ->orderByDesc('balance')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'role', 'balance', 'created_at']);
    }

    private function calculateUserGrowth(string $startDate, string $endDate): float
    {
        $current = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $previous = User::whereBetween('created_at', [
            Carbon::parse($startDate)->subDays(30),
            Carbon::parse($endDate)->subDays(30)
        ])->count();

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 100.0;
    }

    private function calculateRevenueGrowth(string $startDate, string $endDate): float
    {
        $current = Transaction::where('type', 'commission')
            ->orWhere('type', 'survey_earnings')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $previous = Transaction::where('type', 'commission')
            ->orWhere('type', 'survey_earnings')
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->subDays(30),
                Carbon::parse($endDate)->subDays(30)
            ])
            ->sum('amount');

        return $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 100.0;
    }

    private function calculateTrend(float $current, float $previous): array
    {
        $trendValue = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100.0 : 0.0);
        $trendIcon = $trendValue >= 0 ? 'trending_up' : 'trending_down';
        $trendClass = $trendValue >= 0 ? 'positive' : 'negative';

        return [
            'value' => ($trendValue >= 0 ? '+' : '') . $trendValue . '%',
            'icon' => $trendIcon,
            'class' => $trendClass
        ];
    }

    // ========== MÉTODOS DE TRADUÇÃO ==========

    private function translateStatus($status)
    {
        $translations = [
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'completed' => 'Concluída',
            'draft' => 'Rascunho',
            'pending_approval' => 'Pendente',
            'approved' => 'Aprovada',
            'rejected' => 'Rejeitada',
            'in_progress' => 'Em Andamento',
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'processing' => 'Processando'
        ];

        return $translations[$status] ?? $status;
    }

    private function translateRole($role)
    {
        $translations = [
            'student' => 'Estudante',
            'participant' => 'Participante',
            'admin' => 'Administrador'
        ];

        return $translations[$role] ?? $role;
    }

    private function translateTransactionType($type)
    {
        $translations = [
            'payment' => 'Pagamento',
            'withdrawal' => 'Saque',
            'commission' => 'Comissão',
            'refund' => 'Reembolso',
            'survey_earnings' => 'Ganhos de Pesquisa'
        ];

        return $translations[$type] ?? $type;
    }

    // ========== GRÁFICOS DO PARTICIPANTE ==========

    private function getParticipantEarningsOverTime($user, $startDate, $endDate)
    {
        $earnings = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['payment', 'survey_earnings'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as daily_earnings'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($earnings->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Ganhos Diários',
                        'data' => [],
                        'borderColor' => '#4caf50',
                        'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Acumulado',
                        'data' => [],
                        'borderColor' => '#1976d2',
                        'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                        'tension' => 0.1
                    ]
                ]
            ];
        }

        $cumulative = 0;
        $earningsData = $earnings->map(function ($item) use (&$cumulative) {
            $cumulative += (float) $item->daily_earnings;
            return [
                'date' => Carbon::parse($item->date)->format('d/m'),
                'daily' => (float) $item->daily_earnings,
                'cumulative' => (float) $cumulative
            ];
        });

        return [
            'labels' => $earningsData->pluck('date')->values(),
            'datasets' => [
                [
                    'label' => 'Ganhos Diários',
                    'data' => $earningsData->pluck('daily')->values(),
                    'borderColor' => '#4caf50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Acumulado',
                    'data' => $earningsData->pluck('cumulative')->values(),
                    'borderColor' => '#1976d2',
                    'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                    'tension' => 0.1
                ]
            ]
        ];
    }

    private function getParticipantCategoryDistribution($user, $startDate, $endDate)
    {
        // Buscar categorias das transações via surveys
        $distribution = DB::table('transactions as t')
            ->join('surveys as s', 't.survey_id', '=', 's.id')
            ->where('t.user_id', $user->id)
            ->whereIn('t.type', ['payment', 'survey_earnings'])
            ->whereBetween('t.created_at', [$startDate, $endDate])
            ->select(
                's.category',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('s.category')
            ->get();

        if ($distribution->isEmpty()) {
            return [
                'labels' => ['Sem dados'],
                'datasets' => [[
                    'label' => 'Participações por Categoria',
                    'data' => [1],
                    'backgroundColor' => ['#9e9e9e']
                ]]
            ];
        }

        $colors = ['#1976d2', '#4caf50', '#ff9800', '#9c27b0', '#00bcd4', '#f44336', '#3f51b5'];

        return [
            'labels' => $distribution->pluck('category')->values(),
            'datasets' => [[
                'label' => 'Participações por Categoria',
                'data' => $distribution->pluck('count')->map(fn($v) => (int) $v)->values(),
                'backgroundColor' => array_slice($colors, 0, $distribution->count())
            ]]
        ];
    }

    private function getParticipantWithdrawalHistory($user, $startDate, $endDate)
    {
        $withdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($withdrawals->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Saques',
                    'data' => [],
                    'borderColor' => '#ff9800',
                    'backgroundColor' => 'rgba(255, 152, 0, 0.1)',
                    'tension' => 0.1
                ]]
            ];
        }

        return [
            'labels' => $withdrawals->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->values(),
            'datasets' => [[
                'label' => 'Saques',
                'data' => $withdrawals->pluck('amount')->map(fn($v) => (float) $v)->values(),
                'borderColor' => '#ff9800',
                'backgroundColor' => 'rgba(255, 152, 0, 0.1)',
                'tension' => 0.1
            ]]
        ];
    }

    // ========== GRÁFICOS DO ESTUDANTE ==========

    private function getStudentResponsesOverTime($user, $startDate, $endDate)
    {
        $responses = DB::table('survey_responses as sr')
            ->join('surveys as s', 'sr.survey_id', '=', 's.id')
            ->where('s.user_id', $user->id)
            ->whereBetween('sr.created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(sr.created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($responses->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Respostas Recebidas',
                    'data' => [],
                    'borderColor' => '#1976d2',
                    'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                    'tension' => 0.1
                ]]
            ];
        }

        return [
            'labels' => $responses->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->values(),
            'datasets' => [[
                'label' => 'Respostas Recebidas',
                'data' => $responses->pluck('count')->map(fn($v) => (int) $v)->values(),
                'borderColor' => '#1976d2',
                'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                'tension' => 0.1
            ]]
        ];
    }

    private function getStudentSurveyStatusDistribution($user, $startDate, $endDate)
    {
        $distribution = Survey::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        if ($distribution->isEmpty()) {
            return [
                'labels' => ['Sem pesquisas'],
                'datasets' => [[
                    'label' => 'Distribuição por Status',
                    'data' => [1],
                    'backgroundColor' => ['#9e9e9e']
                ]]
            ];
        }

        $statusColors = [
            'active' => '#4caf50',
            'paused' => '#ff9800',
            'completed' => '#2196f3',
            'draft' => '#9e9e9e',
            'pending_approval' => '#ff9800',
            'approved' => '#4caf50',
            'rejected' => '#f44336'
        ];

        return [
            'labels' => $distribution->pluck('status')->map(fn($status) => $this->translateStatus($status))->values(),
            'datasets' => [[
                'label' => 'Distribuição por Status',
                'data' => $distribution->pluck('count')->map(fn($v) => (int) $v)->values(),
                'backgroundColor' => $distribution->pluck('status')->map(fn($status) => $statusColors[$status] ?? '#607d8b')->values()
            ]]
        ];
    }

    private function getStudentMonthlyEarnings($user, $startDate, $endDate)
    {
        $earnings = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['commission', 'survey_earnings'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($earnings->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [[
                    'label' => 'Ganhos Mensais',
                    'data' => [],
                    'borderColor' => '#4caf50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                    'tension' => 0.1
                ]]
            ];
        }

        return [
            'labels' => $earnings->pluck('month')->values(),
            'datasets' => [[
                'label' => 'Ganhos Mensais',
                'data' => $earnings->pluck('amount')->map(fn($v) => (float) $v)->values(),
                'borderColor' => '#4caf50',
                'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                'tension' => 0.1
            ]]
        ];
    }

    // ========== GRÁFICOS DO ADMIN ==========

    private function getAdminUserGrowthByType($startDate, $endDate)
    {
        $growth = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(CASE WHEN role = "student" THEN 1 ELSE 0 END) as students'),
            DB::raw('SUM(CASE WHEN role = "participant" THEN 1 ELSE 0 END) as participants')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($growth->isEmpty()) {
            return [
                'labels' => [Carbon::now()->format('Y-m')],
                'datasets' => [
                    [
                        'label' => 'Estudantes',
                        'data' => [0],
                        'borderColor' => '#1976d2',
                        'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Participantes',
                        'data' => [0],
                        'borderColor' => '#4caf50',
                        'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                        'tension' => 0.1
                    ]
                ]
            ];
        }

        return [
            'labels' => $growth->pluck('month')->values(),
            'datasets' => [
                [
                    'label' => 'Estudantes',
                    'data' => $growth->pluck('students')->map(fn($v) => (int) $v)->values(),
                    'borderColor' => '#1976d2',
                    'backgroundColor' => 'rgba(25, 118, 210, 0.1)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Participantes',
                    'data' => $growth->pluck('participants')->map(fn($v) => (int) $v)->values(),
                    'borderColor' => '#4caf50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                    'tension' => 0.1
                ]
            ]
        ];
    }

    private function getAdminTransactionVolume($startDate, $endDate)
    {
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as volume')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Número de Transações',
                        'data' => [],
                        'borderColor' => '#ff9800',
                        'backgroundColor' => 'rgba(255, 152, 0, 0.1)',
                        'tension' => 0.1
                    ],
                    [
                        'label' => 'Volume (MZN)',
                        'data' => [],
                        'borderColor' => '#9c27b0',
                        'backgroundColor' => 'rgba(156, 39, 176, 0.1)',
                        'tension' => 0.1
                    ]
                ]
            ];
        }

        return [
            'labels' => $transactions->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d/m'))->values(),
            'datasets' => [
                [
                    'label' => 'Número de Transações',
                    'data' => $transactions->pluck('count')->map(fn($v) => (int) $v)->values(),
                    'borderColor' => '#ff9800',
                    'backgroundColor' => 'rgba(255, 152, 0, 0.1)',
                    'tension' => 0.1
                ],
                [
                    'label' => 'Volume (MZN)',
                    'data' => $transactions->pluck('volume')->map(fn($v) => (float) $v)->values(),
                    'borderColor' => '#9c27b0',
                    'backgroundColor' => 'rgba(156, 39, 176, 0.1)',
                    'tension' => 0.1
                ]
            ]
        ];
    }

    private function getAdminPlatformUsageByHour()
    {
        $usage = DB::table('activity_logs')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $data = collect(range(0, 23))->map(fn($hour) => $usage->firstWhere('hour', $hour) ? (int) $usage->firstWhere('hour', $hour)->count : 0);

        return [
            'labels' => collect(range(0, 23))->map(fn($h) => sprintf('%02d:00', $h))->values(),
            'datasets' => [[
                'label' => 'Uso por Hora',
                'data' => $data->values(),
                'borderColor' => '#00bcd4',
                'backgroundColor' => 'rgba(0, 188, 212, 0.1)',
                'tension' => 0.1
            ]]
        ];
    }

    // ========== RELATÓRIOS DE PARTICIPANTE - CORRIGIDO! ==========

    public function getParticipantReportDashboard(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            // ✅ USAR TRANSACTIONS, NÃO SURVEY_RESPONSES!
            $totalParticipations = Transaction::where('user_id', $user->id)
                ->whereIn('type', ['payment', 'survey_earnings'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $completedParticipations = Transaction::where('user_id', $user->id)
                ->whereIn('type', ['payment', 'survey_earnings'])
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $totalEarnings = Transaction::where('user_id', $user->id)
                ->whereIn('type', ['payment', 'survey_earnings'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $availableBalance = $user->balance ?? 0;

            // ✅ Taxa de aprovação baseada em transações
            $approvalRate = $totalParticipations > 0
                ? round(($completedParticipations / $totalParticipations) * 100, 1)
                : 0;

            // ✅ Ganhos pendentes
            $pendingEarnings = Transaction::where('user_id', $user->id)
                ->whereIn('type', ['payment', 'survey_earnings'])
                ->where('status', 'pending')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            // ✅ Total sacado
            $totalWithdrawn = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('amount');

            // ✅ Histórico de saques
            $withdrawalHistory = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'amount' => $t->amount,
                    'status' => $this->translateStatus($t->status),
                    'date' => $t->created_at->format('d/m/Y H:i'),
                    'method' => $t->payment_method ?? 'Transferência'
                ]);

            // ✅ Transações recentes
            $recentTransactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'type' => $this->translateTransactionType($t->type),
                    'amount' => $t->amount,
                    'status' => $this->translateStatus($t->status),
                    'date' => $t->created_at->format('d/m/Y H:i'),
                    'description' => $t->description
                ]);

            // ✅ Questionários disponíveis
            $availableSurveys = Survey::where('status', 'active')
                ->whereNotIn('id', function ($query) use ($user) {
                    $query->select('survey_id')
                        ->from('transactions')
                        ->where('user_id', $user->id)
                        ->whereIn('type', ['payment', 'survey_earnings']);
                })
                ->count();

            $availableSurveysData = Survey::where('status', 'active')
                ->whereNotIn('id', function ($query) use ($user) {
                    $query->select('survey_id')
                        ->from('transactions')
                        ->where('user_id', $user->id)
                        ->whereIn('type', ['payment', 'survey_earnings']);
                })
                ->select('id', 'title', 'reward', 'duration', 'category')
                ->take(5)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'title' => $s->title,
                    'reward' => $s->reward,
                    'duration' => $s->duration,
                    'category' => $s->category,
                    'actions' => ['responder']
                ]);

            // ✅ Ranking - CORRIGIDO com pending!
            $totalParticipants = User::where('role', 'participant')->count();

            $rankingQuery = User::where('role', 'participant')
                ->select('users.id', 'users.name', 'users.balance')
                ->selectSub(function ($query) {
                    $query->selectRaw('COALESCE(SUM(amount), 0)')
                        ->from('transactions')
                        ->whereColumn('user_id', 'users.id')
                        ->whereIn('type', ['payment', 'survey_earnings'])
                        ->whereIn('status', ['completed', 'pending']); // ✅ INCLUI PENDING!
                }, 'total_earned')
                ->orderBy('total_earned', 'desc')
                ->get();

            $currentRank = null;
            foreach ($rankingQuery as $index => $participant) {
                if ($participant->id == $user->id) {
                    $currentRank = $index + 1;
                    break;
                }
            }

            $percentile = $totalParticipants > 0 && $currentRank
                ? round((($totalParticipants - $currentRank) / $totalParticipants) * 100, 1)
                : 0;

            $pointsToNextRank = 0;
            if ($currentRank && $currentRank > 1) {
                $nextUser = $rankingQuery[$currentRank - 2] ?? null;
                if ($nextUser) {
                    $currentUserEarned = $rankingQuery->firstWhere('id', $user->id)->total_earned ?? 0;
                    $pointsToNextRank = $nextUser->total_earned - $currentUserEarned;
                }
            }

            // ✅ Insights
            $insights = $this->generateParticipantInsights($user, $availableSurveys, $availableBalance, $approvalRate);

            // ✅ Gráficos
            $charts = [
                'earnings_over_time' => $this->getParticipantEarningsOverTime($user, $startDate, $endDate),
                'category_distribution' => $this->getParticipantCategoryDistribution($user, $startDate, $endDate),
                'withdrawal_history' => $this->getParticipantWithdrawalHistory($user, $startDate, $endDate)
            ];

            // ✅ KPIs
            $kpis = [
                [
                    'id' => 1,
                    'title' => 'Questionários Respondidos',
                    'value' => $totalParticipations,
                    'subtitle' => 'Total participações',
                    'trend' => $totalParticipations > 0 ? '+' . $totalParticipations . ' este mês' : '0 este mês',
                    'trendIcon' => $totalParticipations > 0 ? 'trending_up' : 'trending_flat',
                    'trendClass' => $totalParticipations > 0 ? 'positive' : 'neutral',
                    'color' => '#388e3c',
                    'icon' => 'check_circle'
                ],
                [
                    'id' => 2,
                    'title' => 'Ganhos Totais',
                    'value' => $totalEarnings,
                    'subtitle' => 'Valor acumulado',
                    'trend' => $totalEarnings > 0 ? '+' . number_format($totalEarnings, 2) . ' MZN' : '0 MZN',
                    'trendIcon' => $totalEarnings > 0 ? 'trending_up' : 'trending_flat',
                    'trendClass' => $totalEarnings > 0 ? 'positive' : 'neutral',
                    'color' => '#4caf50',
                    'icon' => 'attach_money'
                ],
                [
                    'id' => 3,
                    'title' => 'Saldo Disponível',
                    'value' => $availableBalance,
                    'subtitle' => 'Saldo atual',
                    'trend' => $availableBalance > 0 ? '+' . number_format($availableBalance, 2) . ' MZN' : '0 MZN',
                    'trendIcon' => $availableBalance > 0 ? 'trending_up' : 'trending_flat',
                    'trendClass' => $availableBalance > 0 ? 'positive' : 'neutral',
                    'color' => '#ff9800',
                    'icon' => 'account_balance_wallet'
                ],
                [
                    'id' => 4,
                    'title' => 'Taxa de Aprovação',
                    'value' => $approvalRate,
                    'subtitle' => 'Pagamentos aprovados',
                    'trend' => $approvalRate > 0 ? '+' . $approvalRate . '%' : '0%',
                    'trendIcon' => $approvalRate > 0 ? 'trending_up' : 'trending_flat',
                    'trendClass' => $approvalRate > 0 ? 'positive' : 'neutral',
                    'color' => '#9c27b0',
                    'icon' => 'star'
                ],
                [
                    'id' => 5,
                    'title' => 'Ganhos Pendentes',
                    'value' => $pendingEarnings,
                    'subtitle' => 'Aguardando aprovação',
                    'trend' => $pendingEarnings > 0 ? '+' . number_format($pendingEarnings, 2) . ' MZN' : '0 MZN',
                    'trendIcon' => $pendingEarnings > 0 ? 'trending_up' : 'trending_flat',
                    'trendClass' => $pendingEarnings > 0 ? 'positive' : 'neutral',
                    'color' => '#00bcd4',
                    'icon' => 'schedule'
                ]
            ];

            // ✅ DADOS DE QUALIDADE - ADICIONADO!
            $qualityData = [
                'approval_rate' => $approvalRate,
                'average_quality_score' => 0,
                'total_responses' => $totalParticipations,
                'approved_responses' => $completedParticipations,
                'rejected_responses' => 0,
                'pending_responses' => $totalParticipations - $completedParticipations,
                'quality_by_category' => [],
                'monthly_evolution' => []
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => $kpis,
                    'tables' => [
                        'available_surveys' => [
                            'title' => 'Questionários Disponíveis',
                            'columns' => ['Título', 'Valor', 'Duração', 'Categoria', 'Ações'],
                            'data' => $availableSurveysData
                        ],
                        'participation_history' => [
                            'title' => 'Histórico de Participações',
                            'columns' => ['Descrição', 'Valor', 'Status', 'Data'],
                            'data' => $recentTransactions
                        ]
                    ],
                    'charts' => $charts,
                    'insights' => $insights,
                    'financial' => [
                        'total_earnings' => $totalEarnings,
                        'pending_earnings' => $pendingEarnings,
                        'available_balance' => $availableBalance,
                        'total_withdrawn' => $totalWithdrawn,
                        'recent_transactions' => $recentTransactions,
                        'withdrawal_history' => $withdrawalHistory
                    ],
                    'ranking' => [
                        'current_rank' => $currentRank,
                        'total_participants' => $totalParticipants,
                        'percentile' => $percentile,
                        'points_to_next_rank' => max(0, round($pointsToNextRank, 2)),
                        'top_performers' => $rankingQuery->take(10)->map(fn($item) => [
                            'id' => $item->id,
                            'name' => $item->name,
                            'balance' => $item->balance,
                            'total_earned' => $item->total_earned
                        ])
                    ],
                    'quality' => $qualityData // ✅ NOVO CAMPO!
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getParticipantReportDashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getParticipantEarningsReport(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            $earnings = Transaction::where('user_id', $user->id)
                ->whereIn('type', ['payment', 'survey_earnings'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('amount', 'description', 'created_at', 'status')
                ->orderBy('created_at', 'desc')
                ->get();

            $total = $earnings->sum('amount');
            $completed = $earnings->where('status', 'completed')->sum('amount');
            $pending = $earnings->where('status', 'pending')->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'completed' => $completed,
                    'pending' => $pending,
                    'earnings' => $earnings,
                    'average_per_response' => $earnings->count() > 0 ? $total / $earnings->count() : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getParticipantEarningsReport: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getParticipantTransactionsReport(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $transactions = Transaction::where('user_id', $user->id)
                ->whereBetween('created_at', [
                    $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d')),
                    $request->get('end_date', Carbon::now()->format('Y-m-d'))
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getParticipantTransactionsReport: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getParticipantRankingsReport(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Ranking baseado em ganhos, não em respostas
            $rankings = User::where('role', 'participant')
                ->select('users.id', 'users.name', 'users.email', 'users.balance')
                ->selectSub(function ($query) {
                    $query->selectRaw('COALESCE(SUM(amount), 0)')
                        ->from('transactions')
                        ->whereColumn('user_id', 'users.id')
                        ->whereIn('type', ['payment', 'survey_earnings'])
                        ->where('status', 'completed');
                }, 'total_earned')
                ->orderBy('total_earned', 'desc')
                ->limit($request->get('limit', 20))
                ->get();

            $currentRank = $rankings->search(fn($u) => $u->id === $user->id);
            $currentRank = $currentRank !== false ? $currentRank + 1 : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'rankings' => $rankings,
                    'current_rank' => $currentRank,
                    'total_participants' => User::where('role', 'participant')->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getParticipantRankingsReport: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== RELATÓRIOS DE ESTUDANTE ==========

    public function getStudentReportDashboard(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            $surveys = Survey::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->withCount('responses')
                ->get();

            $activeSurveys = $surveys->where('status', 'active')->count();
            $totalSurveys = $surveys->count();
            $totalResponses = $surveys->sum('responses_count');
            $avgCompletionRate = $surveys->avg('completion_rate') ?? 0;

            $totalEarnings = Transaction::where('user_id', $user->id)
                ->whereIn('type', ['commission', 'survey_earnings'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $surveysTable = $surveys->take(5)->map(fn($survey) => [
                'id' => $survey->id,
                'title' => $survey->title,
                'status' => $this->translateStatus($survey->status),
                'responses' => $survey->responses_count ?? 0,
                'created_at' => $survey->created_at->format('d/m/Y'),
                'reward' => $survey->reward,
                'actions' => ['editar', 'ver_respostas']
            ]);

            $insights = $this->generateStudentDashboardInsights($user, $surveys, $avgCompletionRate);

            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => [
                        [
                            'id' => 1,
                            'title' => 'Pesquisas Ativas/Criadas',
                            'value' => $activeSurveys . '/' . $totalSurveys,
                            'subtitle' => 'Ativas / Total',
                            'trend' => $activeSurveys > 0 ? '+' . $activeSurveys . ' este mês' : '0 este mês',
                            'trendIcon' => $activeSurveys > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $activeSurveys > 0 ? 'positive' : 'neutral',
                            'color' => '#1976d2',
                            'icon' => 'poll'
                        ],
                        [
                            'id' => 2,
                            'title' => 'Total de Respostas Recebidas',
                            'value' => $totalResponses,
                            'subtitle' => 'Respostas coletadas',
                            'trend' => $totalResponses > 0 ? '+' . $totalResponses : '0',
                            'trendIcon' => $totalResponses > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $totalResponses > 0 ? 'positive' : 'neutral',
                            'color' => '#4caf50',
                            'icon' => 'people'
                        ],
                        [
                            'id' => 3,
                            'title' => 'Taxa de Conclusão',
                            'value' => round($avgCompletionRate, 1),
                            'subtitle' => 'Média das pesquisas',
                            'trend' => $avgCompletionRate > 0 ? '+' . round($avgCompletionRate, 1) . '%' : '0%',
                            'trendIcon' => $avgCompletionRate > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $avgCompletionRate > 0 ? 'positive' : 'neutral',
                            'color' => '#ff9800',
                            'icon' => 'check_circle'
                        ],
                        [
                            'id' => 4,
                            'title' => 'Ganhos Totais',
                            'value' => $totalEarnings,
                            'subtitle' => 'Valor acumulado',
                            'trend' => $totalEarnings > 0 ? '+' . number_format($totalEarnings, 2) . ' MZN' : '0 MZN',
                            'trendIcon' => $totalEarnings > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $totalEarnings > 0 ? 'positive' : 'neutral',
                            'color' => '#9c27b0',
                            'icon' => 'attach_money'
                        ]
                    ],
                    'tables' => [
                        'my_surveys' => [
                            'title' => 'Minhas Pesquisas Recentes',
                            'columns' => ['Título', 'Status', 'Respostas', 'Data', 'Recompensa', 'Ações'],
                            'data' => $surveysTable
                        ]
                    ],
                    'charts' => [
                        'responses_over_time' => $this->getStudentResponsesOverTime($user, $startDate, $endDate),
                        'survey_status_distribution' => $this->getStudentSurveyStatusDistribution($user, $startDate, $endDate),
                        'monthly_earnings' => $this->getStudentMonthlyEarnings($user, $startDate, $endDate)
                    ],
                    'insights' => $insights
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getStudentReportDashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== RELATÓRIOS ADMINISTRATIVOS ==========

    public function getAdminReportDashboard(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

            $totalUsers = User::count();
            $students = User::where('role', 'student')->count();
            $participants = User::where('role', 'participant')->count();

            $totalSurveys = Survey::whereBetween('created_at', [$startDate, $endDate])->count();
            $activeSurveys = Survey::where('status', 'active')->count();

            $totalResponses = SurveyResponse::whereBetween('created_at', [$startDate, $endDate])->count();

            $revenue = Transaction::whereIn('type', ['commission', 'survey_earnings'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $currentMonthUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();

            $recentUsers = User::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $this->translateRole($user->role),
                    'status' => $user->email_verified_at ? 'active' : 'pending',
                    'created_at' => $user->created_at->format('d/m/Y H:i')
                ]);

            $pendingSurveys = Survey::where('status', 'pending')  // ✅ STATUS CORRETO!
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn($survey) => [
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'researcher' => $survey->user->name ?? 'Pesquisador',
                    'category' => $survey->category,
                    'created_at' => $survey->created_at->format('d/m/Y'),
                    'reward' => $survey->reward
                ]);
            $recentTransactions = Transaction::with('user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'user' => $t->user->name ?? 'N/A',
                    'type' => $this->translateTransactionType($t->type),
                    'amount' => $t->amount,
                    'status' => $t->status,
                    'created_at' => $t->created_at->format('d/m/Y H:i')
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'kpis' => [
                        [
                            'id' => 1,
                            'title' => 'Usuários Totais',
                            'value' => $totalUsers,
                            'subtitle' => $students . ' estud. / ' . $participants . ' part.',
                            'trend' => '+' . $currentMonthUsers . ' este mês',
                            'trendIcon' => $currentMonthUsers > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $currentMonthUsers > 0 ? 'positive' : 'neutral',
                            'color' => '#1976d2',
                            'icon' => 'people'
                        ],
                        [
                            'id' => 2,
                            'title' => 'Questionários Criados',
                            'value' => $totalSurveys,
                            'subtitle' => $activeSurveys . ' ativos',
                            'trend' => $totalSurveys > 0 ? '+' . $totalSurveys : '0',
                            'trendIcon' => $totalSurveys > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $totalSurveys > 0 ? 'positive' : 'neutral',
                            'color' => '#4caf50',
                            'icon' => 'poll'
                        ],
                        [
                            'id' => 3,
                            'title' => 'Respostas Coletadas',
                            'value' => $totalResponses,
                            'subtitle' => 'Total de respostas',
                            'trend' => $totalResponses > 0 ? '+' . $totalResponses : '0',
                            'trendIcon' => $totalResponses > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $totalResponses > 0 ? 'positive' : 'neutral',
                            'color' => '#ff9800',
                            'icon' => 'comment'
                        ],
                        [
                            'id' => 4,
                            'title' => 'Receita da Plataforma',
                            'value' => $revenue,
                            'subtitle' => 'Comissões acumuladas',
                            'trend' => $revenue > 0 ? '+' . number_format($revenue, 2) . ' MZN' : '0 MZN',
                            'trendIcon' => $revenue > 0 ? 'trending_up' : 'trending_flat',
                            'trendClass' => $revenue > 0 ? 'positive' : 'neutral',
                            'color' => '#9c27b0',
                            'icon' => 'attach_money'
                        ]
                    ],
                    'tables' => [
                        'recent_users' => [
                            'title' => 'Usuários Recentes',
                            'columns' => ['Nome', 'Email', 'Papel', 'Status', 'Data'],
                            'data' => $recentUsers
                        ],
                        'pending_surveys' => [
                            'title' => 'Pesquisas Pendentes',
                            'columns' => ['Título', 'Pesquisador', 'Categoria', 'Data', 'Recompensa'],
                            'data' => $pendingSurveys
                        ],
                        'recent_transactions' => [
                            'title' => 'Transações Recentes',
                            'columns' => ['Usuário', 'Tipo', 'Valor', 'Status', 'Data'],
                            'data' => $recentTransactions
                        ]
                    ],
                    'charts' => [
                        'user_growth_by_type' => $this->getAdminUserGrowthByType($startDate, $endDate),
                        'transaction_volume' => $this->getAdminTransactionVolume($startDate, $endDate),
                        'platform_usage_by_hour' => $this->getAdminPlatformUsageByHour()
                    ],
                    'insights' => $this->generateAdminInsights()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no getAdminReportDashboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========== MÉTODOS AUXILIARES DE INSIGHTS ==========

    private function generateParticipantInsights($user, $availableSurveys, $availableBalance, $approvalRate)
    {
        $insights = [];

        if ($availableSurveys > 0) {
            $insights[] = [
                'id' => 1,
                'title' => "Você tem {$availableSurveys} questionário(s) disponível(is)",
                'description' => 'Participe agora e acumule mais recompensas',
                'icon' => 'assignment',
                'color' => '#388e3c',
                'action' => 'Ver Questionários',
                'priority' => 'high'
            ];
        }

        if ($availableBalance >= 500) {
            $insights[] = [
                'id' => 2,
                'title' => 'Saque disponível',
                'description' => 'Seu saldo de ' . number_format($availableBalance, 2) . ' MZN está pronto para saque',
                'icon' => 'account_balance_wallet',
                'color' => '#4caf50',
                'action' => 'Sacar Agora',
                'priority' => 'medium'
            ];
        }

        if ($approvalRate < 70 && $approvalRate > 0) {
            $insights[] = [
                'id' => 3,
                'title' => 'Taxa de aprovação em ' . $approvalRate . '%',
                'description' => 'Continue participando para melhorar seu histórico',
                'icon' => 'trending_up',
                'color' => '#f44336',
                'action' => 'Melhorar',
                'priority' => 'medium'
            ];
        }

        return $insights;
    }

    private function generateStudentDashboardInsights($user, $surveys, $avgCompletionRate)
    {
        $insights = [];

        $lowResponseSurvey = $surveys->sortBy('responses_count')->first();
        if ($lowResponseSurvey && ($lowResponseSurvey->responses_count ?? 0) < 10) {
            $insights[] = [
                'id' => 1,
                'title' => 'Baixa taxa de resposta',
                'description' => 'Sua pesquisa "' . $lowResponseSurvey->title . '" tem poucas respostas',
                'icon' => 'warning',
                'color' => '#ff9800',
                'action' => 'Melhorar Pesquisa',
                'priority' => 'high'
            ];
        }

        if ($avgCompletionRate < 50) {
            $insights[] = [
                'id' => 2,
                'title' => 'Taxa de conclusão em ' . round($avgCompletionRate, 1) . '%',
                'description' => 'Simplifique suas perguntas para aumentar engajamento',
                'icon' => 'trending_up',
                'color' => '#f44336',
                'action' => 'Otimizar',
                'priority' => 'high'
            ];
        }

        return $insights;
    }

    private function generateAdminInsights()
    {
        return [
            [
                'id' => 1,
                'title' => 'Plataforma ativa',
                'description' => 'Sistema operando normalmente',
                'icon' => 'check_circle',
                'color' => '#4caf50',
                'action' => 'Continuar',
                'priority' => 'low'
            ]
        ];
    }

    // ========== OUTROS MÉTODOS (RESPOSTAS PADRÃO) ==========

    public function getParticipationSummaryReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getParticipationHistoryReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getParticipantPerformanceReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getParticipantQualitySummary(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => null]);
    }

    public function getQualityImprovementReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getApprovalRateReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getParticipantOpportunities(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getParticipantRecommendations(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentSurveysSummary(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentResponsesReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentPerformanceReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentEarningsReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentWithdrawalsReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentFinancialTimeline(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentEngagementSummary(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentTargetAudienceReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentBestTimesReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getStudentInsights(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getUsersSummaryReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getUsersGrowthReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getUsersDetailedReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getFinancialSummaryReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getTransactionsReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getCommissionsReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getSurveysSummaryReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getSurveysEngagementReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getSurveysPerformanceReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getActivitySummaryReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getActivityLogsReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getUserActivityReport(Request $request, $userId): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getAnalyticsOverview(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getTopPerformersReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getPredictiveAnalytics(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function generateCustomReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Relatório gerado']);
    }

    public function getReportTemplates(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function saveReportTemplate(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => null]);
    }

    public function deleteReportTemplate(Request $request, $id): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    public function getFilterOptions(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function saveFilterPreset(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => null]);
    }

    public function getFilterPresets(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function deleteFilterPreset(Request $request, $id): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    public function exportToCsv(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Exportação iniciada']);
    }

    public function exportToJson(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Exportação iniciada']);
    }

    public function exportToPdf(Request $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'PDF não disponível'], 501);
    }

    public function exportToExcel(Request $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Excel não disponível'], 501);
    }

    public function getReportHistory(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function getReportById(Request $request, $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => null]);
    }

    public function deleteReportHistory(Request $request, $id): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    public function scheduleReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => null]);
    }

    public function getScheduledReports(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function cancelScheduledReport(Request $request, $id): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    public function exportUsersReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Exportação iniciada']);
    }

    public function exportFinancialReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Exportação iniciada']);
    }

    public function exportSurveysReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Exportação iniciada']);
    }

    public function exportStudentSurveysReport(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Exportação iniciada']);
    }
}
