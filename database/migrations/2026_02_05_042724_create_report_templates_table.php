<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('report_histories')) {
            Schema::create('report_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('report_type');
                $table->string('title');
                $table->string('format');
                $table->json('parameters')->nullable();
                $table->string('file_path')->nullable();
                $table->bigInteger('file_size')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
                $table->index('report_type');
            });
        } else {
            // Adicionar colunas faltantes se a tabela já existe
            Schema::table('report_histories', function (Blueprint $table) {
                if (!Schema::hasColumn('report_histories', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('id');
                }
                if (!Schema::hasColumn('report_histories', 'report_type')) {
                    $table->string('report_type')->after('user_id');
                }
                if (!Schema::hasColumn('report_histories', 'title')) {
                    $table->string('title')->after('report_type');
                }
                if (!Schema::hasColumn('report_histories', 'format')) {
                    $table->string('format')->after('title');
                }
                if (!Schema::hasColumn('report_histories', 'parameters')) {
                    $table->json('parameters')->nullable()->after('format');
                }
                if (!Schema::hasColumn('report_histories', 'file_path')) {
                    $table->string('file_path')->nullable()->after('parameters');
                }
                if (!Schema::hasColumn('report_histories', 'file_size')) {
                    $table->bigInteger('file_size')->nullable()->after('file_path');
                }
                if (!Schema::hasColumn('report_histories', 'generated_at')) {
                    $table->timestamp('generated_at')->nullable()->after('file_size');
                }
            });
        }
    }

    public function down()
    {
        // Não apagar a tabela
        // Schema::dropIfExists('report_histories');
    }
};
