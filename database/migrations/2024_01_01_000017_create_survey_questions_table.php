<?php
// database/migrations/2024_01_01_000017_create_survey_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->text('question')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('text');
            $table->jsonb('options')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->integer('scale_min')->nullable();
            $table->integer('scale_max')->nullable();
            $table->integer('scale_step')->nullable();
            $table->string('scale_low_label')->nullable();
            $table->string('scale_high_label')->nullable();
            $table->integer('scale_value')->nullable();
            $table->string('low_label')->nullable();
            $table->string('high_label')->nullable();
            $table->date('min_date')->nullable();
            $table->date('max_date')->nullable();
            $table->time('min_time')->nullable();
            $table->time('max_time')->nullable();
            $table->boolean('required')->default(false);
            $table->integer('order')->default(0);
            $table->string('image_url')->nullable();
            $table->jsonb('validation_rules')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->index('type');
            $table->index('required');
            $table->index('order');

            // Check constraint for question types
            $table->check("type IN ('text', 'paragraph', 'multiple_choice', 'checkbox', 'dropdown', 'linear_scale', 'date', 'time', 'file_upload', 'rating', 'matrix', 'ranking', 'slider')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_questions');
    }
};
