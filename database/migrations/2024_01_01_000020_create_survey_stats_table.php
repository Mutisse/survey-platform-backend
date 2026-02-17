<?php
// database/migrations/2024_01_01_000019_create_survey_stats_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->integer('total_views')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('total_starts')->default(0);
            $table->integer('total_completions')->default(0);
            $table->integer('total_abandonments')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('average_completion_time', 8, 2)->nullable();
            $table->jsonb('device_stats')->nullable();
            $table->jsonb('location_stats')->nullable();
            $table->jsonb('response_distribution')->nullable();
            $table->jsonb('question_stats')->nullable();
            $table->date('stat_date');
            $table->timestamps();

            $table->index('survey_id');
            $table->index('stat_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_stats');
    }
};
