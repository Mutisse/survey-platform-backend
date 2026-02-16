<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // 1. ALTERAR o ENUM type para os novos valores
            // MySQL não permite alterar ENUM diretamente, vamos usar DB::statement

            // 2. ADICIONAR novas colunas
            if (!Schema::hasColumn('notifications', 'icon')) {
                $table->string('icon')->nullable()->after('message');
            }

            if (!Schema::hasColumn('notifications', 'action_url')) {
                $table->string('action_url')->nullable()->after('icon');
            }

            if (!Schema::hasColumn('notifications', 'action_label')) {
                $table->string('action_label')->nullable()->after('action_url');
            }

            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }

            if (!Schema::hasColumn('notifications', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('read_at');
            }

            if (!Schema::hasColumn('notifications', 'priority')) {
                $table->integer('priority')->default(1)->after('expires_at');
            }
        });

        // 3. Alterar o ENUM type usando SQL bruto (após adicionar colunas)
        $this->updateEnumType();

        // 4. Adicionar índices
        Schema::table('notifications', function (Blueprint $table) {
            // Index composto para user_id + is_read
            $indexName = 'notifications_user_id_is_read_index';
            if (!Schema::hasIndex('notifications', $indexName)) {
                $table->index(['user_id', 'is_read'], $indexName);
            }

            // Index composto para user_id + type
            $indexName = 'notifications_user_id_type_index';
            if (!Schema::hasIndex('notifications', $indexName)) {
                $table->index(['user_id', 'type'], $indexName);
            }

            // Index composto para user_id + created_at
            $indexName = 'notifications_user_id_created_at_index';
            if (!Schema::hasIndex('notifications', $indexName)) {
                $table->index(['user_id', 'created_at'], $indexName);
            }

            // Index para priority
            $indexName = 'notifications_priority_index';
            if (!Schema::hasIndex('notifications', $indexName)) {
                $table->index(['priority'], $indexName);
            }

            // Index para expires_at
            $indexName = 'notifications_expires_at_index';
            if (!Schema::hasIndex('notifications', $indexName)) {
                $table->index(['expires_at'], $indexName);
            }
        });
    }

    /**
     * Método para atualizar o ENUM type
     */
    private function updateEnumType(): void
    {
        // Lista completa dos novos tipos
        $newTypes = [
            // Estudantes
            'survey_response',
            'survey_approved',
            'survey_rejected',
            'survey_expiring',
            'survey_completed',
            'survey_published',
            'payment_received',
            'withdrawal_processed',
            'withdrawal_rejected',
            'low_balance',
            'research_reminder',
            'deadline_alert',

            // Participantes
            'survey_available',
            'survey_invitation',
            'response_completed',
            'payment_credited',
            'profile_update',
            'qualification_approved',
            'bonus_received',
            'rank_improved',
            'weekly_summary',
            'referral_bonus',

            // Administradores
            'new_user_registered',
            'survey_pending_review',
            'withdrawal_requested',
            'user_verification_pending',
            'system_alert',
            'batch_payment_processed',
            'low_system_funds',
            'abuse_reported',
            'high_activity',

            // Para todos os usuários
            'system_maintenance',
            'new_feature',
            'policy_update',
            'security_alert',
            'holiday_schedule',
            'app_update',
            'general_announcement',
            'important_reminder',
        ];

        // MySQL não suporta ALTER ENUM adicionando múltiplos valores de uma vez
        // Vamos alterar a coluna para TEXT temporariamente e depois para ENUM
        DB::statement('ALTER TABLE notifications MODIFY type TEXT');

        // Agora alteramos para o novo ENUM
        $enumString = "'" . implode("','", $newTypes) . "'";
        DB::statement("ALTER TABLE notifications MODIFY type ENUM($enumString) DEFAULT 'general_announcement'");
    }

    public function down(): void
    {
        // Rollback para a estrutura original
        Schema::table('notifications', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex('notifications_user_id_is_read_index');
            $table->dropIndex('notifications_user_id_type_index');
            $table->dropIndex('notifications_user_id_created_at_index');
            $table->dropIndex('notifications_priority_index');
            $table->dropIndex('notifications_expires_at_index');

            // Remover colunas adicionadas
            $columns = ['icon', 'action_url', 'action_label', 'read_at', 'expires_at', 'priority'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Reverter o ENUM type para os valores originais
        DB::statement("ALTER TABLE notifications MODIFY type ENUM('survey_response','payment','system','reminder','announcement','withdrawal')");
    }
};
