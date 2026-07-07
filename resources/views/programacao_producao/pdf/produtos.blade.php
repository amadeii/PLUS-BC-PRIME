<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Produtos a Produzir</title>

    <style>
        @page{ margin:0cm 0cm; }

        body{ margin:1cm; font-family:DejaVu Sans,sans-serif; font-size:11px; color:#111827; }

        header{ margin-bottom:15px; margin-top:-20px; }
        header table{ width:100%; border-collapse:collapse; }
        header td{ border:none !important; vertical-align:middle; }
        header .logo{ width:25%; text-align:left; }
        header .logo img{ height:45px; }
        header .titulo{ width:50%; text-align:center; }
        header .titulo .title{ font-size:18px; font-weight:bold; margin-bottom:2px; }
        header .titulo .subtitle{ color:#6b7280; font-size:11px; }
        header .data{ width:25%; text-align:right; font-size:11px; color:#666; }

        table{ width:100%; border-collapse:collapse; }
        th{ background:#111827; color:#fff; padding:7px; text-align:left; font-size:10px; }
        td{ border-bottom:1px solid #e5e7eb; padding:7px; vertical-align:top; }

        .badge{ display:inline-block; padding:3px 7px; border-radius:10px; font-size:9px; font-weight:bold; }
        .dark{ background:#111827; color:#fff; }
        .info{ background:#e0f2fe; color:#0369a1; }
        .success{ background:#dcfce7; color:#166534; }
        .warning{ background:#fef3c7; color:#92400e; }
        .muted{ color:#6b7280; font-size:9px; }

        .text-end{ text-align:right; }
        .text-center{ text-align:center; }

        footer{ position:fixed; bottom:0.2cm; left:1cm; right:1cm; padding-top:5px; border-top:1px solid #d1d5db; }
        footer table{ width:100%; border:none !important; }
        footer td{ border:none !important; vertical-align:middle; }
        footer .footer-logo img{ height:55px; opacity:.9; }
        footer .footer-site{ text-align:right; font-size:10px; color:#6b7280; }
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
                    <div class="title">Produtos a Produzir</div>
                    <div class="subtitle">Somente itens de pedidos com status pendente</div>
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
                <th class="text-end">Estoque</th>
                <th class="text-end">Demanda</th>
                <th class="text-center">Sugestão</th>
                <th class="text-end">Qtd Produzir</th>
            </tr>
        </thead>

        <tbody>

            @forelse($produtos as $item)

            <tr>

                <td>
                    <span class="badge dark">{{ $item['codigo'] }}</span>
                </td>

                <td>
                    <strong>{{ $item['descricao'] }}</strong>
                    <div class="muted">
                        ID: {{ $item['produto_id'] }}
                    </div>
                </td>

                <td>
                    <span class="badge info">
                        {{ $item['tipo'] }}
                    </span>
                </td>

                <td class="text-end">
                    {{ number_format($item['estoque'], 3, ',', '.') }}
                </td>

                <td class="text-end">
                    {{ number_format($item['demanda'], 3, ',', '.') }}
                </td>

                <td class="text-center">

                    @if($item['sugestao'] > 0)

                    <span class="badge warning">
                        Produzir
                    </span>

                    @else

                    <span class="badge success">
                        OK
                    </span>

                    @endif

                </td>

                <td class="text-end">
                    <strong>
                        {{ number_format($item['qtd_produzir'], 3, ',', '.') }}
                    </strong>
                </td>

            </tr>

            @empty

            <tr>
                <td colspan="7" style="text-align:center; padding:20px;">
                    Nenhum produto pendente para produzir
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