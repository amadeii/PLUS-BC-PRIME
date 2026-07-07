<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumo XML Contador</title>

    <style>
        body{ font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111; margin:25px 35px; }
        .empresa{ text-align:left; margin-left:120px; margin-bottom:8px; line-height:1.4; }
        .empresa strong{ font-size:13px; }
        .titulo{ text-align:center; font-weight:bold; border-top:1px solid #ccc; border-bottom:1px solid #ccc; padding:4px 0; margin-bottom:12px; }
        .situacao{ font-weight:bold; font-size:12px; margin:18px 0 6px; }
        table{ width:100%; border-collapse:collapse; }
        thead th{ background:#e9e9e9; font-weight:bold; text-align:left; padding:3px 4px; border-bottom:1px solid #d5d5d5; }
        tbody td{ padding:4px; border-bottom:1px solid #e1e1e1; }
        .text-right{ text-align:right; }
        .total-row td{ border-bottom:0; padding-top:5px; font-weight:bold; }
        .total-label{ text-align:right; padding-right:35px; }
        .geral td{ font-weight:bold; font-size:12px; border-top:1px solid #ccc; }
    </style>
</head>
<body>

    <div class="empresa">
        <strong>{{ $empresa->razao_social ?? $empresa->nome ?? '' }}</strong><br>
        {{ $empresa->nome_fantasia ?? '' }}<br>
        CNPJ: {{ $empresa->cpf_cnpj ?? $empresa->cnpj ?? '' }}
        @if(isset($empresa->ie) && $empresa->ie)
        &nbsp;&nbsp;&nbsp; IE: {{ $empresa->ie }}
        @endif
        <br>
        {{ $empresa->rua ?? '' }}, {{ $empresa->numero ?? '' }}
        {{ $empresa->bairro ? ', '.$empresa->bairro : '' }}
        {{ $empresa->cidade ? ', '.$empresa->cidade->nome : '' }}
        {{ $empresa->cidade && $empresa->cidade->uf ? ', '.$empresa->cidade->uf : '' }}
        <br>
        {{ $empresa->telefone ?? '' }}
        @if(isset($empresa->email) && $empresa->email)
        &nbsp;&nbsp;&nbsp; EMAIL: {{ $empresa->email }}
        @endif
    </div>

    @php
    $meses = [
    1 => 'JANEIRO',
    2 => 'FEVEREIRO',
    3 => 'MARÇO',
    4 => 'ABRIL',
    5 => 'MAIO',
    6 => 'JUNHO',
    7 => 'JULHO',
    8 => 'AGOSTO',
    9 => 'SETEMBRO',
    10 => 'OUTUBRO',
    11 => 'NOVEMBRO',
    12 => 'DEZEMBRO'
    ];
    @endphp

    <div class="titulo">
        RELATÓRIO DE XML - {{ $meses[(int)$competencia->format('m')] }}/{{ $competencia->format('Y') }}
    </div>

    <div class="situacao">NFe - APROVADAS</div>
    <table>
        <thead>
            <tr>
                <th>Nº NFe</th>
                <th>SÉRIE</th>
                <th>EMISSÃO</th>
                <th>CHAVE</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nfeAprovadas as $item)
            <tr>
                <td>{{ $item->numero }}</td>
                <td>{{ $item->serie }}</td>
                <td>{{ \Carbon\Carbon::parse($item->data_emissao)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->chave }}</td>
                <td class="text-right">{{ number_format($item->total ?? 0, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="total-label">Total --&gt;</td>
                <td class="text-right">{{ number_format($nfeAprovadas->sum('total'), 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="situacao">NFe - CANCELADAS</div>
    <table>
        <thead>
            <tr>
                <th>Nº NFe</th>
                <th>SÉRIE</th>
                <th>EMISSÃO</th>
                <th>CHAVE</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nfeCanceladas as $item)
            <tr>
                <td>{{ $item->numero }}</td>
                <td>{{ $item->serie }}</td>
                <td>{{ \Carbon\Carbon::parse($item->data_emissao)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->chave }}</td>
                <td class="text-right">{{ number_format($item->total ?? 0, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="total-label">Total --&gt;</td>
                <td class="text-right">{{ number_format($nfeCanceladas->sum('total'), 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="situacao">NFC-e - APROVADAS</div>
    <table>
        <thead>
            <tr>
                <th>Nº NFCe</th>
                <th>SÉRIE</th>
                <th>EMISSÃO</th>
                <th>CHAVE</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nfceAprovadas as $item)
            <tr>
                <td>{{ $item->numero }}</td>
                <td>{{ $item->serie }}</td>
                <td>{{ \Carbon\Carbon::parse($item->data_emissao)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->chave }}</td>
                <td class="text-right">{{ number_format($item->total ?? 0, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="total-label">Total --&gt;</td>
                <td class="text-right">{{ number_format($nfceAprovadas->sum('total'), 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="situacao">NFC-e - CANCELADAS</div>
    <table>
        <thead>
            <tr>
                <th>Nº NFCe</th>
                <th>SÉRIE</th>
                <th>EMISSÃO</th>
                <th>CHAVE</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nfceCanceladas as $item)
            <tr>
                <td>{{ $item->numero }}</td>
                <td>{{ $item->serie }}</td>
                <td>{{ \Carbon\Carbon::parse($item->data_emissao)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->chave }}</td>
                <td class="text-right">{{ number_format($item->total ?? 0, 2, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="total-label">Total --&gt;</td>
                <td class="text-right">{{ number_format($nfceCanceladas->sum('total'), 2, ',', '.') }}</td>
            </tr>

            <tr class="geral">
                <td colspan="4" class="total-label">Total geral --&gt;</td>
                <td class="text-right">
                    {{ number_format(
                    $nfeAprovadas->sum('total') +
                    $nfeCanceladas->sum('total') +
                    $nfceAprovadas->sum('total') +
                    $nfceCanceladas->sum('total'), 2, ',', '.'
                    ) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="situacao">INUTILIZADAS</div>

    <table>
        <thead>
            <tr>
                <th>Nº Inicial</th>
                <th>Nº Final</th>
                <th>Série</th>
                <th>Modelo</th>
                <th>Justificativa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inutilizadas as $item)
            <tr>
                <td>{{ $item->numero_inicial }}</td>
                <td>{{ $item->numero_final }}</td>
                <td>{{ $item->numero_serie }}</td>
                <td>{{ $item->modelo }}</td>
                <td>{{ $item->justificativa }}</td>
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="4" class="total-label">Total --&gt;</td>
                <td>{{ $inutilizadas->count() }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>