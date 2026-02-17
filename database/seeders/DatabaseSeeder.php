<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Primeiro as tabelas principais (sem dependências)
        $this->call(UsersTableSeeder::class);
        $this->call(UniversitiesTableSeeder::class);

        // Surveys deve vir ANTES de SurveyQuestions
        $this->call(SurveysTableSeeder::class);

        // Agora as que dependem de surveys
        $this->call(SurveyQuestionsTableSeeder::class);
        $this->call(SurveyResponsesTableSeeder::class);

        // Depois as outras
        $this->call([
            AcademicConfigurationsTableSeeder::class,
            ActivityLogsTableSeeder::class,
            NotificationsTableSeeder::class,
            ParticipantStatsTableSeeder::class,
            PaymentsTableSeeder::class,
            StudentDocumentsTableSeeder::class,
            StudentStatsTableSeeder::class,
            SurveyCategoriesTableSeeder::class,
            SurveyInstitutionsTableSeeder::class,
            SurveyStatsTableSeeder::class,
            TransactionsTableSeeder::class,
            WithdrawalRequestsTableSeeder::class,
        ]);
    }
}
