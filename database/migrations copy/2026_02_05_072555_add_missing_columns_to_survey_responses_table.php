<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ADICIONE ESTA LINHA

return new class extends Migration
{
    public function up()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // Adicione colunas úteis para relatórios
            if (!Schema::hasColumn('survey_responses', 'quality_score')) {
                $table->decimal('quality_score', 3, 1)->nullable()->after('completion_time')
                    ->comment('Pontuação de qualidade da resposta (0-10)');
            }

            if (!Schema::hasColumn('survey_responses', 'time_spent')) {
                $table->integer('time_spent')->nullable()->after('completion_time')
                    ->comment('Tempo gasto em segundos (alias para completion_time)');
            }

            if (!Schema::hasColumn('survey_responses', 'feedback')) {
                $table->text('feedback')->nullable()->after('answers')
                    ->comment('Feedback opcional do respondente');
            }

            if (!Schema::hasColumn('survey_responses', 'rating')) {
                $table->tinyInteger('rating')->nullable()->after('quality_score')
                    ->comment('Avaliação da pesquisa (1-5 estrelas)');
            }
        });

        // Se time_spent foi adicionado, copie dados de completion_time
        if (Schema::hasColumn('survey_responses', 'time_spent') &&
            Schema::hasColumn('survey_responses', 'completion_time')) {
            DB::statement('UPDATE survey_responses SET time_spent = completion_time WHERE completion_time IS NOT NULL');
        }
    }

    public function down()
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['quality_score', 'time_spent', 'feedback', 'rating']);
        });
    }
};
