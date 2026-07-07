<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\PlanoEmpresa;
use App\Models\Nfe;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ValidaNFe
{
    public function handle($request, Closure $next)
    {
        if (!$request->filled('id')) {
            return response()->json('ID da NFe não informado.', 422);
        }

        $nfe = Nfe::find($request->id);

        if (!$nfe) {
            return response()->json('NFe não encontrada.', 404);
        }

        $plano = PlanoEmpresa::where('empresa_id', $nfe->empresa_id)
            ->orderBy('data_expiracao', 'desc')
            ->first();

        if (!$plano || !$plano->plano) {
            return response()->json('Plano da empresa não encontrado.', 422);
        }

        $totalNfe = Nfe::where('empresa_id', $nfe->empresa_id)
            ->where(function($q){
                $q->where('estado', 'aprovado')
                  ->orWhere('estado', 'cancelado');
            })
            ->whereMonth('data_emissao', date('m'))
            ->count('id');

        if ($totalNfe >= $plano->plano->maximo_nfes) {
            return response()->json('Limite de emissões de NFe atingido!', 401);
        }

        return $next($request);
    }
}