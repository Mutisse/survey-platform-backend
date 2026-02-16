<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\University;
use App\Models\StudentStats;
use App\Models\ParticipantStats;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando criação de usuários com dados reais...');

        // Primeiro, limpar os usuários existentes para evitar duplicação
        $this->command->info('🧹 Limpando usuários existentes...');
        User::truncate();
        StudentStats::truncate();
        ParticipantStats::truncate();

        // ====================================
        // 1. ADMINISTRADOR
        // ====================================
        $this->command->info('👑 Criando administrador...');

        User::create([
            'name' => 'Administrador do Sistema',
            'email' => 'admin@mozpesquisa.ac.mz',
            'password' => Hash::make('Admin@2025'),
            'phone' => '+258840001234',
            'role' => 'admin',
            'verification_status' => 'approved',
            'balance' => 10000.00,
            'email_notifications' => true,
            'whatsapp_notifications' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ ADMIN criado: admin@mozpesquisa.ac.mz / Admin@2025');

        // ====================================
        // 2. ESTUDANTES (5 estudantes REAIS)
        // ====================================
        $this->command->info('🎓 Criando 5 estudantes universitários...');

        $students = [
            [
                'name' => 'Carlos Alberto Mondlane',
                'email' => 'carlos.mondlane@uem.ac.mz',
                'password' => Hash::make('Estudante@2025'),
                'phone' => '+258841112233',
                'role' => 'student',
                'university_id' => 1, // UEM
                'course' => 'Engenharia Informática',
                'verification_status' => 'approved',
                'balance' => 850.50,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'student_data' => [
                    'university' => 'Universidade Eduardo Mondlane (UEM)',
                    'course' => 'Engenharia Informática', // ADICIONADO
                    'bi_number' => '123456789012A',
                    'birth_date' => '2000-05-15',
                    'gender' => 'masculino',
                    'admission_year' => 2020,
                    'expected_graduation' => 2024,
                    'academic_level' => 'Licenciatura - 4º ano',
                    'documents_submitted' => true,
                    'student_card_number' => 'UEM202012345',
                    'research_interests' => json_encode(['tecnologia', 'inteligencia_artificial', 'desenvolvimento_web']),
                ]
            ],
            [
                'name' => 'Maria Fernanda Silva',
                'email' => 'maria.silva@up.ac.mz',
                'password' => Hash::make('Estudante@2025'),
                'phone' => '+258842223344',
                'role' => 'student',
                'university_id' => 2, // UP
                'course' => 'Ciências da Educação',
                'verification_status' => 'approved',
                'balance' => 725.00,
                'email_notifications' => true,
                'whatsapp_notifications' => false,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'student_data' => [
                    'university' => 'Universidade Pedagógica (UP)',
                    'course' => 'Ciências da Educação', // ADICIONADO
                    'bi_number' => '234567890123B',
                    'birth_date' => '2001-03-22',
                    'gender' => 'feminino',
                    'admission_year' => 2021,
                    'expected_graduation' => 2025,
                    'academic_level' => 'Licenciatura - 3º ano',
                    'documents_submitted' => true,
                    'student_card_number' => 'UP202123456',
                    'research_interests' => json_encode(['educacao_inclusiva', 'psicologia_educacional', 'tecnologia_educacional']),
                ]
            ],
            [
                'name' => 'João Pedro Tembe',
                'email' => 'joao.tembe@unilurio.ac.mz',
                'password' => Hash::make('Estudante@2025'),
                'phone' => '+258843334455',
                'role' => 'student',
                'university_id' => 3, // UniLúrio
                'course' => 'Medicina',
                'verification_status' => 'approved',
                'balance' => 1200.00,
                'email_notifications' => false,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'student_data' => [
                    'university' => 'Universidade Lúrio (UniLúrio)',
                    'course' => 'Medicina', // ADICIONADO
                    'bi_number' => '345678901234C',
                    'birth_date' => '1999-11-08',
                    'gender' => 'masculino',
                    'admission_year' => 2019,
                    'expected_graduation' => 2025,
                    'academic_level' => 'Licenciatura - 5º ano',
                    'documents_submitted' => true,
                    'student_card_number' => 'UL201934567',
                    'research_interests' => json_encode(['saude_publica', 'medicina_tropical', 'epidemiologia']),
                ]
            ],
            [
                'name' => 'Ana Paula Macuácua',
                'email' => 'ana.macuacua@isctem.ac.mz',
                'password' => Hash::make('Estudante@2025'),
                'phone' => '+258844445566',
                'role' => 'student',
                'university_id' => 8, // ISCTEM
                'course' => 'Arquitetura',
                'verification_status' => 'pending',
                'balance' => 0.00,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'student_data' => [
                    'university' => 'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)',
                    'course' => 'Arquitetura', // ADICIONADO
                    'bi_number' => '456789012345D',
                    'birth_date' => '2002-07-30',
                    'gender' => 'feminino',
                    'admission_year' => 2022,
                    'expected_graduation' => 2026,
                    'academic_level' => 'Licenciatura - 2º ano',
                    'documents_submitted' => false,
                    'student_card_number' => 'ISCTEM202278901',
                    'research_interests' => json_encode(['arquitetura_sustentavel', 'urbanismo', 'design_urbano']),
                ]
            ],
            [
                'name' => 'David Jorge Cossa',
                'email' => 'david.cossa@ucm.ac.mz',
                'password' => Hash::make('Estudante@2025'),
                'phone' => '+258845556677',
                'role' => 'student',
                'university_id' => 19, // UCM
                'course' => 'Direito',
                'verification_status' => 'approved',
                'balance' => 550.25,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'student_data' => [
                    'university' => 'Universidade Católica de Moçambique (UCM)',
                    'course' => 'Direito', // ADICIONADO
                    'bi_number' => '567890123456E',
                    'birth_date' => '2001-01-18',
                    'gender' => 'masculino',
                    'admission_year' => 2021,
                    'expected_graduation' => 2025,
                    'academic_level' => 'Licenciatura - 3º ano',
                    'documents_submitted' => true,
                    'student_card_number' => 'UCM202156789',
                    'research_interests' => json_encode(['direito_humano', 'direito_constitucional', 'direito_internacional']),
                ]
            ]
        ];

        $studentCount = 0;
        foreach ($students as $studentData) {
            $userData = $studentData;
            $studentStatsData = $userData['student_data'];
            unset($userData['student_data']);

            $student = User::create($userData);

            StudentStats::create(array_merge(
                ['user_id' => $student->id],
                $studentStatsData
            ));

            $studentCount++;
            $this->command->info("✅ Estudante {$studentData['name']} criado");
        }

        // ====================================
        // 3. PARTICIPANTES (10 participantes REAIS)
        // ====================================
        $this->command->info('👥 Criando 10 participantes reais...');

        $participants = [
            [
                'name' => 'António Fernando Massinga',
                'email' => 'antonio.massinga@gmail.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258846667788',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 325.75,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '678901234567F',
                    'birth_date' => '1985-04-12',
                    'gender' => 'masculino',
                    'province' => 'Maputo Cidade',
                    'mpesa_number' => '+258846667788',
                    'occupation' => 'Engenheiro Civil',
                    'education_level' => 'Licenciatura',
                    'research_interests' => json_encode(['tecnologia', 'infraestrutura', 'desenvolvimento_urbano']),
                ]
            ],
            [
                'name' => 'Célia Regina Nhaca',
                'email' => 'celia.nhaca@hotmail.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258847778899',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 480.50,
                'email_notifications' => true,
                'whatsapp_notifications' => false,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '789012345678G',
                    'birth_date' => '1990-08-25',
                    'gender' => 'feminino',
                    'province' => 'Maputo Província',
                    'mpesa_number' => '+258847778899',
                    'occupation' => 'Professora',
                    'education_level' => 'Pós-graduação/Mestrado',
                    'research_interests' => json_encode(['educacao', 'formacao_continua', 'tecnologia_educativa']),
                ]
            ],
            [
                'name' => 'Paulo José Matola',
                'email' => 'paulo.matola@yahoo.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258848889900',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 195.00,
                'email_notifications' => false,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '890123456789H',
                    'birth_date' => '1988-12-18',
                    'gender' => 'masculino',
                    'province' => 'Gaza',
                    'mpesa_number' => '+258848889900',
                    'occupation' => 'Empresário',
                    'education_level' => 'Ensino Superior Incompleto',
                    'research_interests' => json_encode(['economia', 'empreendedorismo', 'gestao']),
                ]
            ],
            [
                'name' => 'Sofia Maria Chaúque',
                'email' => 'sofia.chauque@gmail.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258849990011',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 620.25,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '901234567890I',
                    'birth_date' => '1992-03-30',
                    'gender' => 'feminino',
                    'province' => 'Inhambane',
                    'mpesa_number' => '+258849990011',
                    'occupation' => 'Médica',
                    'education_level' => 'Licenciatura',
                    'research_interests' => json_encode(['saude', 'medicina_preventiva', 'nutricao']),
                ]
            ],
            [
                'name' => 'José Carlos Mucavel',
                'email' => 'jose.mucavel@outlook.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258840011122',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 275.50,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '012345678901J',
                    'birth_date' => '1975-06-22',
                    'gender' => 'masculino',
                    'province' => 'Sofala',
                    'mpesa_number' => '+258840011122',
                    'occupation' => 'Funcionário Público',
                    'education_level' => 'Curso Técnico',
                    'research_interests' => json_encode(['administracao_publica', 'politica', 'gestao_publica']),
                ]
            ],
            [
                'name' => 'Teresa Alberto Nhantumbo',
                'email' => 'teresa.nhantumbo@gmail.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258841122233',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 150.00,
                'email_notifications' => false,
                'whatsapp_notifications' => false,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '112345678901K',
                    'birth_date' => '1995-09-14',
                    'gender' => 'feminino',
                    'province' => 'Manica',
                    'mpesa_number' => '+258841122233',
                    'occupation' => 'Estudante Universitário',
                    'education_level' => 'Ensino Superior Incompleto',
                    'research_interests' => json_encode(['sociologia', 'desenvolvimento_comunitario', 'genero']),
                ]
            ],
            [
                'name' => 'Rogério Francisco Sitoe',
                'email' => 'rogerio.sitoe@hotmail.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258842233344',
                'role' => 'participant',
                'verification_status' => 'pending',
                'balance' => 0.00,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'participant_data' => [
                    'bi_number' => '212345678901L',
                    'birth_date' => '1998-01-27',
                    'gender' => 'masculino',
                    'province' => 'Tete',
                    'mpesa_number' => '+258842233344',
                    'occupation' => 'Agricultor',
                    'education_level' => 'Ensino Secundário (até 10ª classe)',
                    'research_interests' => json_encode(['agricultura', 'meio_ambiente', 'desenvolvimento_rural']),
                ]
            ],
            [
                'name' => 'Luísa Domingos Muianga',
                'email' => 'luisa.muianga@yahoo.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258843344455',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 385.75,
                'email_notifications' => true,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '312345678901M',
                    'birth_date' => '1987-11-19',
                    'gender' => 'feminino',
                    'province' => 'Zambézia',
                    'mpesa_number' => '+258843344455',
                    'occupation' => 'Enfermeira',
                    'education_level' => 'Curso Técnico',
                    'research_interests' => json_encode(['saude', 'enfermagem', 'cuidados_paliativos']),
                ]
            ],
            [
                'name' => 'Fernando João Saíde',
                'email' => 'fernando.saide@gmail.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258844455566',
                'role' => 'participant',
                'verification_status' => 'approved',
                'balance' => 225.00,
                'email_notifications' => false,
                'whatsapp_notifications' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
                'participant_data' => [
                    'bi_number' => '412345678901N',
                    'birth_date' => '1982-07-05',
                    'gender' => 'masculino',
                    'province' => 'Nampula',
                    'mpesa_number' => '+258844455566',
                    'occupation' => 'Comerciante',
                    'education_level' => 'Ensino Médio (12ª classe)',
                    'research_interests' => json_encode(['economia', 'comercio', 'gestao_financeira']),
                ]
            ],
            [
                'name' => 'Marta José Amisse',
                'email' => 'marta.amisse@outlook.com',
                'password' => Hash::make('Participante@2025'),
                'phone' => '+258845566677',
                'role' => 'participant',
                'verification_status' => 'rejected',
                'balance' => 0.00,
                'email_notifications' => true,
                'whatsapp_notifications' => false,
                'participant_data' => [
                    'bi_number' => '512345678901O',
                    'birth_date' => '1993-10-15',
                    'gender' => 'feminino',
                    'province' => 'Cabo Delgado',
                    'mpesa_number' => '+258845566677',
                    'occupation' => 'Desempregado(a)',
                    'education_level' => 'Ensino Superior Incompleto',
                    'research_interests' => json_encode(['desenvolvimento_social', 'emprego', 'qualificacao_profissional']),
                ]
            ]
        ];

        $participantCount = 0;
        foreach ($participants as $participantData) {
            $userData = $participantData;
            $participantStatsData = $userData['participant_data'];
            unset($userData['participant_data']);

            $participant = User::create($userData);

            ParticipantStats::create(array_merge(
                ['user_id' => $participant->id],
                $participantStatsData,
                [
                    'total_surveys_completed' => rand(2, 20),
                    'total_earnings' => $participantData['balance'],
                    'consent_data_collection' => true,
                    'sms_notifications' => false,
                    'participation_frequency' => ['Regularmente (várias vezes por semana)', 'Frequentemente (1-2 vezes por semana)', 'Ocasionalmente (1-2 vezes por mês)'][rand(0, 2)],
                    'last_survey_date' => now()->subDays(rand(1, 90)),
                ]
            ));

            $participantCount++;
            $this->command->info("✅ Participante {$participantData['name']} criado");
        }

        // ====================================
        // 4. USUÁRIO PESQUISADOR (para SurveySeeder)
        // ====================================
        $this->command->info('🔬 Criando usuário pesquisador...');

        $researcherEmail = 'pesquisador.academico@mozpesquisa.ac.mz';
        $researcher = User::create([
            'name' => 'Pesquisador Acadêmico',
            'email' => $researcherEmail,
            'password' => Hash::make('Pesquisa@2025'),
            'phone' => '+258840009999',
            'role' => 'student',
            'university_id' => 1, // UEM
            'course' => 'Mestrado em Pesquisa Social',
            'verification_status' => 'approved',
            'balance' => 0.00,
            'email_notifications' => true,
            'whatsapp_notifications' => true,
            'verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        // Adicionar também student_stats para o pesquisador
        StudentStats::create([
            'user_id' => $researcher->id,
            'university' => 'Universidade Eduardo Mondlane (UEM)',
            'course' => 'Mestrado em Pesquisa Social',
            'bi_number' => '999999999999Z',
            'birth_date' => '1990-01-01',
            'gender' => 'masculino',
            'admission_year' => 2024,
            'expected_graduation' => 2026,
            'academic_level' => 'Mestrado',
            'documents_submitted' => true,
            'student_card_number' => 'UEM202499999',
            'research_interests' => json_encode(['pesquisa_social', 'metodologia', 'analise_dados']),
        ]);

        $this->command->info("✅ Pesquisador criado: {$researcherEmail} / Pesquisa@2025");

        // ====================================
        // RESUMO
        // ====================================
        $this->command->info("\n🎉 RESUMO DA CRIAÇÃO DE USUÁRIOS:");
        $this->command->info("==================================");
        $this->command->info("👑 Administradores: 1");
        $this->command->info("🎓 Estudantes: {$studentCount}/5");
        $this->command->info("👥 Participantes: {$participantCount}/10");
        $this->command->info("🔬 Pesquisadores: 1");
        $this->command->info("Total: " . ($studentCount + $participantCount + 2) . " usuários");
        $this->command->info("==================================");
        $this->command->info("\n🔑 CREDENCIAIS PARA LOGIN (senha: tipo@2025):");
        $this->command->info("==============================================");
        $this->command->info("ADMIN: admin@mozpesquisa.ac.mz");
        $this->command->info("ESTUDANTE: carlos.mondlane@uem.ac.mz");
        $this->command->info("PARTICIPANTE: antonio.massinga@gmail.com");
        $this->command->info("PESQUISADOR: pesquisador.academico@mozpesquisa.ac.mz");
        $this->command->info("==============================================");
    }
}
