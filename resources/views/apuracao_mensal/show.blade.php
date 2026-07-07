@extends('layouts.app', ['title' => 'Apuração mensal'])

@section('css')
<style>
    @page { size: auto; margin: 0mm; }

    @media print {
        .print {
            margin: 10px;
        }

        .d-print-none {
            display: none !important;
        }
    }

    .resumo-card {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 14px;
        background: #fafafa;
        height: 100%;
    }

    .resumo-card small {
        color: #777;
        display: block;
    }

    .resumo-card strong {
        font-size: 18px;
    }

    .table-sm td,
    .table-sm th {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="mt-1 print">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="clearfix">
                    <div class="float-start mb-3">
                        <h4 class="m-0">Apuração mensal</h4>
                    </div>

                    <div class="float-end">
                        <h4 class="m-0">{{ $item->funcionario->nome }}</h4>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-sm-6">
                        <p>
                            <b>Mês/Ano:</b>
                            <strong class="text-primary">{{ $item->mes }}/{{ $item->ano }}</strong>
                        </p>

                        <p>
                            <b>Tipo de pagamento:</b>
                            <strong class="text-primary">{{ $item->forma_pagamento }}</strong>
                        </p>
                    </div>

                    <div class="col-sm-6 text-sm-end">
                        <p>
                            <b>Data de registro:</b>
                            <strong class="text-primary">{{ __data_pt($item->created_at) }}</strong>
                        </p>

                        @if($item->observacao)
                            <p>
                                <b>Observação:</b>
                                <strong class="text-primary">{{ $item->observacao }}</strong>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row mt-4 g-3">
                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Horas previstas</small>
                            <strong>{{ $item->horas_previstas ?? '00:00' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Horas trabalhadas</small>
                            <strong>{{ $item->horas_trabalhadas ?? '00:00' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Horas extras</small>
                            <strong class="text-success">{{ $item->horas_extras ?? '00:00' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Saldo mensal</small>

                            @if(($item->saldo_minutos ?? 0) > 0)
                                <strong class="text-success">+{{ $item->saldo_horas }}</strong>
                            @elseif(($item->saldo_minutos ?? 0) < 0)
                                <strong class="text-danger">-{{ $item->saldo_horas }}</strong>
                            @else
                                <strong>00:00</strong>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Horas faltantes</small>
                            <strong class="text-danger">{{ $item->horas_faltas ?? '00:00' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Atrasos</small>
                            <strong class="text-warning">{{ $item->horas_atrasos ?? '00:00' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Saída antecipada</small>
                            <strong class="text-danger">{{ $item->horas_saida_antecipada ?? '00:00' }}</strong>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="resumo-card">
                            <small>Faltas / incompletos</small>
                            <strong>{{ $item->faltas ?? 0 }} / {{ $item->dias_incompletos ?? 0 }}</strong>
                        </div>
                    </div>
                </div>

                <h5 class="mt-4 mb-2">
                    <i class="ri-money-dollar-circle-line"></i> Eventos da apuração
                </h5>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Evento</th>
                                <th>Condição</th>
                                <th>Método</th>
                                <th>Base</th>
                                <th>Qtd.</th>
                                <th>Tipo ref.</th>
                                <th>Valor calculado</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($item->eventos as $i)
                                <tr>
                                    <td>{{ $i->nome }}</td>

                                    <td>
                                        @if($i->condicao == 'soma')
                                            <span class="badge bg-success">Soma</span>
                                        @else
                                            <span class="badge bg-danger">Diminui</span>
                                        @endif
                                    </td>

                                    <td>{{ ucfirst($i->metodo) }}</td>

                                    <td>R$ {{ __moeda($i->valor_base ?? $i->valor) }}</td>

                                    <td>
                                        @if($i->quantidade_referencia && $i->quantidade_referencia > 1)
                                            {{ number_format($i->quantidade_referencia, 2, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if($i->tipo_referencia)
                                            {{ str_replace('_', ' ', $i->tipo_referencia) }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        <strong>
                                            R$ {{ __moeda($i->valor_calculado ?? $i->valor) }}
                                        </strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end text-success">
                                    <strong>Total</strong>
                                </td>
                                <td class="text-success">
                                    <strong>R$ {{ __moeda($item->valor_final) }}</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($item->pontos && $item->pontos->count() > 0)
                    <h5 class="mt-4 mb-2">
                        <i class="ri-time-line"></i> Espelho de ponto
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Dia</th>
                                    <th>Entrada</th>
                                    <th>Intervalo início</th>
                                    <th>Intervalo fim</th>
                                    <th>Saída</th>
                                    <th>Previstas</th>
                                    <th>Trabalhadas</th>
                                    <th>Extras</th>
                                    <th>Atraso</th>
                                    <th>Saída ant.</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($item->pontos as $ponto)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($ponto->data)->format('d/m/Y') }}</td>
                                        <td>{{ $ponto->dia_semana }}</td>
                                        <td>{{ $ponto->entrada ?: '-' }}</td>
                                        <td>{{ $ponto->intervalo_inicio ?: '-' }}</td>
                                        <td>{{ $ponto->intervalo_fim ?: '-' }}</td>
                                        <td>{{ $ponto->saida ?: '-' }}</td>
                                        <td>{{ $ponto->horas_previstas }}</td>
                                        <td><strong>{{ $ponto->horas_trabalhadas }}</strong></td>

                                        <td>
                                            @if($ponto->horas_extras != '00:00')
                                                <span class="badge bg-success">{{ $ponto->horas_extras }}</span>
                                            @else
                                                <span class="text-muted">00:00</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($ponto->atraso != '00:00')
                                                <span class="badge bg-warning text-dark">{{ $ponto->atraso }}</span>
                                            @else
                                                <span class="text-muted">00:00</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($ponto->saida_antecipada != '00:00')
                                                <span class="badge bg-danger">{{ $ponto->saida_antecipada }}</span>
                                            @else
                                                <span class="text-muted">00:00</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($ponto->status == 'Completo')
                                                <span class="badge bg-success">Completo</span>
                                            @elseif($ponto->status == 'Falta')
                                                <span class="badge bg-danger">Falta</span>
                                            @elseif($ponto->status == 'Incompleto')
                                                <span class="badge bg-warning text-dark">Incompleto</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $ponto->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="d-print-none mt-4">
                    <div class="text-end">
                        <a href="{{ route('apuracao-mensal.pdf', [$item->id]) }}" target="_blank" class="btn btn-primary">
                            <i class="ri-printer-line"></i> Imprimir
                        </a>

                        <a href="{{ route('apuracao-mensal.index') }}" class="btn btn-secondary">
                            Voltar
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection