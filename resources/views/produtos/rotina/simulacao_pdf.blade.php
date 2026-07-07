<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Simulação de Custo</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2d3748;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .topo {
            width: 100%;
            border-bottom: 3px solid #2b6cb0;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .topo-titulo {
            font-size: 22px;
            font-weight: bold;
            color: #1a202c;
            margin: 0;
        }

        .topo-subtitulo {
            font-size: 11px;
            color: #718096;
            margin-top: 4px;
        }

        .dados-principais {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .dados-principais td {
            width: 33.33%;
            vertical-align: top;
        }

        .card-info {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
        }

        .card-info .label {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 4px;
            display: block;
        }

        .card-info .valor {
            font-size: 15px;
            font-weight: bold;
            color: #1a202c;
        }

        .produto-box {
            border: 1px solid #cbd5e0;
            background: #edf2f7;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
        }

        .produto-nome {
            font-size: 16px;
            font-weight: bold;
            color: #1a202c;
            margin-bottom: 4px;
        }

        .secao {
            margin-bottom: 20px;
        }

        .secao-titulo {
            font-size: 14px;
            font-weight: bold;
            color: #1a202c;
            padding-left: 10px;
            border-left: 5px solid #2b6cb0;
            margin-bottom: 10px;
        }

        .resumo-linha {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .resumo-linha td {
            width: 50%;
            vertical-align: top;
        }

        .mini-card {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 10px;
            border-radius: 6px;
        }

        .mini-card .mini-label {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }

        .mini-card .mini-valor {
            font-size: 14px;
            font-weight: bold;
            color: #2b6cb0;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.grid th {
            background: #2d3748;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 8px 6px;
            border: 1px solid #cbd5e0;
            text-align: left;
        }

        table.grid td {
            border: 1px solid #e2e8f0;
            padding: 7px 6px;
            font-size: 10px;
            vertical-align: middle;
        }

        table.grid tbody tr:nth-child(even) {
            background: #f7fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .custo-final {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
            margin-top: 8px;
        }

        .custo-final td {
            width: 33.33%;
            vertical-align: top;
        }

        .card-final {
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            text-align: center;
        }

        .card-final .titulo-final {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #4a5568;
            margin-bottom: 6px;
        }

        .card-final .valor-final {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }

        .card-final.destaque {
            background: #2b6cb0;
            border-color: #2b6cb0;
        }

        .card-final.destaque .titulo-final,
        .card-final.destaque .valor-final {
            color: #ffffff;
        }

        .prazo-box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 14px;
            border-radius: 8px;
        }

        .prazo-box p {
            margin: 4px 0;
            font-size: 11px;
        }

        .rodape {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #718096;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="topo">
        <div class="topo-titulo">Simulação de Custo de Produção</div>
        <div class="topo-subtitulo">Relatório gerencial para análise de custo, processo e preço sugerido</div>
    </div>

    <div class="produto-box">
        <div class="produto-nome">{{ $produto->nome ?? '' }}</div>
        <div>
            Produto simulado com base na estrutura de materiais, processos e markup informado.
        </div>
    </div>

    <table class="dados-principais">
        <tr>
            <td>
                <div class="card-info">
                    <span class="label">Quantidade Simulada</span>
                    <span class="valor">{{ number_format($dados['quantidade'] ?? 0, 2, ',', '.') }}</span>
                </div>
            </td>
            <td>
                <div class="card-info">
                    <span class="label">Markup Aplicado</span>
                    <span class="valor">{{ number_format($dados['markup'] ?? 0, 2, ',', '.') }}%</span>
                </div>
            </td>
            <td>
                <div class="card-info">
                    <span class="label">Data da Simulação</span>
                    <span class="valor">{{ date('d/m/Y') }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="secao">
        <div class="secao-titulo">Custo de Materiais</div>

        <table class="resumo-linha">
            <tr>
                <td>
                    <div class="mini-card">
                        <span class="mini-label">Total de Materiais</span>
                        <span class="mini-valor">R$ {{ number_format($dados['total_materiais'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="mini-card">
                        <span class="mini-label">Quantidade de Itens</span>
                        <span class="mini-valor">{{ $dados['qtd_materiais'] }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="grid">
            <thead>
                <tr>
                    <th width="10%">Código</th>
                    <th width="28%">Descrição</th>
                    <th width="12%" class="text-right">Qtde</th>
                    <th width="8%" class="text-center">UM</th>
                    <th width="14%">Categoria</th>
                    <th width="14%" class="text-right">Custo Unit.</th>
                    <th width="14%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($dados['materiais'] ?? []) as $m)
                <tr>
                    <td>{{ $m['codigo'] }}</td>
                    <td>
                        <div style="padding-left: {{ ($m['nivel'] ?? 0) * 12 }}px;">
                            @if(($m['nivel'] ?? 0) > 0)
                            <span style="color:#888;">&#8627;</span>
                            @endif
                            <strong>{{ $m['descricao'] }}</strong>
                        </div>
                    </td>
                    <td class="text-right">
                        {{ number_format($m['quantidade_total'], 2, ',', '.') }}
                    </td>
                    <td class="text-center">{{ $m['unidade'] }}</td>
                    <td>{{ $m['categoria'] }}</td>

                    @if(($m['nivel'] ?? 0) > 0)
                    <td class="text-right" style="color:#999;">--</td>
                    <td class="text-right" style="color:#999;">--</td>
                    @else
                    <td class="text-right">
                        R$ {{ number_format($m['custo_unitario'], 2, ',', '.') }}
                    </td>
                    <td class="text-right">
                        <strong>R$ {{ number_format($m['total'], 2, ',', '.') }}</strong>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Nenhum material informado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- <table class="grid">
            <thead>
                <tr>
                    <th width="10%">Código</th>
                    <th width="28%">Descrição</th>
                    <th width="12%" class="text-right">Qtde</th>
                    <th width="8%" class="text-center">UM</th>
                    <th width="14%">Categoria</th>
                    <th width="14%" class="text-right">Custo Unit.</th>
                    <th width="14%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($dados['materiais'] ?? []) as $m)
                <tr>
                    <td>{{ $m['codigo'] }}</td>
                    <td>
                        <div style="padding-left: {{ ($m['nivel'] ?? 0) * 12 }}px;">
                            @if(($m['nivel'] ?? 0) > 0)
                            <span style="color:#888;">&#8627;</span>
                            @endif
                            {{ $m['descricao'] }}
                        </div>
                    </td>
                    <td class="text-right">{{ number_format($m['quantidade_total'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ $m['unidade'] }}</td>
                    <td>{{ $m['categoria'] }}</td>
                    <td class="text-right">R$ {{ number_format($m['custo_unitario'], 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($m['total'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Nenhum material informado.</td>
                </tr>
                @endforelse
            </tbody>
        </table> -->
    </div>

    <div class="secao">
        <div class="secao-titulo">Custo de Processo</div>

        <table class="resumo-linha">
            <tr>
                <td>
                    <div class="mini-card">
                        <span class="mini-label">Subtotal do Processo</span>
                        <span class="mini-valor">R$ {{ number_format($dados['subtotal_processo'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="mini-card">
                        <span class="mini-label">Operações</span>
                        <span class="mini-valor">{{ count($dados['processos'] ?? []) }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="grid">
            <thead>
                <tr>
                    <th width="26%">Operação</th>
                    <th width="14%" class="text-right">Tempo Total</th>
                    <th width="14%" class="text-right">Custo Hora</th>
                    <th width="16%">Setor</th>
                    <th width="14%">C. Custo</th>
                    <th width="16%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($dados['processos'] ?? []) as $p)
                <tr>
                    <td>{{ $p['operacao'] }}</td>
                    <td class="text-right">{{ number_format($p['tempo_total_min'], 0, ',', '.') }} min</td>
                    <td class="text-right">R$ {{ number_format($p['custo_hora'], 2, ',', '.') }}</td>
                    <td>{{ $p['setor'] }}</td>
                    <td>{{ $p['centro_custo'] }}</td>
                    <td class="text-right">R$ {{ number_format($p['total'], 2, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum processo informado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="secao">
        <div class="secao-titulo">Resumo Final</div>

        <table class="custo-final">
            <tr>
                <td>
                    <div class="card-final">
                        <span class="titulo-final">Custo Total</span>
                        <span class="valor-final">R$ {{ number_format($dados['custo_total'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="card-final">
                        <span class="titulo-final">Custo Unitário</span>
                        <span class="valor-final">R$ {{ number_format($dados['custo_unitario'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </td>
                <td>
                    <div class="card-final destaque">
                        <span class="titulo-final">Preço Sugerido</span>
                        <span class="valor-final">R$ {{ number_format($dados['preco_sugerido'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="secao">
        <div class="secao-titulo">Prazo Estimado</div>

        <div class="prazo-box">
            <p><strong>Carga atual dos setores:</strong> Simulado</p>
            <p><strong>Data estimada de entrega:</strong> {{ $dados['data_entrega'] ?? '--/--/----' }}</p>
        </div>
    </div>

    <div class="rodape">
        Documento gerado automaticamente pelo sistema em {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>