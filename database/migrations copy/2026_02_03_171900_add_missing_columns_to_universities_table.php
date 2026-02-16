<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Adicionar colunas que faltam de forma SEGURA
            // Verificar se cada coluna já existe antes de adicionar

            if (!Schema::hasColumn('universities', 'email')) {
                $table->string('email')->nullable()->after('website');
            }

            if (!Schema::hasColumn('universities', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (!Schema::hasColumn('universities', 'description')) {
                $table->text('description')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('universities', 'logo_url')) {
                $table->string('logo_url')->nullable()->after('description');
            }

            if (!Schema::hasColumn('universities', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('logo_url');
            }

            if (!Schema::hasColumn('universities', 'established_year')) {
                $table->integer('established_year')->nullable()->after('is_verified');
            }

            if (!Schema::hasColumn('universities', 'student_count')) {
                $table->integer('student_count')->default(0)->after('established_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Remover apenas as colunas que foram adicionadas por esta migration
            // Verificar se cada coluna existe antes de remover

            $columnsToRemove = [
                'email',
                'phone',
                'description',
                'logo_url',
                'is_verified',
                'established_year',
                'student_count'
            ];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('universities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
