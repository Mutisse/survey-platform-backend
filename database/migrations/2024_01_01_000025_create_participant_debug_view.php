<?php
// database/migrations/2024_01_01_000025_create_participant_debug_view.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW vw_participant_debug AS
            SELECT
                u.id,
                u.name,
                u.email,
                u.balance as saldo_atual,
                COUNT(DISTINCT sr.id) as total_respostas,
                COUNT(DISTINCT CASE WHEN sr.quality_score >= 3 THEN sr.id END) as aprovadas,
                COUNT(DISTINCT CASE WHEN sr.quality_score < 3 THEN sr.id END) as rejeitadas,
                COUNT(DISTINCT CASE WHEN sr.status = 'in_progress' THEN sr.id END) as pendentes,
                AVG(sr.quality_score) as qualidade_media,
                COALESCE(SUM(sr.payment_amount), 0) as ganhos_totais,
                COALESCE(SUM(wr.amount), 0) as saques_totais,
                COALESCE(SUM(CASE WHEN t.status = 'pending' THEN t.amount END), 0) as ganhos_pendentes
            FROM users u
            LEFT JOIN survey_responses sr ON u.id = sr.user_id
            LEFT JOIN transactions t ON u.id = t.user_id AND t.type = 'survey_earnings'
            LEFT JOIN withdrawal_requests wr ON u.id = wr.user_id AND wr.status = 'completed'
            WHERE u.role IN ('student', 'participant')
            GROUP BY u.id, u.name, u.email, u.balance
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS vw_participant_debug");
    }
};
