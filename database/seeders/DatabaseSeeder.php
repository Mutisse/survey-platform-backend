<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando população do banco de dados...');

        // ORDEM CRÍTICA:
        // 1. Primeiro: dados acadêmicos (universidades, etc.)
        $this->command->info('📚 Executando AcademicDataSeeder...');
        $this->call(AcademicDataSeeder::class);

        // 2. Segundo: usuários (precisam das universidades já criadas)
        $this->command->info('👤 Executando UserSeeder...');
        $this->call(UserSeeder::class);

        // 3. Terceiro: surveys (precisam dos usuários já criados)
        $this->command->info('📊 Executando SurveySeeder...');
        $this->call(SurveySeeder::class);

        // 4. Opcional: dados adicionais de participantes
        $this->command->info('👥 Executando ParticipantDataSeeder...');
        $this->call(ParticipantDataSeeder::class);

        $this->command->info('🎉 Banco de dados populado com sucesso!');
        $this->command->info('==========================================');
        $this->command->info('📈 RESUMO:');
        $this->command->info('- Universidades e cursos acadêmicos');
        $this->command->info('- 16 usuários (admin, estudantes, participantes)');
        $this->command->info('- 3 pesquisas com 13 perguntas');
        $this->command->info('- Dados adicionais de participantes');
        $this->command->info('==========================================');
    }
}
