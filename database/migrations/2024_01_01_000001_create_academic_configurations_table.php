<?php
// database/migrations/2024_01_01_000001_create_academic_configurations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('value', 100);
            $table->string('label');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_configurations');
    }
};
