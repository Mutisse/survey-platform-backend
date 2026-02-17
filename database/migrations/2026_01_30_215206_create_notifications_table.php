<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                // Estudantes
                'survey_response',        // Nova resposta na pesquisa
                'survey_approved',        // Pesquisa aprovada
                'survey_rejected',        // Pesquisa rejeitada
                'survey_expiring',        // Pesquisa expirando
                'survey_completed',       // Pesquisa completada (atingiu meta)
                'survey_published',       // Pesquisa publicada
                'payment_received',       // Pagamento recebido
                'withdrawal_processed',   // Saque processado
                'withdrawal_rejected',    // Saque rejeitado
                'low_balance',            // Saldo baixo
                'research_reminder',      // Lembrete de pesquisa
                'deadline_alert',         // Alerta de prazo

                // Participantes
                'survey_available',       // Nova pesquisa disponível
                'survey_invitation',      // Convite para pesquisa específica
                'response_completed',     // Resposta completada com sucesso
                'payment_credited',       // Pagamento creditado
                'profile_update',         // Atualização de perfil necessária
                'qualification_approved', // Qualificação aprovada
                'bonus_received',         // Bônus recebido
                'rank_improved',          // Ranking melhorado
                'weekly_summary',         // Resumo semanal
                'referral_bonus',         // Bônus por indicação

                // Administradores
                'new_user_registered',    // Novo usuário registrado
                'survey_pending_review',  // Pesquisa pendente de revisão
                'withdrawal_requested',   // Novo pedido de saque
                'user_verification_pending', // Usuário pendente verificação
                'system_alert',           // Alerta do sistema
                'batch_payment_processed',// Pagamento em lote processado
                'low_system_funds',       // Fundos do sistema baixos
                'abuse_reported',         // Abuso denunciado
                'high_activity',          // Alta atividade detectada

                // Para todos os usuários
                'system_maintenance',     // Manutenção do sistema
                'new_feature',            // Nova funcionalidade
                'policy_update',          // Atualização de política
                'security_alert',         // Alerta de segurança
                'holiday_schedule',       // Horário de feriado
                'app_update',             // Atualização do aplicativo
                'general_announcement',   // Anúncio geral
                'important_reminder',     // Lembrete importante
            ])->default('general_announcement');
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable(); // Ícone da notificação
            $table->string('action_url')->nullable(); // URL para ação
            $table->string('action_label')->nullable(); // Label do botão
            $table->json('data')->nullable(); // Dados adicionais (JSON)
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Data de expiração
            $table->integer('priority')->default(1); // 1=baixa, 2=média, 3=alta
            $table->timestamps();

            // Indexes para performance
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index('priority');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
