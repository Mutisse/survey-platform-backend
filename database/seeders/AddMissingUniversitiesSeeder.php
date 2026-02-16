<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\University;
use Illuminate\Support\Facades\DB;

class AddMissingUniversitiesSeeder extends Seeder
{
    /**
     * Universidades que faltam
     */
    private $missingUniversities = [
        [
            'name' => 'Universidade Púnguè (UniPúnguè)',
            'acronym' => 'UniPúnguè',
            'type' => 'Universidade Pública',
            'location' => 'Manica',
            'website' => 'https://www.unipungue.ac.mz',
            'order' => 17, // Adicionar após as existentes
            'email' => 'geral@unipungue.ac.mz',
            'phone' => '+25825123000',
            'description' => 'Universidade pública na província de Manica, com campus em Chimoio e foco em desenvolvimento regional.',
            'logo_url' => '/storage/logos/universities/unipungue.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 6000,
        ],
        [
            'name' => 'Instituto Superior Politécnico de Manica (ISPM)',
            'acronym' => 'ISPM',
            'type' => 'Instituto Superior Público',
            'location' => 'Manica',
            'website' => 'https://www.ispm.ac.mz',
            'order' => 18,
            'email' => 'contacto@ispm.ac.mz',
            'phone' => '+25825121000',
            'description' => 'Instituto politécnico público na província de Manica, especializado em formação técnica e tecnológica.',
            'logo_url' => '/storage/logos/universities/ispm.png',
            'is_verified' => true,
            'established_year' => 1999,
            'student_count' => 3000,
        ],
        [
            'name' => 'Universidade Católica de Moçambique (UCM)',
            'acronym' => 'UCM',
            'type' => 'Universidade Privada',
            'location' => 'Maputo',
            'website' => 'https://www.ucm.ac.mz',
            'order' => 19,
            'email' => 'reitoria@ucm.ac.mz',
            'phone' => '+25824212000',
            'description' => 'Universidade católica privada com várias unidades em Moçambique, oferecendo ensino de qualidade baseado em valores cristãos.',
            'logo_url' => '/storage/logos/universities/ucm.png',
            'is_verified' => true,
            'established_year' => 1996,
            'student_count' => 10000,
        ],
    ];

    public function run(): void
    {
        $this->command->info('🚀 Adicionando universidades faltantes...');

        $added = 0;
        $skipped = 0;

        foreach ($this->missingUniversities as $universityData) {
            // Verificar se já existe (pode ter nome ligeiramente diferente)
            $exists = University::where('name', 'like', '%' . substr($universityData['name'], 0, 20) . '%')
                ->orWhere('acronym', $universityData['acronym'])
                ->exists();

            if (!$exists) {
                University::create($universityData);
                $added++;
                $this->command->info("✅ {$universityData['name']} - Adicionada");
            } else {
                $skipped++;
                $this->command->warn("⚠️ {$universityData['name']} - Já existe (nome similar)");
            }
        }

        $this->command->info("\n🎉 RESUMO:");
        $this->command->info("Adicionadas: {$added}");
        $this->command->info("Puladas (já existem): {$skipped}");
        $this->command->info("Total agora: " . University::count());
    }
}
