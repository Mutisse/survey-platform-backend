<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado'], 401);
        }

        if (empty($roles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Acesso negado. Função necessária: ' . implode(' ou ', $roles),
            'your_role' => $user->role
        ], 403);
    }
}
