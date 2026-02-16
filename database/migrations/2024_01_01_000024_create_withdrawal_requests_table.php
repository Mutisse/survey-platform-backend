<?php
// database/migrations/2024_01_01_000024_create_withdrawal_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('payment_method');
            $table->string('account_details')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('survey_id');
            $table->index('status');

            // Check constraints
            $table->check("status IN ('pending', 'processing', 'approved', 'rejected', 'completed', 'cancelled')");
            $table->check("payment_method IN ('mpesa', 'bank_transfer', 'cash')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
