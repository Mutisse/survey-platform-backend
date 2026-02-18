<?php
// routes/api.php

use App\Http\Controllers\Api\UnifiedConfigController; // ✅ NOVO CONTROLLER
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Http\Controllers\Api\SurveyResponseController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\SystemMonitorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==============================================
// ✅ ROTAS PÚBLICAS - SEM AUTENTICAÇÃO
// ==============================================

// Dados acadêmicos (público) - ✅ AGORA USA UnifiedConfigController
Route::prefix('academic-data')->group(function () {
    Route::get('/universities', [UnifiedConfigController::class, 'getStudentConfigs']); // universities vem daqui
    Route::get('/institution-types', [UnifiedConfigController::class, 'getStudentConfigs']);
    Route::get('/courses', [UnifiedConfigController::class, 'getStudentConfigs']);
    Route::get('/academic-levels', [UnifiedConfigController::class, 'getStudentConfigs']);
    Route::get('/research-areas', [UnifiedConfigController::class, 'getParticipantConfigs']);
    Route::get('/all', [UnifiedConfigController::class, 'getAllConfigs']);
});

// Configurações (público) - ✅ USA UnifiedConfigController
Route::prefix('config')->group(function () {
    Route::get('/all', [UnifiedConfigController::class, 'getAllConfigs']);
    Route::get('/type/{tipo}', [UnifiedConfigController::class, 'listConfigs']); // mudado para listConfigs
    Route::get('/participant', [UnifiedConfigController::class, 'getParticipantConfigs']);
    Route::get('/student', [UnifiedConfigController::class, 'getStudentConfigs']);
    Route::get('/universities-list', [UnifiedConfigController::class, 'getStudentConfigs']); // universities daqui
});

// REMOVIDO: ConfigController antigo foi substituído

// Rotas públicas de surveys (apenas leitura)
Route::prefix('surveys')->group(function () {
    Route::get('/', [SurveyController::class, 'index']);
    Route::get('/categories', [SurveyController::class, 'categories']);
    Route::get('/institutions', [SurveyController::class, 'institutions']);
    Route::get('/global-stats', [SurveyController::class, 'globalStats']);
    Route::get('/{id}', [SurveyController::class, 'show']); // ✅ DETALHES PÚBLICO

    // Rota que requer autenticação
    Route::post('/{id}/start-response', [SurveyController::class, 'startResponse'])
        ->middleware(['auth:sanctum']);
});

// Autenticação pública
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/check-bi', [AuthController::class, 'checkBiNumber']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Registro público
Route::prefix('register')->group(function () {
    Route::post('/student', [StudentController::class, 'register']);
    Route::post('/participant', [ParticipantController::class, 'register']);
    Route::post('/regular', [AuthController::class, 'register']);
});

// Webhook de pagamento (público)
Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

// ==============================================
// ✅ ROTAS PROTEGIDAS - COM AUTENTICAÇÃO (SANCTUM)
// ==============================================
Route::middleware('auth:sanctum')->group(function () {

    // -------------------------------------------------
    // Rotas de autenticação
    // -------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateUser']);
        Route::put('/user/password', [AuthController::class, 'updatePassword']);
    });

    // -------------------------------------------------
    // Perfil do usuário
    // -------------------------------------------------
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'getCompleteProfile']);
        Route::put('/update', [ProfileController::class, 'updateProfile']);
        Route::put('/change-password', [ProfileController::class, 'changePassword']);
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('/remove-avatar', [ProfileController::class, 'removeAvatar']);
        Route::put('/specific-data', [ProfileController::class, 'updateSpecificData']);
        Route::post('/request-verification', [ProfileController::class, 'requestVerification']);
        Route::get('/stats', [ProfileController::class, 'getStats']);
        Route::get('/notification-settings', [ProfileController::class, 'getNotificationSettings']);
        Route::put('/notification-settings', [ProfileController::class, 'updateNotificationSettings']);
        Route::get('/activity', [ProfileController::class, 'getActivity']);
        Route::get('/export', [ProfileController::class, 'exportProfileData']);

        // Perfis específicos por role
        Route::prefix('participant')->middleware('role:participant')->group(function () {
            Route::get('/completed-surveys', [ProfileController::class, 'getParticipantCompletedSurveys']);
            Route::get('/metrics', [ProfileController::class, 'getParticipantMetrics']);
            Route::get('/reward-history', [ProfileController::class, 'getParticipantRewardHistory']);
            Route::post('/withdraw', [ProfileController::class, 'participantWithdraw']);
        });

        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('/dashboard', [ProfileController::class, 'getStudentDashboard']);
            Route::get('/surveys', [ProfileController::class, 'getStudentSurveysList']);
            Route::get('/earnings', [ProfileController::class, 'getStudentEarnings']);
            Route::get('/withdrawals', [ProfileController::class, 'getStudentWithdrawals']);
            Route::post('/withdraw', [ProfileController::class, 'studentWithdraw']);
        });

        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/stats', [ProfileController::class, 'getAdminSystemStats']);
            Route::get('/activity', [ProfileController::class, 'getAdminActivity']);
        });
    });

    // -------------------------------------------------
    // Pagamentos
    // -------------------------------------------------
    Route::prefix('payments')->group(function () {
        Route::post('/', [PaymentController::class, 'store']);
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/stats/summary', [PaymentController::class, 'summary']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::get('/{id}/status', [PaymentController::class, 'status']);
    });

    // -------------------------------------------------
    // Respostas de Surveys
    // -------------------------------------------------
    Route::prefix('survey-responses')->group(function () {
        Route::get('/', [SurveyResponseController::class, 'index']); // LISTAR COM FILTROS
        Route::get('/{id}', [SurveyResponseController::class, 'show']);
        Route::post('/', [SurveyResponseController::class, 'store']);
        Route::put('/{id}/progress', [SurveyResponseController::class, 'updateProgress']);
        Route::put('/{id}/complete', [SurveyResponseController::class, 'complete']);
        Route::delete('/{id}/cancel', [SurveyResponseController::class, 'cancel']);
    });

    // -------------------------------------------------
    // Notificações
    // -------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::get('/types', [NotificationController::class, 'types']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::get('/settings', [NotificationController::class, 'getSettings']);
        Route::put('/settings', [NotificationController::class, 'updateSettings']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/mark-multiple-read', [NotificationController::class, 'markMultipleAsRead']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/delete-multiple', [NotificationController::class, 'deleteMultiple']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
        Route::delete('/clear-expired', [NotificationController::class, 'clearExpired']);
        Route::post('/create', [NotificationController::class, 'create'])->middleware('role:admin');
    });

    // -------------------------------------------------
    // Upload de imagem
    // -------------------------------------------------
    Route::post('/upload-image', [SurveyController::class, 'uploadImage']);

    // -------------------------------------------------
    // Meus Surveys (para pesquisadores)
    // -------------------------------------------------
    Route::prefix('my-surveys')->group(function () {
        Route::post('/', [SurveyController::class, 'store']);
        Route::get('/{id}', [SurveyController::class, 'show']);
        Route::put('/{id}', [SurveyController::class, 'update']);
        Route::delete('/{id}', [SurveyController::class, 'destroy']);
        Route::post('/{id}/duplicate', [SurveyController::class, 'duplicate']);
        Route::post('/{id}/publish', [SurveyController::class, 'publish']);
        Route::post('/{id}/archive', [SurveyController::class, 'archive']);
        Route::get('/{id}/stats', [SurveyController::class, 'stats']);
        Route::get('/{id}/export', [SurveyController::class, 'export']);
        Route::get('/', [SurveyController::class, 'mySurveys']);
        Route::post('/{id}/respond', [SurveyController::class, 'respond']);
        Route::get('/my/responses', [SurveyController::class, 'myResponses']);
        Route::get('/available/list', [SurveyController::class, 'available']);
    });

    // -------------------------------------------------
    // Dashboard Estudante
    // -------------------------------------------------
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/profile', [StudentController::class, 'getProfile']);
        Route::put('/profile', [StudentController::class, 'updateProfile']);
        Route::get('/documents', [StudentController::class, 'getDocuments']);
        Route::post('/documents', [StudentController::class, 'uploadDocument']);
        Route::get('/stats', [StudentController::class, 'getStats']);

        Route::prefix('dashboard')->group(function () {
            Route::get('/', [StudentDashboardController::class, 'getDashboardData']);
            Route::get('/stats', [StudentDashboardController::class, 'getDashboardStats']);
            Route::get('/surveys', [StudentDashboardController::class, 'getStudentSurveys']);
            Route::get('/earnings', [StudentDashboardController::class, 'getEarnings']);
            Route::get('/withdrawals', [StudentDashboardController::class, 'getWithdrawals']);
            Route::post('/withdraw', [StudentDashboardController::class, 'requestWithdrawal']);
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        });
    });

    // -------------------------------------------------
    // Dashboard Participante
    // -------------------------------------------------
    Route::prefix('participant')->middleware('role:participant')->group(function () {
        Route::get('/profile', [ParticipantController::class, 'getProfile']);
        Route::put('/profile', [ParticipantController::class, 'updateProfile']);
        Route::get('/surveys', [ParticipantController::class, 'getAvailableSurveys']);
        Route::post('/surveys/{id}/respond', [ParticipantController::class, 'respondToSurvey']);
        Route::get('/earnings', [ParticipantController::class, 'getEarnings']);

        Route::prefix('dashboard')->group(function () {
            Route::get('/', [ParticipantController::class, 'getDashboardData']);
            Route::get('/transactions', [ParticipantController::class, 'getTransactions']);
            Route::get('/rankings', [ParticipantController::class, 'getRankings']);
            Route::post('/withdraw', [ParticipantController::class, 'requestWithdrawal']);
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::get('/payment-methods', [ParticipantController::class, 'getPaymentMethods']);
            Route::get('/withdrawal-settings', [ParticipantController::class, 'getWithdrawalSettings']);
        });
    });

    // -------------------------------------------------
    // Configurações (admin) - ✅ CRUD COMPLETO
    // -------------------------------------------------
    Route::prefix('config')->middleware('role:admin')->group(function () {
        Route::get('/types', [UnifiedConfigController::class, 'listTypes']); // Listar todos os tipos
        Route::post('/create', [UnifiedConfigController::class, 'createConfig']); // Criar
        Route::get('/list/{tipo}', [UnifiedConfigController::class, 'listConfigs']); // Listar por tipo
        Route::get('/{id}', [UnifiedConfigController::class, 'getConfig']); // Buscar por ID
        Route::put('/{id}', [UnifiedConfigController::class, 'updateConfig']); // Atualizar
        Route::delete('/{id}', [UnifiedConfigController::class, 'deleteConfig']); // Deletar
    });

    // -------------------------------------------------
    // ADMIN - Rotas principais (TUDO COMPLETO)
    // -------------------------------------------------
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'getDashboardData']);
        Route::get('/stats', [AdminController::class, 'getStats']);

        // Usuários
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/users/{id}', [AdminController::class, 'getUser']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::post('/users/bulk-actions', [AdminController::class, 'bulkUserActions']);
        Route::get('/users/export', [AdminController::class, 'exportUsers']);

        // Transações
        Route::get('/transactions', [AdminController::class, 'getTransactions']);
        Route::get('/transactions/recent', [AdminController::class, 'getRecentTransactions']);
        Route::put('/transactions/{id}/status', [AdminController::class, 'updateTransactionStatus']);
        Route::post('/transactions/process-payments', [AdminController::class, 'processPayments']);

        // ✅ SURVEYS - ROTAS COMPLETAS
        Route::get('/surveys', [AdminController::class, 'getSurveys']); // LISTAR
        Route::get('/surveys/{id}', [SurveyController::class, 'show']); // DETALHES
        Route::get('/surveys/{id}/responses', [SurveyController::class, 'getSurveyResponses']); // RESPOSTAS
        Route::get('/surveys/{id}/analytics', [SurveyController::class, 'getSurveyAnalytics']); // ANÁLISES
        Route::get('/surveys/{id}/export', [SurveyController::class, 'export']); // EXPORTAR
        Route::put('/surveys/{id}/status', [AdminController::class, 'updateSurveyStatus']); // MUDAR STATUS

        // Atividade e Relatórios
        Route::get('/activity', [AdminController::class, 'getActivity']);
        Route::post('/reports/generate', [AdminController::class, 'generateReport']);
        Route::get('/reports/{id}', [AdminController::class, 'getReport']);

        // ✅ Universidades e Configurações Acadêmicas (agora com UnifiedConfigController)
        Route::apiResource('universities', UnifiedConfigController::class); // Ajustar se necessário
        Route::put('/academic-configurations/{type}/toggle', [UnifiedConfigController::class, 'toggleConfiguration']);

        // Notificações
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    });

    // -------------------------------------------------
    // RELATÓRIOS - POR ROLE
    // -------------------------------------------------
    Route::prefix('reports')->group(function () {
        // Relatórios de Admin
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/dashboard', [ReportController::class, 'getAdminReportDashboard']);
            Route::get('/users/summary', [ReportController::class, 'getUsersSummaryReport']);
            Route::get('/users/growth', [ReportController::class, 'getUsersGrowthReport']);
            Route::get('/users/detailed', [ReportController::class, 'getUsersDetailedReport']);
            Route::post('/users/export', [ReportController::class, 'exportUsersReport']);
            Route::get('/financial/summary', [ReportController::class, 'getFinancialSummaryReport']);
            Route::get('/financial/transactions', [ReportController::class, 'getTransactionsReport']);
            Route::get('/financial/commissions', [ReportController::class, 'getCommissionsReport']);
            Route::post('/financial/export', [ReportController::class, 'exportFinancialReport']);
            Route::get('/surveys/summary', [ReportController::class, 'getSurveysSummaryReport']);
            Route::get('/surveys/engagement', [ReportController::class, 'getSurveysEngagementReport']);
            Route::get('/surveys/performance', [ReportController::class, 'getSurveysPerformanceReport']);
            Route::post('/surveys/export', [ReportController::class, 'exportSurveysReport']);
            Route::get('/activity/summary', [ReportController::class, 'getActivitySummaryReport']);
            Route::get('/activity/logs', [ReportController::class, 'getActivityLogsReport']);
            Route::get('/activity/user/{userId}', [ReportController::class, 'getUserActivityReport']);
            Route::get('/analytics/overview', [ReportController::class, 'getAnalyticsOverview']);
            Route::get('/analytics/top-performers', [ReportController::class, 'getTopPerformersReport']);
            Route::get('/analytics/predictive', [ReportController::class, 'getPredictiveAnalytics']);
            Route::post('/custom/generate', [ReportController::class, 'generateCustomReport']);
            Route::get('/custom/templates', [ReportController::class, 'getReportTemplates']);
            Route::post('/custom/templates', [ReportController::class, 'saveReportTemplate']);
            Route::delete('/custom/templates/{id}', [ReportController::class, 'deleteReportTemplate']);
        });

        // Relatórios de Estudante
        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('/dashboard', [ReportController::class, 'getStudentReportDashboard']);
            Route::get('/surveys/summary', [ReportController::class, 'getStudentSurveysSummary']);
            Route::get('/surveys/responses', [ReportController::class, 'getStudentResponsesReport']);
            Route::get('/surveys/performance', [ReportController::class, 'getStudentPerformanceReport']);
            Route::post('/surveys/export', [ReportController::class, 'exportStudentSurveysReport']);
            Route::get('/financial/earnings', [ReportController::class, 'getStudentEarningsReport']);
            Route::get('/financial/withdrawals', [ReportController::class, 'getStudentWithdrawalsReport']);
            Route::get('/financial/timeline', [ReportController::class, 'getStudentFinancialTimeline']);
            Route::get('/engagement/summary', [ReportController::class, 'getStudentEngagementSummary']);
            Route::get('/engagement/target-audience', [ReportController::class, 'getStudentTargetAudienceReport']);
            Route::get('/engagement/best-times', [ReportController::class, 'getStudentBestTimesReport']);
            Route::get('/insights', [ReportController::class, 'getStudentInsights']);
            Route::post('/insights/generate', [ReportController::class, 'generateStudentInsights']);
        });

        // Relatórios de Participante
        Route::prefix('participant')->middleware('role:participant')->group(function () {
            Route::get('/dashboard', [ReportController::class, 'getParticipantReportDashboard']);
            Route::get('/participation/summary', [ReportController::class, 'getParticipationSummaryReport']);
            Route::get('/participation/history', [ReportController::class, 'getParticipationHistoryReport']);
            Route::get('/participation/performance', [ReportController::class, 'getParticipantPerformanceReport']);
            Route::get('/financial/earnings', [ReportController::class, 'getParticipantEarningsReport']);
            Route::get('/financial/transactions', [ReportController::class, 'getParticipantTransactionsReport']);
            Route::get('/financial/rankings', [ReportController::class, 'getParticipantRankingsReport']);
            Route::get('/quality/summary', [ReportController::class, 'getParticipantQualitySummary']);
            Route::get('/quality/improvement', [ReportController::class, 'getQualityImprovementReport']);
            Route::get('/quality/approval-rate', [ReportController::class, 'getApprovalRateReport']);
            Route::get('/insights/opportunities', [ReportController::class, 'getParticipantOpportunities']);
            Route::get('/insights/recommendations', [ReportController::class, 'getParticipantRecommendations']);
        });

        // Endpoints compartilhados de exportação
        Route::post('/export/csv', [ReportController::class, 'exportToCsv']);
        Route::post('/export/json', [ReportController::class, 'exportToJson']);
        Route::post('/export/pdf', [ReportController::class, 'exportToPdf']);
        Route::post('/export/excel', [ReportController::class, 'exportToExcel']);

        // Filtros e presets
        Route::get('/filters/options', [ReportController::class, 'getFilterOptions']);
        Route::post('/filters/save', [ReportController::class, 'saveFilterPreset']);
        Route::get('/filters/presets', [ReportController::class, 'getFilterPresets']);
        Route::delete('/filters/presets/{id}', [ReportController::class, 'deleteFilterPreset']);

        // Histórico de relatórios
        Route::get('/history', [ReportController::class, 'getReportHistory']);
        Route::get('/history/{id}', [ReportController::class, 'getReportById']);
        Route::delete('/history/{id}', [ReportController::class, 'deleteReportHistory']);

        // Relatórios agendados
        Route::post('/schedule', [ReportController::class, 'scheduleReport']);
        Route::get('/scheduled', [ReportController::class, 'getScheduledReports']);
        Route::delete('/scheduled/{id}', [ReportController::class, 'cancelScheduledReport']);
    });
});



// ==============================================
// ROTAS DE MONITORAMENTO (ACTIVITY LOGS)
// ==============================================
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('logs')->group(function () {
    // 📋 ROTAS BÁSICAS
    Route::get('/', [ActivityLogController::class, 'index']);
    Route::get('/stats', [ActivityLogController::class, 'stats']);
    Route::get('/errors', [ActivityLogController::class, 'errors']);
    Route::get('/export', [ActivityLogController::class, 'export']);
    Route::delete('/clean', [ActivityLogController::class, 'clean']);

    // 📊 ROTAS PARA GRÁFICOS E ESTATÍSTICAS DETALHADAS
    Route::get('/chart-data', [ActivityLogController::class, 'chartData']);
    Route::get('/detailed-stats', [ActivityLogController::class, 'detailedStats']);

    // 👥 ROTAS COM PARÂMETROS ESPECÍFICOS
    Route::get('/user/{userId}', [ActivityLogController::class, 'userLogs']);
    Route::get('/subject/{subjectType}/{subjectId}', [ActivityLogController::class, 'subjectLogs']);

    // ⚠️ ROTA GENÉRICA POR ÚLTIMO (para não conflitar com as específicas)
    Route::get('/{id}', [ActivityLogController::class, 'show']);
});

// ==============================================
// ROTAS DE MONITORAMENTO DO SISTEMA
// ==============================================
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('monitoramento')->group(function () {
    Route::get('/dashboard_monitoramento', [SystemMonitorController::class, 'dashboard']);
    Route::get('/health', [SystemMonitorController::class, 'healthCheck']);
});


// ==============================================
// ✅ ROTAS DE TESTE E HEALTH CHECK
// ==============================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API está funcionando',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'message' => 'Conexão com banco de dados estabelecida',
            'database' => DB::connection()->getDatabaseName()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erro na conexão com banco de dados',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/debug-ssl', function () {
    $sslPath = storage_path('ssl/global-bundle.pem');

    $info = [
        'ssl_file_path' => $sslPath,
        'file_exists' => file_exists($sslPath),
        'is_readable' => is_readable($sslPath),
        'file_size' => file_exists($sslPath) ? filesize($sslPath) : null,
        'env_vars' => [
            'DB_SSL' => env('DB_SSL'),
            'DB_SSL_VERIFY' => env('DB_SSL_VERIFY'),
            'MYSQL_ATTR_SSL_CA' => env('MYSQL_ATTR_SSL_CA'),
        ]
    ];

    // Tentar conectar ao banco
    try {
        DB::connection()->getPdo();
        $info['db_connection'] = 'SUCCESS';
        $info['db_name'] = DB::connection()->getDatabaseName();
    } catch (\Exception $e) {
        $info['db_connection'] = 'ERROR';
        $info['db_error'] = $e->getMessage();
    }

    return response()->json($info);
});

// ==============================================
// ✅ FALLBACK - ROTA 404
// ==============================================
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint não encontrado. Verifique a URL e o método HTTP.'
    ], 404);
});
