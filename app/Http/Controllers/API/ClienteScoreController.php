<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RotinaExecucao;
use App\Models\Cliente;
use App\Utils\Score\ScoreUtil;
use Illuminate\Support\Facades\DB;

class ClienteScoreController extends Controller
{
    public function scoreDiario(Request $request)
    {
        $empresaId = $request->empresa_id;
        $hoje = now()->toDateString();

        // DB::table('rotina_execucaos')->truncate();
        // DB::table('cliente_scores')->truncate();

        $jaExecutou = RotinaExecucao::where('rotina', 'score_diario')
        ->where('empresa_id', $empresaId)
        ->where('data_execucao', $hoje)
        ->exists();

        if ($jaExecutou) {
            return response()->noContent();
        }

        DB::transaction(function () use ($empresaId, $hoje) {

            RotinaExecucao::create([
                'rotina'        => 'score_diario',
                'empresa_id'    => $empresaId,
                'data_execucao' => $hoje,
                'executado_em'  => now(),
            ]);

            $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('status', 1)
            ->get();

            foreach ($clientes as $cliente) {
                (new ScoreUtil($cliente))->calcular();
            }
        });

        return response()->json("score executado");
    }
}