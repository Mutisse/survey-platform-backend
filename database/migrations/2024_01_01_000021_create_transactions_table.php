<?php
// database/migrations/2024_01_01_000021_create_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('account_details')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('survey_id');
            $table->index('status');
            $table->index('type');

            // Check constraints
           // $table->check("type IN ('survey_earnings', 'withdrawal', 'refund', 'bonus', 'adjustment')");
           // $table->check("status IN ('pending', 'completed', 'failed', 'cancelled')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
