<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TransactionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('transactions')->delete();
        
        \DB::table('transactions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 2,
                'survey_id' => 4,
                'type' => 'survey_earnings',
                'amount' => '150.75',
                'status' => 'completed',
                'description' => 'Ganhos da pesquisa sobre educação digital',
                'payment_method' => NULL,
                'account_details' => NULL,
                'completed_at' => NULL,
                'created_at' => '2026-02-05 08:09:23',
                'updated_at' => '2026-02-05 08:09:23',
                'deleted_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'survey_id' => 4,
                'type' => 'survey_earnings',
                'amount' => '89.50',
                'status' => 'completed',
                'description' => 'Ganhos da pesquisa sobre e-learning',
                'payment_method' => NULL,
                'account_details' => NULL,
                'completed_at' => NULL,
                'created_at' => '2026-02-05 08:09:23',
                'updated_at' => '2026-02-05 08:09:23',
                'deleted_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 10,
                'survey_id' => 10,
                'type' => 'withdrawal',
                'amount' => '50.00',
                'status' => 'completed',
                'description' => 'Solicitação de saque',
                'payment_method' => 'mpesa',
                'account_details' => '+258878612744',
                'completed_at' => '2026-02-07 11:38:27',
                'created_at' => '2026-02-07 05:46:13',
                'updated_at' => '2026-02-07 11:38:27',
                'deleted_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 10,
                'survey_id' => 10,
                'type' => 'withdrawal',
                'amount' => '70.00',
                'status' => 'completed',
                'description' => 'Solicitação de saque',
                'payment_method' => 'mpesa',
                'account_details' => '+258496127446',
                'completed_at' => '2026-02-07 11:38:28',
                'created_at' => '2026-02-07 07:51:59',
                'updated_at' => '2026-02-07 11:38:28',
                'deleted_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'user_id' => 9,
                'survey_id' => 2,
                'type' => 'survey_earnings',
                'amount' => '45.00',
                'status' => 'pending',
                'description' => 'Participação na pesquisa: Satisfação com o Ensino Superior em Moçambique',
                'payment_method' => NULL,
                'account_details' => NULL,
                'completed_at' => NULL,
                'created_at' => '2026-02-07 10:49:06',
                'updated_at' => '2026-02-07 10:49:06',
                'deleted_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'user_id' => 9,
                'survey_id' => NULL,
                'type' => 'withdrawal',
                'amount' => '50.00',
                'status' => 'completed',
                'description' => 'Solicitação de saque',
                'payment_method' => 'mpesa',
                'account_details' => '+258840165527',
                'completed_at' => '2026-02-07 11:38:28',
                'created_at' => '2026-02-07 11:08:05',
                'updated_at' => '2026-02-07 11:38:28',
                'deleted_at' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'user_id' => 9,
                'survey_id' => 1,
                'type' => 'survey_earnings',
                'amount' => '35.00',
                'status' => 'pending',
                'description' => 'Participação na pesquisa: Hábitos de Consumo em Maputo - 2025',
                'payment_method' => NULL,
                'account_details' => NULL,
                'completed_at' => NULL,
                'created_at' => '2026-02-07 11:12:02',
                'updated_at' => '2026-02-07 11:12:02',
                'deleted_at' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'user_id' => 10,
                'survey_id' => 1,
                'type' => 'survey_earnings',
                'amount' => '35.00',
                'status' => 'pending',
                'description' => 'Participação na pesquisa: Hábitos de Consumo em Maputo - 2025',
                'payment_method' => NULL,
                'account_details' => NULL,
                'completed_at' => NULL,
                'created_at' => '2026-02-07 12:09:57',
                'updated_at' => '2026-02-07 12:09:57',
                'deleted_at' => NULL,
            ),
            8 => 
            array (
                'id' => 9,
                'user_id' => 10,
                'survey_id' => 2,
                'type' => 'survey_earnings',
                'amount' => '45.00',
                'status' => 'pending',
                'description' => 'Participação na pesquisa: Satisfação com o Ensino Superior em Moçambique',
                'payment_method' => NULL,
                'account_details' => NULL,
                'completed_at' => NULL,
                'created_at' => '2026-02-07 12:15:50',
                'updated_at' => '2026-02-07 12:15:50',
                'deleted_at' => NULL,
            ),
        ));
        
        
    }
}