<?php

namespace App\Http\Controllers;

use App\Models\ApuracaoMensal;
use App\Models\ApuracaoMensalEvento;
use App\Models\ContaPagar;
use App\Models\EventoSalario;
use App\Models\Funcionario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PontoRegistro;
use App\Models\PontoFuncionario;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\ApuracaoMensalPonto;
use Dompdf\Dompdf;
use Dompdf\Options;

class ApuracaoMensalController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:apuracao_mensal_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:apuracao_mensal_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:apuracao_mensal_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:apuracao_mensal_delete', ['only' => ['destroy']]);
    }
    
    public function index(Request $request)
    {
        $funcionario_id = $request->funcionario_id;
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $data = ApuracaoMensal::select('apuracao_mensals.*')
        ->join('funcionarios', 'apuracao_mensals.funcionario_id', '=', 'funcionarios.id')
        ->where('empresa_id', request()->empresa_id)
        ->when(!empty($funcionario_id), function ($query) use ($funcionario_id) {
            return $query->where('funcionarios.id', $funcionario_id);
        })
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('apuracao_mensals.created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('apuracao_mensals.created_at', '<=', $end_date);
        })
        ->orderBy('id', 'desc')
        ->paginate(__itensPagina());

        $funcionario = null;
        if($funcionario_id){
            $funcionario = Funcionario::findOrFail($funcionario_id);
        }

        return view('apuracao_mensal.index', compact('data', 'funcionario'));
    }

    public function create()
    {
        $funcionarios = Funcionario::orderBy('nome')
        ->where('empresa_id', request()->empresa_id)
        ->get();
        $mesAtual = (int)date('m') - 1;
        return view('apuracao_mensal.create', compact('mesAtual', 'funcionarios'));
    }

    // public function getEventos($id)
    // {
    //     try {
    //         $item = Funcionario::findOrFail($id);
    //         if (sizeof($item->eventos) == 0) {
    //             return response()->json("", 200);
    //         }
    //         return view('apuracao_mensal.eventos', compact('item'));
    //     } catch (\Exception $e) {
    //         return response()->json($e->getMessage(), 401);
    //     }
    // }

    public function getEventos($id, Request $request)
    {
        try {
            $item = Funcionario::with([
                'eventosAtivos.evento'
            ])->findOrFail($id);

            $mesInput = $request->mes ?? now()->month;
            $ano = (int) ($request->ano ?? now()->year);

            $meses = [
                'Janeiro' => 1,
                'Fevereiro' => 2,
                'Março' => 3,
                'Marco' => 3,
                'Abril' => 4,
                'Maio' => 5,
                'Junho' => 6,
                'Julho' => 7,
                'Agosto' => 8,
                'Setembro' => 9,
                'Outubro' => 10,
                'Novembro' => 11,
                'Dezembro' => 12,
            ];

            if (is_numeric($mesInput)) {
                $mesNum = (int) $mesInput;
            } elseif (isset($meses[$mesInput])) {
                $mesNum = $meses[$mesInput];
            } elseif (str_contains($mesInput, '/')) {
                [$mesNum, $ano] = explode('/', $mesInput);
                $mesNum = (int) $mesNum;
                $ano = (int) $ano;
            } elseif (str_contains($mesInput, '-')) {
                $dataMes = Carbon::parse($mesInput . '-01');
                $mesNum = $dataMes->month;
                $ano = $dataMes->year;
            } else {
                $mesNum = now()->month;
            }

            $inicioMes = Carbon::createFromDate($ano, $mesNum, 1)->startOfMonth();
            $fimMes = Carbon::createFromDate($ano, $mesNum, 1)->endOfMonth();

            $vinculoPonto = PontoFuncionario::with(['jornada.dias'])
            ->where('empresa_id', $item->empresa_id)
            ->where('funcionario_id', $item->id)
            ->whereDate('data_inicio', '<=', $fimMes)
            ->where(function ($q) use ($inicioMes) {
                $q->whereNull('data_fim')
                ->orWhereDate('data_fim', '>=', $inicioMes);
            })
            ->first();

            $registros = PontoRegistro::where('empresa_id', $item->empresa_id)
            ->where('funcionario_id', $id)
            ->whereBetween('data_hora', [
                $inicioMes->copy()->startOfDay(),
                $fimMes->copy()->endOfDay()
            ])
            ->orderBy('data_hora')
            ->get();

            $pontosPorDia = $registros->groupBy(function ($r) {
                return Carbon::parse($r->data_hora)->format('Y-m-d');
            });

            $diasSemana = [
                0 => 'Domingo',
                1 => 'Segunda-feira',
                2 => 'Terça-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'Sábado',
            ];

            $relatorioPonto = [];

            $faltas = 0;
            $diasComPonto = 0;
            $diasComExtra = 0;
            $diasIncompletos = 0;

            $totalMinutosPrevistos = 0;
            $totalMinutosTrabalhados = 0;
            $totalMinutosExtras = 0;
            $totalMinutosFaltas = 0;
            $totalMinutosAtrasos = 0;
            $totalMinutosSaidaAntecipada = 0;

            foreach (CarbonPeriod::create($inicioMes, $fimMes) as $diaMes) {

                $data = $diaMes->format('Y-m-d');
                $diaSemana = $diaMes->dayOfWeek;

                $batidasDia = $pontosPorDia->get($data, collect());

                $entrada = $batidasDia->firstWhere('tipo', 'entrada');
                $intervaloInicio = $batidasDia->firstWhere('tipo', 'intervalo_inicio');
                $intervaloFim = $batidasDia->firstWhere('tipo', 'intervalo_fim');
                $saida = $batidasDia->firstWhere('tipo', 'saida');

                $jornadaDia = null;

                if ($vinculoPonto && $vinculoPonto->jornada) {
                    $jornadaDia = $vinculoPonto->jornada->dias
                    ->where('dia_semana', $diaSemana)
                    ->first();
                }

                $minutosPrevistos = 0;
                $minutosTrabalhados = 0;
                $minutosExtrasDia = 0;
                $minutosFaltasDia = 0;
                $minutosAtrasoDia = 0;
                $minutosSaidaAntecipadaDia = 0;

                $statusDia = 'Sem jornada';

                if ($jornadaDia && $jornadaDia->entrada && $jornadaDia->saida) {

                    $prevEntrada = Carbon::parse($data . ' ' . $jornadaDia->entrada);
                    $prevSaida = Carbon::parse($data . ' ' . $jornadaDia->saida);

                    $minutosPrevistos = $prevEntrada->diffInMinutes($prevSaida);

                    if ($jornadaDia->intervalo_inicio && $jornadaDia->intervalo_fim) {
                        $prevIntervaloInicio = Carbon::parse($data . ' ' . $jornadaDia->intervalo_inicio);
                        $prevIntervaloFim = Carbon::parse($data . ' ' . $jornadaDia->intervalo_fim);

                        $minutosPrevistos -= $prevIntervaloInicio->diffInMinutes($prevIntervaloFim);
                    } else {
                        $minutosPrevistos -= (int) ($vinculoPonto->jornada->intervalo_minutos ?? 0);
                    }

                    if ($entrada && $saida) {

                        $realEntrada = Carbon::parse($entrada->data_hora);
                        $realSaida = Carbon::parse($saida->data_hora);

                        $tolerancia = (int) ($vinculoPonto->jornada->tolerancia_atraso ?? 0);

                        if (
                            $realEntrada->gt($prevEntrada) &&
                            $realEntrada->diffInMinutes($prevEntrada) <= $tolerancia
                        ) {
                            $realEntrada = $prevEntrada->copy();
                        }

                        if (
                            $realSaida->lt($prevSaida) &&
                            $realSaida->diffInMinutes($prevSaida) <= $tolerancia
                        ) {
                            $realSaida = $prevSaida->copy();
                        }

                        if ($realEntrada->gt($prevEntrada)) {
                            $minutosAtrasoDia = $realEntrada->diffInMinutes($prevEntrada);
                        }

                        if ($realSaida->lt($prevSaida)) {
                            $minutosSaidaAntecipadaDia = $realSaida->diffInMinutes($prevSaida);
                        }

                        $minutosTrabalhados = $realEntrada->diffInMinutes($realSaida);

                        if ($intervaloInicio && $intervaloFim) {
                            $realIntervaloInicio = Carbon::parse($intervaloInicio->data_hora);
                            $realIntervaloFim = Carbon::parse($intervaloFim->data_hora);

                            $minutosTrabalhados -= $realIntervaloInicio->diffInMinutes($realIntervaloFim);
                        } elseif ((int) ($vinculoPonto->jornada->intervalo_minutos ?? 0) > 0) {
                            $minutosTrabalhados -= (int) $vinculoPonto->jornada->intervalo_minutos;
                        }

                        if ($minutosTrabalhados < 0) {
                            $minutosTrabalhados = 0;
                        }

                        $limiteExtra = (int) ($vinculoPonto->jornada->hora_extra_apos_minutos ?? 0);

                        if ($minutosTrabalhados > ($minutosPrevistos + $limiteExtra)) {
                            $minutosExtrasDia = $minutosTrabalhados - $minutosPrevistos;
                        }

                        if ($minutosTrabalhados < $minutosPrevistos) {
                            $minutosFaltasDia = $minutosPrevistos - $minutosTrabalhados;
                        }

                        $statusDia = 'Completo';

                        if ($minutosExtrasDia > 0) {
                            $diasComExtra++;
                        }

                        $diasComPonto++;

                    } else {

                        $minutosFaltasDia = $minutosPrevistos;

                        if ($batidasDia->count() > 0) {
                            $diasIncompletos++;
                            $diasComPonto++;
                            $statusDia = 'Incompleto';
                        } else {
                            $faltas++;
                            $statusDia = 'Falta';
                        }
                    }
                } else {
                    if ($batidasDia->count() > 0) {
                        $diasComPonto++;
                        $statusDia = 'Sem jornada';
                    }
                }

                if ($minutosPrevistos == 0 && $batidasDia->count() == 0) {
                    continue;
                }

                $totalMinutosPrevistos += $minutosPrevistos;
                $totalMinutosTrabalhados += $minutosTrabalhados;
                $totalMinutosExtras += $minutosExtrasDia;
                $totalMinutosFaltas += $minutosFaltasDia;
                $totalMinutosAtrasos += $minutosAtrasoDia;
                $totalMinutosSaidaAntecipada += $minutosSaidaAntecipadaDia;

                $relatorioPonto[] = [
                    'data' => $diaMes->format('d/m/Y'),
                    'dia_semana' => $diasSemana[$diaSemana],

                    'entrada' => $entrada ? Carbon::parse($entrada->data_hora)->format('H:i') : '-',
                    'intervalo_inicio' => $intervaloInicio ? Carbon::parse($intervaloInicio->data_hora)->format('H:i') : '-',
                    'intervalo_fim' => $intervaloFim ? Carbon::parse($intervaloFim->data_hora)->format('H:i') : '-',
                    'saida' => $saida ? Carbon::parse($saida->data_hora)->format('H:i') : '-',

                    'horas_previstas' => $this->minutosParaHora($minutosPrevistos),
                    'horas_trabalhadas' => $this->minutosParaHora($minutosTrabalhados),
                    'horas_extras' => $this->minutosParaHora($minutosExtrasDia),
                    'horas_faltas' => $this->minutosParaHora($minutosFaltasDia),
                    'atraso' => $this->minutosParaHora($minutosAtrasoDia),
                    'saida_antecipada' => $this->minutosParaHora($minutosSaidaAntecipadaDia),

                    'tem_extra' => $minutosExtrasDia > 0,
                    'status' => $statusDia,
                ];
            }

            $mes = $mesNum;

            $horasExtras = $this->minutosParaHora($totalMinutosExtras);
            $totalHorasTrabalhadas = $this->minutosParaHora($totalMinutosTrabalhados);
            $totalHorasPrevistas = $this->minutosParaHora($totalMinutosPrevistos);
            $totalHorasFaltas = $this->minutosParaHora($totalMinutosFaltas);
            $totalHorasAtrasos = $this->minutosParaHora($totalMinutosAtrasos);
            $totalHorasSaidaAntecipada = $this->minutosParaHora($totalMinutosSaidaAntecipada);

            $saldoMinutos = $totalMinutosTrabalhados - $totalMinutosPrevistos;
            $saldoHoras = $this->minutosParaHora(abs($saldoMinutos));

            $totalMinutosExtrasApuracao = $totalMinutosExtras;
            $horasExtrasDecimal = round($totalMinutosExtras / 60, 2);

            $horasFaltasDecimal = round($totalMinutosFaltas / 60, 2);

            $valorHoraFuncionario = 0;
            $valorDescontoFaltas = 0;

            if ($totalMinutosPrevistos > 0) {
                $valorHoraFuncionario = round($item->salario / ($totalMinutosPrevistos / 60), 4);
                $valorDescontoFaltas = round($valorHoraFuncionario * $horasFaltasDecimal, 2);
            }

            return view('apuracao_mensal.eventos', compact(
                'item',
                'registros',
                'pontosPorDia',
                'relatorioPonto',
                'faltas',
                'diasComPonto',
                'diasComExtra',
                'diasIncompletos',
                'horasExtras',
                'totalHorasTrabalhadas',
                'totalHorasPrevistas',
                'totalHorasFaltas',
                'totalHorasAtrasos',
                'totalHorasSaidaAntecipada',
                'saldoMinutos',
                'saldoHoras',
                'mes',
                'ano',
                'inicioMes',
                'fimMes',
                'vinculoPonto',
                'totalMinutosExtrasApuracao',
                'horasExtrasDecimal',
                'horasFaltasDecimal',
                'valorHoraFuncionario',
                'valorDescontoFaltas'
            ));

        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    private function minutosParaHora($minutos)
    {
        $minutos = (int) $minutos;

        if ($minutos < 0) {
            $minutos = abs($minutos);
        }

        $horas = floor($minutos / 60);
        $mins = $minutos % 60;

        return str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        try {
            $ap = DB::transaction(function () use ($request) {

                $ap = [
                    'funcionario_id' => $request->funcionario_id,
                    'mes' => $request->mes,
                    'ano' => $request->ano,
                    'valor_final' => __convert_value_bd($request->valor_total),
                    'forma_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? '',

                    'horas_previstas' => $request->horas_previstas ?? '00:00',
                    'horas_trabalhadas' => $request->horas_trabalhadas ?? '00:00',
                    'horas_extras' => $request->horas_extras ?? '00:00',
                    'horas_faltas' => $request->horas_faltas ?? '00:00',
                    'horas_atrasos' => $request->horas_atrasos ?? '00:00',
                    'horas_saida_antecipada' => $request->horas_saida_antecipada ?? '00:00',
                    'saldo_horas' => $request->saldo_horas ?? '00:00',
                    'saldo_minutos' => $request->saldo_minutos ?? 0,

                    'faltas' => $request->faltas ?? 0,
                    'dias_com_ponto' => $request->dias_com_ponto ?? 0,
                    'dias_com_extra' => $request->dias_com_extra ?? 0,
                    'dias_incompletos' => $request->dias_incompletos ?? 0,
                ];

                $result = ApuracaoMensal::create($ap);

                if (!empty($request->evento)) {
                    for ($i = 0; $i < count($request->evento); $i++) {

                        $ev = EventoSalario::find($request->evento[$i]);

                        if ($ev) {
                            $valorBase = __convert_value_bd($request->valor[$i] ?? 0);

                            $valorCalculado = __convert_value_bd(
                                $request->valor_total_evento[$i] ?? $request->valor[$i] ?? 0
                            );

                            ApuracaoMensalEvento::create([
                                'apuracao_id' => $result->id,
                                'evento_id' => $ev->id,

                            // mantém compatibilidade
                                'valor' => $valorCalculado,

                            // novos campos
                                'valor_base' => $valorBase,
                                'valor_calculado' => $valorCalculado,
                                'quantidade_referencia' => $request->quantidade_referencia[$i] ?? 1,
                                'tipo_referencia' => $request->tipo_referencia[$i] ?? null,
                                'calculado_automaticamente' => !empty($request->calculado_automaticamente[$i]) ? 1 : 0,

                                'metodo' => $request->metodo[$i],
                                'condicao' => $request->condicao[$i],
                                'nome' => $ev->nome,
                            ]);
                        }
                    }
                }

                if (!empty($request->ponto_data)) {
                    for ($i = 0; $i < count($request->ponto_data); $i++) {
                        ApuracaoMensalPonto::create([
                            'apuracao_id' => $result->id,
                            'data' => $request->ponto_data[$i],
                            'dia_semana' => $request->ponto_dia_semana[$i] ?? '',

                            'entrada' => $request->ponto_entrada[$i] ?? null,
                            'intervalo_inicio' => $request->ponto_intervalo_inicio[$i] ?? null,
                            'intervalo_fim' => $request->ponto_intervalo_fim[$i] ?? null,
                            'saida' => $request->ponto_saida[$i] ?? null,

                            'horas_previstas' => $request->ponto_horas_previstas[$i] ?? '00:00',
                            'horas_trabalhadas' => $request->ponto_horas_trabalhadas[$i] ?? '00:00',
                            'horas_extras' => $request->ponto_horas_extras[$i] ?? '00:00',
                            'horas_faltas' => $request->ponto_horas_faltas[$i] ?? '00:00',
                            'atraso' => $request->ponto_atraso[$i] ?? '00:00',
                            'saida_antecipada' => $request->ponto_saida_antecipada[$i] ?? '00:00',
                            'status' => $request->ponto_status[$i] ?? '',
                        ]);
                    }
                }

                return $result;
            });

__createLog(
    $request->empresa_id,
    'Apuração Mensal',
    'cadastrar',
    $ap->funcionario->nome . " - $ap->mes/$ap->ano"
);

session()->flash("flash_success", "Apuração criada!");

} catch (\Exception $e) {
    __createLog($request->empresa_id, 'Apuração Mensal', 'erro', $e->getMessage());
    session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
}

return redirect()->route('apuracao-mensal.index');
}

public function contaPagar($id)
{
    $item = ApuracaoMensal::findOrFail($id);

    return view('apuracao_mensal.conta_pagar', compact('item'));
}

public function setConta(Request $request, $id)
{
    try {
        $item = ApuracaoMensal::findOrFail($id);

        $local_id = null;
        $caixa = __isCaixaAberto();
        if($caixa != null){
            $local_id = $caixa->local_id;
        }else{
            $local_id = __getLocalAtivo()->id;
        }

        $conta = [
            'compra_id' => null,
            'data_vencimento' => $request->data_vencimento,
            'valor_integral' => str_replace(",", ".", $request->valor_integral),
            'valor_pago' => $request->status ? __convert_value_bd($request->valor_pago) : 0,
            'status' => $request->status,
            'descricao' => $request->descricao,
            'tipo_pagamento' => $request->tipo_pagamento ?? '',
            'fornecedor_id' => null,
            'empresa_id' => request()->empresa_id,
            'local_id' => $local_id
        ];
        $result = ContaPagar::create($conta);

        $item->conta_pagar_id = $result->id;
        $item->save();
        session()->flash("flash_success", "Adicionado em contas a pagar!");
    } catch (\Exception $e) {
        session()->flash('flash_error', 'Algo deu errado: ' . $e->getMessage());
    }
    return redirect()->route('apuracao-mensal.index');
}

public function show($id)
{
    $item = ApuracaoMensal::findOrFail($id);

    return view('apuracao_mensal.show', compact('item'));
}

public function destroy($id)
{
    $item = ApuracaoMensal::findOrFail($id);
    try {
        $descricaoLog = $item->funcionario->nome . " - $item->mes/$item->ano";

        if($item->contaPagar){
            $item->contaPagar->delete();
        }
        $item->eventos()->delete();
        $item->delete();
        __createLog(request()->empresa_id, 'Apuração Mensal', 'excluir', $descricaoLog);

        session()->flash("flash_success", "Registro removido!");
    } catch (\Exception $e) {
        __createLog(request()->empresa_id, 'Apuração Mensal', 'erro', $e->getMessage());
        session()->flash("flash_error", "Algo deu Errado: " . $e->getMessage());
    }
    return redirect()->back();
}

public function pdf($id)
{
    $item = ApuracaoMensal::with([
        'funcionario',
        'eventos',
        'pontos'
    ])->findOrFail($id);

    $html = view('apuracao_mensal.pdf', compact('item'))->render();

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->stream(
        'apuracao-' . $item->funcionario->nome . '-' . $item->mes . '-' . $item->ano . '.pdf',
        ['Attachment' => false]
    );
}

}