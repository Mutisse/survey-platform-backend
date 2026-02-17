<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUniqueDevice
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // 1. Pega o device_id do header (enviado pelo frontend)
        $currentDeviceId = $request->header('X-Device-ID');

        if (!$currentDeviceId) {
            // Se não veio, tenta pegar do token anterior
            $currentDeviceId = $user->currentAccessToken()->abilities['device_id'] ?? null;

            if (!$currentDeviceId) {
                return response()->json([
                    'error' => 'Dispositivo não identificado'
                ], 403);
            }
        }

        // 2. Buscar TODOS os outros tokens ativos deste user
        $otherTokens = $user->tokens()
            ->where('id', '!=', $user->currentAccessToken()->id)
            ->where('created_at', '>', now()->subHours(2))
            ->get();

        // 3. Verificar se algum deles tem device_id diferente
        foreach ($otherTokens as $token) {
            $tokenDeviceId = $token->abilities['device_id'] ?? null;

            if ($tokenDeviceId && $tokenDeviceId !== $currentDeviceId) {
                // Opção A: Apagar o token antigo
                $token->delete();

                // Opção B: Bloquear o novo
                return response()->json([
                    'error' => 'Já existe sessão ativa noutro dispositivo',
                    'code' => 'DEVICE_LIMIT_EXCEEDED'
                ], 403);
            }
        }

        // 4. Atualizar o device_id no token atual (se veio do header)
        if ($request->header('X-Device-ID') &&
            $user->currentAccessToken()->abilities['device_id'] !== $currentDeviceId) {

            $user->currentAccessToken()->abilities = array_merge(
                $user->currentAccessToken()->abilities ?? [],
                ['device_id' => $currentDeviceId]
            );
            $user->currentAccessToken()->save();
        }

        return $next($request);
    }
}
