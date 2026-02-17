<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ADICIONAR

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('bi_number', 13)->unique();
            $table->date('birth_date')->nullable();
            $table->string('gender');
            $table->string('institution_type')->nullable();
            $table->string('university');
            $table->string('course');
            $table->integer('admission_year')->nullable();
            $table->integer('expected_graduation')->nullable();
            $table->string('academic_level')->nullable();
            $table->string('student_card_number')->nullable();
            $table->jsonb('research_interests')->nullable();
            $table->boolean('documents_submitted')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('university');
            $table->index('course');
            $table->index('documents_submitted');

            // REMOVER esta linha:
            // $table->check("gender IN ('masculino', 'feminino')");
        });

        // ADICIONAR constraint CHECK separadamente:
        DB::statement("ALTER TABLE student_stats ADD CONSTRAINT student_stats_gender_check CHECK (gender IN ('masculino', 'feminino'))");
    }

    public function down()
    {
        Schema::dropIfExists('student_stats');
    }
};
