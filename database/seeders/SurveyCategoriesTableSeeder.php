<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SurveyCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('survey_categories')->delete();
        
        \DB::table('survey_categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Economia',
                'slug' => 'economia',
                'description' => 'Pesquisas sobre economia, finanças e consumo',
                'icon' => 'attach_money',
                'color' => '#4CAF50',
                'survey_count' => 2,
                'is_active' => 1,
                'order' => 1,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-05 08:19:46',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Sociologia',
                'slug' => 'sociologia',
                'description' => 'Pesquisas sobre sociedade e relações humanas',
                'icon' => 'people',
                'color' => '#2196F3',
                'survey_count' => 0,
                'is_active' => 1,
                'order' => 2,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Psicologia',
                'slug' => 'psicologia',
                'description' => 'Pesquisas sobre comportamento e mente humana',
                'icon' => 'psychology',
                'color' => '#9C27B0',
                'survey_count' => 1,
                'is_active' => 1,
                'order' => 3,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-13 11:18:56',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Educação',
                'slug' => 'educacao',
                'description' => 'Pesquisas sobre ensino e aprendizagem',
                'icon' => 'school',
                'color' => '#FF9800',
                'survey_count' => 2,
                'is_active' => 1,
                'order' => 4,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-06 10:01:23',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Tecnologia',
                'slug' => 'tecnologia',
                'description' => 'Pesquisas sobre inovação e tecnologia',
                'icon' => 'computer',
                'color' => '#2196F3',
                'survey_count' => 1,
                'is_active' => 1,
                'order' => 5,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Saúde',
                'slug' => 'saude',
                'description' => 'Pesquisas sobre saúde e bem-estar',
                'icon' => 'medical_services',
                'color' => '#F44336',
                'survey_count' => 0,
                'is_active' => 1,
                'order' => 6,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Meio Ambiente',
                'slug' => 'meio-ambiente',
                'description' => 'Pesquisas sobre ecologia e sustentabilidade',
                'icon' => 'eco',
                'color' => '#4CAF50',
                'survey_count' => 0,
                'is_active' => 1,
                'order' => 7,
                'created_at' => '2026-02-03 16:38:19',
                'updated_at' => '2026-02-03 16:38:19',
            ),
        ));
        
        
    }
}