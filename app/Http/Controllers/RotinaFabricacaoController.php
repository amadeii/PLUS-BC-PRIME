<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Setor;
use App\Models\Operacao;
use App\Models\CentroCusto;
use App\Models\RotinaFabricacao;
use App\Models\ProdutoComposicao;
use App\Models\RotinaFabricacaoOperacao;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RotinaFabricacaoController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
        $this->middleware('permission:rotina_fabricacao_create', ['only' => ['create', 'createForm', 'store']]);
        $this->middleware('permission:rotina_fabricacao_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:rotina_fabricacao_view', ['only' => ['index']]);
        $this->middleware('permission:rotina_fabricacao_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $nome = $request->nome;

        $data = Produto::where('empresa_id', $request->empresa_id)
        ->whereHas('rotinaFabricacao')
        ->with(['rotinaFabricacao'])
        ->when(!empty($nome), function ($q) use ($nome) {
            return $q->where('nome', 'LIKE', "%$nome%");
        })
        ->orderBy('nome')
        ->paginate(__itensPagina());

        return view('rotina_fabricacao.index', compact('data'));
    }

    public function create(Request $request)
    {
        $nome = $request->nome;

        $data = Produto::where('empresa_id', $request->empresa_id)
        ->where('tipo_producao', 1)
        ->whereDoesntHave('rotinaFabricacao')
        ->when(!empty($nome), function ($q) use ($nome) {
            return $q->where('nome', 'LIKE', "%$nome%");
        })
        ->paginate(__itensPagina());

        return view('rotina_fabricacao.create', compact('data'));
    }

    public function createForm($produto_id)
    {
        $produto = Produto::with([
            'composicao.ingrediente.categoria',
            'composicao.ingrediente.composicao'
        ])
        ->where('empresa_id', request()->empresa_id)
        ->findOrFail($produto_id);

        __validaObjetoEmpresa($produto);

        $existe = RotinaFabricacao::where('empresa_id', request()->empresa_id)
        ->where('produto_id', $produto->id)
        ->first();

        if($existe){
            return redirect()->route('rotina-fabricacao.edit', $existe->id);
        }

        $setores = Setor::where('empresa_id', request()->empresa_id)->get();
        $operacoes = Operacao::where('empresa_id', request()->empresa_id)->get();
        $centroCustos = CentroCusto::where('empresa_id', request()->empresa_id)->get();

        $composicaoRecursiva = $this->getComposicaoRecursiva($produto->id);

        return view('rotina_fabricacao.create_form', compact(
            'produto',
            'setores',
            'operacoes',
            'centroCustos',
            'composicaoRecursiva'
        ));
    }

    private function getComposicaoRecursiva($produtoId, $nivel = 0, $quantidadePai = 1)
    {
        $composicoes = ProdutoComposicao::with([
            'ingrediente.categoria',
            'ingrediente.composicao.ingrediente.categoria'
        ])
        ->where('produto_id', $produtoId)
        ->get();

        $resultado = collect();

        foreach ($composicoes as $composicao) {
            $composicao->nivel = $nivel;
            $composicao->quantidade_calculada = $composicao->quantidade * $quantidadePai;

            $resultado->push($composicao);

            if (
                $composicao->ingrediente &&
                $composicao->ingrediente->composicao &&
                $composicao->ingrediente->composicao->count() > 0
            ) {
                $subItens = $this->getComposicaoRecursiva(
                    $composicao->ingrediente_id,
                    $nivel + 1,
                    $composicao->quantidade_calculada
                );

                $resultado = $resultado->merge($subItens);
            }
        }

        return $resultado;
    }

    public function store(Request $request)
    {
        $produto = Produto::where('empresa_id', $request->empresa_id)
        ->findOrFail($request->produto_id);

        __validaObjetoEmpresa($produto);

        try {
            DB::beginTransaction();

            $file_name = '';
            if ($request->hasFile('imagem')) {
                $file_name = $this->util->uploadImage($request, '/rotina_fabricacao', 'imagem');
            }

            $rotina = RotinaFabricacao::create([
                'empresa_id' => $request->empresa_id,
                'produto_id' => $produto->id,
                'imagem' => $file_name,
                'user_id' => get_id_user(),
                'lote_minimo' => str_replace(',', '.', $request->lote_minimo ?? 1),
                'instrucoes_especiais' => $request->instrucoes_especiais ?? '',
                'checklist_texto' => $request->checklist_texto ?? '',
                'assinaturas' => $request->assinaturas ?? '',
            ]);

            if($request->operacao_id){
                foreach($request->operacao_id as $i => $operacaoId){

                    if(
                        empty($operacaoId) &&
                        empty($request->descricao[$i]) &&
                        empty($request->setor_id[$i]) &&
                        empty($request->centro_custo_id[$i])
                    ){
                        continue;
                    }

                    RotinaFabricacaoOperacao::create([
                        'rotina_fabricacao_id' => $rotina->id,
                        // 'sequencia' => $request->sequencia[$i] ?? (($i + 1) * 10),
                        'operacao_id' => $operacaoId ?: null,
                        'setor_id' => $request->setor_id[$i] ?? null,
                        'centro_custo_id' => $request->centro_custo_id[$i] ?? null,
                        'descricao' => $request->descricao[$i] ?? '',
                        'tempo_minutos' => $request->tempo_minutos[$i] ?? 0,
                        'setup_minutos' => $request->setup_minutos[$i] ?? 0,
                    ]);
                }
            }

            DB::commit();

            __createLog($request->empresa_id, 'Rotina Fabricação', 'cadastrar', $produto->nome);
            session()->flash('flash_success', 'Rotina de fabricação cadastrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            __createLog($request->empresa_id, 'Rotina Fabricação', 'erro', $e->getMessage());
            session()->flash('flash_error', 'Não foi possível concluir o cadastro: ' . $e->getMessage());
        }

        return redirect()->route('rotina-fabricacao.index');
    }

    public function edit($id)
    {

        $item = RotinaFabricacao::with(['produto', 'operacoes'])
        ->findOrFail($id);

        __validaObjetoEmpresa($item);

        $produto = $item->produto;
        $setores = Setor::where('empresa_id', request()->empresa_id)->get();
        $operacoes = Operacao::where('empresa_id', request()->empresa_id)->get();
        $centroCustos = CentroCusto::where('empresa_id', request()->empresa_id)->get();

        $composicaoRecursiva = $this->getComposicaoRecursiva($produto->id);

        return view('rotina_fabricacao.edit', compact(
            'item',
            'produto',
            'setores',
            'operacoes',
            'centroCustos',
            'composicaoRecursiva'
        ));
    }

    public function update(Request $request, $id)
    {
        $item = RotinaFabricacao::with('produto')->findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            DB::beginTransaction();

            $file_name = $item->imagem;
            if ($request->hasFile('imagem')) {
                $this->util->unlinkImage($item, '/rotina_fabricacao');
                $file_name = $this->util->uploadImage($request, '/rotina_fabricacao', 'imagem');
            }

            $item->imagem = $file_name;
            // $item->user_id = getUsuario();
            $item->lote_minimo = str_replace(',', '.', $request->lote_minimo ?? 1);
            $item->instrucoes_especiais = $request->instrucoes_especiais ?? '';
            $item->checklist_texto = $request->checklist_texto ?? '';
            $item->assinaturas = $request->assinaturas ?? '';
            $item->updated_at = date('Y-m-d H:i');
            $item->save();

            RotinaFabricacaoOperacao::where('rotina_fabricacao_id', $item->id)->delete();

            if($request->operacao_id){
                foreach($request->operacao_id as $i => $operacaoId){

                    if(
                        empty($operacaoId) &&
                        empty($request->descricao[$i]) &&
                        empty($request->setor_id[$i]) &&
                        empty($request->centro_custo_id[$i])
                    ){
                        continue;
                    }

                    RotinaFabricacaoOperacao::create([
                        'rotina_fabricacao_id' => $item->id,
                        // 'sequencia' => $request->sequencia[$i] ?? (($i + 1) * 10),
                        'operacao_id' => $operacaoId ?: null,
                        'setor_id' => $request->setor_id[$i] ?? null,
                        'centro_custo_id' => $request->centro_custo_id[$i] ?? null,
                        'descricao' => $request->descricao[$i] ?? '',
                        'tempo_minutos' => $request->tempo_minutos[$i] ?? 0,
                        'setup_minutos' => $request->setup_minutos[$i] ?? 0,
                    ]);
                }
            }

            DB::commit();

            __createLog($request->empresa_id, 'Rotina Fabricação', 'editar', $item->produto->nome);
            session()->flash('flash_success', 'Rotina de fabricação alterada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            __createLog($request->empresa_id, 'Rotina Fabricação', 'erro', $e->getMessage());
            session()->flash('flash_error', 'Não foi possível alterar o cadastro: ' . $e->getMessage());
        }

        return redirect()->route('rotina-fabricacao.index');
    }

    public function destroy($id)
    {
        $item = RotinaFabricacao::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $produto = $item->produto ? $item->produto->nome : 'Rotina';
            $this->util->unlinkImage($item, '/rotina_fabricacao');
            $item->delete();

            __createLog(request()->empresa_id, 'Rotina Fabricação', 'excluir', $produto);
            session()->flash('flash_success', 'Removido com sucesso!');
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Rotina Fabricação', 'erro', $e->getMessage());
            session()->flash('flash_error', 'Não foi possível deletar: ' . $e->getMessage());
        }

        return redirect()->route('rotina-fabricacao.index');
    }
}