<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_stats')) {
            Schema::create('student_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                // Informações pessoais
                $table->string('bi_number', 13)->unique();
                $table->date('birth_date')->nullable();
                $table->enum('gender', ['masculino', 'feminino']);

                // Informações acadêmicas
                $table->string('institution_type')->nullable();
                $table->string('university');
                $table->string('course');
                $table->integer('admission_year')->nullable();
                $table->integer('expected_graduation')->nullable();
                $table->string('academic_level')->nullable();
                $table->string('student_card_number')->nullable();
                $table->json('research_interests')->nullable();
                $table->boolean('documents_submitted')->default(false);

                $table->timestamps();

                // Índices
                $table->index('bi_number');
                $table->index('university');
                $table->index('course');
                $table->index('documents_submitted');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_stats');
    }
};
