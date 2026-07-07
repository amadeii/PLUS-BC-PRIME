<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page{
            size:80mm auto;
            margin:0;
        }

        *{
            box-sizing:border-box;
            font-family:Arial, sans-serif;
            color:#000;
        }

        html,body{
            width:80mm;
            margin:0;
            padding:0;
            background:#fff;
        }

        body{
            padding:3mm;
            font-size:11px;
        }

        .center{text-align:center;}
        .right{text-align:right;}
        .bold{font-weight:bold;}

        .empresa{
            font-size:13px;
            font-weight:bold;
            text-transform:uppercase;
        }

        .titulo{
            font-size:12px;
            font-weight:bold;
            margin-top:4px;
        }

        .linha{
            border-top:1px dashed #000;
            margin:6px 0;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            padding:2px 0;
            vertical-align:top;
            font-size:11px;
        }

        .total{
            font-size:15px;
            font-weight:bold;
        }

        @media print{
            html,body{
                width:80mm;
            }

            body{
                margin:0;
            }

            .no-print{
                display:none;
            }
        }
    </style>
</head>

<body onload="window.print()">

<div class="center">
    <div class="empresa">{{ $config->nome_fantasia }}</div>
    <div>{{ __setMask($config->cpf_cnpj) }}</div>
    <div>{{ $config->rua }}, {{ $config->numero }}</div>
    <div>{{ $config->cidade->nome ?? '' }} - {{ $config->cidade->uf ?? '' }}</div>
</div>

<div class="linha"></div>

<div class="center">
    <div class="titulo">RELATÓRIO DE CAIXA</div>
    <div>{{ date('d/m/Y H:i') }}</div>
</div>

<div class="linha"></div>

<table>
    <tr>
        <td><b>Abertura:</b></td>
        <td class="right">{{ __data_pt($item->created_at) }}</td>
    </tr>
    <tr>
        <td><b>Fechamento:</b></td>
        <td class="right">{{ __data_pt($item->updated_at) }}</td>
    </tr>
</table>

<div class="linha"></div>

<div class="bold">RESUMO</div>

<table>
    <tr>
        <td>Total vendas</td>
        <td class="right">R$ {{ __moeda($totalVendas) }}</td>
    </tr>
    <tr>
        <td>Total compras</td>
        <td class="right">R$ {{ __moeda($totalCompras) }}</td>
    </tr>
    <tr>
        <td>Suprimentos</td>
        <td class="right">R$ {{ __moeda($somaSuprimento ?? 0) }}</td>
    </tr>
    <tr>
        <td>Sangrias</td>
        <td class="right">R$ {{ __moeda($somaSangria ?? 0) }}</td>
    </tr>
    <tr>
        <td>Serviços</td>
        <td class="right">R$ {{ __moeda($somaServicos) }}</td>
    </tr>
</table>

<div class="linha"></div>

<table>
    <tr>
        <td class="total">EM CAIXA</td>
        <td class="right total">
            R$ {{ __moeda(($somaSuprimento ?? 0) + $totalVendas - ($somaSangria ?? 0)) }}
        </td>
    </tr>
</table>

<div class="linha"></div>

<div class="bold">PAGAMENTOS</div>

<table>
    @foreach($somaTiposPagamento as $key => $tp)
        @if($tp > 0)
            <tr>
                <td>{{ App\Models\Nfce::getTipoPagamento($key) }}</td>
                <td class="right">R$ {{ __moeda($tp) }}</td>
            </tr>
        @endif
    @endforeach
</table>

<div class="linha"></div>

<div class="bold">VENDAS</div>

@foreach($data as $v)
    <div style="margin-top:5px;">
        <b>{{ $v->cliente->razao_social ?? 'NÃO IDENTIFICADO' }}</b>

        <table>
            <tr>
                <td>{{ __data_pt($v->created_at) }}</td>
                <td class="right">
                    {{ $v->tipo_pagamento ? $v->getTipoPagamento($v->tipo_pagamento) : 'Múltiplo' }}
                </td>
            </tr>
            <tr>
                <td>{{ $v->tipo != 'OS' ? strtoupper($v->estado) : '' }}</td>
                <td class="right">
                    R$ {{ __moeda($v->tipo != 'OS' ? $v->total : $v->valor) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="linha"></div>
@endforeach

<div class="center" style="margin-top:20px;">
    __________________________________<br>
    {{ $usuario->name }}<br>
    {{ date('d/m/Y H:i') }}
</div>

</body>
</html>