<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrdemProducao;
use App\Models\OrdemProducaoOperacao;
use App\Models\OrdemProducaoApontamento;
use App\Models\Funcionario;
use App\Models\MotivoRefugo;
use App\Utils\OrdemProducaoUtil;
use Carbon\Carbon;

class ApontamentoProducaoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemProducao::where('empresa_id', $request->empresa_id)
        ->with([
            'itens.produto',
            'itens.cliente',
            'operacoes.apontamentos',
            'apontamentos'
        ]);

        $query->when($request->codigo, function($q) use ($request){
            return $q->where('codigo_sequencial', $request->codigo);
        });

        $query->when($request->produto, function($q) use ($request){
            return $q->whereHas('itens.produto', function($p) use ($request){
                $p->where('nome', 'like', "%{$request->produto}%");
            });
        });

        $query->when($request->estado, function($q) use ($request){
            return $q->where('estado', $request->estado);
        });

        $data = $query->orderBy('id', 'desc')->paginate(__itensPagina());

        return view('apontamento_producao.index', compact('data'));
    }

    public function show(Request $request, $id)
    {
        $item = OrdemProducao::where('empresa_id', $request->empresa_id)
        ->with([
            'itens.produto',
            'itens.cliente',
            'operacoes.apontamentos.funcionario',
            'operacoes.apontamentos.motivoRefugo',
            'materiais.material'
        ])
        ->findOrFail($id);

        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)
        ->where('status', 1)
        ->orderBy('nome')->get();
        $motivosRefugo = MotivoRefugo::where('empresa_id', $request->empresa_id)->orderBy('nome')->get();

        return view('apontamento_producao.show', compact('item', 'funcionarios', 'motivosRefugo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ordem_producao_id' => 'required',
            'ordem_producao_operacao_id' => 'nullable',
            'funcionario_id' => 'required',
            'data_inicio' => 'required',
            'data_fim' => 'nullable',
            'quantidade_produzida' => 'required',
            'quantidade_refugada' => 'nullable',
            'motivo_refugo_id' => 'nullable',
            'observacao' => 'nullable|max:500',
        ],[
            'ordem_producao_id.required' => 'Ordem de produção não informada.',
            'funcionario_id.required' => 'Selecione um funcionário.',
            'data_inicio.required' => 'Informe a data inicial.',
            'quantidade_produzida.required' => 'Informe a quantidade produzida.',
            'observacao.max' => 'A observação deve ter no máximo 500 caracteres.',
        ]);

        try{
            DB::transaction(function () use ($request) {

                $ordem = OrdemProducao::where('empresa_id', $request->empresa_id)
                ->findOrFail($request->ordem_producao_id);

                if($ordem->data_encerramento){
                    throw new \Exception("Esta OP já está encerrada e não permite apontamentos.");
                }

                if($ordem->estado == 'novo'){
                    throw new \Exception("A OP ainda não foi liberada para produção.");
                }

                if($ordem->estado == 'encerrada'){
                    throw new \Exception("A OP está encerrada.");
                }

                $operacao = null;

                if($request->ordem_producao_operacao_id){
                    $operacao = OrdemProducaoOperacao::where('ordem_producao_id', $ordem->id)
                    ->findOrFail($request->ordem_producao_operacao_id);

                    if($operacao->status == 'finalizada'){
                        throw new \Exception("Esta operação já foi finalizada.");
                    }
                }

                $dataInicio = Carbon::parse($request->data_inicio);
                $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

                if($dataFim && $dataFim->lt($dataInicio)){
                    throw new \Exception("Data final não pode ser menor que a data inicial.");
                }

                $tempoReal = $dataFim ? $dataInicio->diffInMinutes($dataFim) : 0;

                $quantidadeProduzida = __convert_value_bd($request->quantidade_produzida);
                $quantidadeRefugada = __convert_value_bd($request->quantidade_refugada ?? 0);

                if($operacao){

                    $saldoPendente = $operacao->itemOrdemProducao->quantidade - $operacao->quantidade_produzida;

                    if($quantidadeProduzida > $saldoPendente){
                        throw new \Exception("Quantidade maior que o saldo pendente da operação.");
                    }
                }

                if(in_array($ordem->estado, ['liberada', 'novo'])){
                    $ordem->estado = 'producao';
                    $ordem->data_inicio = $ordem->data_inicio ?? now();
                    $ordem->save();
                }

                OrdemProducaoApontamento::create([
                    'ordem_producao_id' => $ordem->id,
                    'item_ordem_producao_id' => $operacao ? $operacao->item_ordem_producao_id : null,
                    'ordem_producao_operacao_id' => $operacao ? $operacao->id : null,
                    'funcionario_id' => $request->funcionario_id,
                    'motivo_refugo_id' => $request->motivo_refugo_id,
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'tempo_real_minutos' => $tempoReal,
                    'quantidade_produzida' => $quantidadeProduzida,
                    'quantidade_refugada' => $quantidadeRefugada,
                    'observacao' => $request->observacao,
                    'status' => 'aberto'
                ]);

                if($operacao){
                    $ordem->ultima_operacao_id = $operacao->id;
                    $ordem->save();
                }

                if($operacao){
                    $this->atualizarOperacao($operacao->id);
                }

                $this->atualizarOrdem($ordem->id);
            });

            session()->flash("flash_success", "Apontamento registrado com sucesso!");

        }catch(\Exception $e){
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->back()->withInput();
    }

    private function atualizarOperacao($operacaoId)
    {
        $operacao = OrdemProducaoOperacao::with('apontamentos')->findOrFail($operacaoId);

        $tempoReal = $operacao->apontamentos->sum('tempo_real_minutos');
        $qtdProduzida = $operacao->apontamentos->sum('quantidade_produzida');
        $qtdRefugada = $operacao->apontamentos->sum('quantidade_refugada');

        $operacao->tempo_real_minutos = $tempoReal;
        $operacao->quantidade_produzida = $qtdProduzida;
        $operacao->quantidade_refugada = $qtdRefugada;

        $eficiencia = 0;

        if($tempoReal > 0 && $operacao->tempo_previsto_minutos > 0){

            $eficiencia = ($operacao->tempo_previsto_minutos / $tempoReal) * 100;

            if($eficiencia > 100){
                $eficiencia = 100;
            }
        }

        $operacao->eficiencia = $eficiencia;

        if($qtdProduzida <= 0){
            $operacao->status = 'pendente';
        }elseif($qtdProduzida < $operacao->itemOrdemProducao->quantidade){
            $operacao->status = 'parcial';
        }else{
            $operacao->status = 'finalizada';
        }

        $operacao->save();
    }

    public function finalizarOperacao(Request $request, $id)
    {
        try{

            $operacao = OrdemProducaoOperacao::with([
                'ordemProducao',
                'itemOrdemProducao',
                'apontamentos'
            ])->findOrFail($id);

            $ordem = $operacao->ordemProducao;

            if($ordem->estado == 'encerrada'){
                session()->flash('flash_error', 'Esta OP está encerrada.');
                return redirect()->back();
            }

            $qtdPlanejada = (float) $operacao->itemOrdemProducao->quantidade;
            $qtdProduzida = (float) $operacao->quantidade_produzida;

            $saldoPendente = $qtdPlanejada - $qtdProduzida;

            if($saldoPendente > 0){
                session()->flash('flash_error', 'Ainda existe saldo pendente nesta operação.');
                return redirect()->back();
            }

            foreach($operacao->apontamentos as $ap){
                $ap->status = 'finalizado';
                $ap->save();
            }

            $operacao->status = 'finalizada';
            $operacao->data_finalizacao = now();

            if($operacao->tempo_real_minutos > 0 && $operacao->tempo_previsto_minutos > 0){

                $eficiencia = ($operacao->tempo_previsto_minutos / $operacao->tempo_real_minutos) * 100;

                $operacao->eficiencia = $eficiencia > 100 ? 100 : $eficiencia;
            }

            $operacao->save();

            $this->atualizarOrdem($ordem->id);

            session()->flash('flash_success', 'Operação finalizada com sucesso!');

        }catch(\Exception $e){

            session()->flash('flash_error', 'Algo deu errado: '.$e->getMessage());
        }

        return redirect()->back();
    }
    private function atualizarOrdem($ordemId)
    {
        $ordem = OrdemProducao::with(['itens', 'operacoes'])->findOrFail($ordemId);

        $qtdPlanejada = $ordem->itens->sum('quantidade');
        $qtdProduzida = $ordem->operacoes->sum('quantidade_produzida');
        $qtdRefugada = $ordem->operacoes->sum('quantidade_refugada');

        $ordem->quantidade_produzida = $qtdProduzida;
        $ordem->quantidade_refugada = $qtdRefugada;

        $qtdPendente = max(0, $qtdPlanejada - $qtdProduzida);
        $ordem->quantidade_pendente = $qtdPendente;

        $ordem->percentual_progresso = $qtdPlanejada > 0 ? min(100, ($qtdProduzida / $qtdPlanejada) * 100) : 0;

        if($qtdProduzida <= 0){
            $ordem->estado = 'liberada';
        }elseif($ordem->percentual_progresso < 100){
            $ordem->estado = 'parcial';
        }else{
            $ordem->estado = 'finalizada';
            $ordem->data_finalizacao = $ordem->data_finalizacao ?? now();
        }

        $ordem->save();

        OrdemProducaoUtil::recalcularCustos($ordem);
    }
}