<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;

class OrdemProducaoController extends Controller
{
    public function linha(Request $request){

        return view('ordem_producao.partials.linha');
    }

    public function roteiroProduto($produto_id)
    {
        $produto = Produto::with([
            'roteiro.operacoes.operacao',
            'roteiro.operacoes.setor'
        ])
        ->find($produto_id);

        if(!$produto || !$produto->roteiro){
            return response()->json([
                'success' => false,
                'message' => 'Produto sem roteiro cadastrado',
                'operacoes' => []
            ]);
        }

        $operacoes = $produto->roteiro->operacoes
        ->sortBy('sequencia')
        ->map(function($op){
            return [
                'sequencia' => $op->sequencia,
                'operacao_id' => $op->operacao_id,
                'setor_id' => $op->setor_id,
                'nome_operacao' => $op->operacao->nome ?? '',
                'nome_setor' => $op->setor->nome ?? '',
                'tempo_previsto_minutos' => $op->tempo_previsto_minutos ?? 0,
            ];
        })
        ->values();

        return response()->json([
            'success' => true,
            'roteiro_id' => $produto->roteiro->id,
            'roteiro_nome' => $produto->roteiro->nome,
            'operacoes' => $operacoes
        ]);
    }

    public function materiaisProduto(Request $request, $produto_id)
    {
        $quantidade = (float) str_replace(',', '.', $request->quantidade ?? 1);

        $produto = Produto::with(['composicao.ingrediente'])
        ->find($produto_id);

        if(!$produto || !$produto->composicao || $produto->composicao->count() == 0){
            return response()->json([
                'success' => false,
                'message' => 'Produto sem composição cadastrada',
                'materiais' => []
            ]);
        }

        $materiais = $produto->composicao->map(function($item) use ($quantidade){

            $material = $item->ingrediente;

            $qtdNecessaria = (float) $item->quantidade * $quantidade;
            $estoqueAtual = $material ? (float) $material->estoqueAtual() : 0;

            $custoUnitario = $material ? (float) ($material->valor_compra ?? 0) : 0;
            $custoTotalPrevisto = $qtdNecessaria * $custoUnitario;

            return [
                'material_id' => $material->id ?? null,
                'nome_material' => $material->nome ?? '',
                'quantidade_base' => (float) $item->quantidade,
                'quantidade_prevista' => number_format($qtdNecessaria, 2, ',', '.'),
                'estoque_atual' => number_format($estoqueAtual, 2, ',', '.'),
                'custo_unitario' => $custoUnitario,
                'custo_total_previsto' => $custoTotalPrevisto,
                'disponivel' => $estoqueAtual >= $qtdNecessaria,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'materiais' => $materiais
        ]);
    }
}
