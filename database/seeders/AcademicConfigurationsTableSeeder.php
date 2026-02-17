<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AcademicConfigurationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('academic_configurations')->delete();
        
        \DB::table('academic_configurations')->insert(array (
            0 => 
            array (
                'id' => 1,
                'type' => 'institution_types',
                'value' => 'Universidade Pública',
                'label' => 'Universidade Pública',
                'order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            1 => 
            array (
                'id' => 2,
                'type' => 'institution_types',
                'value' => 'Universidade Privada',
                'label' => 'Universidade Privada',
                'order' => 2,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            2 => 
            array (
                'id' => 3,
                'type' => 'institution_types',
                'value' => 'Instituto Superior',
                'label' => 'Instituto Superior',
                'order' => 3,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            3 => 
            array (
                'id' => 4,
                'type' => 'institution_types',
                'value' => 'Escola Superior',
                'label' => 'Escola Superior',
                'order' => 4,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            4 => 
            array (
                'id' => 5,
                'type' => 'institution_types',
                'value' => 'Outra',
                'label' => 'Outra',
                'order' => 5,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            5 => 
            array (
                'id' => 6,
                'type' => 'courses',
                'value' => 'Engenharia Informática',
                'label' => 'Engenharia Informática',
                'order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            6 => 
            array (
                'id' => 7,
                'type' => 'courses',
                'value' => 'Medicina',
                'label' => 'Medicina',
                'order' => 2,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            7 => 
            array (
                'id' => 8,
                'type' => 'courses',
                'value' => 'Direito',
                'label' => 'Direito',
                'order' => 3,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            8 => 
            array (
                'id' => 9,
                'type' => 'courses',
                'value' => 'Economia',
                'label' => 'Economia',
                'order' => 4,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            9 => 
            array (
                'id' => 10,
                'type' => 'courses',
                'value' => 'Administração e Gestão',
                'label' => 'Administração e Gestão',
                'order' => 5,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            10 => 
            array (
                'id' => 11,
                'type' => 'courses',
                'value' => 'Contabilidade',
                'label' => 'Contabilidade',
                'order' => 6,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            11 => 
            array (
                'id' => 12,
                'type' => 'courses',
                'value' => 'Enfermagem',
                'label' => 'Enfermagem',
                'order' => 7,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            12 => 
            array (
                'id' => 13,
                'type' => 'courses',
                'value' => 'Arquitetura',
                'label' => 'Arquitetura',
                'order' => 8,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            13 => 
            array (
                'id' => 14,
                'type' => 'courses',
                'value' => 'Engenharia Civil',
                'label' => 'Engenharia Civil',
                'order' => 9,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            14 => 
            array (
                'id' => 15,
                'type' => 'courses',
                'value' => 'Psicologia',
                'label' => 'Psicologia',
                'order' => 10,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            15 => 
            array (
                'id' => 16,
                'type' => 'courses',
                'value' => 'Sociologia',
                'label' => 'Sociologia',
                'order' => 11,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            16 => 
            array (
                'id' => 17,
                'type' => 'courses',
                'value' => 'Ciências da Educação',
                'label' => 'Ciências da Educação',
                'order' => 12,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            17 => 
            array (
                'id' => 18,
                'type' => 'courses',
                'value' => 'Biologia',
                'label' => 'Biologia',
                'order' => 13,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            18 => 
            array (
                'id' => 19,
                'type' => 'courses',
                'value' => 'Química',
                'label' => 'Química',
                'order' => 14,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            19 => 
            array (
                'id' => 20,
                'type' => 'courses',
                'value' => 'Matemática',
                'label' => 'Matemática',
                'order' => 15,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            20 => 
            array (
                'id' => 21,
                'type' => 'courses',
                'value' => 'Física',
                'label' => 'Física',
                'order' => 16,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            21 => 
            array (
                'id' => 22,
                'type' => 'courses',
                'value' => 'História',
                'label' => 'História',
                'order' => 17,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            22 => 
            array (
                'id' => 23,
                'type' => 'courses',
                'value' => 'Geografia',
                'label' => 'Geografia',
                'order' => 18,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            23 => 
            array (
                'id' => 24,
                'type' => 'courses',
                'value' => 'Línguas e Literaturas',
                'label' => 'Línguas e Literaturas',
                'order' => 19,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            24 => 
            array (
                'id' => 25,
                'type' => 'courses',
                'value' => 'Artes',
                'label' => 'Artes',
                'order' => 20,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            25 => 
            array (
                'id' => 26,
                'type' => 'courses',
                'value' => 'Turismo',
                'label' => 'Turismo',
                'order' => 21,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            26 => 
            array (
                'id' => 27,
                'type' => 'courses',
                'value' => 'Hotelaria',
                'label' => 'Hotelaria',
                'order' => 22,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            27 => 
            array (
                'id' => 28,
                'type' => 'courses',
                'value' => 'Agronomia',
                'label' => 'Agronomia',
                'order' => 23,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            28 => 
            array (
                'id' => 29,
                'type' => 'courses',
                'value' => 'Veterinária',
                'label' => 'Veterinária',
                'order' => 24,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            29 => 
            array (
                'id' => 30,
                'type' => 'courses',
                'value' => 'Outro',
                'label' => 'Outro',
                'order' => 99,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            30 => 
            array (
                'id' => 31,
                'type' => 'academic_levels',
                'value' => 'Licenciatura - 1º ano',
                'label' => 'Licenciatura - 1º ano',
                'order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            31 => 
            array (
                'id' => 32,
                'type' => 'academic_levels',
                'value' => 'Licenciatura - 2º ano',
                'label' => 'Licenciatura - 2º ano',
                'order' => 2,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            32 => 
            array (
                'id' => 33,
                'type' => 'academic_levels',
                'value' => 'Licenciatura - 3º ano',
                'label' => 'Licenciatura - 3º ano',
                'order' => 3,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            33 => 
            array (
                'id' => 34,
                'type' => 'academic_levels',
                'value' => 'Licenciatura - 4º ano',
                'label' => 'Licenciatura - 4º ano',
                'order' => 4,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            34 => 
            array (
                'id' => 35,
                'type' => 'academic_levels',
                'value' => 'Licenciatura - 5º ano',
                'label' => 'Licenciatura - 5º ano',
                'order' => 5,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            35 => 
            array (
                'id' => 36,
                'type' => 'academic_levels',
                'value' => 'Pós-graduação',
                'label' => 'Pós-graduação',
                'order' => 6,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            36 => 
            array (
                'id' => 37,
                'type' => 'academic_levels',
                'value' => 'Mestrado',
                'label' => 'Mestrado',
                'order' => 7,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            37 => 
            array (
                'id' => 38,
                'type' => 'academic_levels',
                'value' => 'Doutoramento',
                'label' => 'Doutoramento',
                'order' => 8,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            38 => 
            array (
                'id' => 39,
                'type' => 'research_areas',
                'value' => 'ciencias_sociais',
                'label' => 'Ciências Sociais',
                'order' => 1,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            39 => 
            array (
                'id' => 40,
                'type' => 'research_areas',
                'value' => 'saude',
                'label' => 'Saúde',
                'order' => 2,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            40 => 
            array (
                'id' => 41,
                'type' => 'research_areas',
                'value' => 'tecnologia',
                'label' => 'Tecnologia',
                'order' => 3,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            41 => 
            array (
                'id' => 42,
                'type' => 'research_areas',
                'value' => 'educacao',
                'label' => 'Educação',
                'order' => 4,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            42 => 
            array (
                'id' => 43,
                'type' => 'research_areas',
                'value' => 'economia',
                'label' => 'Economia',
                'order' => 5,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            43 => 
            array (
                'id' => 44,
                'type' => 'research_areas',
                'value' => 'ambiente',
                'label' => 'Meio Ambiente',
                'order' => 6,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            44 => 
            array (
                'id' => 45,
                'type' => 'research_areas',
                'value' => 'cultura',
                'label' => 'Cultura',
                'order' => 7,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
            45 => 
            array (
                'id' => 46,
                'type' => 'research_areas',
                'value' => 'politica',
                'label' => 'Política',
                'order' => 8,
                'is_active' => 1,
                'created_at' => '2026-02-03 17:49:05',
                'updated_at' => '2026-02-03 17:49:05',
            ),
        ));
        
        
    }
}