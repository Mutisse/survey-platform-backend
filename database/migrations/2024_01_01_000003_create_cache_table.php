<?php
// database/migrations/2024_01_01_000003_create_cache_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');

            $table->index('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');

            $table->index('expiration');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
