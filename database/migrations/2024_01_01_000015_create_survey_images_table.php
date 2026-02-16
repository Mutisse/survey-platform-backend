<?php
// database/migrations/2024_01_01_000015_create_survey_images_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->nullable()->constrained('survey_questions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('path');
            $table->string('url');
            $table->string('mime_type');
            $table->integer('size');
            $table->jsonb('metadata')->nullable();
            $table->boolean('is_temp')->default(true);
            $table->timestamp('temp_until')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->index('question_id');
            $table->index('user_id');
            $table->index('is_temp');
            $table->index('temp_until');
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_images');
    }
};
