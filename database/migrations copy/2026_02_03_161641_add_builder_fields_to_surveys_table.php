<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Atualizar tabela surveys
        Schema::table('surveys', function (Blueprint $table) {
            // Verificar se researcher_id já existe
            if (!Schema::hasColumn('surveys', 'researcher_id')) {
                $table->foreignId('researcher_id')->nullable()->after('user_id')
                    ->constrained('users')->onDelete('set null');
            }

            // Adicionar outras colunas que não existem
            $newColumns = [
                'responses_count' => ['type' => 'integer', 'default' => 0, 'after' => 'current_responses'],
                'config' => ['type' => 'json', 'nullable' => true, 'after' => 'settings'],
                'published_at' => ['type' => 'timestamp', 'nullable' => true, 'after' => 'status'],
                'total_earned' => ['type' => 'decimal', 'precision' => 12, 'scale' => 2, 'default' => 0],
                'total_paid' => ['type' => 'decimal', 'precision' => 12, 'scale' => 2, 'default' => 0],

                // Configurações do survey
                'allow_anonymous' => ['type' => 'boolean', 'default' => false],
                'require_login' => ['type' => 'boolean', 'default' => false],
                'multiple_responses' => ['type' => 'boolean', 'default' => false],
                'shuffle_questions' => ['type' => 'boolean', 'default' => false],
                'show_progress' => ['type' => 'boolean', 'default' => true],
                'confirmation_message' => ['type' => 'text', 'nullable' => true],

                // Limites e datas
                'time_limit' => ['type' => 'integer', 'nullable' => true],
                'start_date' => ['type' => 'timestamp', 'nullable' => true],
                'end_date' => ['type' => 'timestamp', 'nullable' => true],
                'max_responses' => ['type' => 'integer', 'nullable' => true],

                // Notificações
                'notify_on_response' => ['type' => 'boolean', 'default' => false],
                'notify_email' => ['type' => 'string', 'nullable' => true],

                // Tema
                'theme' => ['type' => 'json', 'nullable' => true],

                // Estatísticas
                'completion_rate' => ['type' => 'integer', 'nullable' => true],
                'average_completion_time' => ['type' => 'decimal', 'precision' => 8, 'scale' => 2, 'nullable' => true],
            ];

            foreach ($newColumns as $column => $config) {
                if (!Schema::hasColumn('surveys', $column)) {
                    $this->addColumn($table, $column, $config);
                }
            }

            // Adicionar índices apenas se não existirem
            $indexes = ['researcher_id', 'published_at', 'start_date', 'end_date'];
            foreach ($indexes as $index) {
                $indexName = "surveys_{$index}_index";
                if (!DB::select("SHOW INDEX FROM surveys WHERE Key_name = ?", [$indexName])) {
                    $table->index($index);
                }
            }
        });

        // 2. Atualizar a tabela survey_questions
        if (Schema::hasTable('survey_questions')) {
            Schema::table('survey_questions', function (Blueprint $table) {
                // Verificar se a coluna 'question' existe e renomear se necessário
                if (Schema::hasColumn('survey_questions', 'question') && !Schema::hasColumn('survey_questions', 'title')) {
                    $table->renameColumn('question', 'title');
                }

                // Adicionar colunas faltantes
                $questionColumns = [
                    'description' => ['type' => 'text', 'nullable' => true],
                    'placeholder' => ['type' => 'string', 'nullable' => true],
                    'default_value' => ['type' => 'string', 'nullable' => true],
                    'min_length' => ['type' => 'integer', 'nullable' => true],
                    'max_length' => ['type' => 'integer', 'nullable' => true],

                    // Para escala linear
                    'scale_min' => ['type' => 'integer', 'nullable' => true],
                    'scale_max' => ['type' => 'integer', 'nullable' => true],
                    'scale_step' => ['type' => 'integer', 'nullable' => true],
                    'scale_low_label' => ['type' => 'string', 'nullable' => true],
                    'scale_high_label' => ['type' => 'string', 'nullable' => true],
                    'scale_value' => ['type' => 'integer', 'nullable' => true],

                    // Para data/hora
                    'min_date' => ['type' => 'date', 'nullable' => true],
                    'max_date' => ['type' => 'date', 'nullable' => true],
                    'min_time' => ['type' => 'time', 'nullable' => true],
                    'max_time' => ['type' => 'time', 'nullable' => true],

                    // Metadata
                    'metadata' => ['type' => 'json', 'nullable' => true],
                ];

                foreach ($questionColumns as $column => $config) {
                    if (!Schema::hasColumn('survey_questions', $column)) {
                        $this->addColumn($table, $column, $config);
                    }
                }
            });
        }

        // 3. Atualizar a tabela survey_responses
        if (Schema::hasTable('survey_responses')) {
            Schema::table('survey_responses', function (Blueprint $table) {
                $responseColumns = [
                    'device_type' => ['type' => 'string', 'nullable' => true],
                    'browser' => ['type' => 'string', 'nullable' => true],
                    'browser_version' => ['type' => 'string', 'nullable' => true],
                    'os' => ['type' => 'string', 'nullable' => true],
                    'os_version' => ['type' => 'string', 'nullable' => true],
                    'ip_address' => ['type' => 'string', 'nullable' => true],
                    'country' => ['type' => 'string', 'nullable' => true],
                    'province' => ['type' => 'string', 'nullable' => true],
                    'city' => ['type' => 'string', 'nullable' => true],
                    'payment_method' => ['type' => 'string', 'nullable' => true],
                    'payment_reference' => ['type' => 'string', 'nullable' => true],
                    'metadata' => ['type' => 'json', 'nullable' => true],
                ];

                foreach ($responseColumns as $column => $config) {
                    if (!Schema::hasColumn('survey_responses', $column)) {
                        $this->addColumn($table, $column, $config);
                    }
                }

                // Renomear paid_at para payment_date se existir
                if (Schema::hasColumn('survey_responses', 'paid_at') && !Schema::hasColumn('survey_responses', 'payment_date')) {
                    $table->renameColumn('paid_at', 'payment_date');
                }

                // Adicionar índices apenas se não existirem
                $indexes = ['device_type', 'province', 'country', 'payment_date'];
                foreach ($indexes as $index) {
                    $indexName = "survey_responses_{$index}_index";
                    if (!DB::select("SHOW INDEX FROM survey_responses WHERE Key_name = ?", [$indexName])) {
                        $table->index($index);
                    }
                }
            });
        }

        // 4. Criar tabela survey_stats (se não existir)
        if (!Schema::hasTable('survey_stats')) {
            Schema::create('survey_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->onDelete('cascade');
                $table->integer('total_views')->default(0);
                $table->integer('unique_visitors')->default(0);
                $table->integer('total_starts')->default(0);
                $table->integer('total_completions')->default(0);
                $table->integer('total_abandonments')->default(0);
                $table->decimal('completion_rate', 5, 2)->default(0);
                $table->decimal('average_completion_time', 8, 2)->nullable();
                $table->json('device_stats')->nullable();
                $table->json('location_stats')->nullable();
                $table->json('response_distribution')->nullable();
                $table->json('question_stats')->nullable();
                $table->date('stat_date');
                $table->timestamps();

                $table->index('survey_id');
                $table->index('stat_date');
                $table->unique(['survey_id', 'stat_date']);
            });
        }

        // 5. Criar tabela survey_exports (se não existir)
        if (!Schema::hasTable('survey_exports')) {
            Schema::create('survey_exports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('format');
                $table->string('filename');
                $table->string('file_path');
                $table->integer('file_size')->nullable();
                $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
                $table->json('options')->nullable();
                $table->text('error_message')->nullable();
                $table->integer('total_records')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index('survey_id');
                $table->index('user_id');
                $table->index('status');
                $table->index('expires_at');
            });
        }

        // 6. Criar tabela survey_images (se não existir)
        if (!Schema::hasTable('survey_images')) {
            Schema::create('survey_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('question_id')->nullable()->constrained('survey_questions')->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('filename');
                $table->string('original_name');
                $table->string('path');
                $table->string('url');
                $table->string('mime_type');
                $table->integer('size');
                $table->json('metadata')->nullable();
                $table->boolean('is_temp')->default(true);
                $table->timestamp('temp_until')->nullable();
                $table->timestamps();

                $table->index('survey_id');
                $table->index('question_id');
                $table->index('user_id');
                $table->index('is_temp');
                $table->index('temp_until');
            });
        }
    }

    public function down(): void
    {
        // Reverter as mudanças
        Schema::table('surveys', function (Blueprint $table) {
            // Remover colunas adicionadas
            $columnsToDrop = [
                'researcher_id', 'responses_count', 'config', 'published_at',
                'total_earned', 'total_paid', 'allow_anonymous', 'require_login',
                'multiple_responses', 'shuffle_questions', 'show_progress',
                'confirmation_message', 'time_limit', 'start_date', 'end_date',
                'max_responses', 'notify_on_response', 'notify_email', 'theme',
                'completion_rate', 'average_completion_time'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('surveys', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remover tabelas criadas
        Schema::dropIfExists('survey_images');
        Schema::dropIfExists('survey_exports');
        Schema::dropIfExists('survey_stats');
    }

    /**
     * Método auxiliar para adicionar colunas de forma dinâmica
     */
    private function addColumn(Blueprint $table, string $column, array $config): void
    {
        $type = $config['type'];
        $nullable = $config['nullable'] ?? false;
        $default = $config['default'] ?? null;
        $after = $config['after'] ?? null;

        switch ($type) {
            case 'foreignId':
                $table->foreignId($column)->nullable($nullable)
                    ->after($after)
                    ->constrained('users')
                    ->onDelete('set null');
                break;
            case 'boolean':
                $table->boolean($column)->default($default)->after($after);
                break;
            case 'integer':
                $table->integer($column)->default($default)->nullable($nullable)->after($after);
                break;
            case 'decimal':
                $table->decimal($column, $config['precision'], $config['scale'])
                    ->default($default)
                    ->nullable($nullable)
                    ->after($after);
                break;
            case 'text':
                $table->text($column)->nullable($nullable)->after($after);
                break;
            case 'string':
                $table->string($column)->nullable($nullable)->after($after);
                break;
            case 'timestamp':
                $table->timestamp($column)->nullable($nullable)->after($after);
                break;
            case 'json':
                $table->json($column)->nullable($nullable)->after($after);
                break;
            case 'date':
                $table->date($column)->nullable($nullable)->after($after);
                break;
            case 'time':
                $table->time($column)->nullable($nullable)->after($after);
                break;
        }
    }
};
