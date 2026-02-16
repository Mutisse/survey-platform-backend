<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔔 Iniciando criação de notificações para todos os perfis...');

        // Primeiro, limpar notificações existentes
        $this->command->info('🧹 Limpando notificações existentes...');
        Notification::truncate();

        $this->createStudentNotifications();
        $this->createParticipantNotifications();
        $this->createAdminNotifications();
        $this->createResearcherNotifications();

        $this->command->info('✅ Notificações criadas com sucesso!');
    }

    /**
     * Criar notificações para estudantes
     */
    private function createStudentNotifications(): void
    {
        $this->command->info('🎓 Criando notificações para estudantes...');

        $students = User::where('role', 'student')->get();

        foreach ($students as $student) {
            // NOTIFICAÇÕES NÃO LIDAS (importantes)
            $unreadNotifications = [
                [
                    'type' => 'survey_approved',
                    'title' => 'Pesquisa aprovada!',
                    'message' => "Sua pesquisa 'Hábitos de Leitura em Universidades Moçambicanas' foi aprovada e está publicada.",
                    'icon' => 'check_circle',
                    'priority' => 2,
                    'data' => ['survey_id' => rand(1, 5), 'action' => 'view_survey'],
                    'created_at' => Carbon::now()->subHours(2)
                ],
                [
                    'type' => 'survey_response',
                    'title' => 'Nova resposta recebida',
                    'message' => "Sua pesquisa recebeu uma nova resposta. Total: 45/50 respostas.",
                    'icon' => 'assignment_turned_in',
                    'priority' => 1,
                    'data' => ['survey_id' => rand(1, 5), 'response_count' => 45],
                    'created_at' => Carbon::now()->subHours(6)
                ],
                [
                    'type' => 'payment_received',
                    'title' => 'Pagamento recebido',
                    'message' => 'Você recebeu MZN 250.00 pelas respostas da sua pesquisa.',
                    'icon' => 'payments',
                    'priority' => 2,
                    'data' => ['amount' => 250.00, 'currency' => 'MZN'],
                    'created_at' => Carbon::now()->subDays(1)
                ],
                [
                    'type' => 'survey_expiring',
                    'title' => 'Pesquisa expirando',
                    'message' => "Sua pesquisa 'Consumo de Energia Residencial' expira em 3 dias.",
                    'icon' => 'schedule',
                    'priority' => 3,
                    'data' => ['survey_id' => rand(6, 10), 'days_left' => 3],
                    'created_at' => Carbon::now()->subDays(2)
                ]
            ];

            // NOTIFICAÇÕES LIDAS (histórico)
            $readNotifications = [
                [
                    'type' => 'survey_completed',
                    'title' => 'Meta atingida!',
                    'message' => "Parabéns! Sua pesquisa atingiu 100% das respostas necessárias.",
                    'icon' => 'task_alt',
                    'priority' => 1,
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(3),
                    'data' => ['survey_id' => rand(11, 15)],
                    'created_at' => Carbon::now()->subDays(4)
                ],
                [
                    'type' => 'withdrawal_processed',
                    'title' => 'Saque realizado',
                    'message' => 'Seu saque de MZN 500.00 foi processado com sucesso.',
                    'icon' => 'account_balance_wallet',
                    'priority' => 2,
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(5),
                    'data' => ['amount' => 500.00],
                    'created_at' => Carbon::now()->subDays(5)
                ]
            ];

            // NOTIFICAÇÕES SISTEMA (para todos)
            $systemNotifications = [
                [
                    'type' => 'new_feature',
                    'title' => 'Nova funcionalidade',
                    'message' => 'Agora você pode exportar relatórios em PDF. Experimente!',
                    'icon' => 'new_releases',
                    'priority' => 1,
                    'is_read' => false,
                    'data' => ['feature' => 'export_pdf'],
                    'created_at' => Carbon::now()->subDays(1)
                ],
                [
                    'type' => 'system_maintenance',
                    'title' => 'Manutenção programada',
                    'message' => 'O sistema estará indisponível das 02:00 às 04:00 para manutenção.',
                    'icon' => 'build',
                    'priority' => 2,
                    'is_read' => false,
                    'data' => ['start_time' => '02:00', 'end_time' => '04:00'],
                    'created_at' => Carbon::now()->subDays(2)
                ]
            ];

            // Criar todas as notificações
            $allNotifications = array_merge($unreadNotifications, $readNotifications, $systemNotifications);

            foreach ($allNotifications as $notificationData) {
                Notification::create(array_merge(
                    [
                        'user_id' => $student->id,
                        'expires_at' => Carbon::now()->addDays(30),
                        'action_url' => '/dashboard',
                        'action_label' => 'Ver detalhes'
                    ],
                    $notificationData
                ));
            }

            $this->command->info("✅ Notificações criadas para estudante: {$student->name}");
        }
    }

    /**
     * Criar notificações para participantes
     */
    private function createParticipantNotifications(): void
    {
        $this->command->info('👥 Criando notificações para participantes...');

        $participants = User::where('role', 'participant')
                          ->where('verification_status', 'approved')
                          ->limit(8) // Apenas participantes aprovados
                          ->get();

        foreach ($participants as $participant) {
            // NOTIFICAÇÕES NÃO LIDAS
            $unreadNotifications = [
                [
                    'type' => 'survey_available',
                    'title' => 'Nova pesquisa disponível',
                    'message' => 'Pesquisa sobre hábitos de consumo - Ganhe MZN 30 por responder.',
                    'icon' => 'assignment',
                    'priority' => 1,
                    'data' => ['survey_id' => rand(16, 20), 'reward' => 30],
                    'created_at' => Carbon::now()->subHours(1)
                ],
                [
                    'type' => 'survey_available',
                    'title' => 'Pesquisa compatível com seu perfil',
                    'message' => 'Pesquisa sobre educação online para moradores de ' . ($participant->participantStats->province ?? 'sua região'),
                    'icon' => 'assignment',
                    'priority' => 1,
                    'data' => ['survey_id' => rand(21, 25), 'reward' => 25],
                    'created_at' => Carbon::now()->subHours(4)
                ],
                [
                    'type' => 'bonus_received',
                    'title' => 'Bônus recebido!',
                    'message' => 'Você ganhou um bônus de MZN 10 por responder 5 pesquisas este mês.',
                    'icon' => 'card_giftcard',
                    'priority' => 2,
                    'data' => ['amount' => 10, 'reason' => 'fidelity'],
                    'created_at' => Carbon::now()->subDays(1)
                ]
            ];

            // NOTIFICAÇÕES LIDAS
            $readNotifications = [
                [
                    'type' => 'response_completed',
                    'title' => 'Resposta completada',
                    'message' => 'Você completou a pesquisa "Tecnologia na Educação" - MZN 25 creditado.',
                    'icon' => 'done_all',
                    'priority' => 1,
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(2),
                    'data' => ['survey_id' => rand(26, 30), 'amount' => 25],
                    'created_at' => Carbon::now()->subDays(2)
                ],
                [
                    'type' => 'payment_credited',
                    'title' => 'Pagamento creditado',
                    'message' => 'Recebeu MZN 75.00 por pesquisas respondidas.',
                    'icon' => 'payments',
                    'priority' => 2,
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(3),
                    'data' => ['amount' => 75.00],
                    'created_at' => Carbon::now()->subDays(3)
                ],
                [
                    'type' => 'qualification_approved',
                    'title' => 'Qualificação aprovada',
                    'message' => 'Seu perfil agora se qualifica para pesquisas de nível superior.',
                    'icon' => 'verified',
                    'priority' => 2,
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(5),
                    'data' => ['new_level' => 'advanced'],
                    'created_at' => Carbon::now()->subDays(5)
                ]
            ];

            // NOTIFICAÇÕES LEMBRETES
            $reminderNotifications = [
                [
                    'type' => 'profile_update',
                    'title' => 'Atualize seu perfil',
                    'message' => 'Complete seu perfil para receber mais pesquisas compatíveis.',
                    'icon' => 'person',
                    'priority' => 1,
                    'is_read' => false,
                    'data' => ['completion_percentage' => '65%'],
                    'created_at' => Carbon::now()->subDays(7)
                ],
                [
                    'type' => 'weekly_summary',
                    'title' => 'Resumo semanal',
                    'message' => 'Esta semana você ganhou MZN 125.00 respondendo pesquisas. Continue assim!',
                    'icon' => 'assessment',
                    'priority' => 1,
                    'is_read' => true,
                    'read_at' => Carbon::now()->subDays(1),
                    'data' => ['earnings' => 125, 'surveys_completed' => 5],
                    'created_at' => Carbon::now()->subDays(1)
                ]
            ];

            $allNotifications = array_merge($unreadNotifications, $readNotifications, $reminderNotifications);

            foreach ($allNotifications as $notificationData) {
                Notification::create(array_merge(
                    [
                        'user_id' => $participant->id,
                        'expires_at' => Carbon::now()->addDays(15),
                        'action_url' => '/surveys',
                        'action_label' => 'Ver pesquisas'
                    ],
                    $notificationData
                ));
            }

            $this->command->info("✅ Notificações criadas para participante: {$participant->name}");
        }
    }

    /**
     * Criar notificações para administrador
     */
    private function createAdminNotifications(): void
    {
        $this->command->info('👑 Criando notificações para administrador...');

        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->warn('⚠️ Admin não encontrado, pulando...');
            return;
        }

        $notifications = [
            // NOTIFICAÇÕES URGENTES (alta prioridade)
            [
                'type' => 'system_alert',
                'title' => 'Alerta do sistema',
                'message' => 'Alta atividade de respostas detectada nas últimas 24h. Verifique logs.',
                'icon' => 'warning',
                'priority' => 3,
                'is_read' => false,
                'data' => ['activity_count' => 245],
                'created_at' => Carbon::now()->subHours(1)
            ],
            [
                'type' => 'new_user_registered',
                'title' => 'Novo estudante registrado',
                'message' => 'Ana Paula Macuácua registrou-se como estudante do ISCTEM.',
                'icon' => 'person_add',
                'priority' => 2,
                'is_read' => false,
                'data' => ['user_id' => 6, 'user_type' => 'student'],
                'created_at' => Carbon::now()->subHours(3)
            ],
            [
                'type' => 'survey_pending_review',
                'title' => 'Pesquisas pendentes',
                'message' => '3 pesquisas aguardando aprovação.',
                'icon' => 'pending_actions',
                'priority' => 2,
                'is_read' => false,
                'data' => ['pending_count' => 3],
                'created_at' => Carbon::now()->subHours(6)
            ],
            [
                'type' => 'withdrawal_requested',
                'title' => 'Novo pedido de saque',
                'message' => 'Carlos Mondlane solicitou saque de MZN 300.00.',
                'icon' => 'request_quote',
                'priority' => 2,
                'is_read' => false,
                'data' => ['user_id' => 2, 'amount' => 300.00],
                'created_at' => Carbon::now()->subHours(12)
            ],

            // NOTIFICAÇÕES LIDAS
            [
                'type' => 'user_verification_pending',
                'title' => 'Verificações pendentes',
                'message' => '2 usuários aguardando verificação de documentos.',
                'icon' => 'verified_user',
                'priority' => 1,
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(1),
                'data' => ['pending_verifications' => 2],
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'type' => 'batch_payment_processed',
                'title' => 'Pagamentos processados',
                'message' => 'Pagamentos em lote processados: 15 transações concluídas.',
                'icon' => 'payments',
                'priority' => 1,
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(2),
                'data' => ['transactions' => 15, 'total_amount' => 1250.00],
                'created_at' => Carbon::now()->subDays(2)
            ],
            [
                'type' => 'low_system_funds',
                'title' => 'Fundos do sistema baixos',
                'message' => 'Fundos do sistema: MZN 1.500 restantes. Considere recarregar.',
                'icon' => 'account_balance',
                'priority' => 2,
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(3),
                'data' => ['balance' => 1500.00, 'threshold' => 2000.00],
                'created_at' => Carbon::now()->subDays(3)
            ],

            // NOTIFICAÇÕES RELATÓRIOS
            [
                'type' => 'general_announcement',
                'title' => 'Relatório semanal disponível',
                'message' => 'Relatório semanal do sistema está disponível para consulta.',
                'icon' => 'assessment',
                'priority' => 1,
                'is_read' => false,
                'data' => ['report_id' => 1, 'period' => 'semanal'],
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'type' => 'high_activity',
                'title' => 'Alta atividade detectada',
                'message' => 'Pico de acessos detectado: 1.234 usuários ativos nas últimas 2h.',
                'icon' => 'trending_up',
                'priority' => 2,
                'is_read' => false,
                'data' => ['active_users' => 1234, 'time_period' => '2h'],
                'created_at' => Carbon::now()->subHours(2)
            ]
        ];

        foreach ($notifications as $notificationData) {
            Notification::create(array_merge(
                [
                    'user_id' => $admin->id,
                    'expires_at' => Carbon::now()->addDays(60),
                    'action_url' => '/admin/dashboard',
                    'action_label' => 'Administrar'
                ],
                $notificationData
            ));
        }

        $this->command->info("✅ Notificações criadas para admin: {$admin->name}");
    }

    /**
     * Criar notificações para o pesquisador
     */
    private function createResearcherNotifications(): void
    {
        $this->command->info('🔬 Criando notificações para o pesquisador...');

        $researcher = User::where('email', 'pesquisador.academico@mozpesquisa.ac.mz')->first();

        if (!$researcher) {
            $this->command->warn('⚠️ Pesquisador não encontrado, pulando...');
            return;
        }

        $notifications = [
            // NOTIFICAÇÕES DE PESQUISA
            [
                'type' => 'survey_response',
                'title' => 'Progresso da pesquisa',
                'message' => 'Sua pesquisa sobre metodologias de ensino já tem 120 respostas.',
                'icon' => 'assignment_turned_in',
                'priority' => 1,
                'is_read' => false,
                'data' => ['survey_id' => 31, 'responses' => 120],
                'created_at' => Carbon::now()->subHours(4)
            ],
            [
                'type' => 'deadline_alert',
                'title' => 'Prazo importante',
                'message' => 'Último dia para coletar dados da sua pesquisa de mestrado.',
                'icon' => 'event',
                'priority' => 3,
                'is_read' => false,
                'data' => ['deadline_date' => Carbon::now()->addDays(1)->format('Y-m-d')],
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'type' => 'research_reminder',
                'title' => 'Lembrete de análise',
                'message' => 'Lembre-se de analisar os dados coletados esta semana.',
                'icon' => 'analytics',
                'priority' => 2,
                'is_read' => false,
                'data' => ['reminder_type' => 'data_analysis'],
                'created_at' => Carbon::now()->subDays(2)
            ],

            // NOTIFICAÇÕES ACADÊMICAS - CORRIGIDO: 'feedback' → 'general_announcement'
            [
                'type' => 'survey_approved',
                'title' => 'Pesquisa aprovada pelo comitê',
                'message' => 'Sua pesquisa foi aprovada pelo comitê de ética.',
                'icon' => 'verified',
                'priority' => 2,
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(3),
                'data' => ['committee' => 'Comitê de Ética em Pesquisa'],
                'created_at' => Carbon::now()->subDays(3)
            ],
            [
                'type' => 'general_announcement',
                'title' => 'Comentários dos participantes',
                'message' => 'Os participantes deixaram comentários sobre sua pesquisa.',
                'icon' => 'forum',
                'priority' => 1,
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(4),
                'data' => ['feedback_count' => 8],
                'created_at' => Carbon::now()->subDays(4)
            ],

            // NOTIFICAÇÕES DE RECURSOS
            [
                'type' => 'general_announcement',
                'title' => 'Novo recurso disponível',
                'message' => 'Curso: "Análise de Dados Qualitativos" disponível na plataforma.',
                'icon' => 'school',
                'priority' => 1,
                'is_read' => false,
                'data' => ['course_id' => 1, 'course_title' => 'Análise de Dados Qualitativos'],
                'created_at' => Carbon::now()->subDays(5)
            ]
        ];

        foreach ($notifications as $notificationData) {
            Notification::create(array_merge(
                [
                    'user_id' => $researcher->id,
                    'expires_at' => Carbon::now()->addDays(45),
                    'action_url' => '/research/dashboard',
                    'action_label' => 'Ver pesquisa'
                ],
                $notificationData
            ));
        }

        $this->command->info("✅ Notificações criadas para pesquisador: {$researcher->name}");
    }
}
