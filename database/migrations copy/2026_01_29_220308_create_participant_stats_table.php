<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log; // ADICIONAR ESTA LINHA

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('participant_stats')) {
            Schema::create('participant_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                // ============ INFORMAÇÕES PESSOAIS ============
                $table->date('birth_date')->nullable();
                $table->enum('gender', ['masculino', 'feminino', 'outro'])->nullable();
                $table->string('province', 100)->nullable();
                $table->string('bi_number', 13)->nullable()->unique();

                // ============ INFORMAÇÕES DE PAGAMENTO ============
                $table->string('mpesa_number', 20);

                // ============ INFORMAÇÕES PROFISSIONAIS ============
                $table->string('occupation', 100);
                $table->string('education_level', 100)->nullable();

                // ============ PREFERÊNCIAS ============
                $table->json('research_interests')->nullable();
                $table->string('participation_frequency', 100)->nullable();
                $table->boolean('consent_data_collection')->default(false);
                $table->boolean('sms_notifications')->default(true);

                // ============ ESTATÍSTICAS ============
                $table->integer('total_surveys_completed')->default(0);
                $table->decimal('total_earnings', 10, 2)->default(0.00);
                $table->timestamp('last_survey_date')->nullable();

                // ============ METADADOS ============
                $table->json('metadata')->nullable();

                // ============ TIMESTAMPS ============
                $table->timestamps();

                // ============ ÍNDICES ============
                $table->index(['user_id']);
                $table->index(['province']);
                $table->index(['occupation']);
                $table->index(['total_surveys_completed']);
                $table->index(['total_earnings']);
                $table->index(['last_survey_date']);
            });

            // Log apenas se a classe Log existir
            if (class_exists('Illuminate\Support\Facades\Log')) {
                Log::info('✅ Tabela participant_stats criada com sucesso');
            }
        } else {
            if (class_exists('Illuminate\Support\Facades\Log')) {
                Log::info('⚠️ Tabela participant_stats já existe');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_stats');

        // Log apenas se a classe Log existir
        if (class_exists('Illuminate\Support\Facades\Log')) {
            Log::info('🗑️ Tabela participant_stats removida');
        }
    }
};
