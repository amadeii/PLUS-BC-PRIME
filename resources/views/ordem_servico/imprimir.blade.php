<html>
<head>
    <style>
        @page{ margin:.75cm .8cm .85cm .8cm; }
        *{ font-family:DejaVu Sans,Arial,sans-serif; box-sizing:border-box; }
        body{ margin:0; color:#111827; font-size:9.4px; line-height:1.24; background:#fff; }
        table{ width:100%; border-collapse:collapse; }
        td,th{ vertical-align:top; }
        p{ margin:0 0 2px 0; }
        .text-left{text-align:left!important}.text-center{text-align:center!important}.text-right{text-align:right!important}
        .nowrap{ white-space:nowrap; }

        .header{ border-bottom:2px solid #111827; padding-bottom:8px; margin-bottom:8px; }
        .logo-box{ width:115px; vertical-align:middle; }
        .logo-box img{ max-width:92px; max-height:58px; }
        .doc-title{ text-align:center; vertical-align:middle; }
        .doc-title h1{ margin:0; font-size:19px; letter-spacing:.7px; color:#111827; }
        .doc-title span{ display:block; margin-top:2px; font-size:10px; color:#6b7280; font-weight:bold; }
        .doc-date{ width:115px; text-align:right; vertical-align:middle; font-size:8.8px; color:#6b7280; }
        .doc-date strong{ color:#111827; }

        .resume{ border:1px solid #dbe3f0; background:#f8fafc; margin-bottom:7px; }
        .resume td{ padding:6px 8px; border-right:1px solid #e5e7eb; }
        .resume td:last-child{ border-right:0; }
        .label{ display:block; color:#6b7280; font-size:8px; margin-bottom:1px; font-weight:normal; }
        .value{ color:#111827; font-weight:bold; }
        .value-money{ color:#15803d; font-weight:bold; font-size:11.5px; }

        .grid td{ width:50%; }
        .pr-4{ padding-right:4px; }
        .pl-4{ padding-left:4px; }

        .section-title{ background:#111827; color:#fff; padding:5px 7px; font-size:8.8px; font-weight:bold; letter-spacing:.25px; margin-top:6px; }
        .info-table{ border:1px solid #e5e7eb; border-top:0; margin-bottom:5px; }
        .info-table td{ padding:5px 7px; border-bottom:1px solid #eef0f4; font-size:8.8px; }
        .info-table tr:last-child td{ border-bottom:0; }

        .content-box{ border:1px solid #e5e7eb; background:#fafafa; padding:6px 7px; min-height:28px; margin-bottom:5px; font-size:8.8px; }

        .items-table{ border:1px solid #e5e7eb; margin-bottom:6px; }
        .items-table th{ background:#111827; color:#fff; padding:5px 6px; font-size:8.4px; border-right:1px solid #374151; }
        .items-table th:last-child{ border-right:0; }
        .items-table td{ padding:4px 6px; border-bottom:1px solid #eef0f4; font-size:8.5px; }
        .items-table tbody tr:nth-child(even){ background:#f9fafb; }
        .items-table tfoot td{ background:#f8fafc; padding:5px 6px; border-top:1px solid #d1d5db; font-size:8.8px; font-weight:bold; }

        .status-ok{ color:#15803d; font-weight:bold; }
        .status-pendente{ color:#b45309; font-weight:bold; }
        .money-success{ color:#15803d; font-weight:bold; }
        .money-danger{ color:#dc2626; font-weight:bold; }

        .finance-box{ border:1px solid #dbe3f0; background:#f8fafc; margin-top:7px; margin-bottom:6px; }
        .finance-box td{ padding:6px 8px; border-bottom:1px solid #e5e7eb; font-size:9px; }
        .finance-box tr:last-child td{ border-bottom:0; }
        .finance-total td{ background:#ecfdf5; font-size:11px; font-weight:bold; }

        .footer{ position:fixed; bottom:-20px; left:.1cm; right:.1cm; border-top:1px solid #d1d5db; padding-top:5px; font-size:8px; color:#6b7280; }
        .footer td{ vertical-align:middle; }
        .footer-logo{ max-width:115px; max-height:42px; }
    </style>
</head>

@php
$totalServicos = $ordem->servicos->sum('subtotal');
$totalProdutos = $ordem->itens->sum('subtotal');
$percentualDescontoProdutos = $ordem->percentual_desconto_produtos ?? 0;
$valorDescontoProdutos = ($totalProdutos * $percentualDescontoProdutos) / 100;
$totalProdutosComDesconto = $totalProdutos - $valorDescontoProdutos;

$estadoTexto = [
'pd' => 'PENDENTE',
'ap' => 'APROVADO',
'rp' => 'REPROVADO',
'fz' => 'FINALIZADO'
][$ordem->estado] ?? strtoupper($ordem->estado);
@endphp

<body>

    <div class="header">
        <table>
            <tr>
                <td class="logo-box">
                    @if($config->logo != null)
                    <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('/uploads/logos/'. $config->logo)))}}">
                    @else
                    <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png')))}}">
                    @endif
                </td>
                <td class="doc-title">
                    <h1>ORDEM DE SERVIÇO</h1>
                    <span>Nº {{ str_pad($ordem->codigo_sequencial, 6, '0', STR_PAD_LEFT) }}</span>
                </td>
                <td class="doc-date">
                    Emissão<br>
                    <strong>{{ date('d/m/Y H:i') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="resume">
        <tr>
            <td style="width:25%;"><span class="label">Data inicial</span><span class="value">{{ __data_pt($ordem->data_inicio) }}</span></td>
            <td style="width:25%;"><span class="label">Data entrega</span><span class="value">{{ $ordem->data_entrega ? __data_pt($ordem->data_entrega) : '--' }}</span></td>
            <td style="width:25%;"><span class="label">Status</span><span class="value">{{ $estadoTexto }}</span></td>
            <td style="width:25%;"><span class="label">Valor total</span><span class="value-money">R$ {{ __moeda($ordem->valor) }}</span></td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td class="pr-4">
                <div class="section-title">DADOS DA EMPRESA</div>
                <table class="info-table">
                    <tr><td><span class="label">Razão social</span><span class="value">{{ $config->nome }}</span></td></tr>
                    <tr><td><span class="label">Documento</span><span class="value">{{ __setMask($config->cpf_cnpj) }}</span></td></tr>
                    <tr><td><span class="label">Endereço</span><span class="value">{{ $config->rua }}, {{ $config->numero }} - {{ $config->bairro }} - {{ $config->cidade->nome }} ({{ $config->cidade->uf }})</span></td></tr>
                    <tr><td><span class="label">Contato</span><span class="value">CEP: {{ $config->cep }} | Tel: {{ $config->celular }} | {{ $config->email ?: '--' }}</span></td></tr>
                </table>
            </td>

            <td class="pl-4">
                <div class="section-title">DADOS DO CLIENTE</div>
                <table class="info-table">
                    <tr><td><span class="label">Nome/Razão social</span><span class="value">{{ $ordem->cliente->razao_social }}</span></td></tr>
                    <tr><td><span class="label">CPF/CNPJ</span><span class="value">{{ $ordem->cliente->cpf_cnpj }}</span></td></tr>
                    <tr><td><span class="label">Endereço</span><span class="value">{{ $ordem->cliente->rua }}, {{ $ordem->cliente->numero }} - {{ $ordem->cliente->bairro }} - {{ $ordem->cliente->cidade ? $ordem->cliente->cidade->info : '' }}</span></td></tr>
                    <tr><td><span class="label">Contato</span><span class="value">{{ $ordem->cliente->telefone ?: '--' }} | {{ $ordem->cliente->celular ?: '--' }} | {{ $ordem->cliente->email ?: '--' }}</span></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td class="pr-4">
                <div class="section-title">RESPONSÁVEIS</div>
                <table class="info-table">
                    <tr>
                        <td style="width:50%;"><span class="label">Funcionário</span><span class="value">{{ $ordem->funcionario ? $ordem->funcionario->nome : '--' }}</span></td>
                        <td style="width:50%;"><span class="label">Técnico</span><span class="value">{{ $ordem->tecnico ? $ordem->tecnico->nome : '--' }}</span></td>
                    </tr>
                </table>
            </td>

            <td class="pl-4">
                @if($ordem->veiculo)
                <div class="section-title">DADOS DO VEÍCULO</div>
                <table class="info-table">
                    <tr>
                        <td><span class="label">Marca</span><span class="value">{{ $ordem->veiculo->marca }}</span></td>
                        <td><span class="label">Modelo</span><span class="value">{{ $ordem->veiculo->modelo }}</span></td>
                        <td><span class="label">Placa</span><span class="value">{{ $ordem->veiculo->placa }}</span></td>
                    </tr>
                </table>
                @endif
            </td>
        </tr>
    </table>

    @if($configGeral->tipo_ordem_servico == 'assistencia técinica')
    <div class="section-title">DADOS DO EQUIPAMENTO</div>
    <table class="info-table">
        <tr>
            <td style="width:25%;"><span class="label">Equipamento</span><span class="value">{{ $ordem->equipamento ?? '--' }}</span></td>
            <td style="width:25%;"><span class="label">Marca</span><span class="value">{{ $ordem->marca ?? '--' }}</span></td>
            <td style="width:25%;"><span class="label">Modelo</span><span class="value">{{ $ordem->modelo ?? '--' }}</span></td>
            <td style="width:25%;"><span class="label">Série / Cor</span><span class="value">{{ $ordem->numero_serie ?? '--' }} / {{ $ordem->cor ?? '--' }}</span></td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td class="pr-4">
                <div class="section-title">DIAGNÓSTICO DO CLIENTE</div>
                <div class="content-box">{!! $ordem->diagnostico_cliente ?: '--' !!}</div>
            </td>
            <td class="pl-4">
                <div class="section-title">DIAGNÓSTICO TÉCNICO</div>
                <div class="content-box">{!! $ordem->diagnostico_tecnico ?: '--' !!}</div>
            </td>
        </tr>
    </table>
    @endif

    <div class="section-title">DESCRIÇÃO / OBSERVAÇÃO</div>
    <div class="content-box">{!! $ordem->descricao ?: '--' !!}</div>

    <div class="section-title">SERVIÇOS</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:52%;">Serviço</th>
                <th style="width:12%;" class="text-center">Qtd</th>
                <th style="width:16%;" class="text-center">Status</th>
                <th style="width:20%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordem->servicos as $item)
            <tr>
                <td>{{ $item->servico->nome }}</td>
                <td class="text-center">{{ $item->quantidade }}</td>
                <td class="text-center">
                    @if($item->status)
                    <span class="status-ok">FINALIZADO</span>
                    @else
                    <span class="status-pendente">PENDENTE</span>
                    @endif
                </td>
                <td class="text-right">R$ {{ __moeda($item->subtotal) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Nenhum serviço informado</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total serviços</td>
                <td class="text-right">R$ {{ __moeda($totalServicos) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">PRODUTOS</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:52%;">Produto</th>
                <th style="width:12%;" class="text-center">Qtd</th>
                <th style="width:16%;" class="text-right">Unitário</th>
                <th style="width:20%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordem->itens as $item)
            <tr>
                <td>{{ $item->produto->nome }}</td>
                <td class="text-center">{{ $item->quantidade }}</td>
                <td class="text-right">R$ {{ __moeda($item->quantidade > 0 ? $item->subtotal/$item->quantidade : 0) }}</td>
                <td class="text-right">R$ {{ __moeda($item->subtotal) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Nenhum produto informado</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total produtos</td>
                <td class="text-right">R$ {{ __moeda($totalProdutos) }}</td>
            </tr>
            @if($percentualDescontoProdutos > 0)
            <tr>
                <td colspan="3" class="money-danger">Desconto produtos ({{ __moeda($percentualDescontoProdutos) }}%)</td>
                <td class="text-right money-danger">- R$ {{ __moeda($valorDescontoProdutos) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="money-success">Total produtos com desconto</td>
                <td class="text-right money-success">R$ {{ __moeda($totalProdutosComDesconto) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    <table class="finance-box">
        <tr>
            <td style="width:25%;">Total serviços</td>
            <td style="width:25%;" class="text-right">R$ {{ __moeda($totalServicos) }}</td>
            <td style="width:25%;">Total produtos</td>
            <td style="width:25%;" class="text-right">R$ {{ __moeda($totalProdutos) }}</td>
        </tr>

        @if($percentualDescontoProdutos > 0)
        <tr>
            <td colspan="3" class="money-danger">Desconto dos produtos ({{ __moeda($percentualDescontoProdutos) }}%)</td>
            <td class="text-right money-danger">- R$ {{ __moeda($valorDescontoProdutos) }}</td>
        </tr>
        @endif

        <tr class="finance-total">
            <td colspan="3">VALOR TOTAL DA OS</td>
            <td class="text-right money-success">R$ {{ __moeda($ordem->valor) }}</td>
        </tr>
    </table>

    @if($configGeral && $configGeral->mensagem_padrao_impressao_os != "")
    <div class="section-title">INFORMAÇÕES ADICIONAIS</div>
    <div class="content-box">{!! $configGeral->mensagem_padrao_impressao_os !!}</div>
    @endif

    <div class="footer">
        <table>
            <tr>
                <td class="text-left">{{ env('SITE_SUPORTE') }}</td>
                <td class="text-right">
                    <img class="footer-logo" src="{{'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png')))}}">
                </td>
            </tr>
        </table>
    </div>

</body>
</html>