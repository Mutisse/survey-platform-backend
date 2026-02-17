<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar colunas necessárias para surveys
        Schema::table('users', function (Blueprint $table) {
            // Se phone não existe, adicionar
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            // Adicionar colunas relacionadas a surveys
            if (!Schema::hasColumn('users', 'university_id')) {
                $table->foreignId('university_id')->nullable()->after('phone')->constrained('universities');
            }

            if (!Schema::hasColumn('users', 'course')) {
                $table->string('course')->nullable()->after('university_id');
            }

            if (!Schema::hasColumn('users', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('course');
            }

            if (!Schema::hasColumn('users', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('balance');
            }

            if (!Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('verification_status');
            }

            if (!Schema::hasColumn('users', 'whatsapp_notifications')) {
                $table->boolean('whatsapp_notifications')->default(true)->after('email_notifications');
            }

            // Indexes - TODOS JÁ EXISTEM, COMENTAR TODOS
            // $table->index('university_id');        // JÁ EXISTE
            // $table->index('verification_status');  // JÁ EXISTE
            // $table->index('role');                 // JÁ EXISTE
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remover apenas as colunas que adicionamos
            $columnsToDrop = ['phone', 'university_id', 'course', 'balance', 'verification_status', 'email_notifications', 'whatsapp_notifications'];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Remover índices - COMENTAR TAMBÉM NO down()
            // $table->dropIndex(['university_id']);        // Não remover, pois já existia
            // $table->dropIndex(['verification_status']);  // Não remover, pois já existia
            // $table->dropIndex(['role']);                 // Não remover, pois já existia
        });
    }
};
