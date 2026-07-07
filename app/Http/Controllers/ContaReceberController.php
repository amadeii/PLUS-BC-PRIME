<?php

namespace App\Http\Controllers;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\CategoriaConta;
use App\Models\Frete;
use App\Models\Nfe;
use App\Models\ConfigGeral;
use App\Models\PlanoConta;
use App\Models\Nfce;
use App\Models\Troca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Utils\ContaEmpresaUtil;
use App\Models\ItemContaEmpresa;
use App\Utils\UploadUtil;
use Dompdf\Dompdf;
use App\Exports\ContaReceberExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ContaReceberController extends Controller
{
    protected $util;
    protected $uploadUtil;

    public function __construct(ContaEmpresaUtil $util, UploadUtil $uploadUtil){
        $this->util = $util;
        $this->uploadUtil = $uploadUtil;

        $this->middleware('permission:conta_receber_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:conta_receber_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:conta_receber_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:conta_receber_delete', ['only' => ['destroy']]);
    }

    private function setNumeroSequencial()
    {
        DB::transaction(function () {

            $empresaId = request()->empresa_id;

            $lastNumero = ContaReceber::where('empresa_id', $empresaId)
            ->where('numero_sequencial', '>', 0)
            ->lockForUpdate()
            ->max('numero_sequencial');

            $numero = ($lastNumero ?? 0) + 1;

            $docs = ContaReceber::where('empresa_id', $empresaId)
            ->whereNull('numero_sequencial')
            ->orderBy('numero_sequencial', 'desc')
            ->get();

            foreach ($docs as $doc) {
                $doc->update([
                    'numero_sequencial' => $numero++
                ]);
            }

        });
    }

    public function index(Request $request)
    {
        $this->setNumeroSequencial();

        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck('id');

        $cliente_id = $request->cliente_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $filtro_data = $request->filtro_data ?: 'data_vencimento';
        $status = $request->status;
        $categoria_conta_id = $request->categoria_conta_id;
        $ordem = $request->ordem;
        $reserva_id = $request->reserva_id;
        $plano_conta_id = $request->plano_conta_id;
        $numero_documento = $request->numero_documento;
        $local_id = $request->get('local_id');

        $planoSelecionado = null;

        if ($request->plano_conta_id) {
            $planoSelecionado = PlanoConta::findOrFail($request->plano_conta_id);
        }

        if ($filtro_data == 'data_recebimento') {
            $status = 1;
        }

        $query = ContaReceber::where('conta_recebers.empresa_id', request()->empresa_id)
        ->select('conta_recebers.*')
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('conta_recebers.cliente_id', $cliente_id);
        })
        ->when(!empty($start_date), function ($query) use ($start_date, $filtro_data) {
            return $query->whereDate('conta_recebers.' . $filtro_data, '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date, $filtro_data) {
            return $query->whereDate('conta_recebers.' . $filtro_data, '<=', $end_date);
        })
        ->when($plano_conta_id, function ($query) use ($plano_conta_id) {
            return $query->where('conta_recebers.plano_conta_id', $plano_conta_id);
        })
        ->when($numero_documento, function ($query) use ($numero_documento) {
            return $query
            ->leftJoin('nves', 'nves.id', '=', 'conta_recebers.nfe_id')
            ->leftJoin('nfces', 'nfces.id', '=', 'conta_recebers.nfce_id')
            ->where(function ($q) use ($numero_documento) {
                $q->where('nves.numero_sequencial', $numero_documento)
                ->orWhere('nfces.numero_sequencial', $numero_documento);
            });
        })
        ->when($local_id, function ($query) use ($local_id) {
            return $query->where('conta_recebers.local_id', $local_id);
        })
        ->when($categoria_conta_id, function ($query) use ($categoria_conta_id) {
            return $query->where('conta_recebers.categoria_conta_id', $categoria_conta_id);
        })
        ->when(!$local_id, function ($query) use ($locais) {
            return $query->whereIn('conta_recebers.local_id', $locais);
        })
        ->when($status !== null && $status !== '', function ($query) use ($status) {
            if ($status != -1) {
                return $query->where('conta_recebers.status', $status);
            }

            return $query->where('conta_recebers.status', 0)
            ->whereDate('conta_recebers.data_vencimento', '<', date('Y-m-d'));
        })
        ->when($reserva_id, function ($query) use ($reserva_id) {
            return $query->join('fatura_reservas', 'fatura_reservas.conta_receber_id', '=', 'conta_recebers.id')
            ->where('fatura_reservas.reserva_id', $reserva_id);
        });

        $resumoCliente = null;

        if ($cliente_id) {
            $resumoCliente = [
                'pendente' => (clone $query)
                ->where('conta_recebers.status', 0)
                ->whereDate('conta_recebers.data_vencimento', '>=', date('Y-m-d'))
                ->sum('conta_recebers.valor_integral'),

                'atraso' => (clone $query)
                ->where('conta_recebers.status', 0)
                ->whereDate('conta_recebers.data_vencimento', '<', date('Y-m-d'))
                ->sum('conta_recebers.valor_integral'),

                'recebido' => (clone $query)
                ->where('conta_recebers.status', 1)
                ->sum('conta_recebers.valor_recebido'),

                'geral' => (clone $query)
                ->sum('conta_recebers.valor_integral'),
            ];
        }

        $data = $query
        ->when($ordem != '', function ($query) {
            return $query->orderBy('conta_recebers.data_vencimento', 'asc');
        })
        ->when($ordem == '', function ($query) {
            return $query->orderBy('conta_recebers.created_at', 'desc');
        })
        ->paginate(__itensPagina());

        $cliente = null;

        if ($cliente_id) {
            $cliente = Cliente::findOrFail($cliente_id);
        }

        $categorias = CategoriaConta::where('empresa_id', request()->empresa_id)
        ->where('status', 1)
        ->where('tipo', 'receber')
        ->get();

        $temPlanoConta = PlanoConta::where('empresa_id', request()->empresa_id)->exists();

        return view('conta-receber.index', compact(
            'data',
            'cliente',
            'categorias',
            'temPlanoConta',
            'planoSelecionado',
            'resumoCliente'
        ));
    }

    public function exportExcel(Request $request){
        $locais = __getLocaisAtivoUsuario();
        $locais = $locais->pluck(['id']);

        $cliente_id = $request->cliente_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $status = $request->status;
        $categoria_conta_id = $request->categoria_conta_id;
        $ordem = $request->ordem;
        $local_id = $request->get('local_id');

        $data = ContaReceber::where('empresa_id', request()->empresa_id)
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('data_vencimento', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('data_vencimento', '<=', $end_date);
        })
        ->when($local_id, function ($query) use ($local_id) {
            return $query->where('local_id', $local_id);
        })
        ->when($categoria_conta_id, function ($query) use ($categoria_conta_id) {
            return $query->where('categoria_conta_id', $categoria_conta_id);
        })
        ->when(!$local_id, function ($query) use ($locais) {
            return $query->whereIn('local_id', $locais);
        })
        ->when($status != '', function ($query) use ($status) {
            return $query->where('status', $status);
        })
        ->when($ordem != '', function ($query) use ($ordem) {
            return $query->orderBy('data_vencimento', 'asc');
        })
        ->when($ordem == '', function ($query) use ($ordem) {
            return $query->orderBy('created_at', 'asc');
        })->get();

        $file = new ContaReceberExport($data);
        return Excel::download($file, 'contas_receber.xlsx');
    }

    public function create(Request $request)
    {

        $item = null;
        $diferenca = null;
        if($request->id){
            $item = ContaReceber::findOrFail($request->id);
            $item->valor_integral = $request->diferenca;
        }

        if($request->diferenca){
            $diferenca = $request->diferenca;
        }

        $categorias = CategoriaConta::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->where('tipo', 'receber')->get();

        $temPlanoConta = PlanoConta::where('empresa_id', request()->empresa_id)->exists();
        return view('conta-receber.create', compact('item', 'diferenca', 'categorias', 'temPlanoConta'));
    }

    public function store(Request $request)
    {
        $this->__validate($request);
        try {

            $file_name = '';
            if ($request->hasFile('file')) {
                $file_name = $this->uploadUtil->uploadFile($request->file, '/financeiro');
            }

            $referencia = "";
            if ($request->dt_recorrencia) {
                $referencia = "Parcela 1 de " . sizeof($request->dt_recorrencia)+1;
            }
            $descricao = $request->descricao;
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_original' => __convert_value_bd($request->valor_integral),
                'valor_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                'arquivo' => $file_name,
                'descricao' => $descricao . " " . $referencia
            ]);
            $conta = ContaReceber::create($request->all());
            $descricaoLog = "Vencimento: " . __data_pt($request->data_vencimento, 0) . " R$ " . __moeda($request->valor_integral);
            __createLog($request->empresa_id, 'Conta a Receber', 'cadastrar', $descricaoLog);
            if(isset($request->frete_id)){
                $frete = Frete::findOrFail($request->frete_id);
                $frete->conta_receber_id = $conta->id;
                $frete->save();
            }

            if ($request->dt_recorrencia) {
                for ($i = 0; $i < sizeof($request->dt_recorrencia); $i++) {
                    $data = $request->dt_recorrencia[$i];
                    $valor = __convert_value_bd($request->valor_recorrencia[$i]);
                    $referencia = "Parcela ".($i+2)." de " . sizeof($request->dt_recorrencia)+1;
                    $data = [
                        'venda_id' => null,
                        'data_vencimento' => $data,
                        // 'data_recebimento' => $data,
                        'valor_integral' => $valor,
                        'valor_recebido' => $request->status ? $valor : 0,
                        'descricao' => $descricao . " " . $referencia,
                        'categoria_conta_id' => $request->categoria_conta_id,
                        'status' => $request->status,
                        'empresa_id' => $request->empresa_id,
                        'cliente_id' => $request->cliente_id,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'local_id' => $request->local_id,
                        'observacao' => $request->observacao,
                        'observacao2' => $request->observacao2,
                        'observacao3' => $request->observacao3,
                    ];
                    $conta = ContaReceber::create($data);
                    $descricaoLog = "Vencimento: " . __data_pt($request->dt_recorrencia[$i], 0) . " R$ " . __moeda($valor);
                    __createLog($request->empresa_id, 'Conta a Receber', 'cadastrar', $descricaoLog);

                }
            }
            session()->flash("flash_success", "Conta a receber cadastrada!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Conta a Receber', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        if(isset($request->redirect)){
            return redirect($request->redirect);
        }
        return redirect()->route('conta-receber.index');
    }

    public function edit($id)
    {
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);
        $categorias = CategoriaConta::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->where('tipo', 'receber')->get();

        $temPlanoConta = PlanoConta::where('empresa_id', request()->empresa_id)->exists();
        return view('conta-receber.edit', compact('item', 'categorias', 'temPlanoConta'));
    }

    public function show($id)
    {
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);
        $categorias = CategoriaConta::where('empresa_id', request()->empresa_id)
        ->where('status', 1)->where('tipo', 'receber')->get();
        return view('conta-receber.show', compact('item', 'categorias'));
    }

    public function estornar($id)
    {
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);

        return view('conta-receber.estornar', compact('item'));
    }

    public function estornarUpdate(Request $request, $id){
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);
        try {
            $item->status = 0;
            $item->motivo_estorno = $request->motivo_estorno;
            $item->save();

            session()->flash("flash_success", "Conta a receber estornada!");
        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-receber.index');
    }

    public function update(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);

        try {
            $file_name = $item->arquivo;
            if ($request->hasFile('file')) {
                $this->uploadUtil->unlinkImage($item, '/financeiro');
                $file_name = $this->uploadUtil->uploadFile($request->file, '/financeiro');
            }
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'valor_recebido' => __convert_value_bd($request->valor_recebido) ? __convert_value_bd($request->valor_recebido) : 0,
                'arquivo' => $file_name
            ]);
            $item->fill($request->all())->save();
            $descricaoLog = "Vencimento: " . __data_pt($item->data_vencimento) . " R$ " . __moeda($item->valor_integral);
            __createLog($request->empresa_id, 'Conta a Receber', 'editar', $descricaoLog);
            session()->flash("flash_success", "Conta a receber atualizada!");
        } catch (\Exception $e) {
            __createLog($request->empresa_id, 'Conta a Receber', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->route('conta-receber.index');
    }

    public function downloadFile($id){
        $item = ContaReceber::findOrFail($id);
        if (file_exists(public_path('uploads/financeiro/') . $item->arquivo)) {
            return response()->download(public_path('uploads/financeiro/') . $item->arquivo);
        } else {
            session()->flash("flash_error", "Arquivo não encontrado");
            return redirect()->back();
        }
    }

    private function __validate(Request $request)
    {
        $rules = [
            'cliente_id' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
            'status' => 'required',
            'tipo_pagamento' => 'required'
        ];
        $messages = [
            'cliente_id.required' => 'Campo obrigatório',
            'valor_integral.required' => 'Campo obrigatório',
            'data_vencimento.required' => 'Campo obrigatório',
            'status.required' => 'Campo obrigatório',
            'tipo_pagamento.required' => 'Campo obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);
        
        try {
            $descricaoLog = "Vencimento: " . __data_pt($item->data_vencimento, 0) . " R$ " . __moeda($item->valor_integral);
            $item->delete();
            __createLog(request()->empresa_id, 'Conta a Receber', 'excluir', $descricaoLog);
            session()->flash("flash_success", "Conta removida!");
        } catch (\Exception $e) {
            __createLog(request()->empresa_id, 'Conta a Receber', 'erro', $e->getMessage());
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }
        return redirect()->back();
    }

    public function destroySelecet(Request $request)
    {
        $removidos = 0;
        $recebidas = 0;
        // dd($request->all());
        for($i=0; $i<sizeof($request->item_delete); $i++){
            $item = ContaReceber::findOrFail($request->item_delete[$i]);
            if($item->boleto){
                session()->flash("flash_error", 'Conta a receber selecionada com boleto vinculado!');
                return redirect()->back();
            }

            if(!$item->status){
                try {
                    $descricaoLog = "Vencimento: " . __data_pt($item->data_vencimento, 0) . " R$ " . __moeda($item->valor_integral);
                    $item->delete();
                    $removidos++;
                    __createLog(request()->empresa_id, 'Conta a Receber', 'excluir', $descricaoLog);
                } catch (\Exception $e) {
                    __createLog(request()->empresa_id, 'Conta a Receber', 'erro', $e->getMessage());
                    session()->flash("flash_error", 'Algo deu errado: '. $e->getMessage());
                    return redirect()->back();
                }
            }else{
                $recebidas++;
            }
        }

        session()->flash("flash_success", "Total de contas removidas: $removidos");
        if($recebidas > 0){
            session()->flash("flash_warning", "Total de contas não removidas: $recebidas");
        }
        return redirect()->back();
    }

    public function receberSelecionados(Request $request)
    {
        $recebidos = 0;
        $data = [];

        $configGeral = ConfigGeral::where('empresa_id', $request->empresa_id)->first();

        for($i=0; $i<sizeof($request->item_recebe_paga); $i++){
            $item = ContaReceber::findOrFail($request->item_recebe_paga[$i]);

            $valorMulta = 0.0;
            $valorJuros = 0.0;
            $valorReceber = $item->valor_integral;

            $valorBase = (float) $item->valor_integral;
            $venc = $item->data_vencimento ? \Carbon\Carbon::parse($item->data_vencimento)->startOfDay() : null;
            $hoje = \Carbon\Carbon::now()->startOfDay();

            if ($configGeral && $venc) {
                $diasAtraso = max(0, $venc->diffInDays($hoje, false) * -1); 

                $diasAtraso = $hoje->greaterThan($venc) ? $venc->diffInDays($hoje) : 0;

                $percMulta = (float) ($configGeral->perc_multa_padrao ?? 0);
                if ($diasAtraso > 0 && $percMulta > 0) {
                    $valorMulta = round($valorBase * ($percMulta / 100), 2);
                }

                $percJurosDia = (float) ($configGeral->perc_juros_padrao ?? 0);

                if ($diasAtraso > 0 && $percJurosDia > 0) {
                    $valorJuros = round($valorBase * ($percJurosDia / 100) * $diasAtraso, 2);
                }

                $valorReceber += $valorJuros + $valorMulta;
            }

            $item->valor_juros = $valorJuros;
            $item->valor_receber = $valorReceber;
            $item->valor_multa = $valorMulta;
            $data[] = $item;
        }

        return view('conta-receber.receive_select', compact('data'));
    }

    public function receivePdv(Request $request)
    {
        $recebidos = 0;
        $data = [];
        $configGeral = ConfigGeral::where('empresa_id', $request->empresa_id)->first();

        for($i=0; $i<sizeof($request->contas); $i++){
            $item = ContaReceber::findOrFail($request->contas[$i]);

            $valorMulta = 0.0;
            $valorJuros = 0.0;
            $valorReceber = $item->valor_integral;

            $valorBase = (float) $item->valor_integral;
            $venc = $item->data_vencimento ? \Carbon\Carbon::parse($item->data_vencimento)->startOfDay() : null;
            $hoje = \Carbon\Carbon::now()->startOfDay();

            if ($configGeral && $venc) {
                $diasAtraso = max(0, $venc->diffInDays($hoje, false) * -1); 

                $diasAtraso = $hoje->greaterThan($venc) ? $venc->diffInDays($hoje) : 0;

                $percMulta = (float) ($configGeral->perc_multa_padrao ?? 0);
                if ($diasAtraso > 0 && $percMulta > 0) {
                    $valorMulta = round($valorBase * ($percMulta / 100), 2);
                }

                $percJurosDia = (float) ($configGeral->perc_juros_padrao ?? 0);

                if ($diasAtraso > 0 && $percJurosDia > 0) {
                    $valorJuros = round($valorBase * ($percJurosDia / 100) * $diasAtraso, 2);
                }

                $valorReceber += $valorJuros + $valorMulta;
            }

            $item->valor_juros = $valorJuros;
            $item->valor_receber = $valorReceber;
            $item->valor_multa = $valorMulta;

            $data[] = $item;
        }

        $redirectPdv = 1;
        return view('conta-receber.receive_select', compact('data', 'redirectPdv'));
    }

    public function pay($id)
    {
        if (!__isCaixaAberto()) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }
        $item = ContaReceber::findOrFail($id);
        if($item->status){
            session()->flash("flash_warning", "Esta conta já esta recebida!");
            return redirect()->route('conta-receber.index');
        }

        $configGeral = ConfigGeral::where('empresa_id', $item->empresa_id)->first();

        $valorMulta = 0.0;
        $valorJuros = 0.0;
        $valorReceber = $item->valor_integral;

        $valorBase = (float) $item->valor_integral;
        $venc = $item->data_vencimento ? \Carbon\Carbon::parse($item->data_vencimento)->startOfDay() : null;
        $hoje = \Carbon\Carbon::now()->startOfDay();

        if ($configGeral && $venc) {
            $diasAtraso = max(0, $venc->diffInDays($hoje, false) * -1); 

            $diasAtraso = $hoje->greaterThan($venc) ? $venc->diffInDays($hoje) : 0;

            $percMulta = (float) ($configGeral->perc_multa_padrao ?? 0);
            if ($diasAtraso > 0 && $percMulta > 0) {
                $valorMulta = round($valorBase * ($percMulta / 100), 2);
            }

            $percJurosDia = (float) ($configGeral->perc_juros_padrao ?? 0);

            if ($diasAtraso > 0 && $percJurosDia > 0) {
                $valorJuros = round($valorBase * ($percJurosDia / 100) * $diasAtraso, 2);
            }

            $valorReceber += $valorJuros + $valorMulta;
        }

        $temPlanoConta = PlanoConta::where('empresa_id', request()->empresa_id)->exists();

        return view('conta-receber.pay', compact('item', 'temPlanoConta', 'valorMulta', 'valorJuros', 'valorReceber'));
    }

    public function payPut(Request $request, $id)
    {
        $usuario = Auth::user()->id;
        $caixa = Caixa::where('usuario_id', $usuario)->where('status', 1)->first();

        if ($caixa == null) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        $item = ContaReceber::findOrFail($id);

        try {

            $valorRecebido = __convert_value_bd($request->valor_pago);
            $valorMulta = __convert_value_bd($request->valor_multa);
            $valorJuros = __convert_value_bd($request->valor_juros);

            $item->valor_recebido = $valorRecebido;
            $item->valor_multa = $valorMulta;
            $item->valor_juros = $valorJuros;
            $item->status = true;
            $item->data_recebimento = $request->data_recebimento;
            $item->tipo_pagamento = $request->tipo_pagamento;
            $item->plano_conta_id = $request->plano_conta_id ?? null;
            $item->caixa_id = $caixa->id;
            $item->valor_original = $item->valor_integral;

            if (isset($request->conta_empresa_id)) {

                $item->conta_empresa_id = $request->conta_empresa_id;

                $nDoc = '';
                $descricao = "Recebimento de conta";

                if ($item->nfe) {

                    if ($item->descricao) {
                        $descricao .= " " . $item->descricao . " ";
                    }

                    $descricao .= " - valor integral R$" . __moeda($item->valor_integral) . " referente a venda Nº " . $item->nfe->numero_sequencial;

                    if ($item->nfe->estado == 'aprovado') {
                        $descricao .= ", emitida em " . __data_pt($item->nfe->data_emissao);
                        $nDoc = $item->nfe->numero;
                    }

                    $descricao .= " VALOR TOTAL VENDA R$ " . __moeda($item->nfe->total);
                }

                if ($valorJuros > 0) {
                    $descricao .= " | Juros R$ " . __moeda($valorJuros);
                }

                if ($valorMulta > 0) {
                    $descricao .= " | Multa R$ " . __moeda($valorMulta);
                }

                $data = [
                    'conta_id' => $request->conta_empresa_id,
                    'descricao' => $descricao,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'valor' => $valorRecebido + $valorJuros + $valorMulta,
                    'tipo' => 'entrada',
                    'cliente_id' => $item->cliente_id,
                    'numero_documento' => $nDoc,
                    'conta_receber_id' => $item->id,
                    'categoria_id' => $item->categoria_conta_id
                ];

                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);
            }

            $item->save();

            $valorSaldoDevedor = 0;

            if ($request->criar_saldo_devedor == 1 && $request->valor_saldo_devedor) {
                $valorSaldoDevedor = (float) str_replace(',', '.', $request->valor_saldo_devedor);
            }

            if ($request->criar_saldo_devedor == 1 && $valorSaldoDevedor > 0) {

                $item->recebimento_parcial = 1;
                $item->save();

                $novaConta = $item->replicate();

                $novaConta->conta_receber_origem_id = $item->id;
                $novaConta->valor_integral = $valorSaldoDevedor;
                $novaConta->valor_original = $valorSaldoDevedor;
                $novaConta->valor_recebido = 0;
                $novaConta->valor_juros = 0;
                $novaConta->valor_multa = 0;
                $novaConta->status = 0;
                $novaConta->caixa_id = null;
                $novaConta->conta_empresa_id = null;
                $novaConta->data_recebimento = null;
                $novaConta->tipo_pagamento = null;
                $novaConta->data_vencimento = $request->data_saldo_devedor;
                $novaConta->referencia = 'Saldo devedor da conta #'.$item->id;
                $novaConta->created_at = now();
                $novaConta->updated_at = now();

                $novaConta->save();

                session()->flash("flash_warning", "Conta recebida com valor parcial e saldo devedor criado!");
            } else {
                session()->flash("flash_success", "Conta recebida!");
            }

        } catch (\Exception $e) {
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
        }

        return redirect()->route('conta-receber.index');
    }

    public function receiveSelect(Request $request)
    {
        $contasId = [];

        $usuario = Auth::user()->id;
        $caixa = Caixa::where('usuario_id', $usuario)->where('status', 1)->first();

        if ($caixa == null) {
            session()->flash("flash_warning", "Abrir caixa antes de continuar!");
            return redirect()->route('caixa.create');
        }

        for ($i = 0; $i < sizeof($request->conta_id); $i++) {

            $item = ContaReceber::findOrFail($request->conta_id[$i]);

            $contasId[] = $item->id;

            $valorOriginalReceber = (float) $item->valor_integral;

            $valorRecebido = __convert_value_bd($request->valor_recebido[$i]);
            $valorJuros = __convert_value_bd($request->valor_juros[$i]);
            $valorMulta = __convert_value_bd($request->valor_multa[$i]);

            $totalPago = $valorRecebido + $valorJuros + $valorMulta;
            $saldoDevedor = max(0, round($valorOriginalReceber - $valorRecebido, 2));

            $item->valor_recebido = $valorRecebido;
            $item->valor_juros = $valorJuros;
            $item->valor_multa = $valorMulta;
            $item->status = 1;
            $item->caixa_id = $caixa->id;
            $item->data_recebimento = $request->data_recebimento[$i];
            $item->tipo_pagamento = $request->tipo_pagamento[$i];

            if (isset($request->conta_empresa_id[$i])) {

                $item->conta_empresa_id = $request->conta_empresa_id[$i];

                $nDoc = '';
                $descricao = "Recebimento da conta";

                if ($item->nfe) {

                    if ($item->descricao) {
                        $descricao .= " " . $item->descricao . " ";
                    }

                    $descricao .= " - valor integral R$" . __moeda($item->valor_integral) . " referente a venda Nº " . $item->nfe->numero_sequencial;

                    if ($item->nfe->estado == 'aprovado') {
                        $descricao .= ", emitida em " . __data_pt($item->nfe->data_emissao);
                        $nDoc = $item->nfe->numero;
                    }

                    $descricao .= " VALOR TOTAL VENDA R$ " . __moeda($item->nfe->total);
                }

                if ($valorJuros > 0) {
                    $descricao .= " | Juros R$ " . __moeda($valorJuros);
                }

                if ($valorMulta > 0) {
                    $descricao .= " | Multa R$ " . __moeda($valorMulta);
                }

                $data = [
                    'conta_id' => $request->conta_empresa_id[$i],
                    'descricao' => $descricao,
                    'tipo_pagamento' => $request->tipo_pagamento[$i],
                    'valor' => $totalPago,
                    'tipo' => 'entrada',
                    'cliente_id' => $item->cliente_id,
                    'numero_documento' => $nDoc,
                    'conta_receber_id' => $item->id,
                    'categoria_id' => $item->categoria_conta_id
                ];

                $itemContaEmpresa = ItemContaEmpresa::create($data);
                $this->util->atualizaSaldo($itemContaEmpresa);
            }

            $item->save();


        }

        $valorSaldoDevedor = (float) $request->valor_saldo_devedor;

        if ($request->criar_saldo_devedor == 1 && $valorSaldoDevedor > 0) {

            $novaConta = $item->replicate();

            $novaConta->valor_integral = $valorSaldoDevedor;
            $novaConta->valor_original = $valorSaldoDevedor;
            $novaConta->conta_receber_origem_id = $item->id;

            $novaConta->valor_recebido = 0;
            $novaConta->valor_juros = 0;
            $novaConta->valor_multa = 0;

            $novaConta->status = 0;
            $novaConta->caixa_id = null;
            $novaConta->conta_empresa_id = null;
            $novaConta->data_recebimento = null;
            $novaConta->tipo_pagamento = null;

            $novaConta->data_vencimento = $request->data_saldo_devedor;
            $novaConta->referencia = 'Saldo devedor da conta #'.$item->id;

            if (isset($novaConta->boleto_id)) {
                $novaConta->boleto_id = null;
            }

            if (isset($novaConta->asaas_id)) {
                $novaConta->asaas_id = null;
            }

            if (isset($novaConta->sicredi_id)) {
                $novaConta->sicredi_id = null;
            }

            if (isset($novaConta->pix_id)) {
                $novaConta->pix_id = null;
            }

            if (isset($novaConta->remessa_id)) {
                $novaConta->remessa_id = null;
            }

            $novaConta->created_at = now();
            $novaConta->updated_at = now();

            $novaConta->save();
        }

        session()->flash("flash_success", "Contas recebidas!");

        if (isset($request->redirect_pdv)) {
            return redirect()->route('frontbox.create')->with('imprimir_ids', implode(',', $contasId));
        }

        return redirect()->route('conta-receber.index')->with('imprimir_ids', implode(',', $contasId));
    }

    public function imprimirComprovanteLote(Request $request)
    {
        $ids = explode(',', $request->ids);

        $itens = ContaReceber::whereIn('id', $ids)->get();

        foreach ($itens as $item) {
            __validaObjetoEmpresa($item);
        }

        $html = view('conta-receber.imprimir_lote', compact('itens'))->render();

        // $options = new Options();
        // $options->set('isRemoteEnabled', true);
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf(["enable_remote" => true]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper([0,0,204,284 * sizeof($itens)]);

        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        return $dompdf->stream("Comprovantes.pdf", [
            "Attachment" => false
        ]);
    }

    public function imprimirComprovante($id){
        $item = ContaReceber::findOrFail($id);
        __validaObjetoEmpresa($item);

        $p = view('conta-receber.imprimir_comprovante', compact('item'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper([0,0,204,284]);
        $domPdf->render();
        header("Content-Disposition: ; filename=Pedido.pdf");
        $domPdf->stream("Comprovante.pdf", array("Attachment" => false));

    }

    public function ajustar(Request $request){
        $tipo = $request->tipo;
        $venda_id = $request->venda_id;
        $troca_id = $request->troca_id;

        if($tipo == 'nfce'){
            $venda = Nfce::findOrFail($venda_id);
        }else{
            $venda = Nfe::findOrFail($venda_id);
        }

        $troca = Troca::findOrFail($troca_id);
        $contas = $venda->contaReceber;

        return view('conta-receber.ajustar_parcelas_troca', compact('troca', 'venda', 'contas'));
    }

    public function ajustarSave(Request $request){
        DB::beginTransaction();

        try {

            $valores = $request->valor ?? [];
            $datas = $request->data_vencimento ?? [];
            $tiposPagamento = $request->tipo_pagamento ?? [];
            $ids = $request->conta_id ?? [];
            $utimaConta = null;

            $padraoDescricao = null;

            for ($i = 0; $i < sizeof($valores); $i++) {

                $valor = __convert_value_bd($valores[$i]);

                if ($valor <= 0) {
                    continue;
                }

                if(!$ids[$i] || $ids[$i] == '0'){
                    //criar conta

                    if($utimaConta){
                        ContaReceber::create([
                            'empresa_id' => $utimaConta->empresa_id,
                            'tipo_pagamento' => $tiposPagamento[$i],
                            'data_vencimento' => $datas[$i],
                            'valor_integral' => $valor,
                            'status' => 0,
                            'nfe_id' => $utimaConta->nfe_id,
                            'nfce_id' => $utimaConta->nfce_id,
                            'local_id' => $utimaConta->local_id,
                            'cliente_id' => $utimaConta->cliente_id,
                            'categoria_id' => $utimaConta->categoria_id,
                            'descricao' => $padraoDescricao . " Parcela " . $i+1 . " de " . sizeof($valores),
                        ]);
                    }

                }else{
                    $utimaConta = $conta = ContaReceber::findOrFail($ids[$i]);
                    if($padraoDescricao == null){
                        $padraoDescricao = explode("Parcela", $utimaConta->descricao);
                        $padraoDescricao = $padraoDescricao[0];
                    }

                    if ($valor <= 0) {
                        if ($conta->status == 1) {
                            continue;
                        }
                        $conta->delete();
                        continue;
                    }


                    $conta->valor_integral = $valor;
                    $conta->data_vencimento = $datas[$i];
                    $conta->tipo_pagamento = $tiposPagamento[$i];
                    $conta->descricao = $padraoDescricao . " Parcela " . $i+1 . " de " . sizeof($valores);
                    $conta->save();
                }

            }

            DB::commit();
            session()->flash("flash_success", "Contas ajustadas com sucesso!");
            return redirect()->route('trocas.index');

        } catch (Exception $e) {

            DB::rollBack();
            session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
            return redirect()->back();
        }
    }
}
