<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;
use App\Models\PontoRegistro;
use App\Models\PontoFuncionario;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PontoRelatorioController extends Controller
{
    public function index(Request $request)
    {
        $empresa_id = $request->empresa_id;

        $funcionarios = Funcionario::where('empresa_id', $empresa_id)
        ->where('bater_ponto', 1)
        ->where('status', 1)
        ->orderBy('nome')
        ->get();

        $relatorio = [];
        $resumo = null;
        $funcionario = null;

        if ($request->filled('mes') && $request->filled('funcionario_id')) {

            $funcionario = Funcionario::where('empresa_id', $empresa_id)
            ->findOrFail($request->funcionario_id);

            $mesInput = $request->mes;

            if (str_contains($mesInput, '/')) {
                [$mesNum, $ano] = explode('/', $mesInput);

                $inicioMes = Carbon::createFromDate($ano, $mesNum, 1)->startOfMonth();
                $fimMes = Carbon::createFromDate($ano, $mesNum, 1)->endOfMonth();
            } else {
                $inicioMes = Carbon::parse($mesInput . '-01')->startOfMonth();
                $fimMes = Carbon::parse($mesInput . '-01')->endOfMonth();
            }

            $vinculoPonto = PontoFuncionario::with(['jornada.dias'])
            ->where('empresa_id', $empresa_id)
            ->where('funcionario_id', $funcionario->id)
            ->whereDate('data_inicio', '<=', $fimMes)
            ->where(function ($q) use ($inicioMes) {
                $q->whereNull('data_fim')
                ->orWhereDate('data_fim', '>=', $inicioMes);
            })
            ->first();

            if (!$vinculoPonto) {
                session()->flash('flash_error', 'Funcionário não possui jornada vinculada nesse período.');
                return view('ponto_relatorio.index', compact(
                    'funcionarios',
                    'relatorio',
                    'resumo',
                    'funcionario'
                ));
            }

            $registros = PontoRegistro::where('empresa_id', $empresa_id)
            ->where('funcionario_id', $funcionario->id)
            ->whereBetween('data_hora', [$inicioMes->copy()->startOfDay(), $fimMes->copy()->endOfDay()])
            ->orderBy('data_hora')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->data_hora)->format('Y-m-d');
            });

            $totalMinutosTrabalhados = 0;
            $totalMinutosPrevistos = 0;
            $totalMinutosExtras = 0;
            $totalMinutosFaltas = 0;

            foreach (CarbonPeriod::create($inicioMes, $fimMes) as $dia) {

                $data = $dia->format('Y-m-d');

                // Carbon: 0 domingo, 1 segunda...
                $diaSemana = $dia->dayOfWeek;

                $jornadaDia = $vinculoPonto->jornada->dias
                ->where('dia_semana', $diaSemana)
                ->first();

                $batidasDia = $registros->get($data, collect());

                $entrada = optional($batidasDia->firstWhere('tipo', 'entrada'))->data_hora;
                $intervaloInicio = optional($batidasDia->firstWhere('tipo', 'intervalo_inicio'))->data_hora;
                $intervaloFim = optional($batidasDia->firstWhere('tipo', 'intervalo_fim'))->data_hora;
                $saida = optional($batidasDia->firstWhere('tipo', 'saida'))->data_hora;

                $minutosPrevistos = 0;
                $minutosTrabalhados = 0;
                $minutosExtras = 0;
                $minutosFaltas = 0;
                $status = 'Sem jornada';

                if ($jornadaDia && $jornadaDia->entrada && $jornadaDia->saida) {

                    $prevEntrada = Carbon::parse($data . ' ' . $jornadaDia->entrada);
                    $prevSaida = Carbon::parse($data . ' ' . $jornadaDia->saida);

                    $minutosPrevistos = $prevEntrada->diffInMinutes($prevSaida);

                    if ($jornadaDia->intervalo_inicio && $jornadaDia->intervalo_fim) {
                        $prevIntervaloInicio = Carbon::parse($data . ' ' . $jornadaDia->intervalo_inicio);
                        $prevIntervaloFim = Carbon::parse($data . ' ' . $jornadaDia->intervalo_fim);

                        $minutosPrevistos -= $prevIntervaloInicio->diffInMinutes($prevIntervaloFim);
                    } else {
                        $minutosPrevistos -= (int) $vinculoPonto->jornada->intervalo_minutos;
                    }

                    if ($entrada && $saida) {
                        $realEntrada = Carbon::parse($entrada);
                        $realSaida = Carbon::parse($saida);

                        $minutosTrabalhados = $realEntrada->diffInMinutes($realSaida);

                        if ($intervaloInicio && $intervaloFim) {
                            $realIntervaloInicio = Carbon::parse($intervaloInicio);
                            $realIntervaloFim = Carbon::parse($intervaloFim);

                            $minutosTrabalhados -= $realIntervaloInicio->diffInMinutes($realIntervaloFim);
                        }

                        $limiteExtra = (int) $vinculoPonto->jornada->hora_extra_apos_minutos;

                        if ($minutosTrabalhados > ($minutosPrevistos + $limiteExtra)) {
                            $minutosExtras = $minutosTrabalhados - $minutosPrevistos;
                        }

                        if ($minutosTrabalhados < $minutosPrevistos) {
                            $minutosFaltas = $minutosPrevistos - $minutosTrabalhados;
                        }

                        $status = 'Completo';
                    } else if ($batidasDia->count() > 0) {
                        $status = 'Incompleto';
                    } else {
                        $status = 'Falta';
                        $minutosFaltas = $minutosPrevistos;
                    }
                }

                $totalMinutosTrabalhados += $minutosTrabalhados;
                $totalMinutosPrevistos += $minutosPrevistos;
                $totalMinutosExtras += $minutosExtras;
                $totalMinutosFaltas += $minutosFaltas;

                $relatorio[] = [
                    'data' => $dia->format('d/m/Y'),
                    'dia_semana' => $this->diaSemana($dia->dayOfWeek),
                    'entrada' => $entrada ? Carbon::parse($entrada)->format('H:i') : '-',
                    'intervalo_inicio' => $intervaloInicio ? Carbon::parse($intervaloInicio)->format('H:i') : '-',
                    'intervalo_fim' => $intervaloFim ? Carbon::parse($intervaloFim)->format('H:i') : '-',
                    'saida' => $saida ? Carbon::parse($saida)->format('H:i') : '-',
                    'previsto' => $this->minutosParaHora($minutosPrevistos),
                    'trabalhado' => $this->minutosParaHora($minutosTrabalhados),
                    'extra' => $this->minutosParaHora($minutosExtras),
                    'falta' => $this->minutosParaHora($minutosFaltas),
                    'status' => $status,
                ];
            }

            $valorHoraExtra = $funcionario->valor_hora_extra ?? 0;
            $valorTotalExtra = ($totalMinutosExtras / 60) * $valorHoraExtra;

            $resumo = [
                'salario' => $funcionario->salario ?? 0,
                'valor_hora_extra' => $valorHoraExtra,
                'total_previsto' => $this->minutosParaHora($totalMinutosPrevistos),
                'total_trabalhado' => $this->minutosParaHora($totalMinutosTrabalhados),
                'total_extra' => $this->minutosParaHora($totalMinutosExtras),
                'total_falta' => $this->minutosParaHora($totalMinutosFaltas),
                'valor_total_extra' => $valorTotalExtra,
                'total_com_extra' => ($funcionario->salario ?? 0) + $valorTotalExtra,
            ];
        }

        return view('ponto_relatorio.index', compact(
            'funcionarios',
            'relatorio',
            'resumo',
            'funcionario'
        ));
    }

    private function minutosParaHora($minutos)
    {
        $horas = floor($minutos / 60);
        $mins = $minutos % 60;

        return str_pad($horas, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT);
    }

    private function diaSemana($dia)
    {
        return [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
        ][$dia];
    }
}