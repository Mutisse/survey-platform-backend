<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->unique();
            $table->enum('role', ['student', 'participant', 'admin'])->default('participant');
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('whatsapp_notifications')->default(true);
            $table->json('profile_info')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('verification_status');
            $table->index(['role', 'verification_status']);
            $table->index('email');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
