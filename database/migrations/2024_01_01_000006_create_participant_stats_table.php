<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ADICIONAR

return new class extends Migration
{
    public function up()
    {
        Schema::create('participant_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('province', 100)->nullable();
            $table->string('bi_number', 13)->unique()->nullable();
            $table->string('mpesa_number', 20);
            $table->string('occupation', 100);
            $table->string('education_level', 100)->nullable();
            $table->jsonb('research_interests')->nullable();
            $table->string('participation_frequency', 100)->nullable();
            $table->boolean('consent_data_collection')->default(false);
            $table->boolean('sms_notifications')->default(true);
            $table->integer('total_surveys_completed')->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0);
            $table->timestamp('last_survey_date')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('province');
            $table->index('occupation');
            $table->index('total_surveys_completed');
            $table->index('total_earnings');
            $table->index('last_survey_date');

            // REMOVER esta linha:
            // $table->check("gender IN ('masculino', 'feminino', 'outro')");
        });

        // ADICIONAR constraint CHECK separadamente:
        DB::statement("ALTER TABLE participant_stats ADD CONSTRAINT participant_stats_gender_check CHECK (gender IN ('masculino', 'feminino', 'outro') OR gender IS NULL)");
    }

    public function down()
    {
        Schema::dropIfExists('participant_stats');
    }
};
