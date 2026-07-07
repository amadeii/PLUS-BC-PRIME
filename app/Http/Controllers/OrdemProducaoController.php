<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdemProducao;
use App\Models\ItemProducao;
use App\Models\Cidade;
use App\Models\Transportadora;
use App\Models\NaturezaOperacao;
use App\Models\Nfe;
use App\Models\Funcionario;
use App\Models\ItemOrdemProducao;
use App\Models\Empresa;
use App\Models\EtiquetaConfiguracao;
use App\Models\ImagemOrdemProducao;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Utils\UploadUtil;

use App\Models\Produto;
use App\Models\OrdemProducaoOperacao;
use App\Models\OrdemProducaoMaterial;
use App\Utils\OrdemProducaoUtil;

class OrdemProducaoController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:ordem_producao_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ordem_producao_edit', ['only' => ['edit', 'update', 'liberar', 'iniciar', 'finalizar', 'encerrar', 'updateEstado']]);
        $this->middleware('permission:ordem_producao_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:ordem_producao_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = OrdemProducao::where('empresa_id', $request->empresa_id)
        ->with([
            'funcionario',
            'usuario',
            'itens.produto',
            'itens.cliente',
            'operacoes'
        ]);

        $query->when($request->codigo, function($q) use ($request){
            return $q->where('codigo_sequencial', $request->codigo);
        });

        $query->when($request->estado, function($q) use ($request){
            return $q->where('estado', $request->estado);
        });

        $query->when($request->prioridade, function($q) use ($request){
            return $q->where('prioridade', $request->prioridade);
        });

        $query->when($request->tipo_producao, function($q) use ($request){
            return $q->where('tipo_producao', $request->tipo_producao);
        });

        $query->when($request->funcionario_id, function($q) use ($request){
            return $q->where('funcionario_id', $request->funcionario_id);
        });

        $query->when($request->data_prevista_entrega, function($q) use ($request){
            return $q->whereDate('data_prevista_entrega', $request->data_prevista_entrega);
        });

        $query->when($request->start_date, function($q) use ($request){
            return $q->whereDate('created_at', '>=', $request->start_date);
        });

        $query->when($request->end_date, function($q) use ($request){
            return $q->whereDate('created_at', '<=', $request->end_date);
        });

        $query->when($request->produto, function($q) use ($request){
            return $q->whereHas('itens.produto', function($p) use ($request){
                $p->where('nome', 'like', "%{$request->produto}%");
            });
        });

        $query->when($request->cliente, function($q) use ($request){
            return $q->whereHas('itens.cliente', function($c) use ($request){
                $c->where('razao_social', 'like', "%{$request->cliente}%")
                ->orWhere('nome_fantasia', 'like', "%{$request->cliente}%");
            });
        });

        if($request->situacao == 'atrasada'){
            $query->whereDate('data_prevista_entrega', '<', now()->toDateString())
            ->whereNotIn('estado', ['entregue', 'encerrada']);
        }

        if($request->situacao == 'parcial'){
            $query->where('percentual_progresso', '>', 0)
            ->where('percentual_progresso', '<', 100);
        }

        if($request->situacao == 'finalizada'){
            $query->where('percentual_progresso', '>=', 100);
        }

        if($request->situacao == 'com_refugo'){
            $query->where('quantidade_refugada', '>', 0);
        }

        $data = $query->orderBy('id', 'desc')->paginate(__itensPagina());

        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        $base = OrdemProducao::where('empresa_id', $request->empresa_id);

        $cards = [
            'em_producao' => (clone $base)->where('estado', 'producao')->count(),
            'parciais' => (clone $base)->where('percentual_progresso', '>', 0)->where('percentual_progresso', '<', 100)->count(),
            'atrasadas' => (clone $base)->whereDate('data_prevista_entrega', '<', now()->toDateString())->whereNotIn('estado', ['entregue', 'encerrada'])->count(),
            'finalizadas_hoje' => (clone $base)->whereDate('data_finalizacao', now()->toDateString())->count(),
            'refugo_medio' => 0,
        ];

        $totalProduzido = (clone $base)->sum('quantidade_produzida');
        $totalRefugo = (clone $base)->sum('quantidade_refugada');

        if(($totalProduzido + $totalRefugo) > 0){
            $cards['refugo_medio'] = ($totalRefugo / ($totalProduzido + $totalRefugo)) * 100;
        }

        return view('ordem_producao.index', compact('data', 'funcionarios', 'cards'));
    }

    public function create(Request $request)
    {
        $data = ItemProducao::where('produtos.empresa_id', $request->empresa_id)
        ->select('item_producaos.*')
        ->join('produtos', 'produtos.id', '=', 'item_producaos.produto_id')
        ->where('item_producaos.status', 0)
        ->get();

        $orcamento = null;

        if(isset($request->orcamento_id)){
            $orcamento = Nfe::findOrFail($request->orcamento_id);
            $data = [];
        }

        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->orderBy('nome')->get();

        return view('ordem_producao.create', compact('data', 'orcamento', 'funcionarios'));
    }

    public function edit(Request $request, $id)
    {
        $item = OrdemProducao::where('empresa_id', $request->empresa_id)
        ->with([
            'itens.produto',
            'itens.cliente',
            'operacoes.operacao',
            'operacoes.setor',
            'materiais.material'
        ])
        ->findOrFail($id);

        OrdemProducaoUtil::recalcularCustos($item);
        $validacao = OrdemProducaoUtil::validarOrdem($item->id);

        $item = OrdemProducao::where('empresa_id', $request->empresa_id)
        ->with([
            'itens.produto',
            'itens.cliente',
            'operacoes.operacao',
            'operacoes.setor',
            'materiais.material'
        ])
        ->findOrFail($id);

        $data = ItemProducao::where('produtos.empresa_id', $request->empresa_id)
        ->select('item_producaos.*')
        ->join('produtos', 'produtos.id', '=', 'item_producaos.produto_id')
        ->where('item_producaos.status', 0)
        ->get();

        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->orderBy('nome')->get();

        return view('ordem_producao.edit', compact('data', 'item', 'funcionarios', 'validacao'));
    }

    public function show(Request $request, $id)
    {
        $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

        if($item->hash_link == null){
            $item->hash_link = Str::random(30);
            $item->save();
        }

        $config = EtiquetaConfiguracao::where('empresa_id', $request->empresa_id)->first();

        return view('ordem_producao.show', compact('item', 'config'));
    }

    public function store(Request $request)
    {
        try{
            DB::transaction(function () use ($request) {

                if(!$request->item_select && !$request->produto_id){
                    throw new \Exception("Selecione ao menos 1 produto!");
                }

                $lastItem = OrdemProducao::where('empresa_id', $request->empresa_id)
                ->orderBy('codigo_sequencial', 'desc')
                ->first();

                $codigo_sequencial = $lastItem ? $lastItem->codigo_sequencial + 1 : 1;

                $request->merge([
                    'observacao' => $request->observacao ?? '',
                    'codigo_sequencial' => $codigo_sequencial,
                    'usuario_id' => get_id_user(),
                    'hash_link' => Str::random(30),
                    'estado' => $request->estado ?? 'novo',
                    'prioridade' => $request->prioridade ?? 'media',
                    'tipo_producao' => $request->tipo_producao ?? 'producao',
                    'estrutura_ok' => $request->has('estrutura_ok') ? 1 : 0,
                    'roteiro_ok' => $request->has('roteiro_ok') ? 1 : 0,
                    'estoque_ok' => $request->has('estoque_ok') ? 1 : 0,
                    'custos_ok' => $request->has('custos_ok') ? 1 : 0,
                ]);

                $ordem = OrdemProducao::create($request->all());

                $nfe = null;

                if(isset($request->orcamento_id) && $request->orcamento_id){
                    $nfe = Nfe::find($request->orcamento_id);

                    if($nfe){
                        $ordem->orcamento_id = $nfe->id;
                        $ordem->save();
                    }
                }

                if($request->item_select){
                    for($i = 0; $i < sizeof($request->item_select); $i++){

                        $itemProducao = ItemProducao::find($request->item_select[$i]);

                        if($itemProducao){
                            $itemProducao->status = 1;
                            $itemProducao->save();

                            $itemOrdem = ItemOrdemProducao::create([
                                'ordem_producao_id' => $ordem->id,
                                'item_producao_id' => $itemProducao->id,
                                'produto_id' => $itemProducao->produto_id,
                                'quantidade' => $request->qtd[$i] ?? 1,
                                'status' => 0,
                                'observacao' => $request->observacao_item[$i] ?? null
                            ]);

                            OrdemProducaoUtil::gerarOperacoesRoteiro($ordem, $itemOrdem);
                            OrdemProducaoUtil::gerarMateriaisProduto($ordem, $itemOrdem);
                        }
                    }
                }

                if($request->produto_id){
                    for($i = 0; $i < sizeof($request->produto_id); $i++){

                        $itemOrdem = ItemOrdemProducao::create([
                            'ordem_producao_id' => $ordem->id,
                            'item_producao_id' => null,
                            'produto_id' => $request->produto_id[$i],
                            'cliente_id' => isset($request->cliente_id[$i]) ? $request->cliente_id[$i] : ($nfe ? $nfe->cliente_id : null),
                            'quantidade' => $request->qtd[$i] ?? 1,
                            'status' => 0,
                            'observacao' => $request->observacao_item[$i] ?? null,
                            'numero_pedido' => $request->numero_pedido[$i] ?? null,
                        ]);

                        OrdemProducaoUtil::gerarOperacoesRoteiro($ordem, $itemOrdem);
                        OrdemProducaoUtil::gerarMateriaisProduto($ordem, $itemOrdem);
                    }
                }

                OrdemProducaoUtil::atualizarQuantidadePendente($ordem);
                OrdemProducaoUtil::recalcularCustos($ordem);
                OrdemProducaoUtil::validarOrdem($ordem->id);

            });

session()->flash("flash_success", "Ordem de Produção criada com sucesso");
return redirect()->route('ordem-producao.index');

}catch(\Exception $e){
    session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    return redirect()->back()->withInput();
}
}


public function update(Request $request, $id)
{
    try{
        DB::transaction(function () use ($request, $id) {

            $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

            $request->merge([
                'observacao' => $request->observacao ?? '',
                'estrutura_ok' => $request->has('estrutura_ok') ? 1 : 0,
                'roteiro_ok' => $request->has('roteiro_ok') ? 1 : 0,
                'estoque_ok' => $request->has('estoque_ok') ? 1 : 0,
                'custos_ok' => $request->has('custos_ok') ? 1 : 0,
            ]);

            $item->fill($request->all())->save();

            foreach($item->itens as $i){
                $itemProducao = $i->itemProducao ?? null;
                if($itemProducao){
                    $itemProducao->status = 0;
                    $itemProducao->save();
                }
            }

            OrdemProducaoOperacao::where('ordem_producao_id', $item->id)->delete();
            OrdemProducaoMaterial::where('ordem_producao_id', $item->id)->delete();

            $item->itens()->delete();

            if($request->item_select){
                for($i = 0; $i < sizeof($request->item_select); $i++){

                    $itemProducao = ItemProducao::find($request->item_select[$i]);

                    if($itemProducao){
                        $itemProducao->status = 1;
                        $itemProducao->save();

                        $itemOrdem = ItemOrdemProducao::create([
                            'ordem_producao_id' => $item->id,
                            'item_producao_id' => $itemProducao->id,
                            'produto_id' => $itemProducao->produto_id,
                            'quantidade' => $request->qtd[$i] ?? 1,
                            'status' => 0,
                            'observacao' => $request->observacao_item[$i] ?? null
                        ]);

                        OrdemProducaoUtil::gerarOperacoesRoteiro($item, $itemOrdem);
                        OrdemProducaoUtil::gerarMateriaisProduto($item, $itemOrdem);
                    }
                }
            }

            if($request->produto_id){
                for($i = 0; $i < sizeof($request->produto_id); $i++){

                    $itemOrdem = ItemOrdemProducao::create([
                        'ordem_producao_id' => $item->id,
                        'item_producao_id' => null,
                        'produto_id' => $request->produto_id[$i],
                        'cliente_id' => $request->cliente_id[$i] ?? null,
                        'quantidade' => $request->qtd[$i] ?? 1,
                        'status' => 0,
                        'observacao' => $request->observacao_item[$i] ?? null,
                        'numero_pedido' => $request->numero_pedido[$i] ?? null,
                    ]);

                    OrdemProducaoUtil::gerarOperacoesRoteiro($item, $itemOrdem);
                    OrdemProducaoUtil::gerarMateriaisProduto($item, $itemOrdem);
                }
            }

            OrdemProducaoUtil::atualizarQuantidadePendente($item);
            OrdemProducaoUtil::recalcularCustos($item);
        });

        session()->flash("flash_success", "Ordem de Produção atualizada com sucesso");
        return redirect()->route('ordem-producao.index');

    }catch(\Exception $e){
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        return redirect()->back()->withInput();
    }
}

public function updateEstado(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);
    $item->estado = $request->estado;
    $item->save();

    session()->flash("flash_success", "Estado alterado!");
    return redirect()->back();
}

public function liberar(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

    if($item->estado != 'novo'){
        session()->flash("flash_error", "Somente OP com status Novo pode ser liberada.");
        return redirect()->back();
    }

    OrdemProducaoUtil::recalcularCustos($item);
    $validacao = OrdemProducaoUtil::validarOrdem($item->id);

    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

    $item->estrutura_ok = $validacao['estrutura_ok'] ?? 0;
    $item->roteiro_ok = $validacao['roteiro_ok'] ?? 0;
    $item->estoque_ok = $validacao['estoque_ok'] ?? 0;
    $item->custos_ok = $validacao['custos_ok'] ?? 0;
    $item->save();

    if(!$item->estrutura_ok || !$item->roteiro_ok || !$item->estoque_ok || !$item->custos_ok){
        session()->flash("flash_error", "Antes de liberar, confirme estrutura, roteiro, estoque e custos.");
        return redirect()->back();
    }

    $item->data_liberacao = now();
    $item->usuario_liberou_id = get_id_user();
    $item->estado = 'liberada';
    $item->save();

    session()->flash("flash_success", "Ordem liberada para produção!");
    return redirect()->back();
}

public function iniciar(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

    if(!$item->data_liberacao){
        session()->flash("flash_error", "Libere a ordem antes de iniciar a produção.");
        return redirect()->back();
    }

    $item->data_inicio = $item->data_inicio ?? now();
    $item->estado = 'producao';
    $item->save();

    session()->flash("flash_success", "Produção iniciada!");
    return redirect()->back();
}

public function finalizar(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)
    ->with(['operacoes', 'apontamentos'])
    ->findOrFail($id);

    if($item->estado == 'encerrada'){
        session()->flash("flash_error", "Esta OP já está encerrada.");
        return redirect()->back();
    }

    if($item->quantidade_pendente > 0){
        session()->flash("flash_error", "Não é possível finalizar. Ainda existe quantidade pendente.");
        return redirect()->back();
    }

    $operacoesPendentes = $item->operacoes->where('status', '!=', 'finalizada')->count();

    if($operacoesPendentes > 0){
        session()->flash("flash_error", "Existem operações pendentes ou parciais.");
        return redirect()->back();
    }

    $apontamentosAbertos = $item->apontamentos->where('status', 'aberto')->count();

    if($apontamentosAbertos > 0){
        session()->flash("flash_error", "Existem apontamentos em aberto.");
        return redirect()->back();
    }

    $item->data_finalizacao = $item->data_finalizacao ?? now();
    $item->percentual_progresso = 100;
    $item->estado = 'finalizada';
    $item->save();

    session()->flash("flash_success", "Produção finalizada!");
    return redirect()->back();
}

public function encerrar(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)
    ->with(['operacoes', 'apontamentos'])
    ->findOrFail($id);

    if($item->estado == 'encerrada'){
        session()->flash("flash_error", "Esta OP já está encerrada.");
        return redirect()->back();
    }

    if($item->estado != 'finalizada'){
        session()->flash("flash_error", "Finalize a produção antes de encerrar.");
        return redirect()->back();
    }

    if($item->quantidade_pendente > 0){
        session()->flash("flash_error", "Não é possível encerrar. Ainda existe quantidade pendente.");
        return redirect()->back();
    }

    $operacoesPendentes = $item->operacoes->where('status', '!=', 'finalizada')->count();

    if($operacoesPendentes > 0){
        session()->flash("flash_error", "Não é possível encerrar. Existem operações não finalizadas.");
        return redirect()->back();
    }

    $apontamentosAbertos = $item->apontamentos->where('status', 'aberto')->count();

    if($apontamentosAbertos > 0){
        session()->flash("flash_error", "Não é possível encerrar. Existem apontamentos em aberto.");
        return redirect()->back();
    }

    $item->data_encerramento = now();
    $item->usuario_encerrou_id = get_id_user();
    $item->estado = 'encerrada';
    $item->save();

    session()->flash("flash_success", "Ordem encerrada com sucesso!");
    return redirect()->back();
}

public function alterarStatusItem($id)
{
    $item = ItemOrdemProducao::findOrFail($id);
    $item->status = !$item->status;
    $item->save();

    session()->flash("flash_success", "Status do item alterado!");
    return redirect()->back();
}

public function destroy(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

    try{
        foreach($item->itens as $i){
            $itemProducao = $i->itemProducao ?? null;
            if($itemProducao){
                $itemProducao->status = 0;
                $itemProducao->save();
            }
        }

        $item->itens()->delete();
        $item->delete();

        session()->flash("flash_success", "Ordem de produção removida");

    }catch(\Exception $e){
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->back();
}


public function config(Request $request)
{
    $item = EtiquetaConfiguracao::where('empresa_id', $request->empresa_id)->first();

    try{
        if($item == null){
            EtiquetaConfiguracao::create($request->all());
        }else{
            $item->fill($request->all())->save();
        }

        session()->flash("flash_success", "Configuração definida!");

    }catch(\Exception $e){
        session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->back();
}

public function imprimirEtiquetas($id)
{
    $item = OrdemProducao::findOrFail($id);
    $empresa = Empresa::findOrFail(request()->empresa_id);
    $config = EtiquetaConfiguracao::where('empresa_id', $empresa->id)->first();

    return view('ordem_producao.etiqueta', compact('item', 'empresa', 'config'));
}

public function imprimir($id)
{
    $item = OrdemProducao::findOrFail($id);
    $config = Empresa::findOrFail(request()->empresa_id);

    $html = view('ordem_producao.imprimir_detalhe', compact('item', 'config'))->render();

    $domPdf = new Dompdf(["enable_remote" => true]);
    $domPdf->loadHtml($html);
    $domPdf->setPaper("A4");
    $domPdf->render();

    return $domPdf->stream("Ordem de produção.pdf", ["Attachment" => false]);
}

public function uploadImagens(Request $request, $id)
{
    $request->validate([
        'imagens.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096'
    ]);

    $ordem = OrdemProducao::findOrFail($id);

    if($request->hasFile('imagens')){
        foreach($request->file('imagens') as $file){

            $fakeRequest = new \Illuminate\Http\Request();
            $fakeRequest->files->set('file', $file);

            $file_name = $this->util->uploadImage($fakeRequest, '/ordem_producao', 'file');

            ImagemOrdemProducao::create([
                'ordem_producao_id' => $ordem->id,
                'imagem' => $file_name
            ]);
        }
    }

    return back()->with('flash_success', 'Imagens adicionadas!');
}

public function removerImagem($id)
{
    $img = ImagemOrdemProducao::findOrFail($id);

    $this->util->unlinkImage($img, '/ordem_producao');
    $img->delete();

    return back()->with('flash_success', 'Imagem removida!');
}

public function impressaoTecnica($id)
{
    $ordem = OrdemProducao::with(['itens.produto'])->findOrFail($id);
    $empresa = $ordem->empresa;

    $insumosAgrupados = [];

    foreach($ordem->itens as $item){

        $quantidadeProduzir = (float) $item->quantidade;

        if(!$item->produto || !$item->produto->composicao){
            continue;
        }

        foreach($item->produto->composicao as $composicao){
            if(!$composicao->ingrediente){
                continue;
            }

            $insumoId = $composicao->ingrediente->id;
            $quantidadeBase = (float) $composicao->quantidade;
            $quantidadeTotal = $quantidadeBase * $quantidadeProduzir;

            if(!isset($insumosAgrupados[$insumoId])){
                $insumosAgrupados[$insumoId] = [
                    'nome' => $composicao->ingrediente->nome,
                    'codigo' => $composicao->ingrediente->numero_sequencial,
                    'unidade' => $composicao->ingrediente->unidade ?? 'un',
                    'quantidade' => 0,
                ];
            }

            $insumosAgrupados[$insumoId]['quantidade'] += $quantidadeTotal;
        }
    }

    $insumosAgrupados = collect($insumosAgrupados)->values();

    $html = view('ordem_producao.impressao_tecnica', compact('ordem', 'empresa', 'insumosAgrupados'))->render();

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return response($dompdf->output())
    ->header('Content-Type', 'application/pdf')
    ->header('Content-Disposition', 'inline; filename="ordem-producao-'.$ordem->codigo_sequencial.'.pdf"');
}

public function gerarVenda($id)
{
    $item = OrdemProducao::findOrFail($id);

    $cliente = $item->itens[0]->cliente ?? null;

    if($cliente){
        $item->cliente = $cliente;
        $item->cliente_id = $cliente->id;
    }

    $cidades = Cidade::all();
    $transportadoras = Transportadora::where('empresa_id', request()->empresa_id)->get();

    $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)->get();

    if(sizeof($naturezas) == 0){
        session()->flash("flash_warning", "Primeiro cadastre uma natureza de operação!");
        return redirect()->route('natureza-operacao.create');
    }

    $empresa = $item->empresa;
    $caixa = __isCaixaAberto();
    $empresa = __objetoParaEmissao($empresa, $caixa->local_id);

    $numeroNfe = Nfe::lastNumero($empresa);
    $isOrdemProducao = 1;

    $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

    $naturezaPadrao = NaturezaOperacao::where('empresa_id', request()->empresa_id)
    ->where('padrao', 1)
    ->first();

    if($naturezaPadrao){
        $item->natureza_id = $naturezaPadrao->id;
    }

    return view('nfe.create', compact(
        'item',
        'cidades',
        'transportadoras',
        'naturezas',
        'isOrdemProducao',
        'numeroNfe',
        'caixa',
        'funcionarios',
        'naturezaPadrao'
    ));
}

public function simularCustos(Request $request, $id)
{
    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

    OrdemProducaoUtil::recalcularCustos($item);
    $validacao = OrdemProducaoUtil::validarOrdem($item->id);

    $item = OrdemProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

    $item->estrutura_ok = $validacao['estrutura_ok'] ?? $item->estrutura_ok;
    $item->roteiro_ok = $validacao['roteiro_ok'] ?? $item->roteiro_ok;
    $item->estoque_ok = $validacao['estoque_ok'] ?? $item->estoque_ok;
    $item->custos_ok = $validacao['custos_ok'] ?? $item->custos_ok;
    $item->save();

    session()->flash("flash_success", "Custos simulados com sucesso!");
    return redirect()->back();
}

}