<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Instrução de Trabalho</title>
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
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .topo-titulo {
            font-size: 20px;
            font-weight: bold;
            color: #1a202c;
            margin: 0;
        }

        .topo-subtitulo {
            font-size: 11px;
            color: #718096;
            margin-top: 4px;
        }

        .produto-box {
            border: 1px solid #cbd5e0;
            background: #edf2f7;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 6px;
        }

        .produto-nome {
            font-size: 16px;
            font-weight: bold;
            color: #1a202c;
            margin-bottom: 4px;
        }

        .dados-principais {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 8px;
        }

        .dados-principais td {
            width: 25%;
            vertical-align: top;
        }

        .card-info {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 10px;
            border-radius: 6px;
            min-height: 56px;
        }

        .card-info .label {
            font-size: 10px;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 4px;
            display: block;
        }

        .card-info .valor {
            font-size: 13px;
            font-weight: bold;
            color: #1a202c;
        }

        .secao {
            margin-bottom: 18px;
        }

        .secao-titulo {
            font-size: 14px;
            font-weight: bold;
            color: #1a202c;
            padding-left: 10px;
            border-left: 5px solid #2b6cb0;
            margin-bottom: 10px;
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

        .lista-box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
        }

        .lista-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .lista-box li {
            margin-bottom: 5px;
        }

        .assinatura-tabela {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .assinatura-tabela th,
        .assinatura-tabela td {
            border: 1px solid #dfe6ee;
            padding: 8px;
            font-size: 10px;
        }

        .assinatura-tabela th {
            background: #edf2f7;
            font-weight: bold;
        }

        .imagem-box {
            text-align: center;
            margin-bottom: 14px;
        }

        .imagem-box img {
            max-width: 420px;
            max-height: 350px;
            border: 1px solid #e2e8f0;
            padding: 4px;
            background: #fff;
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
        <div class="topo-titulo">INSTRUÇÃO DE TRABALHO - FABRICAÇÃO INDUSTRIAL</div>
        <div class="topo-subtitulo">Documento operacional para produção, conferência e aprovação</div>
    </div>

    <div class="produto-box">
        <div class="produto-nome">{{ $produto->nome ?? '' }}</div>
        <div>
            Código:
            <strong>{{ $produto->numero_sequencial }}</strong>
        </div>
    </div>

    @php
    $imagemPdf = public_path('uploads/rotina_fabricacao/' . $item->imagem);

    if (empty($item->imagem) || !file_exists($imagemPdf)) {
        $imagemPdf = public_path('imgs/no-image.png');
    }
    @endphp

    <div class="imagem-box">
        <img src="file://{{ $imagemPdf }}">
    </div>

    <table class="dados-principais">
        <tr>
            <td>
                <div class="card-info">
                    <span class="label">Responsável</span>
                    <span class="valor">{{ auth()->user()->name ?? 'Sistema' }}</span>
                </div>
            </td>
            <td>
                <div class="card-info">
                    <span class="label">Data</span>
                    <span class="valor">{{ date('d/m/Y') }}</span>
                </div>
            </td>
            <td>
                <div class="card-info">
                    <span class="label">Revisão</span>
                    <span class="valor">{{ str_pad($item->id, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
            </td>
            <td>
                <div class="card-info">
                    <span class="label">Referência</span>
                    <span class="valor">{{ $produto->referencia }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="secao">
        <div class="secao-titulo">1. Estrutura do Produto</div>

        <table class="grid">
            <thead>
                <tr>
                    <th width="8%">Item</th>
                    <th width="14%">Código</th>
                    <th width="42%">Descrição</th>
                    <th width="12%" class="text-right">Qtd</th>
                    <th width="10%" class="text-center">Un</th>
                    <th width="14%">Categoria</th>
                </tr>
            </thead>
            <tbody>
                @php $cont = 1; @endphp
                @forelse($materiais as $m)
                @if(($m['nivel'] ?? 0) == 0)
                <tr>
                    <td>{{ $cont++ }}</td>
                    <td>{{ $m['codigo'] ?? '--' }}</td>
                    <td>{{ $m['descricao'] ?? '--' }}</td>
                    <td class="text-right">{{ number_format($m['quantidade_total'] ?? 0, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $m['unidade'] ?? '--' }}</td>
                    <td>{{ $m['categoria'] ?? '--' }}</td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum material informado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="secao">
        <div class="secao-titulo">2. Operações de Fabricação</div>

        <table class="grid">
            <thead>
                <tr>
                    <th width="10%">Seq</th>
                    <th width="34%">Operação</th>
                    <th width="18%">Centro / Setor</th>
                    <th width="18%" class="text-right">Tempo</th>
                    <th width="20%">C. Custo</th>
                </tr>
            </thead>
            <tbody>
                @php $seq = 10; @endphp
                @forelse(($item->operacoes ?? []) as $op)
                <tr>
                    <td>{{ $seq }}</td>
                    <td>{{ $op->operacao->nome ?? $op->nome ?? 'Operação' }}</td>
                    <td>{{ $op->setor->nome ?? $op->operacao->setor->nome ?? '--' }}</td>
                    <td class="text-right">
                        {{ number_format((float)($op->tempo ?? $op->tempo_min ?? 0), 0, ',', '.') }} min
                    </td>
                    <td>
                        {{ $op->setor->centroCusto->nome ?? $op->operacao->setor->centroCusto->nome ?? '--' }}
                    </td>
                </tr>
                @php $seq += 10; @endphp
                @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhuma operação informada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <!-- <div class="secao">
        <div class="secao-titulo">3. Instruções Especiais</div>
        <div class="lista-box">
            <ul>
                @foreach($instrucoesEspeciais as $linha)
                <li>{{ $linha }}</li>
                @endforeach
            </ul>
        </div>
    </div> -->


    <div class="secao">
        <div class="secao-titulo">3. Check-list de Qualidade</div>
        <div class="lista-box">
            <ul>
                {!! $item->checklist_texto !!}
            </ul>
        </div>
    </div>

    <div class="secao">
        <div class="secao-titulo">4. Assinaturas</div>

        <div class="lista-box">
            <ul>

                {!! $item->assinaturas !!}

            </ul>
        </div>

    </div>

    <div class="rodape">
        Documento gerado automaticamente pelo sistema em {{ date('d/m/Y H:i') }}
    </div>

</body>
</html>