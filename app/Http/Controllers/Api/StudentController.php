<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentStats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Registro de estudante
     */
    public function register(Request $request)
    {
        Log::info('📤 Iniciando cadastro de estudante', ['request_data' => $request->except(['password', 'password_confirmation'])]);

        $validator = Validator::make($request->all(), [
            // ============ INFORMAÇÕES PESSOAIS (User) ============
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+258\d{9}$/',
                'unique:users,phone',
            ],
            'gender' => 'required|in:masculino,feminino',

            // ============ INFORMAÇÕES DE ESTUDANTE (StudentStats) ============
            'bi_number' => [
                'required',
                'string',
                'size:13',
                'regex:/^\d{12}[A-Z]$/',
                'unique:student_stats,bi_number',
            ],
            'birth_date' => 'nullable|date_format:Y-m-d',
            'institution_type' => 'nullable|string',
            'university' => 'required|string|max:255',
            'course' => 'required|string|max:255',
            'admission_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'expected_graduation' => 'nullable|integer|min:' . date('Y') . '|max:' . (date('Y') + 10),
            'academic_level' => 'nullable|string',
            'student_card_number' => 'nullable|string',
            'research_interests' => 'nullable',

            // ============ DOCUMENTAÇÃO ============
            'academic_integrity' => 'boolean',

            // ============ SEGURANÇA ============
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',

            // ============ PREFERÊNCIAS (User) ============
            'email_notifications' => 'boolean',
            'whatsapp_notifications' => 'boolean',
            'accept_terms' => 'required|accepted',

            // ============ ARQUIVOS (OPCIONAL) ============
            'student_card_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'enrollment_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            // Mensagens customizadas
            'bi_number.regex' => 'O número de BI/DIRE deve ter 12 dígitos seguidos de 1 letra maiúscula.',
            'bi_number.size' => 'O número de BI/DIRE deve ter exatamente 13 caracteres.',
            'bi_number.unique' => 'Este número de BI/DIRE já está registrado.',
            'phone.regex' => 'O número de telefone deve estar no formato: +258 seguido de 9 dígitos (ex: +258841234567).',
            'birth_date.date_format' => 'A data de nascimento deve estar no formato AAAA-MM-DD.',
            'admission_year.max' => 'O ano de ingresso não pode ser no futuro.',
            'expected_graduation.min' => 'O ano de formatura não pode ser no passado.',
        ]);

        if ($validator->fails()) {
            Log::warning('❌ Erro de validação no cadastro', ['errors' => $validator->errors()->toArray()]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            Log::info('✅ Validação passou, criando usuário...');

            // ============ 1. CRIAR USUÁRIO BÁSICO ============
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'student',
                'verification_status' => 'pending',
                'email_notifications' => $request->boolean('email_notifications') ?? true,
                'whatsapp_notifications' => $request->boolean('whatsapp_notifications') ?? true,
            ]);

            Log::info('✅ Usuário básico criado com ID: ' . $user->id);

            // ============ 2. CRIAR STUDENT_STATS ============
            $researchInterests = $request->research_interests ?? [];
            if (is_array($researchInterests) && !empty($researchInterests)) {
                $researchInterests = json_encode($researchInterests);
            } else {
                $researchInterests = null;
            }

            // Processar data de nascimento
            $birthDate = null;
            if ($request->birth_date) {
                $birthDate = $request->birth_date;
            }

            // Criar student_stats
            $studentStatsData = [
                'bi_number' => $request->bi_number,
                'birth_date' => $birthDate,
                'gender' => $request->gender,
                'institution_type' => $request->institution_type,
                'university' => $request->university,
                'course' => $request->course,
                'admission_year' => $request->admission_year ? (int) $request->admission_year : null,
                'expected_graduation' => $request->expected_graduation ? (int) $request->expected_graduation : null,
                'academic_level' => $request->academic_level,
                'student_card_number' => $request->student_card_number,
                'research_interests' => $researchInterests,
                'documents_submitted' => false,
            ];

            $studentStats = $user->studentStats()->create($studentStatsData);

            Log::info('📊 StudentStats criado para usuário ID: ' . $user->id);

            // ============ 3. PROCESSAR DOCUMENTOS ============
            $hasDocuments = false;

            if ($request->hasFile('student_card_file')) {
                Log::info('📄 Processando cartão de estudante...');
                $this->processStudentDocument($user, $request->file('student_card_file'), 'student_card');
                $hasDocuments = true;
            }

            if ($request->hasFile('enrollment_proof_file')) {
                Log::info('📄 Processando comprovativo de matrícula...');
                $this->processStudentDocument($user, $request->file('enrollment_proof_file'), 'enrollment_proof');
                $hasDocuments = true;
            }

            // ============ 4. ATUALIZAR STATUS DE DOCUMENTOS ============
            if ($hasDocuments) {
                $studentStats->update(['documents_submitted' => true]);
            }

            // ============ 5. ATUALIZAR PROFILE_INFO ============
            $profileInfo = [
                'registration_date' => now()->toDateTimeString(),
                'academic_integrity_accepted' => (bool) $request->academic_integrity,
                'terms_accepted' => true,
                'registration_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_uploaded_documents' => $hasDocuments,
            ];

            if ($hasDocuments) {
                $profileInfo['documents_uploaded_at'] = now()->toDateTimeString();
            }

            $user->profile_info = json_encode($profileInfo);
            $user->save();

            Log::info('📋 Profile info atualizado');

            // ============ 6. CRIAR TOKEN ============
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            Log::info('🎉 Cadastro concluído com sucesso para usuário ID: ' . $user->id);

            // Carregar student_stats com o usuário
            $user->load('studentStats');

            return response()->json([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso! Verificação em andamento.',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('❌ Erro no cadastro de estudante: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar cadastro',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Obter perfil completo do estudante
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para estudantes'
            ], 403);
        }

        $user->load('studentStats', 'documents');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'student_stats' => $user->studentStats,
                'documents' => $user->documents
            ]
        ]);
    }

    /**
     * Atualizar perfil do estudante
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para estudantes'
            ], 403);
        }

        Log::info('🔄 Atualização de perfil de estudante', ['user_id' => $user->id]);

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

            // Campos de student_stats
            'university' => 'sometimes|string|max:255',
            'course' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string',
            'academic_level' => 'nullable|string',
            'student_card_number' => 'nullable|string',
        ], [
            'phone.regex' => 'O número de telefone deve estar no formato: +258 seguido de 9 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        // Atualizar campos básicos do usuário
        $userData = $request->only(['name', 'phone', 'email_notifications', 'whatsapp_notifications']);
        $userData = array_filter($userData, function($value) {
            return !is_null($value);
        });

        if (!empty($userData)) {
            $user->update($userData);
        }

        // Atualizar student_stats
        if ($user->studentStats) {
            $studentStatsData = $request->only([
                'university', 'course', 'institution_type',
                'academic_level', 'student_card_number'
            ]);

            // Remover null values
            $studentStatsData = array_filter($studentStatsData, function($value) {
                return !is_null($value);
            });

            if (!empty($studentStatsData)) {
                $user->studentStats->update($studentStatsData);
            }
        }

        Log::info('✅ Perfil de estudante atualizado com sucesso', ['user_id' => $user->id]);

        // Recarregar dados atualizados
        $user->load('studentStats');

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso',
            'data' => $user
        ]);
    }

    /**
     * Obter documentos do estudante
     */
    public function getDocuments(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para estudantes'
            ], 403);
        }

        $documents = $user->documents()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Upload de documento
     */
    public function uploadDocument(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas estudantes podem enviar documentos'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:student_card,enrollment_proof',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->processStudentDocument($user, $request->file('file'), $request->document_type);

            if ($result) {
                // Atualizar status de documentos em student_stats
                if ($user->studentStats) {
                    $user->studentStats->update(['documents_submitted' => true]);
                }

                Log::info('📄 Documento enviado com sucesso', [
                    'user_id' => $user->id,
                    'document_type' => $request->document_type
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Documento enviado com sucesso',
                    'data' => $user->load('studentStats')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao processar documento'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('❌ Erro ao enviar documento: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'document_type' => $request->document_type
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar documento',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }

    /**
     * Obter estatísticas do estudante
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso permitido apenas para estudantes'
            ], 403);
        }

        $stats = [
            'verification_status' => $user->verification_status,
            'documents_submitted' => $user->studentStats ? $user->studentStats->documents_submitted : false,
            'documents_count' => $user->documents()->count(),
            'registration_date' => $user->created_at,
            'balance' => $user->balance,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Processar documento do estudante (método privado)
     */
    private function processStudentDocument($user, $file, $type)
    {
        try {
            // Verificar se o Model StudentDocument existe
            if (class_exists('App\Models\StudentDocument')) {
                // Se o Model existe, usar a tabela student_documents
                $path = $file->store("student-documents/{$user->id}/{$type}", 'public');

                $user->documents()->create([
                    'document_type' => $type,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'status' => 'pending',
                ]);

                Log::info("📁 Documento {$type} salvo no banco: {$path}");
            } else {
                // Se não existir, armazenar no profile_info
                $path = $file->store("student-documents/{$user->id}/{$type}", 'public');

                // Atualizar profile_info com informação do documento
                $profileInfo = json_decode($user->profile_info, true) ?? [];
                $profileInfo["{$type}_document"] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];

                $user->profile_info = json_encode($profileInfo);
                $user->save();

                Log::info("📁 Documento {$type} salvo no profile_info: {$path}");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("❌ Erro ao processar documento {$type}: " . $e->getMessage());
            return false;
        }
    }
}
