<?php

namespace App\Http\Controllers\API\Kotlin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Estoque;
use App\Models\CategoriaProduto;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProdutoController extends Controller
{
    public function index(Request $request){

        $categoria_id = $request->categoria_id;
        $busca = $request->busca;

        $inicioMes = Carbon::now()->startOfMonth();
        $fimMes = Carbon::now()->endOfMonth();

        if (!$request->empresa_id) {
            return response()->json([
                'error' => 'empresa_id não informado'
            ], 400);
        }

        $data = Produto::query()
        ->with([
            'adicionaisMobile.adicional.categoria'
        ])
        ->where('produtos.empresa_id', $request->empresa_id)
        ->where('produtos.status', 1)
        ->when($categoria_id, function ($q) use ($categoria_id) {
            $q->where('produtos.categoria_id', $categoria_id);
        })
        ->when($busca, function ($q) use ($busca) {
            $q->where(function ($sub) use ($busca) {
                $sub->where('produtos.nome', 'like', "%{$busca}%")
                ->orWhere('produtos.codigo_barras', 'like', "%{$busca}%")
                ->orWhere('produtos.referencia', 'like', "%{$busca}%")
                ->orWhere('produtos.numero_sequencial', 'like', "%{$busca}%");
            });
        })
        ->leftJoin('estoques', 'estoques.produto_id', '=', 'produtos.id')
        ->leftJoin('item_nfces', function ($join) use ($inicioMes, $fimMes) {
            $join->on('item_nfces.produto_id', '=', 'produtos.id')
            ->whereBetween('item_nfces.created_at', [$inicioMes, $fimMes]);
        })

        ->select([
            'produtos.id',
            'produtos.nome',
            'produtos.codigo_barras',
            'produtos.gerenciar_estoque',
            'produtos.imagem',
            'produtos.valor_unitario',
            'produtos.numero_sequencial',
            'produtos.referencia',
            'produtos.unidade',

            DB::raw('COALESCE(SUM(DISTINCT estoques.quantidade), 0) as quantidade'),
            DB::raw('COALESCE(SUM(item_nfces.quantidade), 0) as total_vendido_mes')
        ])

        ->groupBy(
            'produtos.id',
            'produtos.nome',
            'produtos.codigo_barras',
            'produtos.gerenciar_estoque',
            'produtos.imagem',
            'produtos.valor_unitario',
            'produtos.numero_sequencial',
            'produtos.referencia',
            'produtos.unidade'
        )
        ->orderByDesc('total_vendido_mes')
        ->paginate(21);

        $data->getCollection()->each(function ($produto) {
            $produto->setAppends(['imgApp']);
        });

        return response()->json($data, 200);
    }

    public function find(Request $request, $id)
    {
        $empresaId = $request->empresa_id ?? auth()->user()->empresa_id ?? null;
        $localId   = $request->local_id; 

        $produto = Produto::query()
        ->leftJoin('categoria_produtos', 'categoria_produtos.id', '=', 'produtos.categoria_id')
        ->where('produtos.id', $id)
        ->when($empresaId, function ($q) use ($empresaId) {
            $q->where('produtos.empresa_id', $empresaId);
        })
        ->select([
            'produtos.id',
            'produtos.numero_sequencial',
            'produtos.nome',
            'produtos.valor_unitario as valor',
            'produtos.unidade',
            'produtos.codigo_barras',
            'produtos.ncm',
            'produtos.referencia',
            \DB::raw("
                CONCAT(
                COALESCE(produtos.cfop_estadual, ''),
                '/',
                COALESCE(produtos.cfop_outro_estado, '')
                ) as cfop
                "),

            'produtos.cst_csosn as cst_icms',
            'produtos.cst_pis',
            'produtos.cst_cofins',
            'produtos.cst_ipi',

            'produtos.perc_icms',
            'produtos.perc_pis',
            'produtos.perc_cofins',
            'produtos.perc_ipi',

            'produtos.categoria_id',
            'categoria_produtos.nome as categoria_nome',

            'produtos.imagem',
            'produtos.gerenciar_estoque',
        ])
        ->first();

        if (!$produto) {
            return response()->json(['message' => 'Produto não encontrado'], 404);
        }

        if ($produto) {
            $produto->setAppends(['imgApp']);
        }

        $estoqueQuery = Estoque::query()
        ->where('produto_id', $produto->id)
        ->when(!empty($localId), function ($q) use ($localId) {
            $q->where('local_id', $localId);
        });

        $produto->estoque = (float) ($estoqueQuery->sum('quantidade') ?? 0);
        return response()->json($produto);
    }

    public function categorias(Request $request){
        $data = CategoriaProduto::where('empresa_id', $request->empresa_id)
        ->select('id', 'nome')
        ->where('status', 1)
        ->orderBy('nome')
        ->get();

        return response()->json($data, 200);
    }
}
