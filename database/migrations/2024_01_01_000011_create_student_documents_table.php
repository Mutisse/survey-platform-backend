<?php
// database/migrations/2024_01_01_000011_create_student_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');

            // Check constraints
            $table->check("document_type IN ('student_card', 'enrollment_proof', 'other')");
            $table->check("status IN ('pending', 'approved', 'rejected')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_documents');
    }
};
