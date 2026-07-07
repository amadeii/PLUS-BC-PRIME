<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recorrencia;
use App\Models\RecorrenciaServico;
use App\Models\RecorrenciaCobranca;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class RecorrenciaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:recorrencia_create', ['only' => ['create', 'store', 'gerarCobranca']]);
        $this->middleware('permission:recorrencia_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:recorrencia_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:recorrencia_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Recorrencia::where('empresa_id', request()->empresa_id)
        ->when(!empty($request->cliente_id), function ($q) use ($request) {
            return $q->where('cliente_id', $request->cliente_id);
        })
        ->when(!empty($request->status), function ($q) use ($request) {
            return $q->where('status', $request->status);
        })
        ->when(!empty($request->descricao), function ($q) use ($request) {
            return $q->where('descricao', 'LIKE', "%$request->descricao%");
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        $clientes = Cliente::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->get();

        return view('recorrencias.index', compact('data', 'clientes'));
    }

    public function create()
    {
        $clientes = Cliente::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->get();

        if(sizeof($clientes) == 0){
            session()->flash("flash_warning", 'Cadastre um cliente!');
            return redirect()->route('clientes.create');
        }

        $servicos = Servico::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->get();

        if(sizeof($servicos) == 0){
            session()->flash("flash_warning", 'Cadastre um serviço!');
            return redirect()->route('servicos.create');
        }

        return view('recorrencias.create', compact('clientes', 'servicos'));
    }

    public function edit($id)
    {
        $item = Recorrencia::findOrFail($id);
        __validaObjetoEmpresa($item);

        $clientes = Cliente::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->get();

        $servicos = Servico::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->get();

        return view('recorrencias.edit', compact('item', 'clientes', 'servicos'));
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'empresa_id' => request()->empresa_id,
                'valor' => __convert_value_bd($request->valor),
                'proxima_cobranca' => $this->getPrimeiraCobranca($request->data_inicio, $request->dia_vencimento),
                'gerar_automatico' => $request->gerar_automatico ? 1 : 0,
                'enviar_whatsapp' => $request->enviar_whatsapp ? 1 : 0,
                'enviar_email' => $request->enviar_email ? 1 : 0,
                'gera_nfse' => $request->gera_nfse ? 1 : 0,
                'gera_nfe' => $request->gera_nfe ? 1 : 0,
            ]);

            $item = Recorrencia::create($request->all());

            $this->salvarServicos($request, $item);

            session()->flash("flash_success", "Recorrência criada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencias.index');
    }

    public function update(Request $request, $id)
    {
        $item = Recorrencia::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'gerar_automatico' => $request->gerar_automatico ? 1 : 0,
                'enviar_whatsapp' => $request->enviar_whatsapp ? 1 : 0,
                'enviar_email' => $request->enviar_email ? 1 : 0,
                'gera_nfse' => $request->gera_nfse ? 1 : 0,
                'gera_nfe' => $request->gera_nfe ? 1 : 0,
            ]);

            $item->fill($request->all())->save();

            $item->servicos()->delete();
            $this->salvarServicos($request, $item);

            session()->flash("flash_success", "Recorrência alterada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencias.index');
    }

    public function destroy($id)
    {
        $item = Recorrencia::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $item->delete();
            session()->flash("flash_success", "Recorrência removida com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->route('recorrencias.index');
    }

    public function show($id)
    {
        $item = Recorrencia::with(['cliente', 'servicos.servico', 'cobrancas'])
        ->findOrFail($id);

        __validaObjetoEmpresa($item);

        return view('recorrencias.show', compact('item'));
    }

    public function gerarCobranca($id)
    {
        $item = Recorrencia::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $vencimento = $item->proxima_cobranca ?? date('Y-m-d');

            RecorrenciaCobranca::create([
                'empresa_id' => $item->empresa_id,
                'recorrencia_id' => $item->id,
                'cliente_id' => $item->cliente_id,
                'data_vencimento' => $vencimento,
                'valor' => $item->valor,
                'status' => 'pendente',
                'forma_pagamento' => $item->forma_pagamento,
                'observacao' => 'Cobrança gerada manualmente'
            ]);

            $item->proxima_cobranca = $this->proximaData($vencimento, $item->periodicidade, $item->dia_vencimento);
            $item->save();

            session()->flash("flash_success", "Cobrança gerada com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
        }

        return redirect()->back();
    }

    private function salvarServicos(Request $request, $item)
    {
        if (!$request->servico_id) {
            return;
        }

        for($i=0; $i<sizeof($request->servico_id); $i++){
            if(!$request->servico_id[$i]){
                continue;
            }

            $quantidade = __convert_value_bd($request->quantidade[$i] ?? 1);
            $valorUnitario = __convert_value_bd($request->valor_unitario[$i] ?? 0);

            RecorrenciaServico::create([
                'recorrencia_id' => $item->id,
                'servico_id' => $request->servico_id[$i],
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'subtotal' => $quantidade * $valorUnitario
            ]);
        }
    }

    private function getPrimeiraCobranca($dataInicio, $diaVencimento)
    {
        $data = Carbon::parse($dataInicio)->day((int)$diaVencimento);

        if ($data->lt(Carbon::parse($dataInicio))) {
            $data->addMonth();
        }

        return $data->format('Y-m-d');
    }

    private function proximaData($dataAtual, $periodicidade, $diaVencimento)
    {
        $data = Carbon::parse($dataAtual);

        if($periodicidade == 'mensal'){
            $data->addMonth();
        }elseif($periodicidade == 'bimestral'){
            $data->addMonths(2);
        }elseif($periodicidade == 'trimestral'){
            $data->addMonths(3);
        }elseif($periodicidade == 'semestral'){
            $data->addMonths(6);
        }elseif($periodicidade == 'anual'){
            $data->addYear();
        }

        $data->day((int)$diaVencimento);

        return $data->format('Y-m-d');
    }
}