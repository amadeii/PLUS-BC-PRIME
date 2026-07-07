<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Caixa</title>

    <style>
        @page{ margin:22px; }
        *{ box-sizing:border-box; font-family:DejaVu Sans, Arial, sans-serif; color:#111; }
        body{ margin:0; padding:0; font-size:11px; background:#fff; }
        .header{ width:100%; border-bottom:2px solid #111; padding-bottom:12px; margin-bottom:14px; }
        .header-table{ width:100%; border-collapse:collapse; }
        .logo{ width:90px; }
        .title{ text-align:center; font-size:20px; font-weight:bold; text-transform:uppercase; }
        .emissao{ text-align:right; font-size:10px; color:#555; }
        .box{ border:1px solid #ddd; border-radius:6px; padding:10px; margin-bottom:12px; }
        .box-title{ font-size:13px; font-weight:bold; text-transform:uppercase; margin-bottom:8px; border-bottom:1px solid #ddd; padding-bottom:5px; }
        table{ width:100%; border-collapse:collapse; }
        td,th{ padding:6px 5px; vertical-align:top; }
        th{ background:#f2f2f2; font-size:10px; text-transform:uppercase; border-bottom:1px solid #ccc; text-align:left; }
        .line td{ border-bottom:1px solid #eee; }
        .label{ color:#666; font-size:10px; }
        .value{ font-weight:bold; }
        .right{ text-align:right; }
        .center{ text-align:center; }
        .total-box{ background:#f7f7f7; border:1px solid #ccc; padding:10px; margin-top:12px; }
        .total-label{ font-size:12px; font-weight:bold; }
        .total-value{ font-size:16px; font-weight:bold; text-align:right; }
        .assinatura{ margin-top:35px; width:300px; text-align:center; }
        .small{ font-size:9px; color:#666; }
    </style>
</head>

<body>

    @php
    $somaSuprimento = 0;
    $somaSangria = 0;
    $valorEmDinheiro = 0;

    foreach($suprimentos as $s){
        $somaSuprimento += $s->valor;
    }

    foreach($sangrias as $s){
        $somaSangria += $s->valor;
    }
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:120px;">
                    <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('logo.png'))) }}" class="logo">
                </td>

                <td class="title">
                    Relatório de Caixa
                </td>

                <td style="width:160px;" class="emissao">
                    Emissão:<br>
                    <strong>{{ date('d/m/Y H:i') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Dados da empresa</div>

        <table>
            <tr>
                <td style="width:65%;">
                    <span class="label">Razão social</span><br>
                    <span class="value">{{ $config->nome_fantasia }}</span>
                </td>

                <td>
                    <span class="label">Documento</span><br>
                    <span class="value">{{ str_replace(" ", "", $config->cpf_cnpj) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Resumo do caixa</div>

        <table>
            <tr>
                <td>
                    <span class="label">Valor de abertura</span><br>
                    <span class="value">R$ {{ __moeda($item->valor_abertura) }}</span>
                </td>

                <td>
                    <span class="label">Data de abertura</span><br>
                    <span class="value">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</span>
                </td>

                <td>
                    <span class="label">Data de fechamento</span><br>
                    <span class="value">{{ \Carbon\Carbon::parse($item->updated_at)->format('d/m/Y H:i') }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="label">Total de vendas</span><br>
                    <span class="value">R$ {{ __moeda($totalVendas) }}</span>
                </td>

                <td>
                    <span class="label">Total de compras</span><br>
                    <span class="value">R$ {{ __moeda($totalCompras) }}</span>
                </td>

                <td>
                    <span class="label">Soma de serviços</span><br>
                    <span class="value">R$ {{ __moeda($somaServicos) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Total por tipo de pagamento</div>

        <table>
            <thead>
                <tr>
                    <th>Tipo de pagamento</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>

            <tbody>
                @foreach($somaTiposPagamento as $key => $tp)
                @if($tp > 0)
                <tr class="line">
                    <td>{{ App\Models\Nfce::getTipoPagamento($key) }}</td>
                    <td class="right value">R$ {{ number_format($tp, 2, ',', '.') }}</td>
                </tr>

                @php if($key == '01') $valorEmDinheiro = $tp; @endphp
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Vendas</div>

        <table>
            <thead>
                <tr>
                    <th style="width:22%;">Cliente</th>
                    <th style="width:13%;">Data</th>
                    <th style="width:15%;">Pagamento</th>
                    <th style="width:10%;">Estado</th>
                    <th style="width:10%;">NF</th>
                    <th style="width:8%;">Tipo</th>
                    <th class="right" style="width:11%;">Valor</th>
                    <th class="right" style="width:11%;">Desconto</th>
                </tr>
            </thead>

            <tbody>
                @foreach($data as $v)
                <tr class="line">
                    <td>{{ $v->cliente->razao_social ?? 'NÃO IDENTIFICADO' }}</td>

                    <td>{{ \Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i') }}</td>

                    <td>
                        @if($v->tipo_pagamento == '99')
                        Outros
                        @else
                        {{ $v->tipo_pagamento ? $v->getTipoPagamento($v->tipo_pagamento) : 'Pag. Múltiplo' }}
                        @endif
                    </td>

                    <td>{{ $v->tipo != 'OS' ? strtoupper($v->estado) : '' }}</td>

                    <td>
                        @if($v->estado == 'aprovado')
                        {{ $v->numero > 0 ? $v->numero : '--' }}
                        @else
                        --
                        @endif
                    </td>

                    <td>{{ $v->tipo }}</td>

                    <td class="right">
                        @if($v->tipo != 'OS')
                        R$ {{ __moeda($v->total) }}
                        @else
                        R$ {{ __moeda($v->valor) }}
                        @endif
                    </td>

                    <td class="right">R$ {{ __moeda($v->desconto) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Suprimentos</div>

        <table>
            <thead>
                <tr>
                    <th style="width:25%;">Data</th>
                    <th>Observação</th>
                    <th class="right" style="width:20%;">Valor</th>
                </tr>
            </thead>

            <tbody>
                @forelse($suprimentos as $s)
                <tr class="line">
                    <td>{{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $s->observacao }}</td>
                    <td class="right value">R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="center small">Nenhum suprimento informado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Sangrias</div>

        <table>
            <thead>
                <tr>
                    <th style="width:25%;">Data</th>
                    <th>Observação</th>
                    <th class="right" style="width:20%;">Valor</th>
                </tr>
            </thead>

            <tbody>
                @forelse($sangrias as $s)
                <tr class="line">
                    <td>{{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $s->observacao }}</td>
                    <td class="right value">R$ {{ number_format($s->valor, 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="center small">Nenhuma sangria informada</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="total-box">
        <table>
            <tr>
                <td>
                    <span class="label">Soma de vendas</span><br>
                    <span class="value">R$ {{ number_format($totalVendas, 2, ',', '.') }}</span>
                </td>

                <td>
                    <span class="label">Soma de sangria</span><br>
                    <span class="value">R$ {{ number_format($somaSangria, 2, ',', '.') }}</span>
                </td>

                <td>
                    <span class="label">Soma de suprimento</span><br>
                    <span class="value">R$ {{ number_format($somaSuprimento, 2, ',', '.') }}</span>
                </td>
            </tr>

            <tr>
                <td>
                    <span class="label">Contagem da gaveta</span><br>
                    <span class="value">R$ {{ number_format($item->valor_dinheiro, 2, ',', '.') }}</span>
                </td>

                <td>
                    <span class="label">Soma de serviços</span><br>
                    <span class="value">R$ {{ number_format($somaServicos, 2, ',', '.') }}</span>
                </td>

                <td>
                    <span class="total-label">Valor em caixa</span><br>
                    <span class="total-value">R$ {{ number_format($somaSuprimento + $totalVendas - $somaSangria, 2, ',', '.') }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="box" style="margin-top:12px;">
        <div class="box-title">Produtos vendidos</div>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="right" style="width:15%;">Quantidade</th>
                    <th class="right" style="width:18%;">Valor venda</th>
                    <th class="right" style="width:18%;">Valor compra</th>
                </tr>
            </thead>

            <tbody>
                @foreach($produtos as $p)
                <tr class="line">
                    <td>{{ $p['nome'] }}</td>
                    <td class="right">{{ $p['quantidade'] }}</td>
                    <td class="right">R$ {{ __moeda($p['valor_venda']) }}</td>
                    <td class="right">R$ {{ __moeda($p['valor_compra']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="assinatura">
        ________________________________________<br>
        <strong>{{ $usuario->name }}</strong><br>
        <span class="small">{{ date('d/m/Y H:i') }}</span>
    </div>

</body>
</html>