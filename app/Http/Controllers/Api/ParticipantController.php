<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParticipantStats;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ParticipantController extends Controller
{
    /**
     * Registro de participante
     */
    public function register(Request $request)
    {
        Log::info('📤 Iniciando cadastro de participante', ['request_data' => $request->except(['password', 'password_confirmation'])]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+258\d{9}$/',
                'unique:users,phone',
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|in:masculino,feminino,outro',
            'province' => 'nullable|string|max:100',
            'mpesa_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+258\d{9}$/',
            ],
            'bi_number' => [
                'nullable',
                'string',
                'min:13',
                'max:13',
                'regex:/^[A-Z0-9]{13}$/',
            ],
            'occupation' => 'required|string|max:100',
            'education_level' => 'nullable|string|max:100',
            'research_interests' => 'nullable|array',
            'research_interests.*' => 'string|max:100',
            'participation_frequency' => 'nullable|string|max:100',
            'email_notifications' => 'sometimes|boolean',
            'whatsapp_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'accept_terms' => [
                'required',
                function ($attribute, $value, $fail) {
                    $acceptedValues = ['1', 1, true, 'true', 'on', 'yes'];
                    if (!in_array($value, $acceptedValues, true)) {
                        $fail('Você deve aceitar os termos e condições.');
                    }
                },
            ],
            'consent_data_collection' => 'sometimes|boolean',
        ], [
            'phone.required' => 'O número de telefone é obrigatório.',
            'phone.regex' => 'O número de telefone deve estar no formato: +258 seguido de 9 dígitos.',
            'phone.unique' => 'Este número de telefone já está cadastrado.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'Por favor, insira um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'mpesa_number.required' => 'O número M-Pesa é obrigatório para pagamentos.',
            'mpesa_number.regex' => 'O número M-Pesa deve estar no formato: +258 seguido de 9 dígitos.',
            'bi_number.regex' => 'O número de BI/DIRE deve ter exatamente 13 caracteres alfanuméricos.',
            'bi_number.size' => 'O número de BI/DIRE deve ter exatamente 13 caracteres.',
            'birth_date.date_format' => 'A data de nascimento deve estar no formato AAAA-MM-DD.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
            'occupation.required' => 'A ocupação profissional é obrigatória.',
            'accept_terms.required' => 'Você deve aceitar os termos e condições.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userData = [
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'phone' => trim($request->phone),
                'role' => 'participant',
                'balance' => 0.00,
                'verification_status' => 'approved',
                'email_notifications' => $request->boolean('email_notifications') ?? true,
                'whatsapp_notifications' => $request->boolean('whatsapp_notifications') ?? true,
            ];

            $user = User::create($userData);

            $researchInterests = $request->research_interests ?? [];
            if (is_array($researchInterests) && !empty($researchInterests)) {
                $researchInterests = json_encode($researchInterests);
            } else {
                $researchInterests = null;
            }

            $participantStatsData = [
                'user_id' => $user->id,
                'birth_date' => $request->birth_date ?: null,
                'gender' => $request->gender ?: null,
                'province' => $request->province ?: null,
                'bi_number' => $request->bi_number ? strtoupper(trim($request->bi_number)) : null,
                'mpesa_number' => trim($request->mpesa_number),
                'occupation' => trim($request->occupation),
                'education_level' => $request->education_level ?: null,
                'research_interests' => $researchInterests,
                'participation_frequency' => $request->participation_frequency ?: null,
                'consent_data_collection' => $request->boolean('consent_data_collection') ?? false,
                'sms_notifications' => $request->boolean('sms_notifications') ?? true,
                'total_surveys_completed' => 0,
                'total_earnings' => 0.00,
            ];

            if (class_exists('App\Models\ParticipantStats') && DB::getSchemaBuilder()->hasTable('participant_stats')) {
                ParticipantStats::create($participantStatsData);
            }

            $profileInfo = [
                'registration_date' => now()->toDateTimeString(),
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accept_terms' => true,
                'consent_data_collection' => (bool) ($request->consent_data_collection ?? false),
                'preferences' => [
                    'email_notifications' => (bool) ($request->email_notifications ?? true),
                    'whatsapp_notifications' => (bool) ($request->whatsapp_notifications ?? true),
                    'sms_notifications' => (bool) ($request->sms_notifications ?? true),
                ],
                'participant_data' => $participantStatsData,
            ];

            $user->profile_info = json_encode($profileInfo);
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso! Bem-vindo ao MozPesquisa.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'balance' => $user->balance,
                        'verification_status' => $user->verification_status,
                        'profile_info' => $profileInfo,
                    ],
                    'token' => $token,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ ERRO CRÍTICO no cadastro de participante: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao realizar cadastro',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obter perfil completo do participante
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->isParticipant()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para participantes'
            ], 403);
        }

        if (class_exists('App\Models\ParticipantStats')) {
            $user->load('participantStats');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'participant_stats' => $user->participantStats ?? null
            ]
        ]);
    }

    /**
     * Atualizar perfil do participante
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->isParticipant()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para participantes'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^\+258\d{9}$/',
            ],
            'email_notifications' => 'sometimes|boolean',
            'whatsapp_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'province' => 'nullable|string|max:100',
            'mpesa_number' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\+258\d{9}$/',
            ],
            'occupation' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:100',
            'participation_frequency' => 'nullable|string|max:100',
            'research_interests' => 'nullable|array',
            'research_interests.*' => 'string|max:100',
        ], [
            'phone.regex' => 'O número de telefone deve estar no formato: +258 seguido de 9 dígitos.',
            'mpesa_number.regex' => 'O número M-Pesa deve estar no formato: +258 seguido de 9 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userData = $request->only(['name', 'phone', 'email_notifications', 'whatsapp_notifications']);
            $userData = array_filter($userData, function ($value) {
                return !is_null($value);
            });

            if (!empty($userData)) {
                $user->update($userData);
            }

            if ($user->participantStats && class_exists('App\Models\ParticipantStats')) {
                $statsData = $request->only([
                    'province',
                    'mpesa_number',
                    'occupation',
                    'education_level',
                    'participation_frequency'
                ]);

                if ($request->has('research_interests')) {
                    $researchInterests = $request->research_interests;
                    if (is_array($researchInterests) && !empty($researchInterests)) {
                        $statsData['research_interests'] = json_encode($researchInterests);
                    }
                }

                if ($request->has('sms_notifications')) {
                    $statsData['sms_notifications'] = $request->boolean('sms_notifications');
                }

                $statsData = array_filter($statsData, function ($value) {
                    return !is_null($value);
                });

                if (!empty($statsData)) {
                    $user->participantStats->update($statsData);
                }
            }

            if (class_exists('App\Models\ParticipantStats')) {
                $user->load('participantStats');
            }

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao atualizar perfil: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obter pesquisas disponíveis para o participante - VERSÃO COM active APENAS
     */
    public function getAvailableSurveys(Request $request)
    {
        $user = $request->user();

        if (!$user->isParticipant()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para participantes'
            ], 403);
        }

        try {
            // 1. Obter pesquisas que o usuário já respondeu
            $answeredIds = SurveyResponse::where('user_id', $user->id)
                ->pluck('survey_id')
                ->toArray();

            // 2. Buscar pesquisas ativas - CORREÇÃO AQUI!
            $surveys = Survey::where('status', 'active')
                ->whereNotIn('id', $answeredIds)  // Apenas não respondidas
                // ->where('user_id', '!=', $user->id)  // ❌❌❌ REMOVA ESTA LINHA COMPLETAMENTE!
                ->select(
                    'id',
                    'title',
                    'description',
                    'category',
                    'duration',
                    'reward',
                    'target_responses',
                    'current_responses',
                    'user_id',
                    'institution',
                    'created_at'
                )
                ->get()
                ->map(function ($survey) {
                    $researcher = User::find($survey->user_id);

                    return [
                        'id' => (string) $survey->id,
                        'title' => $survey->title,
                        'description' => $survey->description,
                        'category' => $survey->category,
                        'estimated_time' => (int) $survey->duration,
                        'reward' => (float) $survey->reward,
                        'target_responses' => (int) $survey->target_responses,
                        'current_responses' => (int) $survey->current_responses,
                        'researcher' => [
                            'id' => (string) $survey->user_id,
                            'name' => $researcher ? $researcher->name : 'Desconhecido',
                            'institution' => $survey->institution,
                        ],
                        'created_at' => $survey->created_at->toDateTimeString(),
                        'questions_count' => $survey->questions()->count(),
                        'available_slots' => max(0, $survey->target_responses - $survey->current_responses),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Pesquisas disponíveis carregadas com sucesso',
                'data' => [
                    'available_surveys' => $surveys,
                    'total_available' => $surveys->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'message' => 'Pesquisas disponíveis carregadas com sucesso',
                'data' => [
                    'available_surveys' => [],
                    'total_available' => 0
                ]
            ]);
        }
    }
    /**
     * Responder a uma pesquisa - VERSÃO COM active APENAS
     */
    public function respondToSurvey(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user->isParticipant()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403);
            }

            // ✅ APENAS pesquisas com status 'active'
            $survey = Survey::where('id', $id)
                ->where('status', 'active')  // ✅ APENAS 'active'
                ->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesquisa não encontrada ou não disponível',
                ], 404);
            }

            // Verificar se já respondeu
            $existingResponse = SurveyResponse::where('user_id', $user->id)
                ->where('survey_id', $id)
                ->first();

            if ($existingResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você já respondeu a esta pesquisa',
                ], 400);
            }

            // Verificar se atingiu o limite de respostas
            $currentResponses = SurveyResponse::where('survey_id', $id)
                ->where('status', 'completed')
                ->count();

            if ($currentResponses >= $survey->target_responses) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta pesquisa já atingiu o número máximo de respostas',
                ], 400);
            }

            // Criar resposta
            $response = SurveyResponse::create([
                'user_id' => $user->id,
                'survey_id' => $id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Criar transação pendente
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'survey_id' => $id,
                'type' => 'survey_earnings',
                'amount' => $survey->reward,
                'status' => 'pending',
                'description' => 'Participação na pesquisa: ' . $survey->title,
            ]);

            Log::info('✅ Participante iniciou pesquisa', [
                'user_id' => $user->id,
                'survey_id' => $id,
                'response_id' => $response->id,
                'transaction_id' => $transaction->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesquisa iniciada com sucesso',
                'data' => [
                    'id' => (string) $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'survey_title' => $survey->title,
                    'survey_id' => (string) $survey->id,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao iniciar pesquisa: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'guest',
                'survey_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar pesquisa',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter ganhos do participante
     */
    public function getEarnings(Request $request)
    {
        $user = $request->user();

        if (!$user->isParticipant()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para participantes'
            ], 403);
        }

        try {
            $totalEarned = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->sum('amount');

            $completedSurveys = SurveyResponse::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();

            $recentTransactions = Transaction::where('user_id', $user->id)
                ->where('type', 'survey_earnings')
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) {
                    $surveyTitle = null;
                    if ($transaction->survey_id) {
                        $survey = Survey::find($transaction->survey_id);
                        $surveyTitle = $survey ? $survey->title : null;
                    }

                    return [
                        'id' => (string) $transaction->id,
                        'amount' => (float) $transaction->amount,
                        'survey_title' => $surveyTitle,
                        'created_at' => $transaction->created_at->toDateTimeString(),
                    ];
                });

            $earnings = [
                'total_balance' => (float) $user->balance,
                'total_earnings' => (float) $totalEarned,
                'total_surveys_completed' => $completedSurveys,
                'average_per_survey' => $completedSurveys > 0 ? $totalEarned / $completedSurveys : 0,
                'recent_transactions' => $recentTransactions,
            ];

            return response()->json([
                'success' => true,
                'data' => $earnings
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao carregar ganhos: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar ganhos',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obter dados completos do dashboard do participante - VERSÃO SIMPLIFICADA
     */
    /**
     * Obter dados completos do dashboard do participante - VERSÃO COM RANKING
     */
    public function getDashboardData(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || $user->role !== 'participant') {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso permitido apenas para participantes',
                ], 403);
            }

            // Obter estatísticas
            $stats = $this->getParticipantStats($user);

            // Obter pesquisas disponíveis
            $availableSurveys = $this->getAvailableSurveysForDashboard($user);

            // Obter transações recentes
            $recentTransactions = $this->getRecentTransactions($user);

            // 🔥 NOVO: Obter ranking do participante
            $rankingData = $this->getUserRankingInfo($user);

            return response()->json([
                'success' => true,
                'message' => 'Dashboard do participante carregado com sucesso',
                'data' => [
                    'stats' => $stats,
                    'available_surveys' => $availableSurveys,
                    'recent_transactions' => $recentTransactions,
                    'notifications' => [], // Temporariamente vazio
                    'last_updated' => now()->toDateTimeString(),
                    // 🔥 ADICIONAR DADOS DO RANKING
                    'ranking_info' => $rankingData,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao carregar dashboard: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'guest'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dashboard',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * 🔥 NOVO MÉTODO: Obter informações de ranking do usuário
     */
    private function getUserRankingInfo($user)
    {
        try {
            // Buscar todos os participantes ordenados por balance
            $allParticipants = User::where('role', 'participant')
                ->where('verification_status', 'approved')
                ->orderByDesc('balance')
                ->get(['id', 'name', 'balance']);

            // Encontrar posição do usuário atual
            $position = 0;
            foreach ($allParticipants as $index => $participant) {
                if ($participant->id == $user->id) {
                    $position = $index + 1;
                    break;
                }
            }

            // Calcular informações do ranking
            $totalParticipants = $allParticipants->count();
            $topParticipants = $allParticipants->take(5)->map(function ($participant, $index) {
                return [
                    'position' => $index + 1,
                    'name' => $participant->name,
                    'balance' => (float) $participant->balance,
                    'badge' => $this->getBadgeForPosition($index + 1),
                ];
            });

            // Distância para próximo (se não for 1º)
            $distanceToNext = 0;
            if ($position > 1 && $position <= $totalParticipants) {
                $userBalance = (float) $user->balance;
                $participantAbove = $allParticipants[$position - 2]; // -2 porque array começa em 0
                $distanceToNext = max(0, ((float) $participantAbove->balance) - $userBalance + 0.01);
            }

            // Distância do anterior (se não for último)
            $distanceFromPrevious = 0;
            if ($position < $totalParticipants && $position > 0) {
                $userBalance = (float) $user->balance;
                $participantBelow = $allParticipants[$position]; // Posição atual no array
                $distanceFromPrevious = max(0, $userBalance - ((float) $participantBelow->balance));
            }

            return [
                'current_position' => $position,
                'total_participants' => $totalParticipants,
                'badge' => $this->getBadgeForPosition($position),
                'tier' => $this->getTierForPosition($position),
                'top_participants' => $topParticipants,
                'user_balance' => (float) $user->balance,
                'distance_to_next' => round($distanceToNext, 2),
                'distance_from_previous' => round($distanceFromPrevious, 2),
                'is_in_top_three' => $position <= 3,
                'is_in_top_ten' => $position <= 10,
                'progress_percentage' => $totalParticipants > 0 ?
                    round((($totalParticipants - $position + 1) / $totalParticipants) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao calcular ranking info: ' . $e->getMessage());
            return [
                'current_position' => 0,
                'total_participants' => 0,
                'badge' => '📊',
                'tier' => 'participant',
                'top_participants' => [],
                'user_balance' => (float) $user->balance,
                'distance_to_next' => 0,
                'distance_from_previous' => 0,
                'is_in_top_three' => false,
                'is_in_top_ten' => false,
                'progress_percentage' => 0,
            ];
        }
    }

    /**
     * 🔥 FUNÇÃO AUXILIAR: Obter badge para posição
     */
    private function getBadgeForPosition($position)
    {
        if ($position === 1) return '🥇';
        if ($position === 2) return '🥈';
        if ($position === 3) return '🥉';
        if ($position <= 10) return '⭐';
        return '📊';
    }

    /**
     * 🔥 FUNÇÃO AUXILIAR: Obter tier para posição
     */
    private function getTierForPosition($position)
    {
        if ($position <= 3) return 'gold';
        if ($position <= 10) return 'silver';
        if ($position <= 25) return 'bronze';
        return 'participant';
    }

    /**
     * Obter estatísticas do participante (método privado)
     */
    private function getParticipantStats($user)
    {
        $currentBalance = $user->balance ?? 0;

        $totalEarned = Transaction::where('user_id', $user->id)
            ->where('type', 'survey_earnings')
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawn = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'completed')
            ->sum('amount');

        $completedSurveys = SurveyResponse::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalSurveysResponded = SurveyResponse::where('user_id', $user->id)->count();

        return [
            'current_balance' => (float) $currentBalance,
            'total_earned' => (float) $totalEarned,
            'completed_surveys' => $completedSurveys,
            'total_surveys_responded' => $totalSurveysResponded,
            'total_withdrawn' => (float) $totalWithdrawn,
        ];
    }

    /**
     * Obter pesquisas disponíveis para dashboard (método privado) - VERSÃO COM active APENAS
     */
    private function getAvailableSurveysForDashboard($user)
    {
        try {
            $answeredSurveyIds = SurveyResponse::where('user_id', $user->id)
                ->pluck('survey_id')
                ->toArray();

            $surveys = Survey::available()
                ->whereNotIn('id', $answeredSurveyIds)
                ->where('user_id', '!=', $user->id)

                // ✅ CORREÇÃO: Remover 'university as institution'
                ->with(['user' => function ($query) {
                    $query->select('id', 'name');
                }])

                ->select([
                    'id',
                    'title',
                    'description',
                    'category',
                    'duration',
                    'reward',
                    'target_responses',
                    'current_responses',
                    'user_id',
                    'institution',  // ✅ Usar esta coluna
                    'created_at',
                ])
                ->limit(10)
                ->get()
                ->map(function ($survey) {
                    return [
                        'id' => (string) $survey->id,
                        'title' => $survey->title,
                        'description' => $survey->description,
                        'category' => $survey->category,
                        'estimated_time' => (int) $survey->duration,
                        'reward' => (float) $survey->reward,
                        'target_responses' => (int) $survey->target_responses,
                        'current_responses' => (int) $survey->current_responses,
                        'researcher' => [
                            'id' => (string) $survey->user->id,
                            'name' => $survey->user->name,
                            'institution' => $survey->institution,  // ✅ Da pesquisa
                        ],
                        'created_at' => $survey->created_at->toDateTimeString(),
                        'questions_count' => $survey->questions()->count(),
                    ];
                });

            return $surveys;
        } catch (\Exception $e) {
            Log::error('Erro ao carregar pesquisas para dashboard: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter transações recentes do participante (método privado)
     */
    private function getRecentTransactions($user)
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaction) {
                $surveyTitle = null;
                if ($transaction->survey_id) {
                    $survey = Survey::find($transaction->survey_id);
                    $surveyTitle = $survey ? $survey->title : null;
                }

                return [
                    'id' => (string) $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'survey_title' => $surveyTitle,
                    'survey_id' => $transaction->survey_id ? (string) $transaction->survey_id : null,
                    'payment_method' => $transaction->payment_method,
                    'account_details' => $transaction->account_details,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                    'completed_at' => $transaction->completed_at ? $transaction->completed_at->toDateTimeString() : null,
                ];
            });

        return $transactions;
    }

    /**
     * Solicitar saque
     */
    public function requestWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:50',
            'payment_method' => 'required|in:mpesa,bank_transfer,cash',
            'account_details' => 'required_if:payment_method,mpesa,bank_transfer|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            if ($user->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente para saque',
                ], 400);
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'status' => 'pending',
                'description' => 'Solicitação de saque',
                'payment_method' => $request->payment_method,
                'account_details' => $request->account_details,
            ]);

            $user->balance -= $request->amount;
            $user->save();

            Log::info('💰 Solicitação de saque criada', [
                'user_id' => $user->id,
                'amount' => $request->amount,
                'transaction_id' => $transaction->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Saque solicitado com sucesso',
                'data' => [
                    'id' => (string) $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'payment_method' => $transaction->payment_method,
                    'account_details' => $transaction->account_details,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao solicitar saque: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'guest'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao solicitar saque',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter histórico de transações com paginação
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = $request->user();

            $perPage = $request->get('limit', 15);
            $page = $request->get('page', 1);
            $type = $request->get('type', 'all');

            $query = Transaction::where('user_id', $user->id);

            if ($type !== 'all') {
                $query->where('type', $type);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $transactionsData = $transactions->map(function ($transaction) {
                $surveyTitle = null;
                if ($transaction->survey_id) {
                    $survey = Survey::find($transaction->survey_id);
                    $surveyTitle = $survey ? $survey->title : null;
                }

                return [
                    'id' => (string) $transaction->id,
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'survey_title' => $surveyTitle,
                    'survey_id' => $transaction->survey_id ? (string) $transaction->survey_id : null,
                    'payment_method' => $transaction->payment_method,
                    'account_details' => $transaction->account_details,
                    'created_at' => $transaction->created_at->toDateTimeString(),
                    'completed_at' => $transaction->completed_at ? $transaction->completed_at->toDateTimeString() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Transações carregadas com sucesso',
                'data' => $transactionsData,
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'total_pages' => $transactions->lastPage(),
                    'total_items' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Erro ao carregar transações: ' . $e->getMessage(), [
                'user_id' => $request->user()->id ?? 'guest'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar transações',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter ranking detalhado
     */
    /**
     * Obter ranking dos participantes - VERSÃO ATUALIZADA COM MAIS INFORMAÇÕES
     */
    public function getRankings(Request $request)
    {
        try {
            $user = $request->user();

            // VERSÃO DEFINITIVA - Combina transações + balance
            $rankings = User::where('role', 'participant')
                ->where('verification_status', 'approved')
                ->select('users.*')

                // Subquery para calcular ganhos de transações
                ->selectSub(function ($query) {
                    $query->from('transactions')
                        ->selectRaw('COALESCE(SUM(amount), 0)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('type', 'survey_earnings')
                        ->whereIn('status', ['completed', 'approved', 'pending']);
                }, 'earnings_from_transactions')

                // Subquery para contar pesquisas completadas
                ->selectSub(function ($query) {
                    $query->from('survey_responses')
                        ->selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'completed');
                }, 'completed_surveys')

                // Subquery para obter data da última pesquisa
                ->selectSub(function ($query) {
                    $query->from('survey_responses')
                        ->selectRaw('MAX(created_at)')
                        ->whereColumn('user_id', 'users.id')
                        ->where('status', 'completed');
                }, 'last_survey_date')

                // Ordenar: Usar o MAIOR entre (transações + balance) e (apenas balance)
                ->orderByRaw('GREATEST(earnings_from_transactions, users.balance) DESC')
                ->orderByDesc('completed_surveys')
                ->orderBy('users.name')

                ->get()
                ->map(function ($user, $index) {
                    // Calcular total_earned: máximo entre transações e balance
                    $earningsFromTransactions = (float) $user->earnings_from_transactions;
                    $currentBalance = (float) $user->balance;

                    // Usar o maior valor disponível
                    $totalEarned = max($earningsFromTransactions, $currentBalance);

                    // Calcular rating real
                    $averageRating = 0;
                    try {
                        $responses = \App\Models\SurveyResponse::where('user_id', $user->id)
                            ->whereNotNull('rating')
                            ->get();

                        if ($responses->count() > 0) {
                            $averageRating = $responses->avg('rating');
                        }
                    } catch (\Exception $e) {
                        $averageRating = 0;
                    }

                    // Obter data da última pesquisa
                    $lastSurveyDate = null;
                    if ($user->last_survey_date) {
                        try {
                            $lastSurveyDate = \Carbon\Carbon::parse($user->last_survey_date)
                                ->format('Y-m-d H:i:s');
                        } catch (\Exception $e) {
                            $lastSurveyDate = null;
                        }
                    }

                    // Determinar badge baseado na posição
                    $position = $index + 1;
                    $badge = match (true) {
                        $position === 1 => '🥇',
                        $position === 2 => '🥈',
                        $position === 3 => '🥉',
                        $position <= 10 => '⭐',
                        default => '📊'
                    };

                    // Determinar tier/nível
                    $tier = match (true) {
                        $position <= 3 => 'gold',
                        $position <= 10 => 'silver',
                        $position <= 25 => 'bronze',
                        default => 'participant'
                    };

                    // Determinar status de atividade
                    $activityStatus = 'active';
                    if ($lastSurveyDate) {
                        $lastSurvey = \Carbon\Carbon::parse($lastSurveyDate);
                        $daysSinceLastSurvey = $lastSurvey->diffInDays(now());

                        $activityStatus = match (true) {
                            $daysSinceLastSurvey <= 7 => 'very_active',
                            $daysSinceLastSurvey <= 30 => 'active',
                            $daysSinceLastSurvey <= 90 => 'inactive',
                            default => 'very_inactive'
                        };
                    } elseif ($totalEarned == 0) {
                        $activityStatus = 'new';
                    }

                    return [
                        'user_id' => (string) $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'position' => $position,
                        'badge' => $badge,
                        'tier' => $tier,
                        'total_earned' => round($totalEarned, 2),
                        'earnings_from_transactions' => round($earningsFromTransactions, 2),
                        'current_balance' => round($currentBalance, 2),
                        'completed_surveys' => (int) $user->completed_surveys,
                        'average_rating' => round($averageRating, 1),
                        'last_survey_date' => $lastSurveyDate,
                        'join_date' => $user->created_at->format('Y-m-d'),
                        'activity_status' => $activityStatus,
                        'earnings_source' => $earningsFromTransactions > 0 ? 'transactions' : 'balance_field',
                        'profile_completion' => $this->calculateProfileCompletion($user),
                    ];
                });

            // Encontrar posição do usuário atual
            $currentUserPosition = null;
            foreach ($rankings as $ranking) {
                if ($ranking['user_id'] == (string) $user->id) {
                    $currentUserPosition = $ranking['position'];
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Ranking carregado com sucesso',
                'data' => [
                    'rankings' => $rankings,
                    'current_user_position' => $currentUserPosition,
                    'current_user_info' => $currentUserPosition ? $rankings[$currentUserPosition - 1] : null,
                    'stats' => [
                        'total_participants' => $rankings->count(),
                        'top_earner' => $rankings->first(),
                        'average_earnings' => round($rankings->avg('total_earned'), 2),
                        'total_earnings_all' => round($rankings->sum('total_earned'), 2),
                        'active_participants' => $rankings->where('total_earned', '>', 0)->count(),
                    ],
                    'pagination' => [
                        'current_page' => 1,
                        'total_pages' => 1,
                        'per_page' => $rankings->count(),
                        'total_items' => $rankings->count(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar ranking',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Calcular porcentagem de conclusão do perfil
     */
    private function calculateProfileCompletion($user)
    {
        $completedFields = 0;
        $totalFields = 8; // Campos importantes

        $fieldsToCheck = [
            'name' => !empty($user->name),
            'email' => !empty($user->email) && $user->hasVerifiedEmail(),
            'phone' => !empty($user->phone),
            'profile_info' => !empty($user->profile_info),
            'avatar' => !empty($user->avatar),
            'bi_number' => false, // Verificar em participant_stats
            'mpesa_number' => false, // Verificar em participant_stats
            'occupation' => false, // Verificar em participant_stats
        ];

        // Verificar campos em participant_stats se existir
        if ($user->participantStats) {
            $fieldsToCheck['bi_number'] = !empty($user->participantStats->bi_number);
            $fieldsToCheck['mpesa_number'] = !empty($user->participantStats->mpesa_number);
            $fieldsToCheck['occupation'] = !empty($user->participantStats->occupation);
        }

        foreach ($fieldsToCheck as $field => $isCompleted) {
            if ($isCompleted) $completedFields++;
        }

        return round(($completedFields / $totalFields) * 100);
    }

    /**
     * Obter métodos de pagamento do participante
     */
    public function getPaymentMethods(Request $request)
    {
        try {
            $user = $request->user();

            // Buscar métodos de pagamento salvos
            $profileInfo = json_decode($user->profile_info ?? '{}', true);
            $paymentMethods = $profileInfo['payment_methods'] ?? [];

            // Se não tiver nenhum, retornar métodos padrão
            if (empty($paymentMethods)) {
                $paymentMethods = [
                    [
                        'id' => 'mpesa',
                        'name' => 'M-Pesa',
                        'icon' => 'smartphone',
                        'color' => 'green',
                        'account' => $user->participantStats->mpesa_number ?? '+258 84 123 4567',
                        'is_default' => true,
                        'min_withdrawal' => 100,
                        'processing_time' => 'Instantâneo'
                    ],
                    [
                        'id' => 'bank',
                        'name' => 'Conta Bancária',
                        'icon' => 'account_balance',
                        'color' => 'primary',
                        'account' => 'BIM •••• 1234',
                        'is_default' => false,
                        'min_withdrawal' => 500,
                        'processing_time' => '1-2 dias úteis'
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $paymentMethods
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar métodos de pagamento: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [
                    [
                        'id' => 'mpesa',
                        'name' => 'M-Pesa',
                        'icon' => 'smartphone',
                        'color' => 'green',
                        'account' => '+258 84 123 4567',
                        'is_default' => true,
                        'min_withdrawal' => 100,
                        'processing_time' => 'Instantâneo'
                    ]
                ]
            ]);
        }
    }

    /**
     * Obter configurações de saque
     */
    public function getWithdrawalSettings(Request $request)
    {
        try {
            // Configurações padrão
            $settings = [
                'min_amount' => 100,
                'max_amount' => null,
                'processing_days' => 3,
                'fee_percentage' => 0,
                'allowed_methods' => ['mpesa', 'bank'],
                'working_hours' => [
                    'start' => '08:00',
                    'end' => '17:00'
                ],
                'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
            ];

            // Buscar configurações do sistema (se existir tabela)
            if (DB::getSchemaBuilder()->hasTable('system_settings')) {
                $dbSettings = DB::table('system_settings')
                    ->whereIn('key', ['min_withdrawal', 'max_withdrawal', 'processing_days'])
                    ->pluck('value', 'key');

                if (isset($dbSettings['min_withdrawal'])) {
                    $settings['min_amount'] = (float) $dbSettings['min_withdrawal'];
                }
                if (isset($dbSettings['max_withdrawal'])) {
                    $settings['max_amount'] = (float) $dbSettings['max_withdrawal'];
                }
                if (isset($dbSettings['processing_days'])) {
                    $settings['processing_days'] = (int) $dbSettings['processing_days'];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar configurações de saque: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [
                    'min_amount' => 100,
                    'max_amount' => null,
                    'processing_days' => 3,
                    'fee_percentage' => 0,
                    'allowed_methods' => ['mpesa', 'bank']
                ]
            ]);
        }
    }

    
}
