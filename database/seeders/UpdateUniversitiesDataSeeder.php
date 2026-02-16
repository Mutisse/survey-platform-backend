<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\University;
use Illuminate\Support\Facades\DB;

class UpdateUniversitiesDataSeeder extends Seeder
{
    /**
     * Dados atualizados das universidades
     */
    private $universitiesData = [
        // UNIVERSIDADES PÚBLICAS
        'Universidade Eduardo Mondlane (UEM)' => [
            'email' => 'info@uem.mz',
            'phone' => '+25821490700',
            'description' => 'Principal universidade pública de Moçambique, fundada em 1962. Referência nacional em ensino superior e pesquisa.',
            'logo_url' => '/storage/logos/universities/uem.png',
            'is_verified' => true,
            'established_year' => 1962,
            'student_count' => 45000,
        ],
        'Universidade Pedagógica (UP)' => [
            'email' => 'reitoria@up.ac.mz',
            'phone' => '+25821491400',
            'description' => 'Universidade pública especializada em ciências da educação e formação de professores.',
            'logo_url' => '/storage/logos/universities/up.png',
            'is_verified' => true,
            'established_year' => 1985,
            'student_count' => 28000,
        ],
        'Universidade Lúrio (UniLúrio)' => [
            'email' => 'geral@unilurio.ac.mz',
            'phone' => '+25827111000',
            'description' => 'Universidade pública do norte de Moçambique, com campus em Nampula, Cabo Delgado e Niassa.',
            'logo_url' => '/storage/logos/universities/unilurio.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 15000,
        ],
        'Universidade Zambeze (UniZambeze)' => [
            'email' => 'secretaria@unizambeze.ac.mz',
            'phone' => '+25823321000',
            'description' => 'Universidade pública na região centro de Moçambique, com sede na cidade da Beira.',
            'logo_url' => '/storage/logos/universities/unizambeze.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 12000,
        ],
        'Universidade Save (UniSave)' => [
            'email' => 'info@unisave.ac.mz',
            'phone' => '+25829320000',
            'description' => 'Universidade pública na província de Inhambane, focada em desenvolvimento regional.',
            'logo_url' => '/storage/logos/universities/unisave.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 8000,
        ],
        'Universidade Rovuma (UniRovuma)' => [
            'email' => 'contacto@unirovuma.ac.mz',
            'phone' => '+25827210000',
            'description' => 'Universidade pública nas províncias de Cabo Delgado e Niassa.',
            'logo_url' => '/storage/logos/universities/unirovuma.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 7000,
        ],
        'Universidade Púnguè (UniPúnguè)' => [
            'email' => 'geral@unipungue.ac.mz',
            'phone' => '+25825123000',
            'description' => 'Universidade pública na província de Manica, com campus em Chimoio.',
            'logo_url' => '/storage/logos/universities/unipungue.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 6000,
        ],
        'Universidade Licungo (UniLicungo)' => [
            'email' => 'info@unilicungo.ac.mz',
            'phone' => '+25824220000',
            'description' => 'Universidade pública na província da Zambézia, sede em Quelimane.',
            'logo_url' => '/storage/logos/universities/unilicungo.png',
            'is_verified' => true,
            'established_year' => 2006,
            'student_count' => 5000,
        ],

        // INSTITUTOS SUPERIORES
        'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)' => [
            'email' => 'secretaria@isctem.ac.mz',
            'phone' => '+25821490000',
            'description' => 'Instituto superior público especializado em ciências e tecnologia.',
            'logo_url' => '/storage/logos/universities/isctem.png',
            'is_verified' => true,
            'established_year' => 1996,
            'student_count' => 12000,
        ],
        'Instituto Superior de Transportes e Comunicações (ISUTC)' => [
            'email' => 'info@isutc.ac.mz',
            'phone' => '+25821492000',
            'description' => 'Instituto superior público especializado em transportes e comunicações.',
            'logo_url' => '/storage/logos/universities/isutc.png',
            'is_verified' => true,
            'established_year' => 1997,
            'student_count' => 8000,
        ],
        'Instituto Superior Politécnico de Manica (ISPM)' => [
            'email' => 'contacto@ispm.ac.mz',
            'phone' => '+25825121000',
            'description' => 'Instituto politécnico público na província de Manica.',
            'logo_url' => '/storage/logos/universities/ispm.png',
            'is_verified' => true,
            'established_year' => 1999,
            'student_count' => 3000,
        ],

        // UNIVERSIDADES PRIVADAS
        'Universidade Católica de Moçambique (UCM)' => [
            'email' => 'reitoria@ucm.ac.mz',
            'phone' => '+25824212000',
            'description' => 'Universidade católica privada com várias unidades pelo país.',
            'logo_url' => '/storage/logos/universities/ucm.png',
            'is_verified' => true,
            'established_year' => 1996,
            'student_count' => 10000,
        ],
        'Universidade São Tomás de Moçambique (USTM)' => [
            'email' => 'info@ustm.ac.mz',
            'phone' => '+25821430000',
            'description' => 'Universidade privada com enfoque em ciências sociais e humanas.',
            'logo_url' => '/storage/logos/universities/ustm.png',
            'is_verified' => true,
            'established_year' => 1996,
            'student_count' => 6000,
        ],
        'Universidade Técnica de Moçambique (UDM)' => [
            'email' => 'geral@udm.ac.mz',
            'phone' => '+25821435000',
            'description' => 'Universidade privada técnica e tecnológica.',
            'logo_url' => '/storage/logos/universities/udm.png',
            'is_verified' => true,
            'established_year' => 2001,
            'student_count' => 5000,
        ],
        'Instituto Superior Monitor (ISM)' => [
            'email' => 'secretaria@ism.ac.mz',
            'phone' => '+25821456000',
            'description' => 'Instituto superior privado com várias unidades.',
            'logo_url' => '/storage/logos/universities/ism.png',
            'is_verified' => true,
            'established_year' => 1995,
            'student_count' => 4000,
        ],
    ];

    public function run(): void
    {
        $this->command->info('🚀 Atualizando dados das universidades...');

        $updated = 0;
        $notFound = [];

        foreach ($this->universitiesData as $name => $data) {
            $university = University::where('name', $name)->first();

            if ($university) {
                $university->update($data);
                $updated++;
                $this->command->info("✅ {$name} - Atualizada");
            } else {
                $notFound[] = $name;
                $this->command->warn("⚠️ {$name} - Não encontrada");
            }
        }

        // Atualizar também as colunas created_at e updated_at
        University::whereNull('created_at')->update(['created_at' => now()]);
        University::whereNull('updated_at')->update(['updated_at' => now()]);

        $this->command->info("\n🎉 RESUMO:");
        $this->command->info("Universidades atualizadas: {$updated}/" . count($this->universitiesData));

        if (!empty($notFound)) {
            $this->command->warn("Não encontradas: " . implode(', ', $notFound));
        }

        $this->command->info("\n📊 VERIFICAÇÃO FINAL:");
        $this->command->info("Total universidades: " . University::count());
        $this->command->info("Com email: " . University::whereNotNull('email')->count());
        $this->command->info("Verificadas: " . University::where('is_verified', true)->count());
    }
}
