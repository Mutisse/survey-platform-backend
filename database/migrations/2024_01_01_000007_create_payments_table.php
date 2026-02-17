<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ADICIONAR

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

            // REMOVER estas linhas:
            // $table->check("currency IN ('MZN', 'USD', 'ZAR')");
            // $table->check("status IN ('pending', 'processing', 'completed', 'failed', 'cancelled')");
        });

        // ADICIONAR constraints CHECK separadamente:
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_currency_check CHECK (currency IN ('MZN', 'USD', 'ZAR'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('pending', 'processing', 'completed', 'failed', 'cancelled'))");
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
