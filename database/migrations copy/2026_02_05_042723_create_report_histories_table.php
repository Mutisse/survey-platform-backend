<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('report_templates')) {
            Schema::create('report_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->string('report_type');
                $table->json('filters')->nullable();
                $table->json('columns')->nullable();
                $table->string('format')->default('json');
                $table->json('schedule')->nullable();
                $table->json('recipients')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_public')->default(false);
                $table->boolean('is_filter_preset')->default(false);
                $table->boolean('is_scheduled')->default(false);
                $table->timestamp('next_run')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
                $table->index(['is_scheduled', 'next_run']);
            });
        } else {
            // Adicionar colunas faltantes se a tabela já existe
            Schema::table('report_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('report_templates', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('id');
                }
                if (!Schema::hasColumn('report_templates', 'name')) {
                    $table->string('name')->after('user_id');
                }
                if (!Schema::hasColumn('report_templates', 'report_type')) {
                    $table->string('report_type')->after('name');
                }
                if (!Schema::hasColumn('report_templates', 'filters')) {
                    $table->json('filters')->nullable()->after('report_type');
                }
                if (!Schema::hasColumn('report_templates', 'columns')) {
                    $table->json('columns')->nullable()->after('filters');
                }
                if (!Schema::hasColumn('report_templates', 'format')) {
                    $table->string('format')->default('json')->after('columns');
                }
                if (!Schema::hasColumn('report_templates', 'schedule')) {
                    $table->json('schedule')->nullable()->after('format');
                }
                if (!Schema::hasColumn('report_templates', 'recipients')) {
                    $table->json('recipients')->nullable()->after('schedule');
                }
                if (!Schema::hasColumn('report_templates', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('recipients');
                }
                if (!Schema::hasColumn('report_templates', 'is_public')) {
                    $table->boolean('is_public')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('report_templates', 'is_filter_preset')) {
                    $table->boolean('is_filter_preset')->default(false)->after('is_public');
                }
                if (!Schema::hasColumn('report_templates', 'is_scheduled')) {
                    $table->boolean('is_scheduled')->default(false)->after('is_filter_preset');
                }
                if (!Schema::hasColumn('report_templates', 'next_run')) {
                    $table->timestamp('next_run')->nullable()->after('is_scheduled');
                }
            });
        }
    }

    public function down()
    {
        // Não apagar a tabela, apenas remover colunas se necessário
        // Schema::dropIfExists('report_templates');
    }
};
