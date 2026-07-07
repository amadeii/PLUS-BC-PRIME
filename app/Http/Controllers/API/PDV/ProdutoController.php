<?php

namespace App\Http\Controllers\API\PDV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\ListaPreco;
use App\Models\CategoriaProduto;
use App\Models\Localizacao;
use App\Models\PadraoTributacaoProduto;
use App\Models\ProdutoLocalizacao;

class ProdutoController extends Controller
{
    public function produtos(Request $request){
        $updated_at = $request->updated_at;
        $user_id = $request->user_id;

        $locais = Localizacao::where('usuario_localizacaos.usuario_id', $user_id)
        ->select('localizacaos.*')
        ->join('usuario_localizacaos', 'usuario_localizacaos.localizacao_id', '=', 'localizacaos.id')
        ->where('localizacaos.status', 1)->get();
        $locais = $locais->pluck(['id']);

        if(sizeof($locais) == 0){
            $locais = Localizacao::where('empresa_id', $request->empresa_id)
            ->where('localizacaos.status', 1)->get()->pluck(['id']);
        }
        
        $data = Produto::where('empresa_id', $request->empresa_id)
        ->select('produtos.id as id', 'produtos.nome as nome', 'valor_unitario', 'categoria_id', 'codigo_barras', 'imagem', 'gerenciar_estoque', 
            'referencia_balanca', 'numero_sequencial', 'valor_compra', 'referencia', 'padrao_id', 'unidade')
        ->with(['categoria', 'estoque'])
        ->where('status', 1)
        ->join('produto_localizacaos', 'produto_localizacaos.produto_id', '=', 'produtos.id')
        ->whereIn('produto_localizacaos.localizacao_id', $locais)
        ->groupBy('produtos.id')
        ->get();

        return response()->json($data, 200);
    }

    public function categorias(Request $request){
        $data = CategoriaProduto::where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->get();
        foreach($data as $item){
            $item->total_produtos = sizeof($item->produtos);
        }
        return response()->json($data, 200);
    }

    public function padraoTributacao(Request $request){
        $data = PadraoTributacaoProduto::where('empresa_id', $request->empresa_id)
        ->get();
        
        return response()->json($data, 200);
    }

    public function listaPreco(Request $request){
        $data = ListaPreco::where('empresa_id', $request->empresa_id)
        ->with('itens')
        ->where('status', 1)
        ->get();
        return response()->json($data, 200);
    }

    public function store(Request $request){

        $padrao = PadraoTributacaoProduto::findOrFail($request->padrao_id);

        $produto = Produto::create([
            'nome' => $request->nome,
            'valor_unitario' => __convert_value_bd($request->valor_unitario),
            'valor_compra' => __convert_value_bd($request->valor_compra),
            'padrao_id' => $request->padrao_id,
            'codigo_barras' => $request->codigo_barras,
            'referencia' => $request->referencia ?? '',
            'categoria_id' => $request->categoria_id,
            'gerenciar_estoque' => $request->gerenciar_estoque ?? true,
            'empresa_id' => $padrao->empresa_id,
            'unidade' => 'UN',
            'perc_icms' => $padrao->perc_icms,
            'perc_red_bc' => $padrao->perc_red_bc,
            'modBCST' => $padrao->modBCST,
            'pMVAST' => $padrao->pMVAST,
            'pICMSST' => $padrao->pICMSST,
            'redBCST' => $padrao->redBCST,
            'pST' => $padrao->pST,

            'perc_pis' => $padrao->perc_pis,
            'perc_cofins' => $padrao->perc_cofins,
            'perc_ipi' => $padrao->perc_ipi,
            'cst_pis' => $padrao->cst_pis,
            'cst_cofins' => $padrao->cst_cofins,
            'cst_ipi' => $padrao->cst_ipi,
            'cEnq' => $padrao->cEnq,

            'cst_csosn' => $padrao->cst_csosn,
            'cfop_estadual' => $padrao->cfop_estadual,
            'cfop_outro_estado' => $padrao->cfop_outro_estado,
            'cfop_entrada_estadual' => $padrao->cfop_entrada_estadual,
            'cfop_entrada_outro_estado' => $padrao->cfop_entrada_outro_estado,

            'ncm' => $padrao->ncm,
            'cest' => $padrao->cest,
            'codigo_beneficio_fiscal' => $padrao->codigo_beneficio_fiscal,
            'cst_ibscbs' => $padrao->cst_ibscbs,
            'cclass_trib' => $padrao->cclass_trib,
            'perc_ibs_uf' => $padrao->perc_ibs_uf,
            'perc_ibs_mun' => $padrao->perc_ibs_mun,
            'perc_cbs' => $padrao->perc_cbs,
            'perc_dif' => $padrao->perc_dif,
        ]);

        $localPadrao = Localizacao::where('empresa_id', $padrao->empresa_id)->first();
        ProdutoLocalizacao::updateOrCreate([
            'produto_id' => $produto->id, 
            'localizacao_id' => $localPadrao->id
        ]);

        return response()->json($produto, 201);
    }

    public function update(Request $request){

        $produto = Produto::findOrFail($request->id);

        $padrao = PadraoTributacaoProduto::findOrFail($request->padrao_id);

        $data = [
            'nome' => $request->nome,
            'valor_unitario' => __convert_value_bd($request->valor_unitario),
            'valor_compra' => __convert_value_bd($request->valor_compra),
            'padrao_id' => $request->padrao_id,
            'codigo_barras' => $request->codigo_barras,
            'categoria_id' => $request->categoria_id,
            'gerenciar_estoque' => $request->gerenciar_estoque ?? true,
            'referencia' => $request->referencia ?? '',

            'perc_icms' => $padrao->perc_icms,
            'perc_red_bc' => $padrao->perc_red_bc,
            'modBCST' => $padrao->modBCST,
            'pMVAST' => $padrao->pMVAST,
            'pICMSST' => $padrao->pICMSST,
            'redBCST' => $padrao->redBCST,
            'pST' => $padrao->pST,

            'perc_pis' => $padrao->perc_pis,
            'perc_cofins' => $padrao->perc_cofins,
            'perc_ipi' => $padrao->perc_ipi,
            'cst_pis' => $padrao->cst_pis,
            'cst_cofins' => $padrao->cst_cofins,
            'cst_ipi' => $padrao->cst_ipi,
            'cEnq' => $padrao->cEnq,

            'cst_csosn' => $padrao->cst_csosn,
            'cfop_estadual' => $padrao->cfop_estadual,
            'cfop_outro_estado' => $padrao->cfop_outro_estado,
            'cfop_entrada_estadual' => $padrao->cfop_entrada_estadual,
            'cfop_entrada_outro_estado' => $padrao->cfop_entrada_outro_estado,

            'ncm' => $padrao->ncm,
            'cest' => $padrao->cest,
            'codigo_beneficio_fiscal' => $padrao->codigo_beneficio_fiscal,
            'cst_ibscbs' => $padrao->cst_ibscbs,
            'cclass_trib' => $padrao->cclass_trib,
            'perc_ibs_uf' => $padrao->perc_ibs_uf,
            'perc_ibs_mun' => $padrao->perc_ibs_mun,
            'perc_cbs' => $padrao->perc_cbs,
            'perc_dif' => $padrao->perc_dif,
        ];

        $produto->fill($data)->save();

        return response()->json($produto, 201);
    }
}
