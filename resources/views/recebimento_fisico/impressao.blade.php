<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Recebimento</title>

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .titulo {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitulo {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        th {
            background: #e9e9e9;
        }

        .mb {
            margin-bottom: 10px;
        }

        .sem-borda {
            border: none !important;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .linha-vazia {
            height: 25px;
        }

        .assinatura {
            margin-top: 40px;
            width: 100%;
        }

        .assinatura td {
            border: none;
            text-align: center;
            padding-top: 30px;
        }

        .linha {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <div class="titulo">RECEBIMENTO</div>
    <div class="subtitulo">Conferência física de mercadorias</div>

    <!-- INFORMAÇÕES -->
    <table class="mb">
        <tr>
            <td width="25%">
                <strong>Nº NFe</strong><br>
                {{ $item->numero_nfe ?? $item->numero ?? '--' }}
            </td>

            <td width="45%">
                <strong>Fornecedor</strong><br>
                {{ $item->fornecedor->razao_social ?? $item->fornecedor->nome ?? '--' }}
            </td>

            <td width="30%">
                <strong>Data emissão</strong><br>
                {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '--' }}
            </td>
        </tr>
    </table>

    <!-- ITENS -->
    <table>
        <thead>
            <tr>
                <th style="width: 70px;">Cód.</th>
                <th>Produto</th>
                <th style="width: 140px;">Referência</th>
                <th style="width: 120px;">Qtd Conferida</th>
                <th style="width: 180px;">Observação</th>
            </tr>
        </thead>

        <tbody>
            @forelse($item->itens as $it)
            <tr class="linha-vazia">
                <td class="center">
                    {{ $it->produto->numero_sequencial ?? '--' }}
                </td>

                <td>
                    {{ $it->produto->nome ?? $it->nome ?? 'Produto não identificado' }}
                </td>

                <td class="center">
                    {{ $it->produto->referencia ?? $it->referencia ?? '--' }}
                </td>

                <!-- NÃO MOSTRA QTD XML -->
                <td></td>

                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="center">Nenhum item encontrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- OBSERVAÇÃO -->
    <table class="mb" style="margin-top: 15px;">
        <tr>
            <td style="height: 60px;">
                <strong>Observação geral:</strong>
            </td>
        </tr>
    </table>

    <!-- ASSINATURA -->
    <table class="assinatura">
        <tr>
            <td>
                <div class="linha"></div>
                Conferido por
            </td>

            <td>
                <div class="linha"></div>
                Data / Assinatura
            </td>
        </tr>
    </table>

    <!-- RODAPÉ -->
    <div class="right" style="margin-top: 10px; font-size: 10px;">
        Impresso em: {{ now()->format('d/m/Y H:i') }}
    </div>

    <!-- PAGINAÇÃO -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(500, 800, "Página {PAGE_NUM} / {PAGE_COUNT}", null, 8, array(0,0,0));
        }
    </script>

</body>
</html>