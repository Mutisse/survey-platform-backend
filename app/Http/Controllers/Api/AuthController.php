<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        Log::info('🔐 Tentativa de login', ['email' => $request->email]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            Log::warning('❌ Credenciais inválidas para email: ' . $request->email);

            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        Log::info('✅ Login bem-sucedido para usuário ID: ' . $user->id);

        // Verificar se o usuário está ativo/verificado
        if ($user->verification_status === 'rejected') {
            Log::warning('🚫 Usuário rejeitado tentou login', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Sua conta foi rejeitada. Entre em contato com o suporte.'
            ], 403);
        }

        if ($user->verification_status === 'pending' && $user->isStudent()) {
            Log::info('⏳ Usuário pendente de verificação', ['user_id' => $user->id]);

            return response()->json([
                'success' => false,
                'message' => 'Sua conta está pendente de verificação. Aguarde a aprovação.'
            ], 403);
        }

        // Carregar relacionamentos apropriados
        if ($user->isStudent()) {
            $user->load('studentStats');
        } elseif ($user->isParticipant() && class_exists('App\Models\ParticipantStats')) {
            $user->load('participantStats');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        Log::info('👋 Logout realizado para usuário ID: ' . $user->id);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Obter usuário atual
     */
    public function user(Request $request)
    {
        $user = $request->user();

        // Carregar student_stats se for estudante
        if ($user->isStudent()) {
            $user->load('studentStats');
        }

        // Carregar participant_stats se for participante
        if ($user->isParticipant() && class_exists('App\Models\ParticipantStats')) {
            $user->load('participantStats');
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Atualizar usuário (informações básicas)
     */
    public function updateUser(Request $request)
    {
        $user = $request->user();

        Log::info('🔄 Atualização de informações básicas', ['user_id' => $user->id]);

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

        Log::info('✅ Informações básicas atualizadas com sucesso', ['user_id' => $user->id]);

        // Recarregar dados atualizados
        if ($user->isStudent()) {
            $user->load('studentStats');
        } elseif ($user->isParticipant() && class_exists('App\Models\ParticipantStats')) {
            $user->load('participantStats');
        }

        return response()->json([
            'success' => true,
            'message' => 'Informações atualizadas com sucesso',
            'data' => $user
        ]);
    }

    /**
     * Atualizar senha
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        Log::info('🔑 Atualização de senha', ['user_id' => $user->id]);

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar senha atual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha atual incorreta'
            ], 400);
        }

        // Atualizar senha
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        Log::info('✅ Senha atualizada com sucesso', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Senha atualizada com sucesso'
        ]);
    }

    /**
     * Verificar disponibilidade de email
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email inválido'
            ], 422);
        }

        $exists = User::where('email', $request->email)->exists();

        Log::info('📧 Verificação de email', [
            'email' => $request->email,
            'disponivel' => !$exists
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
                'email' => $request->email
            ]
        ]);
    }

    /**
     * Verificar disponibilidade de BI (apenas estudantes)
     */
    public function checkBiNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bi_number' => [
                'required',
                'string',
                'size:13',
                'regex:/^\d{12}[A-Z]$/',
            ]
        ], [
            'bi_number.regex' => 'Formato inválido. Use 12 dígitos + 1 letra maiúscula.',
            'bi_number.size' => 'Deve ter exatamente 13 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Número de BI inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = \App\Models\StudentStats::where('bi_number', $request->bi_number)->exists();

        Log::info('🆔 Verificação de BI/DIRE', [
            'bi_number' => $request->bi_number,
            'disponivel' => !$exists
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
                'bi_number' => $request->bi_number
            ]
        ]);
    }

    /**
     * Esqueci minha senha
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email não encontrado no sistema'
            ], 422);
        }

        // TODO: Implementar lógica de recuperação de senha
        Log::info('🔐 Solicitação de recuperação de senha', ['email' => $request->email]);

        return response()->json([
            'success' => true,
            'message' => 'Instruções de recuperação de senha enviadas para seu email'
        ]);
    }

    /**
     * Redefinir senha
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Implementar lógica de redefinição de senha
        Log::info('🔑 Redefinição de senha', ['email' => $request->email]);

        return response()->json([
            'success' => true,
            'message' => 'Senha redefinida com sucesso'
        ]);
    }
}
