<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function auth(Request $request){

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = Auth::user();

        if($user->token_app == null){
            $user->token_app = Str::random(50);
            $user->save();
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nome' => $user->name,
                'token' => $user->token_app,
                'empresa_nome' => $user->empresa->empresa->nome,
                'empresa_id' => $user->empresa->empresa_id,
            ]
        ], 200);
    }
}
