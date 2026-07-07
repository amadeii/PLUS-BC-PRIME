@extends('layouts.app', ['title' => 'Relatório de Ponto'])

@section('content')

<style>
    .ponto-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 8px 28px rgba(32, 33, 36, .08);
        border: 1px solid #eef0f7;
        margin-bottom: 8px;
    }

    .ponto-title {
        font-size: 22px;
        font-weight: 800;
        color: #25284a;
        margin-bottom: 4px;
    }

    .ponto-subtitle {
        color: #7b7f95;
        font-size: 14px;
        margin-bottom: 18px;
    }

    .ponto-resumo {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .ponto-box {
        background: linear-gradient(135deg, #f7f8ff, #ffffff);
        border: 1px solid #eceefe;
        border-radius: 16px;
        padding: 16px;
    }

    .ponto-box span {
        display: block;
        font-size: 12px;
        color: #7b7f95;
        margin-bottom: 6px;
    }

    .ponto-box strong {
        font-size: 20px;
        color: #4254BA;
        font-weight: 800;
    }

    .ponto-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .ponto-table thead th {
        font-size: 12px;
        color: #7b7f95;
        text-transform: uppercase;
        padding: 10px;
        border-bottom: 1px solid #eef0f7;
    }

    .ponto-table tbody tr {
        background: #fff;
        box-shadow: 0 3px 12px rgba(32, 33, 36, .05);
    }

    .ponto-table tbody td {
        padding: 12px 10px;
        font-size: 13px;
        vertical-align: middle;
        border-top: 1px solid #f0f1f7;
        border-bottom: 1px solid #f0f1f7;
    }

    .ponto-table tbody td:first-child {
        border-left: 1px solid #f0f1f7;
        border-radius: 12px 0 0 12px;
        font-weight: 700;
    }

    .ponto-table tbody td:last-child {
        border-right: 1px solid #f0f1f7;
        border-radius: 0 12px 12px 0;
    }

    .badge-ponto {
        padding: 6px 10px;
        border-radius: 30px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-completo {
        background: #eef7ff;
        color: #2364aa;
    }

    .badge-falta {
        background: #fff0f0;
        color: #c03636;
    }

    .badge-incompleto {
        background: #fff7e6;
        color: #a96b00;
    }

    .badge-sem {
        background: #f1f1f5;
        color: #777;
    }

    .btn-ponto {
        background: #4254BA;
        color: #fff;
        border: 0;
        border-radius: 12px;
        padding: 10px 18px;
        font-weight: 700;
    }

    .btn-ponto:hover {
        background: #3444a0;
        color: #fff;
    }
</style>

<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="ponto-card">
                    <div class="ponto-title">Relatório de Ponto</div>
                    <div class="ponto-subtitle">
                        Consulte as entradas, saídas, horas trabalhadas, faltas e horas extras por funcionário.
                    </div>

                    <form method="get" action="{{ route('ponto-relatorio.index') }}">
                        <input type="hidden" name="empresa_id" value="{{ request()->empresa_id }}">

                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label>Mês</label>
                                <input type="text" name="mes" class="form-control mes"
                                value="{{ request('mes') ?? date('m/Y') }}" placeholder="MM/AAAA" required>
                            </div>

                            <div class="col-md-4">
                                <label>Funcionário</label>
                                <select name="funcionario_id" class="form-control form-select" required>
                                    <option value="">Selecione</option>
                                    @foreach($funcionarios as $f)
                                    <option value="{{ $f->id }}" @selected(request('funcionario_id') == $f->id)>
                                        {{ $f->nome }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-ponto w-100">
                                    Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if($resumo && $funcionario)
                <div class="ponto-card">

                    <div class="ponto-title">{{ $funcionario->nome }}</div>
                    <div class="ponto-subtitle">
                        Resumo financeiro e horas do mês selecionado.
                    </div>

                    <!-- <div class="ponto-resumo">
                        <div class="ponto-box">
                            <span>Salário</span>
                            <strong>R$ {{ number_format($resumo['salario'], 2, ',', '.') }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Valor hora extra</span>
                            <strong>R$ {{ number_format($resumo['valor_hora_extra'], 2, ',', '.') }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Total previsto</span>
                            <strong>{{ $resumo['total_previsto'] }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Total trabalhado</span>
                            <strong>{{ $resumo['total_trabalhado'] }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Total hora extra</span>
                            <strong>{{ $resumo['total_extra'] }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Total faltas/horas negativas</span>
                            <strong>{{ $resumo['total_falta'] }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Valor total extra</span>
                            <strong>R$ {{ number_format($resumo['valor_total_extra'], 2, ',', '.') }}</strong>
                        </div>

                        <div class="ponto-box">
                            <span>Salário + extras</span>
                            <strong>R$ {{ number_format($resumo['total_com_extra'], 2, ',', '.') }}</strong>
                        </div>
                    </div> -->

                    <div class="table-responsive">
                        <table class="ponto-table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Dia</th>
                                    <th>Entrada</th>
                                    <th>Intervalo início</th>
                                    <th>Intervalo fim</th>
                                    <th>Saída</th>
                                    <th>Previsto</th>
                                    <th>Trabalhado</th>
                                    <th>Extra</th>
                                    <th>Falta</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($relatorio as $item)
                                <tr>
                                    <td>{{ $item['data'] }}</td>
                                    <td>{{ $item['dia_semana'] }}</td>
                                    <td>{{ $item['entrada'] }}</td>
                                    <td>{{ $item['intervalo_inicio'] }}</td>
                                    <td>{{ $item['intervalo_fim'] }}</td>
                                    <td>{{ $item['saida'] }}</td>
                                    <td>{{ $item['previsto'] }}</td>
                                    <td>{{ $item['trabalhado'] }}</td>
                                    <td>{{ $item['extra'] }}</td>
                                    <td>{{ $item['falta'] }}</td>
                                    <td>
                                        @php
                                        $classe = match($item['status']) {
                                            'Completo' => 'badge-completo',
                                            'Falta' => 'badge-falta',
                                            'Incompleto' => 'badge-incompleto',
                                            default => 'badge-sem'
                                        };
                                        @endphp

                                        <span class="badge-ponto {{ $classe }}">
                                            {{ $item['status'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
<script type="text/javascript">
    $('.mes').mask('00/0000');
</script>
@endsection