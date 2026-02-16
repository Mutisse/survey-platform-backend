<?php
// database/migrations/2024_01_01_000009_create_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('report_type');
            $table->jsonb('filters')->nullable();
            $table->jsonb('columns')->nullable();
            $table->string('format')->default('pdf');
            $table->jsonb('schedule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('report_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('report_type');
            $table->string('title');
            $table->string('format');
            $table->jsonb('parameters')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_histories');
        Schema::dropIfExists('report_templates');
    }
};
