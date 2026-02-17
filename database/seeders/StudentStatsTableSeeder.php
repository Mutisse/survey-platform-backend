<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StudentStatsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('student_stats')->delete();

        \DB::table('student_stats')->insert(array(
            0 =>
            array(
                'id' => 1,
                'user_id' => 2,
                'bi_number' => '123456789012A',
                'birth_date' => '2000-05-15',
                'gender' => 'masculino',
                'institution_type' => NULL,
                'university' => 'Universidade Eduardo Mondlane (UEM)',
                'course' => 'Engenharia Informática',
                'admission_year' => 2020,
                'expected_graduation' => 2024,
                'academic_level' => 'Licenciatura - 4º ano',
                'student_card_number' => 'UEM202012345',
                'research_interests' => '"[\\"tecnologia\\",\\"inteligencia_artificial\\",\\"desenvolvimento_web\\"]"',
                'documents_submitted' => 1,
                'created_at' => '2026-02-03 21:34:59',
                'updated_at' => '2026-02-03 21:34:59',
            ),
            1 =>
            array(
                'id' => 2,
                'user_id' => 3,
                'bi_number' => '234567890123B',
                'birth_date' => '2001-03-22',
                'gender' => 'feminino',
                'institution_type' => NULL,
                'university' => 'Universidade Pedagógica (UP)',
                'course' => 'Ciências da Educação',
                'admission_year' => 2021,
                'expected_graduation' => 2025,
                'academic_level' => 'Licenciatura - 3º ano',
                'student_card_number' => 'UP202123456',
                'research_interests' => '"[\\"educacao_inclusiva\\",\\"psicologia_educacional\\",\\"tecnologia_educacional\\"]"',
                'documents_submitted' => 1,
                'created_at' => '2026-02-03 21:34:59',
                'updated_at' => '2026-02-03 21:34:59',
            ),
            2 =>
            array(
                'id' => 3,
                'user_id' => 4,
                'bi_number' => '345678901234C',
                'birth_date' => '1999-11-08',
                'gender' => 'masculino',
                'institution_type' => NULL,
                'university' => 'Universidade Lúrio (UniLúrio)',
                'course' => 'Medicina',
                'admission_year' => 2019,
                'expected_graduation' => 2025,
                'academic_level' => 'Licenciatura - 5º ano',
                'student_card_number' => 'UL201934567',
                'research_interests' => '"[\\"saude_publica\\",\\"medicina_tropical\\",\\"epidemiologia\\"]"',
                'documents_submitted' => 1,
                'created_at' => '2026-02-03 21:34:59',
                'updated_at' => '2026-02-03 21:34:59',
            ),
            3 =>
            array(
                'id' => 4,
                'user_id' => 5,
                'bi_number' => '456789012345D',
                'birth_date' => '2002-07-30',
                'gender' => 'feminino',
                'institution_type' => NULL,
                'university' => 'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)',
                'course' => 'Arquitetura',
                'admission_year' => 2022,
                'expected_graduation' => 2026,
                'academic_level' => 'Licenciatura - 2º ano',
                'student_card_number' => 'ISCTEM202278901',
                'research_interests' => '"[\\"arquitetura_sustentavel\\",\\"urbanismo\\",\\"design_urbano\\"]"',
                'documents_submitted' => 0,
                'created_at' => '2026-02-03 21:34:59',
                'updated_at' => '2026-02-03 21:34:59',
            ),
            4 =>
            array(
                'id' => 5,
                'user_id' => 6,
                'bi_number' => '567890123456E',
                'birth_date' => '2001-01-18',
                'gender' => 'masculino',
                'institution_type' => NULL,
                'university' => 'Universidade Católica de Moçambique (UCM)',
                'course' => 'Direito',
                'admission_year' => 2021,
                'expected_graduation' => 2025,
                'academic_level' => 'Licenciatura - 3º ano',
                'student_card_number' => 'UCM202156789',
                'research_interests' => '"[\\"direito_humano\\",\\"direito_constitucional\\",\\"direito_internacional\\"]"',
                'documents_submitted' => 1,
                'created_at' => '2026-02-03 21:34:59',
                'updated_at' => '2026-02-03 21:34:59',
            ),
            5 =>
            array(
                'id' => 6,
                'user_id' => 16,
                'bi_number' => '999999999999Z',
                'birth_date' => '1990-01-01',
                'gender' => 'masculino',
                'institution_type' => NULL,
                'university' => 'Universidade Eduardo Mondlane (UEM)',
                'course' => 'Mestrado em Pesquisa Social',
                'admission_year' => 2024,
                'expected_graduation' => 2026,
                'academic_level' => 'Mestrado',
                'student_card_number' => 'UEM202499999',
                'research_interests' => '"[\\"pesquisa_social\\",\\"metodologia\\",\\"analise_dados\\"]"',
                'documents_submitted' => 1,
                'created_at' => '2026-02-03 21:35:02',
                'updated_at' => '2026-02-03 21:35:02',
            ),
        ));
    }
}
