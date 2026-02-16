<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('academic_configurations')) {
            Schema::create('academic_configurations', function (Blueprint $table) {
                $table->id();

                // ✅ CORRIGIDO: Definir tamanhos explícitos
                $table->string('type', 50); // 'institution_types', 'courses', 'academic_levels', 'research_areas'
                $table->string('value', 100);
                $table->string('label', 255);

                $table->integer('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                // ✅ CORRIGIDO: Agora a chave única tem tamanho adequado
                // (50 + 100 = 150 bytes × 3 = 450 bytes < 1000 bytes)
                $table->unique(['type', 'value']);
                $table->index('type');
                $table->index('order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_configurations');
    }
};
