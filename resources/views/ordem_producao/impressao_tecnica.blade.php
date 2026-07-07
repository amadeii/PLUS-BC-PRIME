<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de produção Nº {{ $ordem->numero }}</title>
    <style>
        @page {
            margin: 18px 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border {
            border: none !important;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .logo-box {
            width: 140px;
        }

        .logo {
            max-width: 110px;
            max-height: 55px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }

        .spacer {
            width: 140px;
        }

        .info-table td,
        .items-table td,
        .items-table th,
        .obs-table td {
            border: 1px solid #444;
            padding: 6px 8px;
        }

        .info-label {
            width: 62%;
            font-weight: bold;
            background: #f3f3f3;
        }

        .info-value {
            width: 38%;
        }

        .section-title {
            margin-top: 14px;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .items-table th {
            text-align: left;
            background: #f3f3f3;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-label {
            font-weight: bold;
            text-align: right;
        }

        .obs-box {
            height: 170px;
            vertical-align: top;
            white-space: pre-line;
            padding: 10px;
        }

        .small {
            font-size: 11px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- Cabeçalho --}}
        <table class="header-table mb-10">
            <tr>
                <td class="logo-box">
                    @if(isset($empresa) && !empty($empresa->logo))
                    @php
                    $logoPath = public_path('uploads/logos/' . $empresa->logo);
                    @endphp

                    @if(file_exists($logoPath))
                    <img class="logo" src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}">
                    @endif
                    @endif
                </td>
                <td class="title">
                    Relatório de produção Nº {{ $ordem->codigo_sequencial }}
                </td>
                <td class="spacer"></td>
            </tr>
        </table>

        {{-- Dados principais --}}
        <table class="info-table">
            <tr>
                <td class="info-label">Número do pedido</td>
                <td class="info-value">
                    {{ $ordem->numero_pedido ?? $ordem->numero }}
                </td>
            </tr>
            <tr>
                <td class="info-label">Data</td>
                <td class="info-value">
                    {{ $ordem->data ? \Carbon\Carbon::parse($ordem->data)->format('d/m/Y') : \Carbon\Carbon::parse($ordem->created_at)->format('d/m/Y') }}
                </td>
            </tr>
            <tr>
                <td class="info-label">Data prevista</td>
                <td class="info-value">
                    {{ !empty($ordem->data_prevista) ? \Carbon\Carbon::parse($ordem->data_prevista)->format('d/m/Y') : '--/--/----' }}
                </td>
            </tr>
        </table>

        {{-- Itens --}}
        <div class="section-title">Itens do pedido de venda</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 42%;">Descrição do produto/serviço</th>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 10%;">Unidade</th>
                    <th style="width: 15%;">Qtd Total</th>
                    <th style="width: 15%;">Tag</th>
                </tr>
            </thead>
            <tbody>
                @php
                $numeroItens = 0;
                $somaQtdes = 0;
                $numeroItensEstruturas = 0;
                $somaQtdesEstruturas = 0;
                @endphp

                @forelse($ordem->itens as $item)
                @php
                $numeroItens++;
                $qtd = (float)($item->quantidade ?? 0);
                $somaQtdes += $qtd;
                @endphp
                <tr>
                    <td>
                        {{ $item->produto->nome }}
                    </td>
                    <td>
                        {{ $item->produto->numero_sequencial ?? '-' }}
                    </td>
                    <td>
                        {{ $item->produto->unidade ?? 'UN' }}
                    </td>
                    <td class="text-right">
                        {{ number_format($qtd, 0, ',', '.') }}
                    </td>
                    <td>
                        {{ $item->tag ?? '' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum item encontrado</td>
                </tr>
                @endforelse

                <tr>
                    <td colspan="4" class="summary-label">Nº de itens</td>
                    <td class="text-right">{{ number_format($numeroItens, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="summary-label">Soma das Qtdes</td>
                    <td class="text-right">{{ number_format($somaQtdes, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="summary-label">Nº de itens das estruturas</td>
                    <td class="text-right">{{ number_format($ordem->numero_itens_estruturas ?? $numeroItensEstruturas, 2, ',', '.') }}</td>
                </tr>
                <!-- <tr>
                    <td colspan="4" class="summary-label">Soma das Qtdes das estruturas</td>
                    <td class="text-right">{{ number_format($ordem->soma_qtdes_estruturas ?? $somaQtdesEstruturas, 0, ',', '.') }}</td>
                </tr> -->
            </tbody>
        </table>

        <div class="section-title">Observações</div>

        <table class="obs-table">
            <tr>
                <td class="obs-box">
                    {!! $ordem->observacao !!}
                </td>
            </tr>
        </table>

        @if(isset($ordem->imagens) && count($ordem->imagens))
        <div style="margin-top: 20px; margin-bottom: 20px;" class="section-title">Imagens da Produção</div>

        <table style="width: 100%; border-collapse: collapse;">
            @foreach($ordem->imagens->chunk(2) as $chunk)
            <tr>
                @foreach($chunk as $img)
                <td style="width: 50%; padding: 5px; text-align: center; border: none">

                    @php
                    $path = public_path('uploads/ordem_producao/'.$img->imagem);
                    @endphp

                    @if(file_exists($path))
                    <img 
                    src="data:image/jpeg;base64,{{ base64_encode(file_get_contents($path)) }}" 
                    style="max-width: 100%; max-height: 200px; border-radius: 8px;"
                    >
                    @else
                    <span style="font-size: 10px;">Imagem não encontrada</span>
                    @endif

                </td>
                @endforeach

                {{-- completa linha se faltar colunas --}}
                @for($i = count($chunk); $i < 2; $i++)
                <td style="width: 50%; border: none"></td>
                @endfor
            </tr>
            @endforeach
        </table>
        @endif

        @if(isset($insumosAgrupados) && count($insumosAgrupados))
        <div class="section-title">Lista de insumos para fabricação</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 44%;">Insumo</th>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 14%;">Unidade</th>
                    <th style="width: 24%;">Qtd Necessária</th>
                </tr>
            </thead>
            <tbody>
                @php
                $totalInsumos = 0;
                $somaQuantidadeInsumos = 0;
                @endphp

                @foreach($insumosAgrupados as $insumo)
                @php
                $totalInsumos++;
                $somaQuantidadeInsumos += (float) $insumo['quantidade'];
                @endphp
                <tr>
                    <td>{{ $insumo['nome'] }}</td>
                    <td>{{ $insumo['codigo'] }}</td>
                    <td>{{ $insumo['unidade'] }}</td>
                    <td class="text-right">
                        {{ number_format((float)$insumo['quantidade'], 3, ',', '.') }}
                    </td>
                </tr>
                @endforeach

                <tr>
                    <td colspan="3" class="summary-label">Nº de insumos</td>
                    <td class="text-right">{{ $totalInsumos }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="summary-label">Soma das Qtdes</td>
                    <td class="text-right">{{ number_format($somaQuantidadeInsumos, 3, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>
</body>
</html>