<?php
// database/migrations/2024_01_01_000023_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->unique();
            $table->foreignId('university_id')->nullable()->constrained()->onDelete('set null');
            $table->string('course')->nullable();
            $table->string('role')->default('participant');
            $table->string('verification_status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('whatsapp_notifications')->default(true);
            $table->jsonb('profile_info')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('verification_status');
            $table->index('university_id');

            // Check constraints
            $table->check("role IN ('student', 'participant', 'admin')");
            $table->check("verification_status IN ('pending', 'approved', 'rejected')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
