<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>

        body{
            font-family: DejaVu Sans;
            font-size:12px;
        }

        .titulo{
            text-align:center;
            font-size:18px;
            font-weight:bold;
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,td{
            border:1px solid #000;
            padding:5px;
            font-size:11px;
        }

        th{
            background:#eee;
        }

    </style>

</head>

<body>

    <div class="titulo">
        RELATÓRIO DE DIVERGÊNCIAS
    </div>

    <b>Compra:</b> {{ $compra->id }} <br>
    <b>Fornecedor:</b> {{ $compra->fornecedor->info ?? '' }} <br>
    <b>Data:</b> {{ __data_pt($compra->created_at) }} <br>

    <br>

    <h3>Divergências de Itens</h3>

    <table>

        <tr>
            <th>Produto</th>
            <th>Status</th>
            <th>Qtd XML</th>
            <th>Qtd Compra</th>
            <th>Valor XML</th>
            <th>Valor Compra</th>
        </tr>

        @foreach($divergenciasItens as $item)

        <tr>
            <td>{{ $item->produto }}</td>
            <td>{{ $item->status }}</td>
            <td>{{ $item->quantidade_xml }}</td>
            <td>{{ $item->quantidade_compra }}</td>
            <td>{{ $item->valor_xml ? __moeda($item->valor_xml) : '' }}</td>
            <td>{{ $item->valor_compra ? __moeda($item->valor_compra) : '' }}</td>

        </tr>

        @endforeach

    </table>


    <br>

    <h3>Divergências de Faturas</h3>

    <table>

        <tr>
            <th>Status</th>
            <th>Valor XML</th>
            <th>Valor Compra</th>
            <th>Vencimento XML</th>
            <th>Vencimento Compra</th>
        </tr>

        @foreach($divergenciasFaturas as $fat)

        <tr>
            <td>{{ $fat->status }}</td>
            <td>{{ __moeda($fat->valor_xml) }}</td>
            <td>{{ __moeda($fat->valor_compra) }}</td>
            <td>{{ $fat->vencimento_xml ? __data_pt($fat->vencimento_xml,0) : '' }}</td>
            <td>{{ $fat->vencimento_compra ? __data_pt($fat->vencimento_compra,0) : '' }}</td>
        </tr>

        @endforeach

    </table>


    @if($divergenciaTotal)

    <br>
    <b style="color:red">
        {{ $divergenciaTotal->status }}
    </b>

    @endif

</body>
</html>