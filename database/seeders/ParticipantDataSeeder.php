<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParticipantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Populando configurações para participantes...');

        // Províncias de Moçambique
        $this->seedProvinces();

        // Ocupações
        $this->seedOccupations();

        // Níveis de Educação
        $this->seedEducationLevels();

        // Áreas de Pesquisa
        $this->seedResearchAreas();

        // Frequência de Participação
        $this->seedParticipationFrequencies();

        $this->command->info('✅ Configurações para participantes populadas com sucesso!');
        $this->command->info('📊 Total de configurações: ' . DB::table('academic_configurations')->count());
    }

    /**
     * Seed das províncias de Moçambique
     */
    private function seedProvinces(): void
    {
        $provinces = [
            ['value' => 'Maputo Cidade', 'order' => 1],
            ['value' => 'Maputo Província', 'order' => 2],
            ['value' => 'Gaza', 'order' => 3],
            ['value' => 'Inhambane', 'order' => 4],
            ['value' => 'Sofala', 'order' => 5],
            ['value' => 'Manica', 'order' => 6],
            ['value' => 'Tete', 'order' => 7],
            ['value' => 'Zambézia', 'order' => 8],
            ['value' => 'Nampula', 'order' => 9],
            ['value' => 'Cabo Delgado', 'order' => 10],
            ['value' => 'Niassa', 'order' => 11],
        ];

        foreach ($provinces as $province) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'provinces',
                    'value' => $province['value']
                ],
                [
                    'label' => $province['value'],
                    'order' => $province['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('✅ 11 províncias populadas');
    }

    /**
     * Seed das ocupações
     */
    private function seedOccupations(): void
    {
        $occupations = [
            ['value' => 'Estudante', 'order' => 1],
            ['value' => 'Profissional', 'order' => 2],
            ['value' => 'Desempregado(a)', 'order' => 3],
            ['value' => 'Empresário(a)', 'order' => 4],
            ['value' => 'Funcionário Público', 'order' => 5],
            ['value' => 'Professor(a)', 'order' => 6],
            ['value' => 'Médico(a)/Enfermeiro(a)', 'order' => 7],
            ['value' => 'Engenheiro(a)', 'order' => 8],
            ['value' => 'Técnico(a)', 'order' => 9],
            ['value' => 'Agricultor(a)', 'order' => 10],
            ['value' => 'Comerciante', 'order' => 11],
            ['value' => 'Advogado(a)', 'order' => 12],
            ['value' => 'Estudante Universitário', 'order' => 13],
            ['value' => 'Outro', 'order' => 99],
        ];

        foreach ($occupations as $occupation) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'occupations',
                    'value' => $occupation['value']
                ],
                [
                    'label' => $occupation['value'],
                    'order' => $occupation['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('✅ 14 ocupações populadas');
    }

    /**
     * Seed dos níveis de educação
     */
    private function seedEducationLevels(): void
    {
        $educationLevels = [
            ['value' => 'Ensino Primário', 'order' => 1],
            ['value' => 'Ensino Secundário (até 10ª classe)', 'order' => 2],
            ['value' => 'Ensino Médio (12ª classe)', 'order' => 3],
            ['value' => 'Curso Técnico', 'order' => 4],
            ['value' => 'Ensino Superior Incompleto', 'order' => 5],
            ['value' => 'Licenciatura', 'order' => 6],
            ['value' => 'Pós-graduação/Mestrado', 'order' => 7],
            ['value' => 'Doutoramento', 'order' => 8],
        ];

        foreach ($educationLevels as $level) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'education_levels',
                    'value' => $level['value']
                ],
                [
                    'label' => $level['value'],
                    'order' => $level['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('✅ 8 níveis de educação populados');
    }

    /**
     * Seed das áreas de pesquisa
     */
    private function seedResearchAreas(): void
    {
        $researchAreas = [
            ['value' => 'saude', 'label' => 'Saúde e Bem-estar', 'order' => 1],
            ['value' => 'educacao', 'label' => 'Educação', 'order' => 2],
            ['value' => 'tecnologia', 'label' => 'Tecnologia e Inovação', 'order' => 3],
            ['value' => 'economia', 'label' => 'Economia e Finanças', 'order' => 4],
            ['value' => 'cultura', 'label' => 'Cultura e Arte', 'order' => 5],
            ['value' => 'politica', 'label' => 'Política e Governação', 'order' => 6],
            ['value' => 'ambiente', 'label' => 'Meio Ambiente', 'order' => 7],
            ['value' => 'social', 'label' => 'Questões Sociais', 'order' => 8],
            ['value' => 'agricultura', 'label' => 'Agricultura', 'order' => 9],
            ['value' => 'turismo', 'label' => 'Turismo', 'order' => 10],
        ];

        foreach ($researchAreas as $area) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'research_areas',
                    'value' => $area['value']
                ],
                [
                    'label' => $area['label'],
                    'order' => $area['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('✅ 10 áreas de pesquisa populadas');
    }

    /**
     * Seed das frequências de participação
     */
    private function seedParticipationFrequencies(): void
    {
        $participationFrequencies = [
            ['value' => 'Regularmente (várias vezes por semana)', 'order' => 1],
            ['value' => 'Frequentemente (1-2 vezes por semana)', 'order' => 2],
            ['value' => 'Ocasionalmente (1-2 vezes por mês)', 'order' => 3],
            ['value' => 'Raramente (quando disponível)', 'order' => 4],
            ['value' => 'Primeira vez', 'order' => 5],
        ];

        foreach ($participationFrequencies as $frequency) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'participation_frequencies',
                    'value' => $frequency['value']
                ],
                [
                    'label' => $frequency['value'],
                    'order' => $frequency['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info('✅ 5 frequências de participação populadas');
    }
}
