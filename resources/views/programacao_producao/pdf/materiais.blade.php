<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Necessidade de Materiais</title>

    <style>
        @page{ margin:0cm 0cm; }

        body{
            margin:1cm;
            font-family:DejaVu Sans,sans-serif;
            font-size:11px;
            color:#111827;
        }

        header{
            margin-bottom:15px;
            margin-top:-20px;
        }

        header table{
            width:100%;
            border-collapse:collapse;
        }

        header td{
            border:none !important;
            vertical-align:middle;
        }

        header .logo{
            width:25%;
            text-align:left;
        }

        header .logo img{
            height:45px;
        }

        header .titulo{
            width:50%;
            text-align:center;
        }

        header .titulo .title{
            font-size:18px;
            font-weight:bold;
            margin-bottom:2px;
        }

        header .titulo .subtitle{
            color:#6b7280;
            font-size:11px;
        }

        header .data{
            width:25%;
            text-align:right;
            font-size:11px;
            color:#666;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#111827;
            color:#fff;
            padding:7px;
            text-align:left;
            font-size:10px;
        }

        td{
            border-bottom:1px solid #e5e7eb;
            padding:7px;
            vertical-align:top;
        }

        .badge{
            display:inline-block;
            padding:3px 7px;
            border-radius:10px;
            font-size:9px;
            font-weight:bold;
        }

        .dark{ background:#111827; color:#fff; }
        .light{ background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; }
        .success{ background:#dcfce7; color:#166534; }
        .danger{ background:#fee2e2; color:#991b1b; }

        .muted{
            color:#6b7280;
            font-size:9px;
        }

        .text-end{ text-align:right; }
        .text-center{ text-align:center; }

        footer{
            position:fixed;
            bottom:0.2cm;
            left:1cm;
            right:1cm;
            padding-top:5px;
            border-top:1px solid #d1d5db;
        }

        footer table{
            width:100%;
            border:none !important;
        }

        footer td{
            border:none !important;
            vertical-align:middle;
        }

        footer .footer-logo img{
            height:55px;
            opacity:.9;
        }

        footer .footer-site{
            text-align:right;
            font-size:10px;
            color:#6b7280;
        }
    </style>
</head>

<body>

    @php
        $config = \App\Models\Empresa::findOrFail(request()->empresa_id);
    @endphp

    <header>
        <table>
            <tr>

                <td class="logo">
                    @if($config->logo)
                    <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('uploads/logos/' . $config->logo))) }}">
                    @else
                    <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('logo.png'))) }}">
                    @endif
                </td>

                <td class="titulo">
                    <div class="title">
                        Necessidade de Materiais
                    </div>

                    <div class="subtitle">
                        Materiais necessários com base nos produtos a produzir
                    </div>
                </td>

                <td class="data">
                    <strong>Emissão:</strong><br>
                    {{ date('d/m/Y H:i') }}
                </td>

            </tr>
        </table>
    </header>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th>Tipo</th>
                <th class="text-end">Necessidade</th>
                <th class="text-center">Un.</th>
                <th class="text-end">Estoque</th>
                <th class="text-end">Falta</th>
                <th class="text-center">Situação</th>
            </tr>
        </thead>

        <tbody>

            @forelse($materiais as $item)

            <tr>

                <td>
                    <span class="badge dark">{{ $item['codigo'] }}</span>
                </td>

                <td>
                    <strong>{{ $item['descricao'] }}</strong>
                    <div class="muted">
                        ID: {{ $item['produto_id'] ?? '-' }}
                    </div>
                </td>

                <td>
                    <span class="badge light">
                        {{ $item['tipo'] }}
                    </span>
                </td>

                <td class="text-end">
                    <strong>{{ number_format($item['necessidade'], 3, ',', '.') }}</strong>
                </td>

                <td class="text-center">
                    {{ $item['unidade'] }}
                </td>

                <td class="text-end">
                    {{ number_format($item['estoque'], 3, ',', '.') }}
                </td>

                <td class="text-end">

                    @if($item['falta'] > 0)

                    <strong style="color:#991b1b;">
                        {{ number_format($item['falta'], 3, ',', '.') }}
                    </strong>

                    @else

                    <strong style="color:#166534;">
                        0,000
                    </strong>

                    @endif

                </td>

                <td class="text-center">

                    @if($item['situacao'] == 'FALTA')

                    <span class="badge danger">
                        Falta Material
                    </span>

                    @else

                    <span class="badge success">
                        OK
                    </span>

                    @endif

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="8" style="text-align:center; padding:20px;">
                    Nenhuma necessidade de material encontrada
                </td>
            </tr>

            @endforelse

        </tbody>
    </table>

    <footer>
        <table>
            <tr>

                <td class="footer-logo">
                    <img src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('logo.png'))) }}">
                </td>

                <td class="footer-site">
                    {{ env('SITE_SUPORTE') ?? 'https://slym.com.br' }}
                </td>

            </tr>
        </table>
    </footer>

</body>
</html>