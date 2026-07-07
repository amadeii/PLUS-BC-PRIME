<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Composição do Produto</title>
    <style>
        @page {
            margin: 22px 24px;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            line-height: 1.4;
        }

        .header{
            border-bottom: 2px solid #4254BA;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .title{
            font-size: 26px;
            font-weight: bold;
            color: #4254BA;
            margin: 0 0 4px 0;
        }

        .subtitle{
            font-size: 11px;
            color: #6b7280;
            margin: 0;
        }

        .info-box{
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f8faff;
            border: 1px solid #d9e1f2;
        }

        .info-box td{
            padding: 10px 12px;
            font-size: 12px;
            border: none;
        }

        .info-label{
            font-weight: bold;
            color: #374151;
        }

        .table{
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table thead th{
            background: #4254BA;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 10px 12px;
            border: 1px solid #5a6bd1;
        }

        .table tbody td{
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .table tbody tr:nth-child(even){
            background: #fafbff;
        }

        .produto-col{
            width: 74%;
            color: #111827;
        }

        .qtd-col{
            width: 26%;
            text-align: center;
            font-weight: bold;
            color: #4254BA;
            white-space: nowrap;
        }

        .nivel-0{
            font-weight: bold;
        }

        .nivel-1,
        .nivel-2,
        .nivel-3,
        .nivel-4,
        .nivel-5{
            font-weight: normal;
        }

        .seta{
            color: #6b7280;
            font-weight: bold;
            margin-right: 4px;
        }

        .footer{
            margin-top: 14px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: right;
        }

        .empty{
            text-align: center;
            color: #6b7280;
            padding: 18px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">Composição do Produto</h1>
        <p class="subtitle">Documento gerado automaticamente pelo sistema</p>
    </div>

    <table class="info-box">
        <tr>
            <td width="65%">
                <span class="info-label">Produto:</span> {{ $item->nome }}
            </td>
            <td width="35%" style="text-align: right;">
                <span class="info-label">Emitido em:</span> {{ date('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th class="produto-col">Ingrediente / Produto</th>
                <th class="qtd-col">Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $comp)
            <tr>
                <td class="produto-col">
                    <div class="nivel-{{ $comp->nivel }}" style="padding-left: {{ $comp->nivel * 16 }}px;">
                        @if($comp->nivel > 0)
                        <span class="seta">↳</span>
                        @endif
                        {{ $comp->ingrediente->nome }}
                    </div>
                </td>
                <td class="qtd-col">
                    {{ number_format((float)$comp->quantidade, 3, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="empty">Nenhum ingrediente adicionado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ env('APP_NAME') }} • Ficha técnica do produto
    </div>

</body>
</html>