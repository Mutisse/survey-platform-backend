<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de categorias de pesquisa
        if (!Schema::hasTable('survey_categories')) {
            Schema::create('survey_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable()->default('#1976D2');
                $table->integer('survey_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();

                $table->index('slug');
                $table->index('is_active');
                $table->index('order');
            });
        }

        // 2. Tabela de instituições
        if (!Schema::hasTable('survey_institutions')) {
            Schema::create('survey_institutions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('abbreviation');
                $table->enum('type', ['university', 'college', 'research_center', 'company', 'ngo', 'government', 'other']);
                $table->string('logo_url')->nullable();
                $table->string('website')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_verified')->default(false);
                $table->integer('survey_count')->default(0);
                $table->timestamps();

                $table->index('abbreviation');
                $table->index('type');
                $table->index('is_verified');
                $table->index('survey_count');
            });
        }

        // 3. Tabela de pesquisas (surveys)
        if (!Schema::hasTable('surveys')) {
            Schema::create('surveys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('researcher_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category');
                $table->string('institution');
                $table->integer('duration'); // em minutos
                $table->decimal('reward', 10, 2); // em MZN
                $table->json('requirements')->nullable(); // array de requisitos
                $table->integer('target_responses');
                $table->integer('current_responses')->default(0);
                $table->integer('responses_count')->default(0); // total de respostas incluindo incompletas
                $table->enum('status', ['draft', 'active', 'paused', 'completed', 'archived', 'rejected'])->default('draft');
                $table->json('settings')->nullable(); // configurações adicionais
                $table->json('config')->nullable(); // configurações do builder
                $table->timestamp('published_at')->nullable();
                $table->decimal('total_earned', 12, 2)->default(0);
                $table->decimal('total_paid', 12, 2)->default(0);
                $table->boolean('allow_anonymous')->default(false);
                $table->boolean('require_login')->default(false);
                $table->boolean('multiple_responses')->default(false);
                $table->boolean('shuffle_questions')->default(false);
                $table->boolean('show_progress')->default(true);
                $table->text('confirmation_message')->nullable();
                $table->integer('time_limit')->nullable(); // em minutos
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->integer('max_responses')->nullable();
                $table->boolean('notify_on_response')->default(false);
                $table->string('notify_email')->nullable();
                $table->json('theme')->nullable(); // configurações de tema
                $table->integer('completion_rate')->nullable(); // taxa de conclusão em porcentagem
                $table->decimal('average_completion_time', 8, 2)->nullable(); // tempo médio em minutos
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index('user_id');
                $table->index('researcher_id');
                $table->index('category');
                $table->index('institution');
                $table->index('status');
                $table->index('published_at');
                $table->index('created_at');
                $table->index('start_date');
                $table->index('end_date');
            });
        }

        // 4. Tabela de perguntas (survey_questions)
        if (!Schema::hasTable('survey_questions')) {
            Schema::create('survey_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->onDelete('cascade');
                $table->text('question')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', [
                    'text',
                    'paragraph',
                    'multiple_choice',
                    'checkboxes',
                    'dropdown',
                    'linear_scale',
                    'date',
                    'time'
                ]);
                $table->json('options')->nullable(); // opções para múltipla escolha, checkbox, dropdown
                $table->string('placeholder')->nullable();
                $table->string('default_value')->nullable();
                $table->integer('min_length')->nullable();
                $table->integer('max_length')->nullable();
                $table->integer('min_value')->nullable(); // para escala linear
                $table->integer('max_value')->nullable(); // para escala linear
                $table->integer('scale_min')->nullable(); // para escala linear (mínimo)
                $table->integer('scale_max')->nullable(); // para escala linear (máximo)
                $table->integer('scale_step')->nullable(); // passo da escala
                $table->string('scale_low_label')->nullable(); // label baixo da escala
                $table->string('scale_high_label')->nullable(); // label alto da escala
                $table->integer('scale_value')->nullable(); // valor padrão da escala
                $table->string('low_label')->nullable(); // label baixo da escala (compatibilidade)
                $table->string('high_label')->nullable(); // label alto da escala (compatibilidade)
                $table->date('min_date')->nullable();
                $table->date('max_date')->nullable();
                $table->time('min_time')->nullable();
                $table->time('max_time')->nullable();
                $table->boolean('required')->default(false);
                $table->integer('order')->default(0);
                $table->string('image_url')->nullable();
                $table->json('validation_rules')->nullable(); // regras de validação específicas
                $table->json('metadata')->nullable(); // metadados adicionais
                $table->timestamps();

                // Indexes
                $table->index('survey_id');
                $table->index('type');
                $table->index('order');
                $table->index('required');
            });
        }

        // 5. Tabela de respostas (survey_responses)
        if (!Schema::hasTable('survey_responses')) {
            Schema::create('survey_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->json('answers'); // armazenar todas as respostas como JSON
                $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->integer('completion_time')->nullable(); // em segundos
                $table->string('device_type')->nullable(); // desktop, mobile, tablet
                $table->string('browser')->nullable();
                $table->string('browser_version')->nullable();
                $table->string('os')->nullable();
                $table->string('os_version')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('country')->nullable();
                $table->string('province')->nullable();
                $table->string('city')->nullable();
                $table->boolean('is_paid')->default(false);
                $table->decimal('payment_amount', 10, 2)->nullable();
                $table->timestamp('payment_date')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                // Indexes
                $table->unique(['survey_id', 'user_id']); // cada usuário só pode responder uma vez (se user_id não for null)
                $table->index('survey_id');
                $table->index('user_id');
                $table->index('status');
                $table->index('completed_at');
                $table->index('is_paid');
                $table->index('payment_date');
                $table->index('device_type');
                $table->index('province');
                $table->index('country');
            });
        }

        // 6. Tabela de estatísticas de pesquisa (survey_stats) - opcional
        if (!Schema::hasTable('survey_stats')) {
            Schema::create('survey_stats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->onDelete('cascade');
                $table->integer('total_views')->default(0);
                $table->integer('unique_visitors')->default(0);
                $table->integer('total_starts')->default(0);
                $table->integer('total_completions')->default(0);
                $table->integer('total_abandonments')->default(0);
                $table->decimal('completion_rate', 5, 2)->default(0); // em porcentagem
                $table->decimal('average_completion_time', 8, 2)->nullable(); // em segundos
                $table->json('device_stats')->nullable(); // estatísticas por dispositivo
                $table->json('location_stats')->nullable(); // estatísticas por localização
                $table->json('response_distribution')->nullable(); // distribuição de respostas
                $table->json('question_stats')->nullable(); // estatísticas por pergunta
                $table->date('stat_date'); // data da estatística (para tracking diário)
                $table->timestamps();

                // Indexes
                $table->index('survey_id');
                $table->index('stat_date');
                $table->unique(['survey_id', 'stat_date']);
            });
        }

        // 7. Tabela de exportações (survey_exports)
        if (!Schema::hasTable('survey_exports')) {
            Schema::create('survey_exports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('survey_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('format'); // csv, json, pdf, excel
                $table->string('filename');
                $table->string('file_path');
                $table->integer('file_size')->nullable();
                $table->string('status')->default('processing'); // processing, completed, failed
                $table->json('options')->nullable(); // opções de exportação
                $table->text('error_message')->nullable();
                $table->integer('total_records')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('survey_id');
                $table->index('user_id');
                $table->index('status');
                $table->index('expires_at');
            });
        }

        // 8. Tabela de imagens de pesquisa (survey_images)
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

                // Indexes
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
        // Remover na ordem inversa para evitar erros de chave estrangeira
        Schema::dropIfExists('survey_images');
        Schema::dropIfExists('survey_exports');
        Schema::dropIfExists('survey_stats');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('survey_institutions');
        Schema::dropIfExists('survey_categories');
    }
};
