<?php
// database/migrations/2024_01_01_000007_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
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
            $table->string('status')->default('pending');
            $table->string('mpesa_reference')->nullable();
            $table->string('mpesa_response_code')->nullable();
            $table->text('mpesa_response_message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->string('idempotency_key')->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('customer_phone');
            $table->index('status');
            $table->index('mpesa_reference');
            $table->index('created_at');

            // Check constraints
            $table->check("currency IN ('MZN', 'USD', 'ZAR')");
            $table->check("status IN ('pending', 'processing', 'completed', 'failed', 'cancelled')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
