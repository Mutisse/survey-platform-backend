<?php
// database/migrations/2024_01_01_000014_create_survey_exports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('format');
            $table->string('filename');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('status')->default('processing');
            $table->jsonb('options')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('total_records')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_exports');
    }
};
