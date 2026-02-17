<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SurveyInstitutionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('survey_institutions')->delete();
        
        \DB::table('survey_institutions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Universidade Eduardo Mondlane',
                'abbreviation' => 'UEM',
                'type' => 'university',
                'logo_url' => NULL,
                'website' => 'https://www.uem.mz',
                'contact_email' => 'pesquisa@uem.mz',
                'phone' => NULL,
                'address' => NULL,
                'description' => 'Principal universidade de Moçambique',
                'is_verified' => 1,
                'survey_count' => 1,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Universidade Pedagógica',
                'abbreviation' => 'UP',
                'type' => 'university',
                'logo_url' => NULL,
                'website' => 'https://www.up.ac.mz',
                'contact_email' => 'investigacao@up.ac.mz',
                'phone' => NULL,
                'address' => NULL,
                'description' => 'Universidade focada em ciências da educação',
                'is_verified' => 1,
                'survey_count' => 3,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-13 11:18:56',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Instituto Superior de Transportes e Comunicações',
                'abbreviation' => 'ISUTC',
                'type' => 'college',
                'logo_url' => NULL,
                'website' => 'https://www.isutc.ac.mz',
                'contact_email' => 'pesquisa@isutc.ac.mz',
                'phone' => NULL,
                'address' => NULL,
                'description' => 'Instituição especializada em transportes e comunicações',
                'is_verified' => 1,
                'survey_count' => 0,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Universidade Católica de Moçambique',
                'abbreviation' => 'UCM',
                'type' => 'university',
                'logo_url' => NULL,
                'website' => 'https://www.ucm.ac.mz',
                'contact_email' => 'investigacao@ucm.ac.mz',
                'phone' => NULL,
                'address' => NULL,
                'description' => 'Universidade católica privada',
                'is_verified' => 1,
                'survey_count' => 1,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-06 10:01:23',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Universidade Lúrio',
                'abbreviation' => 'UniLúrio',
                'type' => 'university',
                'logo_url' => NULL,
                'website' => 'https://www.unilurio.ac.mz',
                'contact_email' => 'ciencia@unilurio.ac.mz',
                'phone' => NULL,
                'address' => NULL,
                'description' => 'Universidade pública do norte de Moçambique',
                'is_verified' => 1,
                'survey_count' => 0,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
        ));
        
        
    }
}