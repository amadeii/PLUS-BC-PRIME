<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orçamento 80mm</title>

    <style>
        @page{ size:80mm auto; margin:0; }
        *{ box-sizing:border-box; font-family:Arial, Helvetica, sans-serif; color:#000; }
        html,body{ width:80mm; margin:0; padding:0; background:#fff; }
        body{ padding:3mm; font-size:11px; line-height:1.35; }
        .center{text-align:center;}
        .right{text-align:right;}
        .bold{font-weight:700;}
        .linha{ border-top:1px dashed #000; margin:7px 0; }
        .empresa{ font-size:13px; font-weight:700; text-transform:uppercase; }
        .titulo{ font-size:13px; font-weight:700; text-transform:uppercase; margin-bottom:2px; }
        .small{ font-size:10px; }
        .produto{ font-size:11px; font-weight:700; text-transform:uppercase; }
        .muted{ font-size:10px; }
        .total{ font-size:16px; font-weight:700; }
        table{ width:100%; border-collapse:collapse; table-layout:fixed; }
        td{ padding:2px 0; vertical-align:top; word-break:break-word; }
        .no-print{ margin-bottom:8px; text-align:center; }
        .btn-print{ border:0; background:#111; color:#fff; padding:7px 12px; border-radius:6px; font-size:12px; cursor:pointer; }

        @media print{
            html,body{ width:80mm; }
            body{ padding:3mm; }
            .no-print{ display:none!important; }
        }
    </style>
</head>

<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">Imprimir</button>
</div>

<div class="center">
    <div class="empresa">{{ $config->nome }}</div>
    <div>{{ $config->cpf_cnpj }}</div>
    <div>{{ $config->rua }}, {{ $config->numero }}</div>
    <div>{{ $config->bairro }} - {{ $config->cidade->nome ?? '' }} / {{ $config->cidade->uf ?? '' }}</div>
    <div>Telefone: {{ $config->celular }}</div>
</div>

<div class="linha"></div>

<div class="center">
    <div class="titulo">Orçamento Nº {{ $item->numero_sequencial }}</div>
    <div>{{ date('d/m/Y H:i') }}</div>
</div>

<div class="linha"></div>

@if($item->cliente)
    <div><span class="bold">Cliente:</span> {{ $item->cliente->razao_social }}</div>
    <div><span class="bold">CPF/CNPJ:</span> {{ $item->cliente->cpf_cnpj }}</div>
    <div><span class="bold">Telefone:</span> {{ $item->cliente->telefone ?? $item->cliente->celular }}</div>
@else
    <div><span class="bold">Cliente:</span> Consumidor Final</div>
@endif

<div class="linha"></div>

<table>
    <tr>
        <td class="bold" style="width:54%;">Item</td>
        <td class="right bold" style="width:18%;">Qtd</td>
        <td class="right bold" style="width:28%;">Total</td>
    </tr>
</table>

@php
    $somaItens = 0;
    $somaTotalItens = 0;
@endphp

@foreach($item->itens as $i)

@php
    $totalItem = $i->quantidade * $i->valor_unitario;
    $somaItens += $i->quantidade;
    $somaTotalItens += $totalItem;
@endphp

<div style="margin-top:6px;">
    <div class="produto">{{ $i->descricao() }}</div>

    <table>
        <tr>
            <td style="width:54%;">R$ {{ __moeda($i->valor_unitario) }} un</td>
            <td class="right" style="width:18%;">{{ number_format($i->quantidade, 2, ',', '.') }}</td>
            <td class="right" style="width:28%;">R$ {{ __moeda($totalItem) }}</td>
        </tr>
    </table>

    @if(sizeof($i->itensDimensao) > 0)
        @foreach($i->itensDimensao as $id)
            <div class="small">
                L: {{ $id->largura }} | A: {{ $id->altura }} | Qtd: {{ $id->quantidade }} | M²: {{ $id->m2_total }}
            </div>
        @endforeach
    @endif
</div>

@endforeach

<div class="linha"></div>

<table>
    <tr>
        <td>Qtd. Itens:</td>
        <td class="right">{{ number_format($somaItens, 2, ',', '.') }}</td>
    </tr>

    <tr>
        <td>Subtotal:</td>
        <td class="right">R$ {{ __moeda($somaTotalItens) }}</td>
    </tr>

    <tr>
        <td>Desconto:</td>
        <td class="right">R$ {{ __moeda($item->desconto) }}</td>
    </tr>

    <tr>
        <td>Acréscimo:</td>
        <td class="right">R$ {{ __moeda($item->acrescimo) }}</td>
    </tr>

    <tr>
        <td>Frete:</td>
        <td class="right">
            @if($item->frete)
                R$ {{ __moeda($item->frete->valor) }}
            @else
                R$ 0,00
            @endif
        </td>
    </tr>
</table>

<div class="linha"></div>

<table>
    <tr>
        <td class="total">TOTAL:</td>
        <td class="right total">R$ {{ __moeda($item->total) }}</td>
    </tr>
</table>

@if($item->fatura()->exists())

<div class="linha"></div>

<div class="bold">FATURA</div>

@foreach($item->fatura as $f)
<table>
    <tr>
        <td>{{ __data_pt($f->data_vencimento, 0) }}</td>
        <td class="right">R$ {{ __moeda($f->valor) }}</td>
    </tr>
</table>
@endforeach

@endif

@if($item->observacao != "" || $config->campo_obs_pedido != "")

<div class="linha"></div>

<div class="bold">OBSERVAÇÃO</div>
<div>{{ $config->campo_obs_pedido }} {{ $item->observacao }}</div>

@endif

@if($config->validade_orcamento > 0)

<div class="linha"></div>

<div class="center">
    Orçamento válido por {{ $config->validade_orcamento }} dias.
</div>

@endif

<div class="linha"></div>

<div class="center">
    <div class="bold">Obrigado pela preferência!</div>
</div>

<script>
    window.onload = function(){
        setTimeout(function(){
            window.print();
        }, 500);
    };
</script>

</body>
</html>