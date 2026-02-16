<?php
// database/migrations/2024_01_01_000022_create_universities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acronym')->nullable();
            $table->string('type');
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->integer('established_year')->nullable();
            $table->integer('student_count')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('name');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('universities');
    }
};
