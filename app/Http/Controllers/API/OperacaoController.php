<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Operacao;

class OperacaoController extends Controller
{
    public function find($id)
    {
        $item = Operacao::with(['setor.centroCusto'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Operação não encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'nome' => $item->nome,
                'descricao' => $item->descricao,
                'tempo_padrao' => (float)$item->tempo_padrao,
                'setor_id' => $item->setor_id,
                'setor_nome' => $item->setor ? $item->setor->nome : null,
                'centro_custo_id' => $item->setor && $item->setor->centroCusto
                ? $item->setor->centroCusto->id
                : null,
                'centro_custo_nome' => $item->setor && $item->setor->centroCusto
                ? $item->setor->centroCusto->nome
                : null,
                'custo_operacao' => (float)$item->custo_operacao
            ]
        ]);
    }
}
