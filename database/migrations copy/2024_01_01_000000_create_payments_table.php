<?php
// database/migrations/2024_01_01_000000_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_intent_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('MZN');
            $table->string('customer_phone', 20);
            $table->string('payment_method')->default('mpesa');
            $table->string('provider')->nullable();
            $table->string('status')->default('pending'); // pending, success, failed, processing
            $table->string('mpesa_reference')->nullable();
            $table->string('mpesa_response_code')->nullable();
            $table->text('mpesa_response_message')->nullable();
            $table->json('metadata')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->softDeletes(); // Para exclusão lógica
            $table->timestamps();

            $table->index('status');
            $table->index('customer_phone');
            $table->index('mpesa_reference');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
