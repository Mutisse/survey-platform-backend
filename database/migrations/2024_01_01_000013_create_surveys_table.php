<?php
// database/migrations/2024_01_01_000020_create_surveys_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ADICIONAR

return new class extends Migration
{
    public function up()
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('researcher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('institution');
            $table->integer('duration');
            $table->decimal('reward', 10, 2);
            $table->jsonb('requirements')->nullable();
            $table->integer('target_responses');
            $table->integer('current_responses')->default(0);
            $table->integer('responses_count')->default(0);
            $table->string('status')->default('pending');
            $table->jsonb('settings')->nullable();
            $table->jsonb('config')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->boolean('allow_anonymous')->default(false);
            $table->boolean('require_login')->default(false);
            $table->boolean('multiple_responses')->default(false);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_progress')->default(true);
            $table->text('confirmation_message')->nullable();
            $table->integer('time_limit')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('max_responses')->nullable();
            $table->boolean('notify_on_response')->default(false);
            $table->string('notify_email')->nullable();
            $table->jsonb('theme')->nullable();
            $table->integer('completion_rate')->nullable();
            $table->decimal('average_completion_time', 8, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('researcher_id');
            $table->index('category');
            $table->index('institution');
            $table->index('status');
            $table->index('published_at');
            $table->index('start_date');
            $table->index('end_date');

            // REMOVER esta linha:
            // $table->check("status IN ('draft', 'active', 'paused', 'completed', 'archived', 'pending')");
        });

        // ADICIONAR constraint CHECK separadamente:
        DB::statement("ALTER TABLE surveys ADD CONSTRAINT surveys_status_check CHECK (status IN ('draft', 'active', 'paused', 'completed', 'archived', 'pending'))");
    }

    public function down()
    {
        Schema::dropIfExists('surveys');
    }
};
