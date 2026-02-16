<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedUniversities();
            $this->seedInstitutionTypes();
            $this->seedCourses();
            $this->seedAcademicLevels();
            $this->seedResearchAreas();
        });

        $this->command->info('✅ Dados acadêmicos populados com sucesso!');
    }

    private function seedUniversities(): void
    {
        $universities = [
            // Públicas
            [
                'name' => 'Universidade Eduardo Mondlane (UEM)',
                'acronym' => 'UEM',
                'type' => 'Universidade Pública',
                'location' => 'Maputo',
                'website' => 'https://www.uem.mz',
                'order' => 1,
            ],
            [
                'name' => 'Universidade Pedagógica (UP)',
                'acronym' => 'UP',
                'type' => 'Universidade Pública',
                'location' => 'Maputo',
                'website' => 'https://www.up.ac.mz',
                'order' => 2,
            ],
            [
                'name' => 'Universidade Lúrio (UniLúrio)',
                'acronym' => 'UniLúrio',
                'type' => 'Universidade Pública',
                'location' => 'Nampula',
                'website' => 'https://www.unilurio.ac.mz',
                'order' => 3,
            ],
            [
                'name' => 'Universidade Zambeze (UniZambeze)',
                'acronym' => 'UniZambeze',
                'type' => 'Universidade Pública',
                'location' => 'Beira',
                'website' => 'https://www.unizambeze.ac.mz',
                'order' => 4,
            ],
            [
                'name' => 'Universidade Save (UniSave)',
                'acronym' => 'UniSave',
                'type' => 'Universidade Pública',
                'location' => 'Inhambane',
                'website' => 'https://www.unisave.ac.mz',
                'order' => 5,
            ],
            [
                'name' => 'Universidade Rovuma (UniRovuma)',
                'acronym' => 'UniRovuma',
                'type' => 'Universidade Pública',
                'location' => 'Nampula',
                'website' => 'https://www.unirovuma.ac.mz',
                'order' => 6,
            ],
            [
                'name' => 'Universidade Licungo (UniLicungo)',
                'acronym' => 'UniLicungo',
                'type' => 'Universidade Pública',
                'location' => 'Quelimane',
                'website' => 'https://www.unilicungo.ac.mz',
                'order' => 7,
            ],

            // Privadas
            [
                'name' => 'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)',
                'acronym' => 'ISCTEM',
                'type' => 'Universidade Privada',
                'location' => 'Maputo',
                'website' => 'https://www.isctem.ac.mz',
                'order' => 8,
            ],
            [
                'name' => 'Instituto Superior de Transportes e Comunicações (ISUTC)',
                'acronym' => 'ISUTC',
                'type' => 'Universidade Privada',
                'location' => 'Maputo',
                'website' => 'https://www.isutc.ac.mz',
                'order' => 9,
            ],
            [
                'name' => 'Universidade São Tomás de Moçambique (USTM)',
                'acronym' => 'USTM',
                'type' => 'Universidade Privada',
                'location' => 'Maputo',
                'website' => 'https://www.ustm.ac.mz',
                'order' => 10,
            ],
            [
                'name' => 'Universidade Técnica de Moçambique (UDM)',
                'acronym' => 'UDM',
                'type' => 'Universidade Privada',
                'location' => 'Maputo',
                'website' => 'https://www.udm.ac.mz',
                'order' => 11,
            ],
            [
                'name' => 'Universidade Politécnica (UniPoli)',
                'acronym' => 'UniPoli',
                'type' => 'Universidade Privada',
                'location' => 'Maputo',
                'website' => 'https://www.unipoli.ac.mz',
                'order' => 12,
            ],

            // Institutos
            [
                'name' => 'Instituto Superior de Ciências de Saúde (ISCISA)',
                'acronym' => 'ISCISA',
                'type' => 'Instituto Superior',
                'location' => 'Maputo',
                'website' => 'https://www.iscisa.ac.mz',
                'order' => 13,
            ],
            [
                'name' => 'Instituto Superior de Tecnologias e Gestão (ISTEG)',
                'acronym' => 'ISTEG',
                'type' => 'Instituto Superior',
                'location' => 'Maputo',
                'website' => 'https://www.isteg.ac.mz',
                'order' => 14,
            ],
            [
                'name' => 'Instituto Superior Monitor (ISM)',
                'acronym' => 'ISM',
                'type' => 'Instituto Superior',
                'location' => 'Maputo',
                'website' => 'https://www.ism.ac.mz',
                'order' => 15,
            ],
            [
                'name' => 'Outra',
                'acronym' => null,
                'type' => 'Outra',
                'location' => null,
                'website' => null,
                'order' => 99,
            ],
        ];

        foreach ($universities as $university) {
            DB::table('universities')->updateOrInsert(
                ['name' => $university['name']],
                $university
            );
        }

        $this->command->info('✅ Universidades populadas: ' . count($universities));
    }

    private function seedInstitutionTypes(): void
    {
        $types = [
            ['value' => 'Universidade Pública', 'order' => 1],
            ['value' => 'Universidade Privada', 'order' => 2],
            ['value' => 'Instituto Superior', 'order' => 3],
            ['value' => 'Escola Superior', 'order' => 4],
            ['value' => 'Outra', 'order' => 5],
        ];

        foreach ($types as $type) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'institution_types',
                    'value' => $type['value']
                ],
                [
                    'label' => $type['value'],
                    'order' => $type['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Tipos de instituição: ' . count($types));
    }

    private function seedCourses(): void
    {
        $courses = [
            'Engenharia Informática' => 1,
            'Medicina' => 2,
            'Direito' => 3,
            'Economia' => 4,
            'Administração e Gestão' => 5,
            'Contabilidade' => 6,
            'Enfermagem' => 7,
            'Arquitetura' => 8,
            'Engenharia Civil' => 9,
            'Psicologia' => 10,
            'Sociologia' => 11,
            'Ciências da Educação' => 12,
            'Biologia' => 13,
            'Química' => 14,
            'Matemática' => 15,
            'Física' => 16,
            'História' => 17,
            'Geografia' => 18,
            'Línguas e Literaturas' => 19,
            'Artes' => 20,
            'Turismo' => 21,
            'Hotelaria' => 22,
            'Agronomia' => 23,
            'Veterinária' => 24,
            'Outro' => 99,
        ];

        foreach ($courses as $course => $order) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'courses',
                    'value' => $course
                ],
                [
                    'label' => $course,
                    'order' => $order,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Cursos: ' . count($courses));
    }

    private function seedAcademicLevels(): void
    {
        $levels = [
            'Licenciatura - 1º ano' => 1,
            'Licenciatura - 2º ano' => 2,
            'Licenciatura - 3º ano' => 3,
            'Licenciatura - 4º ano' => 4,
            'Licenciatura - 5º ano' => 5,
            'Pós-graduação' => 6,
            'Mestrado' => 7,
            'Doutoramento' => 8,
        ];

        foreach ($levels as $level => $order) {
            DB::table('academic_configurations')->updateOrInsert(
                [
                    'type' => 'academic_levels',
                    'value' => $level
                ],
                [
                    'label' => $level,
                    'order' => $order,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Níveis acadêmicos: ' . count($levels));
    }

    private function seedResearchAreas(): void
    {
        $areas = [
            ['value' => 'ciencias_sociais', 'label' => 'Ciências Sociais', 'order' => 1],
            ['value' => 'saude', 'label' => 'Saúde', 'order' => 2],
            ['value' => 'tecnologia', 'label' => 'Tecnologia', 'order' => 3],
            ['value' => 'educacao', 'label' => 'Educação', 'order' => 4],
            ['value' => 'economia', 'label' => 'Economia', 'order' => 5],
            ['value' => 'ambiente', 'label' => 'Meio Ambiente', 'order' => 6],
            ['value' => 'cultura', 'label' => 'Cultura', 'order' => 7],
            ['value' => 'politica', 'label' => 'Política', 'order' => 8],
        ];

        foreach ($areas as $area) {
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

        $this->command->info('✅ Áreas de pesquisa: ' . count($areas));
    }
}
