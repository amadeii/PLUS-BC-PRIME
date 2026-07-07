<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido {{ $item->id }}</title>

    <style>
        @page{ size:80mm auto; margin:0; }
        *{ box-sizing:border-box; }
        body{ margin:0; padding:0; background:#fff; color:#000; font-family:Arial, sans-serif; font-size:11px; }
        .cupom{ width:80mm; max-width:80mm; padding:8px 6px; }
        .center{ text-align:center; }
        .title{ font-size:14px; font-weight:700; margin:0 0 4px; }
        .comanda{ font-size:34px; font-weight:800; margin:0; line-height:1; }
        .empresa{ font-size:12px; font-weight:700; margin:6px 0 2px; }
        .endereco{ font-size:9px; margin:0; line-height:1.3; }
        .divider{ border-top:1px dashed #000; margin:8px 0; }
        table{ width:100%; border-collapse:collapse; }
        th{ font-size:9px; text-align:left; border-bottom:1px dashed #000; padding:4px 0; }
        td{ font-size:10px; padding:4px 0; vertical-align:top; }
        .produto{ width:38mm; }
        .qtd{ width:9mm; text-align:center; }
        .valor{ width:15mm; text-align:right; }
        .extra td{ font-size:9px; font-weight:700; padding-top:0; }
        .linha{ display:flex; justify-content:space-between; gap:8px; margin-bottom:4px; font-size:11px; }
        .linha-full{ display:block; margin-bottom:4px; font-size:11px; }
        .total{ display:flex; justify-content:space-between; font-size:14px; font-weight:800; margin-top:6px; }
        .footer{ text-align:center; font-size:9px; margin-top:10px; }
        @media print{
            html, body{ width:80mm; margin:0; padding:0; }
            .cupom{ width:80mm; max-width:80mm; }
            .no-print{ display:none !important; }
        }
    </style>
</head>

<body onload="window.print()">

<div class="cupom">

    <div class="center">
        <p class="title">IMPRESSÃO DE PEDIDO</p>
        <p class="comanda">{{ $item->comanda }}</p>
        <p class="empresa">{{ $config->nome }}</p>
        <p class="endereco">{{ $config->rua }}, {{ $config->numero }} - {{ $config->bairro }}</p>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th class="produto">Produto</th>
                <th class="qtd">Qtd</th>
                <th class="valor">Unit</th>
                <th class="valor">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($item->itens as $i)
            <tr>
                <td class="produto">
                    @if(sizeof($i->pizzas) > 0 && str_contains(strtolower($i->produto->nome), 'izza'))
                        Pizza
                    @else
                        {{ $i->produto->nome }}
                    @endif
                </td>
                <td class="qtd">{{ number_format($i->quantidade, 2, ',', '.') }}</td>
                <td class="valor">{{ __moeda($i->valor_unitario) }}</td>
                <td class="valor">{{ __moeda($i->sub_total) }}</td>
            </tr>

            @if(sizeof($i->adicionais) > 0)
            <tr class="extra">
                <td colspan="4">Adicionais: {{ $i->getAdicionaisStr() }}</td>
            </tr>
            @endif

            @if($i->observacao != '')
            <tr class="extra">
                <td colspan="4">Obs: {{ $i->observacao }}</td>
            </tr>
            @endif

            @if(sizeof($i->pizzas) > 0)
            <tr class="extra">
                <td colspan="4">
                    Sabores:
                    @foreach($i->pizzas as $s)
                        {{ $s->sabor->nome }}@if(!$loop->last) | @endif
                    @endforeach
                </td>
            </tr>
            @endif

            @if($i->tamanho)
            <tr class="extra">
                <td colspan="4">Tamanho: {{ $i->tamanho->nome }}</td>
            </tr>
            @endif
            @endforeach

            @foreach($item->itensServico as $i)
            <tr>
                <td class="produto">Serviço: {{ $i->servico->nome }}</td>
                <td class="qtd">{{ number_format($i->quantidade, 2, ',', '.') }}</td>
                <td class="valor">{{ __moeda($i->valor_unitario) }}</td>
                <td class="valor">{{ __moeda($i->sub_total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="linha">
        <span>Total de itens</span>
        <strong>{{ sizeof($item->itens) }}</strong>
    </div>

    @if($item->_mesa)
    <div class="linha-full">Mesa: <strong>{{ $item->_mesa->nome }}</strong></div>
    @endif

    @if($item->cliente_nome != '')
    <div class="linha-full">Cliente: <strong>{{ $item->cliente_nome }}</strong></div>
    @endif

    @if($item->cliente_fone != '')
    <div class="linha-full">Telefone: <strong>{{ $item->cliente_fone }}</strong></div>
    @endif

    <div class="linha">
        <span>Produtos</span>
        <strong>R$ {{ __moeda($item->total) }}</strong>
    </div>

    @if($item->acrescimo > 0)
    <div class="linha">
        <span>Acréscimo</span>
        <strong>R$ {{ __moeda($item->acrescimo) }}</strong>
    </div>

    <div class="total">
        <span>Total</span>
        <span>R$ {{ __moeda($item->total + $item->acrescimo) }}</span>
    </div>
    @else
    <div class="total">
        <span>Total</span>
        <span>R$ {{ __moeda($item->total) }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="footer">
        {{ now()->format('d/m/Y H:i') }}
    </div>

</div>

</body>
</html>