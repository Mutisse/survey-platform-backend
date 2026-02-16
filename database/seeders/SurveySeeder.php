<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Survey;
use App\Models\SurveyCategory;
use App\Models\SurveyInstitution;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Criar usuário pesquisador (estudante)
        $researcher = User::firstOrCreate(
            ['email' => 'pesquisador@exemplo.com'],
            [
                'name' => 'Pesquisador Acadêmico',
                'password' => bcrypt('password123'),
                'phone' => '+258841234567',
                'role' => 'student',
                'email_verified_at' => now(),
                'verification_status' => 'approved',
                'email_notifications' => true,
                'whatsapp_notifications' => true,
            ]
        );

        // 2. Criar categorias usando a NOVA tabela survey_categories
        $categoriesData = [
            [
                'name' => 'Economia',
                'slug' => 'economia',
                'icon' => 'attach_money',
                'color' => '#4CAF50',
                'description' => 'Pesquisas sobre economia, finanças e consumo'
            ],
            [
                'name' => 'Sociologia',
                'slug' => 'sociologia',
                'icon' => 'people',
                'color' => '#2196F3',
                'description' => 'Pesquisas sobre sociedade e relações humanas'
            ],
            [
                'name' => 'Psicologia',
                'slug' => 'psicologia',
                'icon' => 'psychology',
                'color' => '#9C27B0',
                'description' => 'Pesquisas sobre comportamento e mente humana'
            ],
            [
                'name' => 'Educação',
                'slug' => 'educacao',
                'icon' => 'school',
                'color' => '#FF9800',
                'description' => 'Pesquisas sobre ensino e aprendizagem'
            ],
            [
                'name' => 'Tecnologia',
                'slug' => 'tecnologia',
                'icon' => 'computer',
                'color' => '#2196F3',
                'description' => 'Pesquisas sobre inovação e tecnologia'
            ],
            [
                'name' => 'Saúde',
                'slug' => 'saude',
                'icon' => 'medical_services',
                'color' => '#F44336',
                'description' => 'Pesquisas sobre saúde e bem-estar'
            ],
            [
                'name' => 'Meio Ambiente',
                'slug' => 'meio-ambiente',
                'icon' => 'eco',
                'color' => '#4CAF50',
                'description' => 'Pesquisas sobre ecologia e sustentabilidade'
            ],
        ];

        foreach ($categoriesData as $index => $category) {
            SurveyCategory::firstOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'order' => $index + 1,
                    'is_active' => true,
                ])
            );
        }

        // 3. Criar instituições usando a NOVA tabela survey_institutions
        $institutionsData = [
            [
                'name' => 'Universidade Eduardo Mondlane',
                'abbreviation' => 'UEM',
                'type' => 'university',
                'contact_email' => 'pesquisa@uem.mz',
                'website' => 'https://www.uem.mz',
                'is_verified' => true,
                'description' => 'Principal universidade de Moçambique'
            ],
            [
                'name' => 'Universidade Pedagógica',
                'abbreviation' => 'UP',
                'type' => 'university',
                'contact_email' => 'investigacao@up.ac.mz',
                'website' => 'https://www.up.ac.mz',
                'is_verified' => true,
                'description' => 'Universidade focada em ciências da educação'
            ],
            [
                'name' => 'Instituto Superior de Transportes e Comunicações',
                'abbreviation' => 'ISUTC',
                'type' => 'college',
                'contact_email' => 'pesquisa@isutc.ac.mz',
                'website' => 'https://www.isutc.ac.mz',
                'is_verified' => true,
                'description' => 'Instituição especializada em transportes e comunicações'
            ],
            [
                'name' => 'Universidade Católica de Moçambique',
                'abbreviation' => 'UCM',
                'type' => 'university',
                'contact_email' => 'investigacao@ucm.ac.mz',
                'website' => 'https://www.ucm.ac.mz',
                'is_verified' => true,
                'description' => 'Universidade católica privada'
            ],
            [
                'name' => 'Universidade Lúrio',
                'abbreviation' => 'UniLúrio',
                'type' => 'university',
                'contact_email' => 'ciencia@unilurio.ac.mz',
                'website' => 'https://www.unilurio.ac.mz',
                'is_verified' => true,
                'description' => 'Universidade pública do norte de Moçambique'
            ],
        ];

        foreach ($institutionsData as $institution) {
            SurveyInstitution::firstOrCreate(
                ['abbreviation' => $institution['abbreviation']],
                $institution
            );
        }

        // 4. Criar pesquisas de exemplo com as NOVAS colunas
        $surveysData = [
            [
                'title' => 'Hábitos de Consumo em Maputo - 2025',
                'description' => 'Pesquisa sobre padrões de consumo de produtos alimentícios e não-alimentícios na cidade de Maputo. O objetivo é entender as preferências dos consumidores e fatores que influenciam as decisões de compra.',
                'category' => 'Economia',
                'institution' => 'Universidade Eduardo Mondlane',
                'duration' => 12, // em minutos
                'reward' => 35.00, // MZN
                'requirements' => json_encode([
                    'idade_minima' => 18,
                    'localizacao' => 'Maputo',
                    'frequencia_compras' => 'pelo menos 1 vez por mês',
                    'renda_minima' => '5000 MZN mensais'
                ]),
                'target_responses' => 250,
                'current_responses' => 127,
                'responses_count' => 142,
                'status' => 'active',
                'config' => json_encode([
                    'theme' => 'default',
                    'welcome_message' => 'Bem-vindo à nossa pesquisa sobre hábitos de consumo!',
                    'completion_message' => 'Obrigado por participar! Suas respostas são muito valiosas.',
                    'progress_bar' => true,
                    'randomize_questions' => false,
                    'allow_pause' => true,
                    'time_per_question' => 60,
                ]),
                'settings' => json_encode([
                    'language' => 'pt',
                    'currency' => 'MZN',
                    'region' => 'Maputo',
                    'data_retention_days' => 365,
                ]),
                'allow_anonymous' => false,
                'require_login' => true,
                'multiple_responses' => false,
                'shuffle_questions' => false,
                'show_progress' => true,
                'confirmation_message' => 'Obrigado por completar nossa pesquisa! Suas respostas foram registradas com sucesso.',
                'time_limit' => null,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(30),
                'max_responses' => 300,
                'notify_on_response' => true,
                'notify_email' => 'pesquisador@exemplo.com',
                'theme' => json_encode([
                    'primary_color' => '#1976D2',
                    'secondary_color' => '#4CAF50',
                    'font_family' => 'Roboto, Arial, sans-serif',
                    'button_style' => 'rounded',
                    'background_color' => '#FFFFFF',
                ]),
                'completion_rate' => 89,
                'average_completion_time' => 8.5,
                'total_earned' => 4445.00,
                'total_paid' => 4000.00,
                'published_at' => Carbon::now()->subDays(10),
                'questions' => [
                    [
                        'title' => 'Com que frequência você faz compras no supermercado?',
                        'description' => 'Considere tanto supermercados grandes como pequenos mercados de bairro.',
                        'type' => 'multiple_choice',
                        'options' => json_encode([
                            ['value' => 'daily', 'label' => 'Diariamente'],
                            ['value' => 'weekly_2_3', 'label' => '2-3 vezes por semana'],
                            ['value' => 'weekly_1', 'label' => '1 vez por semana'],
                            ['value' => 'monthly_1_2', 'label' => '1-2 vezes por mês'],
                            ['value' => 'rarely', 'label' => 'Raramente'],
                        ]),
                        'placeholder' => 'Selecione uma opção',
                        'required' => true,
                        'order' => 1,
                        'metadata' => json_encode([
                            'help_text' => 'Considere todos os tipos de supermercado',
                            'skip_logic' => [],
                            'validation_rules' => ['required'],
                        ]),
                    ],
                    [
                        'title' => 'Qual o valor médio que você gasta por mês em supermercado?',
                        'type' => 'multiple_choice',
                        'options' => json_encode([
                            ['value' => '0_500', 'label' => 'Até 500 MZN'],
                            ['value' => '501_1000', 'label' => '501-1000 MZN'],
                            ['value' => '1001_2000', 'label' => '1001-2000 MZN'],
                            ['value' => '2001_5000', 'label' => '2001-5000 MZN'],
                            ['value' => '5000_plus', 'label' => 'Acima de 5000 MZN'],
                        ]),
                        'required' => true,
                        'order' => 2,
                        'metadata' => json_encode([
                            'currency' => 'MZN',
                            'include_not_sure' => false,
                        ]),
                    ],
                    [
                        'title' => 'Que fatores influenciam mais suas escolhas de produtos?',
                        'description' => 'Selecione todos que se aplicam (máximo 3)',
                        'type' => 'checkboxes',
                        'options' => json_encode([
                            ['value' => 'price', 'label' => 'Preço'],
                            ['value' => 'quality', 'label' => 'Qualidade'],
                            ['value' => 'brand', 'label' => 'Marca'],
                            ['value' => 'promotions', 'label' => 'Promoções'],
                            ['value' => 'recommendations', 'label' => 'Recomendações'],
                            ['value' => 'availability', 'label' => 'Disponibilidade'],
                            ['value' => 'packaging', 'label' => 'Embalagem'],
                        ]),
                        'required' => true,
                        'order' => 3,
                        'min_length' => 1,
                        'max_length' => 3,
                        'metadata' => json_encode([
                            'max_selections' => 3,
                            'randomize_options' => false,
                        ]),
                    ],
                    [
                        'title' => 'Em uma escala de 1 a 10, como você avalia a variedade de produtos nos supermercados?',
                        'type' => 'linear_scale',
                        'scale_min' => 1,
                        'scale_max' => 10,
                        'scale_step' => 1,
                        'scale_low_label' => 'Muito pouca variedade',
                        'scale_high_label' => 'Variedade excelente',
                        'required' => true,
                        'order' => 4,
                        'metadata' => json_encode([
                            'show_labels' => true,
                            'show_numbers' => true,
                        ]),
                    ],
                    [
                        'title' => 'Que tipo de produtos você gostaria de ver mais nos supermercados?',
                        'type' => 'paragraph',
                        'placeholder' => 'Descreva aqui os produtos que sente falta...',
                        'required' => false,
                        'order' => 5,
                        'min_length' => 10,
                        'max_length' => 500,
                        'metadata' => json_encode([
                            'allow_formatting' => false,
                            'character_count' => true,
                        ]),
                    ],
                ],
            ],
            [
                'title' => 'Satisfação com o Ensino Superior em Moçambique',
                'description' => 'Avaliação abrangente da qualidade do ensino, infraestrutura e serviços nas instituições de ensino superior moçambicanas. Esta pesquisa visa identificar áreas de melhoria e boas práticas.',
                'category' => 'Educação',
                'institution' => 'Universidade Pedagógica',
                'duration' => 18,
                'reward' => 45.00,
                'requirements' => json_encode([
                    'tipo_participante' => 'estudante_atual_ou_egresso',
                    'nivel_ensino' => 'superior',
                    'minimo_tempo' => 'pelo menos 1 semestre concluído',
                ]),
                'target_responses' => 180,
                'current_responses' => 92,
                'responses_count' => 105,
                'status' => 'active',
                'config' => json_encode([
                    'theme' => 'academic',
                    'welcome_message' => 'Bem-vindo à pesquisa sobre ensino superior!',
                    'completion_message' => 'Obrigado por contribuir para a melhoria da educação em Moçambique.',
                    'progress_bar' => true,
                    'randomize_sections' => false,
                    'allow_back_navigation' => true,
                ]),
                'allow_anonymous' => true,
                'require_login' => false,
                'multiple_responses' => false,
                'shuffle_questions' => true,
                'show_progress' => true,
                'time_limit' => 30, // minutos
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(45),
                'max_responses' => 200,
                'notify_on_response' => false,
                'theme' => json_encode([
                    'primary_color' => '#FF9800',
                    'secondary_color' => '#2196F3',
                    'font_family' => 'Arial, sans-serif',
                    'button_style' => 'square',
                ]),
                'completion_rate' => 87,
                'average_completion_time' => 12.3,
                'total_earned' => 4140.00,
                'total_paid' => 3600.00,
                'published_at' => Carbon::now()->subDays(5),
                'questions' => [
                    [
                        'title' => 'Em que instituição de ensino superior você estuda/estudou?',
                        'type' => 'dropdown',
                        'options' => json_encode([
                            ['value' => 'uem', 'label' => 'Universidade Eduardo Mondlane (UEM)'],
                            ['value' => 'up', 'label' => 'Universidade Pedagógica (UP)'],
                            ['value' => 'isutc', 'label' => 'ISUTC'],
                            ['value' => 'ucm', 'label' => 'Universidade Católica (UCM)'],
                            ['value' => 'unilurio', 'label' => 'Universidade Lúrio'],
                            ['value' => 'outra', 'label' => 'Outra instituição'],
                        ]),
                        'required' => true,
                        'order' => 1,
                    ],
                    [
                        'title' => 'Em uma escala de 1 a 5, como você avalia a qualidade geral do ensino?',
                        'type' => 'linear_scale',
                        'scale_min' => 1,
                        'scale_max' => 5,
                        'scale_step' => 1,
                        'scale_low_label' => 'Muito insatisfeito',
                        'scale_high_label' => 'Muito satisfeito',
                        'scale_value' => 3,
                        'required' => true,
                        'order' => 2,
                    ],
                    [
                        'title' => 'Que áreas precisam de mais investimento? (Selecione até 3)',
                        'type' => 'checkboxes',
                        'options' => json_encode([
                            ['value' => 'infraestrutura', 'label' => 'Infraestrutura física'],
                            ['value' => 'laboratorios', 'label' => 'Laboratórios e equipamentos'],
                            ['value' => 'biblioteca', 'label' => 'Biblioteca e recursos digitais'],
                            ['value' => 'professores', 'label' => 'Qualificação dos professores'],
                            ['value' => 'material', 'label' => 'Material didático'],
                            ['value' => 'tecnologia', 'label' => 'Tecnologia e internet'],
                            ['value' => 'transporte', 'label' => 'Transporte e acesso'],
                            ['value' => 'bolsas', 'label' => 'Bolsas de estudo'],
                        ]),
                        'required' => true,
                        'order' => 3,
                        'min_length' => 1,
                        'max_length' => 3,
                    ],
                    [
                        'title' => 'Quando você começou seus estudos?',
                        'type' => 'date',
                        'min_date' => '2010-01-01',
                        'max_date' => Carbon::now()->format('Y-m-d'),
                        'required' => true,
                        'order' => 4,
                        'placeholder' => 'Selecione a data',
                    ],
                    [
                        'title' => 'Em média, quantas horas por semana você dedica aos estudos fora da sala de aula?',
                        'type' => 'text',
                        'placeholder' => 'Ex: 10-15 horas',
                        'required' => false,
                        'order' => 5,
                        'metadata' => json_encode([
                            'input_type' => 'number',
                            'min_value' => 0,
                            'max_value' => 80,
                            'suffix' => 'horas',
                        ]),
                    ],
                    [
                        'title' => 'Comentários ou sugestões para melhorar o ensino superior:',
                        'type' => 'paragraph',
                        'placeholder' => 'Compartilhe suas ideias e experiências...',
                        'required' => false,
                        'order' => 6,
                        'min_length' => 20,
                        'max_length' => 1000,
                    ],
                ],
            ],
            [
                'title' => 'Impacto da Tecnologia no Trabalho Remoto',
                'description' => 'Pesquisa sobre como as tecnologias digitais afetam a produtividade e bem-estar no trabalho remoto. Foco em ferramentas, desafios e oportunidades.',
                'category' => 'Tecnologia',
                'institution' => 'ISUTC',
                'duration' => 15,
                'reward' => 40.00,
                'requirements' => json_encode([
                    'experiencia_trabalho_remoto' => 'pelo menos 3 meses',
                    'uso_tecnologia' => 'regular',
                    'setor' => 'qualquer',
                ]),
                'target_responses' => 120,
                'current_responses' => 58,
                'responses_count' => 65,
                'status' => 'draft',
                'config' => json_encode([
                    'theme' => 'tech',
                    'welcome_message' => 'Pesquisa sobre trabalho remoto e tecnologia',
                    'estimated_time' => '15 minutos',
                ]),
                'allow_anonymous' => false,
                'require_login' => true,
                'multiple_responses' => false,
                'shuffle_questions' => false,
                'show_progress' => true,
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addDays(60),
                'published_at' => null, // Ainda não publicado
                'questions' => [
                    [
                        'title' => 'Que ferramentas tecnológicas você usa regularmente para trabalho remoto?',
                        'type' => 'checkboxes',
                        'options' => json_encode([
                            ['value' => 'videoconf', 'label' => 'Videoconferência (Zoom, Teams, Meet)'],
                            ['value' => 'colaboracao', 'label' => 'Ferramentas de colaboração (Slack, Trello)'],
                            ['value' => 'cloud', 'label' => 'Armazenamento em nuvem (Google Drive, Dropbox)'],
                            ['value' => 'vpn', 'label' => 'VPN e segurança'],
                            ['value' => 'projetos', 'label' => 'Gestão de projetos (Asana, Jira)'],
                            ['value' => 'comunicacao', 'label' => 'Comunicação instantânea (WhatsApp, Telegram)'],
                        ]),
                        'required' => true,
                        'order' => 1,
                    ],
                    [
                        'title' => 'Qual seu nível de satisfação com a conectividade de internet?',
                        'type' => 'linear_scale',
                        'scale_min' => 1,
                        'scale_max' => 10,
                        'scale_low_label' => 'Muito insatisfeito',
                        'scale_high_label' => 'Muito satisfeito',
                        'required' => true,
                        'order' => 2,
                    ],
                ],
            ],
        ];

        foreach ($surveysData as $surveyData) {
            // Buscar a categoria pelo nome
            $category = SurveyCategory::where('name', $surveyData['category'])->first();
            $institution = SurveyInstitution::where('name', $surveyData['institution'])->first();

            $survey = Survey::create([
                'user_id' => $researcher->id,
                'researcher_id' => $researcher->id,
                'title' => $surveyData['title'],
                'description' => $surveyData['description'],
                'category' => $category ? $category->name : $surveyData['category'],
                'institution' => $institution ? $institution->name : $surveyData['institution'],
                'duration' => $surveyData['duration'],
                'reward' => $surveyData['reward'],
                'requirements' => $surveyData['requirements'],
                'target_responses' => $surveyData['target_responses'],
                'current_responses' => $surveyData['current_responses'],
                'responses_count' => $surveyData['responses_count'],
                'status' => $surveyData['status'],
                'config' => $surveyData['config'] ?? null,
                'settings' => $surveyData['settings'] ?? null,
                'allow_anonymous' => $surveyData['allow_anonymous'] ?? false,
                'require_login' => $surveyData['require_login'] ?? true,
                'multiple_responses' => $surveyData['multiple_responses'] ?? false,
                'shuffle_questions' => $surveyData['shuffle_questions'] ?? false,
                'show_progress' => $surveyData['show_progress'] ?? true,
                'confirmation_message' => $surveyData['confirmation_message'] ?? null,
                'time_limit' => $surveyData['time_limit'] ?? null,
                'start_date' => $surveyData['start_date'] ?? null,
                'end_date' => $surveyData['end_date'] ?? null,
                'max_responses' => $surveyData['max_responses'] ?? null,
                'notify_on_response' => $surveyData['notify_on_response'] ?? false,
                'notify_email' => $surveyData['notify_email'] ?? null,
                'theme' => $surveyData['theme'] ?? null,
                'completion_rate' => $surveyData['completion_rate'] ?? null,
                'average_completion_time' => $surveyData['average_completion_time'] ?? null,
                'total_earned' => $surveyData['total_earned'] ?? 0,
                'total_paid' => $surveyData['total_paid'] ?? 0,
                'published_at' => $surveyData['published_at'] ?? null,
            ]);

            // Criar perguntas
            foreach ($surveyData['questions'] as $questionData) {
                SurveyQuestion::create(array_merge(
                    $questionData,
                    ['survey_id' => $survey->id]
                ));
            }
        }

        // 5. Criar algumas respostas de exemplo (opcional)
        $this->createSampleResponses();

        // 6. Atualizar contadores nas categorias e instituições
        $this->updateCounters();

        $this->command->info('✅ Dados de surveys criados com sucesso!');
        $this->command->info('📊 ' . Survey::count() . ' pesquisas criadas');
        $this->command->info('❓ ' . SurveyQuestion::count() . ' perguntas criadas');
        $this->command->info('🏷️ ' . SurveyCategory::count() . ' categorias criadas');
        $this->command->info('🎓 ' . SurveyInstitution::count() . ' instituições criadas');
    }

    /**
     * Criar algumas respostas de exemplo
     */
    private function createSampleResponses(): void
    {
        // Se você tiver o model SurveyResponse criado, pode adicionar aqui
        // Por enquanto vamos pular esta parte

        $this->command->info('📝 Nota: Para criar respostas de exemplo, crie primeiro o Model SurveyResponse');
    }

    /**
     * Atualizar contadores nas categorias e instituições
     */
    private function updateCounters(): void
    {
        // Atualizar contadores de surveys por categoria
        $categories = SurveyCategory::all();
        foreach ($categories as $category) {
            $count = Survey::where('category', $category->name)->count();
            $category->update(['survey_count' => $count]);
        }

        // Atualizar contadores de surveys por instituição
        $institutions = SurveyInstitution::all();
        foreach ($institutions as $institution) {
            $count = Survey::where('institution', $institution->name)->count();
            $institution->update(['survey_count' => $count]);
        }
    }
}
