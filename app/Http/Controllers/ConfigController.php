<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ParticipantStats;

class ConfigController extends Controller
{
    /**
     * Obter todas as configurações organizadas por tipo
     */
    public function getAllConfigurations()
    {
        try {
            $configurations = DB::table('academic_configurations')
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('order')
                ->get()
                ->groupBy('type');

            // Transformar para o formato esperado pelo frontend
            $formattedConfigs = [
                'provinces' => $configurations->get('provinces', collect())->pluck('value')->toArray(),
                'occupations' => $configurations->get('occupations', collect())->pluck('value')->toArray(),
                'education_levels' => $configurations->get('education_levels', collect())->pluck('value')->toArray(),
                'research_areas' => $configurations->get('research_areas', collect())->map(function ($item) {
                    return [
                        'value' => $item->value,
                        'label' => $item->label
                    ];
                })->toArray(),
                'participation_frequencies' => $configurations->get('participation_frequencies', collect())->pluck('value')->toArray(),
                'institution_types' => $configurations->get('institution_types', collect())->pluck('value')->toArray(),
                'courses' => $configurations->get('courses', collect())->pluck('value')->toArray(),
                'academic_levels' => $configurations->get('academic_levels', collect())->pluck('value')->toArray(),
            ];

            return response()->json([
                'success' => true,
                'data' => $formattedConfigs,
                'message' => 'Configurações obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter configurações por tipo
     */
    public function getConfigurationsByType($type)
    {
        try {
            $configs = DB::table('academic_configurations')
                ->where('type', $type)
                ->where('is_active', true)
                ->orderBy('order')
                ->get(['value', 'label', 'order']);

            return response()->json([
                'success' => true,
                'data' => $configs,
                'message' => "Configurações do tipo '{$type}' obtidas com sucesso"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter configurações para participantes (frontend)
     */
    public function getParticipantConfigurations()
    {
        try {
            $configurations = DB::table('academic_configurations')
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('order')
                ->get()
                ->groupBy('type');

            return response()->json([
                'success' => true,
                'data' => [
                    'provinces' => $configurations->get('provinces', collect())->pluck('value')->toArray(),
                    'occupations' => $configurations->get('occupations', collect())->pluck('value')->toArray(),
                    'education_levels' => $configurations->get('education_levels', collect())->pluck('value')->toArray(),
                    'research_areas' => $configurations->get('research_areas', collect())->map(function ($item) {
                        return [
                            'value' => $item->value,
                            'label' => $item->label
                        ];
                    })->toArray(),
                    'participation_frequencies' => $configurations->get('participation_frequencies', collect())->pluck('value')->toArray(),
                ],
                'message' => 'Configurações para participantes obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter configurações para estudantes
     */
    public function getStudentConfigurations()
    {
        try {
            $configurations = DB::table('academic_configurations')
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('order')
                ->get()
                ->groupBy('type');

            // Obter universidades
            $universities = DB::table('universities')
                ->whereNotNull('name')
                ->orderBy('order')
                ->get(['name', 'acronym', 'type', 'location']);

            return response()->json([
                'success' => true,
                'data' => [
                    'universities' => $universities,
                    'institution_types' => $configurations->get('institution_types', collect())->pluck('value')->toArray(),
                    'courses' => $configurations->get('courses', collect())->pluck('value')->toArray(),
                    'academic_levels' => $configurations->get('academic_levels', collect())->pluck('value')->toArray(),
                    'research_areas' => $configurations->get('research_areas', collect())->map(function ($item) {
                        return [
                            'value' => $item->value,
                            'label' => $item->label
                        ];
                    })->toArray(),
                ],
                'message' => 'Configurações para estudantes obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter configurações: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas dos participantes
     */
    public function getParticipantStats()
    {
        try {
            $totalParticipants = User::where('role', 'participant')->count();
            $verifiedParticipants = User::where('role', 'participant')
                ->where('verification_status', 'approved')
                ->count();
            $pendingParticipants = User::where('role', 'participant')
                ->where('verification_status', 'pending')
                ->count();

            // Participantes por província
            $participantsByProvince = DB::table('participant_stats')
                ->join('users', 'participant_stats.user_id', '=', 'users.id')
                ->select('participant_stats.province', DB::raw('COUNT(*) as total'))
                ->where('users.role', 'participant')
                ->groupBy('participant_stats.province')
                ->orderBy('total', 'desc')
                ->get();

            // Participantes por ocupação
            $participantsByOccupation = DB::table('participant_stats')
                ->join('users', 'participant_stats.user_id', '=', 'users.id')
                ->select('participant_stats.occupation', DB::raw('COUNT(*) as total'))
                ->where('users.role', 'participant')
                ->groupBy('participant_stats.occupation')
                ->orderBy('total', 'desc')
                ->get();

            // Top participantes por pesquisas completadas
            $topParticipants = User::where('role', 'participant')
                ->with('participantStats')
                ->whereHas('participantStats', function ($query) {
                    $query->where('total_surveys_completed', '>', 0);
                })
                ->orderByDesc(function ($query) {
                    $query->select('total_surveys_completed')
                        ->from('participant_stats')
                        ->whereColumn('user_id', 'users.id')
                        ->limit(1);
                })
                ->limit(10)
                ->get(['id', 'name', 'email', 'phone', 'balance']);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_participants' => $totalParticipants,
                    'verified_participants' => $verifiedParticipants,
                    'pending_participants' => $pendingParticipants,
                    'participants_by_province' => $participantsByProvince,
                    'participants_by_occupation' => $participantsByOccupation,
                    'top_participants' => $topParticipants,
                    'example_participants' => $this->getExampleParticipants(),
                ],
                'message' => 'Estatísticas de participantes obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter participantes de exemplo
     */
    public function getExampleParticipants()
    {
        return [
            [
                'name' => 'Carlos Mendes',
                'occupation' => 'Profissional',
                'province' => 'Maputo Cidade',
                'description' => 'Participante ativo com múltiplas pesquisas completadas'
            ],
            [
                'name' => 'Ana Pereira',
                'occupation' => 'Professora',
                'province' => 'Maputo Província',
                'description' => 'Especialista em pesquisas educacionais'
            ],
            [
                'name' => 'Pedro Santos',
                'occupation' => 'Engenheiro',
                'province' => 'Gaza',
                'description' => 'Contribui com pesquisas tecnológicas'
            ],
            [
                'name' => 'Sofia Costa',
                'occupation' => 'Médica',
                'province' => 'Inhambane',
                'description' => 'Participante em pesquisas de saúde'
            ],
            [
                'name' => 'José Matola',
                'occupation' => 'Empresário',
                'province' => 'Sofala',
                'description' => 'Fornece insights sobre negócios'
            ],
            [
                'name' => 'Marta Chaúque',
                'occupation' => 'Estudante',
                'province' => 'Manica',
                'description' => 'Jovem participante universitária'
            ],
            [
                'name' => 'António Macuácua',
                'occupation' => 'Funcionário Público',
                'province' => 'Tete',
                'description' => 'Experiência em pesquisas sociais'
            ],
            [
                'name' => 'Luísa Nhaca',
                'occupation' => 'Agricultora',
                'province' => 'Zambézia',
                'description' => 'Representante do setor agrícola'
            ],
        ];
    }

    /**
     * Obter lista de universidades
     */
    public function getUniversities()
    {
        try {
            $universities = DB::table('universities')
                ->whereNotNull('name')
                ->orderBy('order')
                ->get(['id', 'name', 'acronym', 'type', 'location', 'website']);

            return response()->json([
                'success' => true,
                'data' => $universities,
                'message' => 'Universidades obtidas com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter universidades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dashboard de administração
     */
    public function getAdminDashboard()
    {
        try {
            // Estatísticas gerais
            $totalUsers = User::count();
            $totalStudents = User::where('role', 'student')->count();
            $totalParticipants = User::where('role', 'participant')->count();
            $totalAdmins = User::where('role', 'admin')->count();

            // Estatísticas de verificação
            $verifiedUsers = User::where('verification_status', 'approved')->count();
            $pendingUsers = User::where('verification_status', 'pending')->count();
            $rejectedUsers = User::where('verification_status', 'rejected')->count();

            // Configurações disponíveis
            $totalConfigs = DB::table('academic_configurations')->count();
            $activeConfigs = DB::table('academic_configurations')->where('is_active', true)->count();

            // Últimos registros
            $recentUsers = User::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'email', 'role', 'verification_status', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'general_stats' => [
                        'total_users' => $totalUsers,
                        'total_students' => $totalStudents,
                        'total_participants' => $totalParticipants,
                        'total_admins' => $totalAdmins,
                    ],
                    'verification_stats' => [
                        'verified_users' => $verifiedUsers,
                        'pending_users' => $pendingUsers,
                        'rejected_users' => $rejectedUsers,
                    ],
                    'config_stats' => [
                        'total_configurations' => $totalConfigs,
                        'active_configurations' => $activeConfigs,
                        'configuration_types' => DB::table('academic_configurations')
                            ->select('type', DB::raw('COUNT(*) as count'))
                            ->groupBy('type')
                            ->get(),
                    ],
                    'recent_users' => $recentUsers,
                ],
                'message' => 'Dashboard de administração obtido com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adicionar nova configuração (apenas admin)
     */
    public function addConfiguration(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string|max:50',
                'value' => 'required|string|max:200',
                'label' => 'required|string|max:200',
                'order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
                'metadata' => 'nullable|array',
            ]);

            // Verificar se já existe
            $exists = DB::table('academic_configurations')
                ->where('type', $validated['type'])
                ->where('value', $validated['value'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração já existe'
                ], 409);
            }

            $id = DB::table('academic_configurations')->insertGetId([
                'type' => $validated['type'],
                'value' => $validated['value'],
                'label' => $validated['label'],
                'order' => $validated['order'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'metadata' => $validated['metadata'] ? json_encode($validated['metadata']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => ['id' => $id],
                'message' => 'Configuração adicionada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar configuração (apenas admin)
     */
    public function updateConfiguration(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'label' => 'nullable|string|max:200',
                'order' => 'nullable|integer',
                'is_active' => 'nullable|boolean',
                'metadata' => 'nullable|array',
            ]);

            $updated = DB::table('academic_configurations')
                ->where('id', $id)
                ->update(array_filter([
                    'label' => $validated['label'] ?? null,
                    'order' => $validated['order'] ?? null,
                    'is_active' => $validated['is_active'] ?? null,
                    'metadata' => $validated['metadata'] ? json_encode($validated['metadata']) : null,
                    'updated_at' => now(),
                ]));

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuração atualizada com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar configuração (apenas admin)
     */
    public function deleteConfiguration($id)
    {
        try {
            $deleted = DB::table('academic_configurations')
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuração deletada com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuração não encontrada'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar configuração: ' . $e->getMessage()
            ], 500);
        }
    }
}
