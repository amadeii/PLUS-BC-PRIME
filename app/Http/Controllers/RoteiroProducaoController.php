<?php

namespace App\Http\Controllers;

use App\Models\RoteiroProducao;
use App\Models\RoteiroProducaoItem;
use App\Models\Produto;
use App\Models\Operacao;
use App\Models\Setor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoteiroProducaoController extends Controller
{
    public function index(Request $request)
    {
        $roteiros = RoteiroProducao::where('empresa_id', $request->empresa_id)
        ->with(['produto', 'itens'])
        ->when($request->nome, function($q) use ($request){
            return $q->where('nome', 'like', "%{$request->nome}%");
        })
        ->when($request->produto_id, function($q) use ($request){
            return $q->where('produto_id', $request->produto_id);
        })
        ->when($request->ativo !== null && $request->ativo !== '', function($q) use ($request){
            return $q->where('ativo', $request->ativo);
        })
        ->orderByDesc('id')
        ->paginate(__itensPagina());

        $produtos = Produto::where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->orderBy('nome')
        ->get();

        return view('roteiro_producao.index', compact('roteiros', 'produtos'));
    }

    public function create(Request $request)
    {
        $produtos = Produto::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->where('status', 1)
        ->get();

        $operacoes = Operacao::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        $setores = Setor::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        return view('roteiro_producao.create', compact('produtos', 'operacoes', 'setores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|max:120',
            'produto_id' => 'nullable',
            'descricao' => 'nullable',
            'sequencia' => 'required|array',
            'nome_operacao' => 'required|array',
        ]);

        try {
            DB::transaction(function() use ($request) {
                $roteiro = RoteiroProducao::create([
                    'empresa_id' => $request->empresa_id,
                    'produto_id' => $request->produto_id,
                    'nome' => $request->nome,
                    'descricao' => $request->descricao,
                    'ativo' => $request->ativo ? true : false
                ]);

                $this->salvarItens($request, $roteiro);
            });

            session()->flash('success', 'Roteiro de produção cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao cadastrar roteiro: ' . $e->getMessage());
        }

        return redirect()->route('roteiro-producao.index');
    }

    public function edit(Request $request, $id)
    {
        $roteiro = RoteiroProducao::where('empresa_id', $request->empresa_id)
        ->with('itens')
        ->findOrFail($id);

        $produtos = Produto::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->where('status', 1)
        ->get();

        $operacoes = Operacao::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        $setores = Setor::where('empresa_id', $request->empresa_id)
        ->orderBy('nome')
        ->get();

        return view('roteiro_producao.edit', compact('roteiro', 'produtos', 'operacoes', 'setores'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|max:120',
            'produto_id' => 'nullable',
            'descricao' => 'nullable',
            'sequencia' => 'required|array',
            'nome_operacao' => 'required|array',
        ]);

        $roteiro = RoteiroProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

        try {
            DB::transaction(function() use ($request, $roteiro) {
                $roteiro->update([
                    'produto_id' => $request->produto_id,
                    'nome' => $request->nome,
                    'descricao' => $request->descricao,
                    'ativo' => $request->ativo ? true : false
                ]);

                $roteiro->itens()->delete();

                $this->salvarItens($request, $roteiro);
            });

            session()->flash('success', 'Roteiro de produção atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao atualizar roteiro: ' . $e->getMessage());
        }

        return redirect()->route('roteiro-producao.index');
    }

    public function destroy(Request $request, $id)
    {
        $roteiro = RoteiroProducao::where('empresa_id', $request->empresa_id)->findOrFail($id);

        try {
            $roteiro->delete();
            session()->flash('success', 'Roteiro removido com sucesso!');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao remover roteiro: ' . $e->getMessage());
        }

        return redirect()->route('roteiro-producao.index');
    }

    private function salvarItens(Request $request, RoteiroProducao $roteiro)
    {
        foreach ($request->sequencia as $key => $sequencia) {
            if (!isset($request->nome_operacao[$key]) || trim($request->nome_operacao[$key]) == '') {
                continue;
            }

            $operacaoId = $request->operacao_id[$key] ?? null;
            $setorId = $request->setor_id[$key] ?? null;

            $nomeOperacao = $request->nome_operacao[$key];
            $nomeSetor = $request->nome_setor[$key] ?? null;

            if ($operacaoId) {
                $operacao = Operacao::where('empresa_id', $request->empresa_id)->find($operacaoId);
                if ($operacao) {
                    $nomeOperacao = $operacao->nome;
                }
            }

            if ($setorId) {
                $setor = Setor::where('empresa_id', $request->empresa_id)->find($setorId);
                if ($setor) {
                    $nomeSetor = $setor->nome;
                }
            }

            RoteiroProducaoItem::create([
                'roteiro_producao_id' => $roteiro->id,
                'operacao_id' => $operacaoId,
                'setor_id' => $setorId,
                'sequencia' => $sequencia ?: ($key + 1),
                'nome_operacao' => $nomeOperacao,
                'nome_setor' => $nomeSetor,
                'tempo_previsto_minutos' => $request->tempo_previsto_minutos[$key] ?? 0,
                'observacao' => $request->observacao_item[$key] ?? null
            ]);
        }
    }
}