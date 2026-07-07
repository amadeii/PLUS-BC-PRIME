<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 20px 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            border-bottom: 2px solid #4254BA;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #4254BA;
        }

        .subtitle {
            font-size: 11px;
            color: #666;
        }

        .info-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 4px 0;
        }

        .label {
            color: #555;
            font-weight: bold;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #4254BA;
            border-left: 4px solid #4254BA;
            padding-left: 6px;
            margin-top: 14px;
            margin-bottom: 6px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary td {
            width: 25%;
            border: 1px solid #ddd;
            padding: 8px;
            background: #fafafa;
        }

        .summary small {
            display: block;
            color: #777;
            font-size: 9px;
        }

        .summary strong {
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f1f3ff;
            border: 1px solid #d9dcef;
            padding: 5px;
            font-size: 10px;
            text-align: left;
        }

        td {
            border: 1px solid #e5e5e5;
            padding: 5px;
            font-size: 10px;
        }

        tfoot td {
            background: #f8f9ff;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: #198754;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-warning {
            color: #b7791f;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            color: #fff;
        }

        .badge-success {
            background: #198754;
        }

        .badge-danger {
            background: #dc3545;
        }

        .badge-warning {
            background: #f59e0b;
            color: #222;
        }

        .badge-secondary {
            background: #6c757d;
        }

        .signature {
            margin-top: 35px;
            width: 100%;
        }

        .signature td {
            border: none;
            padding-top: 28px;
            text-align: center;
        }

        .line {
            border-top: 1px solid #333;
            width: 80%;
            margin: 0 auto 5px auto;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">Apuração mensal</div>
        <div class="subtitle">Resumo financeiro e espelho de ponto do funcionário</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div><span class="label">Funcionário:</span> {{ $item->funcionario->nome }}</div>
                <div><span class="label">Mês/Ano:</span> {{ $item->mes }}/{{ $item->ano }}</div>
                <div><span class="label">Forma de pagamento:</span> {{ $item->forma_pagamento }}</div>
            </td>

            <td>
                <div><span class="label">Data de registro:</span> {{ __data_pt($item->created_at) }}</div>

                @if($item->observacao)
                    <div><span class="label">Observação:</span> {{ $item->observacao }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">Resumo do ponto</div>

    <table class="summary">
        <tr>
            <td>
                <small>Horas previstas</small>
                <strong>{{ $item->horas_previstas ?? '00:00' }}</strong>
            </td>

            <td>
                <small>Horas trabalhadas</small>
                <strong>{{ $item->horas_trabalhadas ?? '00:00' }}</strong>
            </td>

            <td>
                <small>Horas extras</small>
                <strong class="text-success">{{ $item->horas_extras ?? '00:00' }}</strong>
            </td>

            <td>
                <small>Saldo mensal</small>

                @if(($item->saldo_minutos ?? 0) > 0)
                    <strong class="text-success">+{{ $item->saldo_horas }}</strong>
                @elseif(($item->saldo_minutos ?? 0) < 0)
                    <strong class="text-danger">-{{ $item->saldo_horas }}</strong>
                @else
                    <strong>00:00</strong>
                @endif
            </td>
        </tr>

        <tr>
            <td>
                <small>Horas faltantes</small>
                <strong class="text-danger">{{ $item->horas_faltas ?? '00:00' }}</strong>
            </td>

            <td>
                <small>Atrasos</small>
                <strong class="text-warning">{{ $item->horas_atrasos ?? '00:00' }}</strong>
            </td>

            <td>
                <small>Saída antecipada</small>
                <strong class="text-danger">{{ $item->horas_saida_antecipada ?? '00:00' }}</strong>
            </td>

            <td>
                <small>Faltas / incompletos</small>
                <strong>{{ $item->faltas ?? 0 }} / {{ $item->dias_incompletos ?? 0 }}</strong>
            </td>
        </tr>
    </table>

    <div class="section-title">Eventos da apuração</div>

    <table>
        <thead>
            <tr>
                <th>Evento</th>
                <th>Condição</th>
                <th>Método</th>
                <th class="text-right">Base</th>
                <th class="text-right">Qtd.</th>
                <th>Tipo ref.</th>
                <th class="text-right">Valor calculado</th>
            </tr>
        </thead>

        <tbody>
            @foreach($item->eventos as $i)
                <tr>
                    <td>{{ $i->nome }}</td>
                    <td>{{ $i->condicao == 'soma' ? 'Soma' : 'Diminui' }}</td>
                    <td>{{ ucfirst($i->metodo) }}</td>
                    <td class="text-right">R$ {{ __moeda($i->valor_base ?? $i->valor) }}</td>
                    <td class="text-right">
                        @if($i->quantidade_referencia && $i->quantidade_referencia > 1)
                            {{ number_format($i->quantidade_referencia, 2, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $i->tipo_referencia ? str_replace('_', ' ', $i->tipo_referencia) : '-' }}</td>
                    <td class="text-right">R$ {{ __moeda($i->valor_calculado ?? $i->valor) }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total</td>
                <td class="text-right text-success">R$ {{ __moeda($item->valor_final) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($item->pontos && $item->pontos->count() > 0)

        <div class="page-break"></div>

        <div class="header">
            <div class="title">Espelho de ponto</div>
            <div class="subtitle">{{ $item->funcionario->nome }} - {{ $item->mes }}/{{ $item->ano }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Dia</th>
                    <th>Entrada</th>
                    <th>Int. início</th>
                    <th>Int. fim</th>
                    <th>Saída</th>
                    <th>Prev.</th>
                    <th>Trab.</th>
                    <th>Extra</th>
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
                        <td>{{ $ponto->horas_trabalhadas }}</td>
                        <td>{{ $ponto->horas_extras }}</td>
                        <td>{{ $ponto->atraso }}</td>
                        <td>{{ $ponto->saida_antecipada }}</td>
                        <td>{{ $ponto->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    @endif

    <table class="signature">
        <tr>
            <td>
                <div class="line"></div>
                {{ $item->funcionario->nome }}<br>
                Funcionário
            </td>

            <td>
                <div class="line"></div>
                Responsável<br>
                Empresa
            </td>
        </tr>
    </table>

</body>
</html>