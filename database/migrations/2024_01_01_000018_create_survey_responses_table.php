<?php
// database/migrations/2024_01_01_000018_create_survey_responses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->jsonb('answers')->nullable();
            $table->text('feedback')->nullable();
            $table->string('status')->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('completion_time')->nullable();
            $table->integer('time_spent')->nullable();
            $table->decimal('quality_score', 3, 1)->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('os')->nullable();
            $table->string('os_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('completed_at');
            $table->index('device_type');
            $table->index('country');
            $table->index('province');
            $table->index('is_paid');
            $table->index('payment_date');

            // Check constraint for status
            $table->check("status IN ('in_progress', 'completed', 'abandoned')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('survey_responses');
    }
};
