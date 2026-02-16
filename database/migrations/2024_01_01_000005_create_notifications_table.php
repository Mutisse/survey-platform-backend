<?php
// database/migrations/2024_01_01_000005_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('general_announcement');
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('priority')->default(1);
            $table->jsonb('data')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('expires_at');
            $table->index('priority');

            // Check constraint for notification types
            $table->check("type IN ('survey_response', 'survey_approved', 'survey_rejected', 'payment_received', 'withdrawal_processed', 'general_announcement')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
