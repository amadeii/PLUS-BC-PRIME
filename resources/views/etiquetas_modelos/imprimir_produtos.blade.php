<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Imprimir Etiquetas</title>

    @php
    $porLinha = max(1, (int)($item->etiquetas_por_linha ?? 1));
    $larguraEtiqueta = (float)($item->largura ?? 60);
    $alturaEtiqueta = (float)($item->altura ?? 40);
    $espacoHorizontal = (float)($item->espaco_horizontal ?? 0);
    $espacoVertical = (float)($item->espaco_vertical ?? 0);
    $fontePadrao = (int)($item->fonte_padrao ?? 10);
    $mostrarNumeroCodigoBarras = (bool)($item->mostrar_numero_codigo_barras ?? true);

    $larguraFolha = ($larguraEtiqueta * $porLinha) + ($espacoHorizontal * max(0, $porLinha - 1));
    @endphp


    <style>
        @page{ margin:0; }
        *{ box-sizing:border-box; }
        body{ margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; color:#000; }

        .no-print{ padding:10px; border-bottom:1px solid #ddd; margin-bottom:10px; }
        .no-print button{ padding:8px 14px; border:0; border-radius:6px; background:#198754; color:#fff; cursor:pointer; }

        .pagina{
            width:{{ $larguraFolha }}mm;
            display:grid;
            grid-template-columns:repeat({{ $porLinha }}, {{ $larguraEtiqueta }}mm);
            grid-auto-rows:{{ $alturaEtiqueta }}mm;
            column-gap:{{ $espacoHorizontal }}mm;
            row-gap:{{ $espacoVertical }}mm;
            align-items:start;
            justify-content:start;
        }

        .etiqueta{
            position:relative;
            width:{{ $larguraEtiqueta }}mm;
            height:{{ $alturaEtiqueta }}mm;
            overflow:hidden;
            page-break-inside:avoid;
            break-inside:avoid;
        }

        .campo{
            position:absolute;
            line-height:.85;
            color:#000;
            white-space:nowrap;
            padding:0;
            margin:0;
            font-size:{{ $fontePadrao }}px !important;
        }

        @media print{
            .no-print{ display:none!important; }
            body{ margin:0; padding:0; }
            .pagina{ margin:0; }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <div class="pagina">

        @foreach($produtosSelecionados as $linha)
        @php
        $produto = $linha['produto'];
        $qtd = (int)($linha['qtd'] ?? 0);
        @endphp

        @for($i = 0; $i < $qtd; $i++)

        <div class="etiqueta">

            @foreach($layout as $campo)

            @php
            $valor = '';

            switch($campo['tipo']){
                case 'produto_nome':
                $valor = $produto->nome ?? '';
                break;

                case 'produto_valor':
                $valor = 'R$ ' . __moeda($produto->valor_unitario);
                break;

                case 'produto_codigo':
                $valor = $produto->numero_sequencial ?? $produto->id;
                break;

                case 'produto_referencia':
                $valor = $produto->referencia ?? '';
                break;

                case 'codigo_barras':
                $valor = $produto->codigo_barras ?? '';
                break;

                case 'empresa_nome':
                $valor = $produto->empresa->nome ?? '';
                break;
            }
            @endphp

            @php
            $escalaEditor = 4;
            $tipoCampo = $campo['tipo'] ?? '';
            @endphp

            <div class="campo" style="
            left:{{ (($campo['x'] ?? 0) / $escalaEditor) }}mm;
            top:{{ ((($campo['y'] ?? 0) / $escalaEditor) - 1.5) }}mm;
            font-size:{{ (($campo['fontSize'] ?? $fontePadrao) * 0.85) }}px;
            font-weight:{{ !empty($campo['bold']) ? '700' : '400' }};
            line-height:.85;
            ">

            @if($tipoCampo == 'codigo_barras')

            <div style="width:{{ $item->largura_codigo_barras ?? 38 }}mm;text-align:center;">

                @if(!empty($produto->barcode_base64))
                <img
                src="{{ $produto->barcode_base64 }}"
                style="
                width:{{ $item->largura_codigo_barras ?? 38 }}mm;
                height:{{ $item->altura_codigo_barras ?? 10 }}mm;
                display:block;
                margin:0 auto;
                "
                >
                @endif

                @if($mostrarNumeroCodigoBarras && !empty($produto->codigo_barras))
                <div style="
                width:100%;
                text-align:center;
                font-size:9px;
                line-height:.8;
                margin-top:1px;
                font-weight:600;
                ">
                {{ $produto->codigo_barras }}
            </div>
            @endif

        </div>

        @else

        {{ $valor }}

        @endif

    </div>

    @endforeach

</div>

@endfor
@endforeach

</div>

<script>
    window.onload = function(){
        setTimeout(function(){
            // window.print();
        }, 500);
    }
</script>

</body>
</html>