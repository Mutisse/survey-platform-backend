<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_documents')) {
            Schema::create('student_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');

                // Tipo de documento
                $table->enum('document_type', ['student_card', 'enrollment_proof', 'other']);

                // Informações do arquivo
                $table->string('file_path');
                $table->string('file_name');
                $table->string('mime_type');
                $table->integer('file_size');

                // Status
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('rejection_reason')->nullable();

                $table->timestamps();

                // Índices
                $table->index(['user_id', 'document_type']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
