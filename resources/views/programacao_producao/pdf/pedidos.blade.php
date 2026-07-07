<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Programação de Pedidos</title>

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
        .success{ background:#dcfce7; color:#166534; }
        .warning{ background:#fef3c7; color:#92400e; }
        .primary{ background:#dbeafe; color:#1d4ed8; }
        .danger{ background:#fee2e2; color:#991b1b; }
        .muted{ background:#f3f4f6; color:#374151; }

        .text-end{ text-align:right; }

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
                    <div class="title">Programação de Pedidos</div>
                    <div class="subtitle">Pedidos pendentes de faturamento e produção</div>
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
                <th>Pedido</th>
                <th>Cliente</th>
                <th class="text-end">Qtd</th>
                <th class="text-end">Produzido</th>
                <th class="text-end">% Produção</th>
                <th>Emissão</th>
                <th>Entrega</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($pedidos as $item)

            <tr>
                <td>#{{ $item['pedido'] }}</td>
                <td>{{ $item['cliente'] }}</td>
                <td class="text-end">{{ number_format($item['qtde'], 3, ',', '.') }}</td>
                <td class="text-end">{{ number_format($item['produzido'], 3, ',', '.') }}</td>
                <td class="text-end">{{ number_format($item['percentual'], 1, ',', '.') }}%</td>
                <td>{{ $item['data_emissao'] ? \Carbon\Carbon::parse($item['data_emissao'])->format('d/m/Y') : '-' }}</td>
                <td>{{ $item['data_entrega'] ? \Carbon\Carbon::parse($item['data_entrega'])->format('d/m/Y') : '-' }}</td>

                <td>
                    @if($item['status_producao'] == 'Finalizado')
                    <span class="badge success">Finalizado</span>
                    @elseif($item['status_producao'] == 'Andamento')
                    <span class="badge primary">Andamento</span>
                    @else
                    <span class="badge warning">Pendente</span>
                    @endif

                    @if($item['status_prazo'] == 'Atrasado')
                    <span class="badge danger">Atrasado</span>
                    @else
                    <span class="badge muted">Normal</span>
                    @endif
                </td>
            </tr>

            @empty

            <tr>
                <td colspan="8" style="text-align:center; padding:20px;">
                    Nenhum pedido encontrado
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