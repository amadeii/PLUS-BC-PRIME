<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthKotlin
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            $token = $request->header('X-PDV-TOKEN');
        }

        if (!$token) {
            return response()->json([
                'message' => 'Token não informado'
            ], 401);
        }

        $user = User::where('token_app', $token)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Token inválido'
            ], 401);
        }

        $request->merge([
            'empresa_id' => $user->empresa->empresa_id
        ]);

        return $next($request);
    }
}
