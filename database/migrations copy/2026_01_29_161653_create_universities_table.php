<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('universities')) {
            Schema::create('universities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('acronym')->nullable();
                $table->string('type');
                $table->string('location')->nullable();
                $table->string('website')->nullable();
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index('name');
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
