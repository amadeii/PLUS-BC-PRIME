<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        width: 240px;
        margin: -30px;
        padding: 0;
    }

    .container {
        width: 100%;
    }

    .center {
        text-align: center;
    }

    .title {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .line {
        border-top: 1px dashed #000;
        margin: 6px 0;
    }

    .row {
        margin-bottom: 4px;
    }

    .label {
        font-weight: bold;
    }

    .value {
        float: right;
    }

    .clearfix {
        clear: both;
    }

    .footer {
        margin-top: 10px;
        font-size: 9px;
        text-align: center;
        color: #555;
    }
</style>
</head>

<body>
<div class="container">

    <div class="center title">
        COMPROVANTE DE PAGAMENTO
    </div>

    <div class="line"></div>

    @if($item->cliente)
    <div class="row">
        <span class="label">Cliente:</span><br>
        {{ $item->cliente->info }}
    </div>
    @endif

    <div class="line"></div>

    <div class="row">
        <span class="label">Valor Integral:</span>
        <span class="value">
            R$ {{ __moeda($item->valor_original > 0 ? $item->valor_original : $item->valor_integral) }}
        </span>
        <div class="clearfix"></div>
    </div>

    <div class="row">
        <span class="label">Multa:</span>
        <span class="value">
            R$ {{ __moeda($item->valor_multa) }}
        </span>
        <div class="clearfix"></div>
    </div>

    <div class="row">
        <span class="label">Juros:</span>
        <span class="value">
            R$ {{ __moeda($item->valor_juros) }}
        </span>
        <div class="clearfix"></div>
    </div>

    <div class="row">
        <span class="label">Valor Recebido:</span>
        <span class="value">
            R$ {{ __moeda($item->valor_recebido) }}
        </span>
        <div class="clearfix"></div>
    </div>

    @if(($item->valor_original > 0 ? $item->valor_original : $item->valor_integral) > $item->valor_recebido)
    <div class="row">
        <span class="label">Valor Restante:</span>
        <span class="value">
            R$ {{ __moeda(($item->valor_original > 0 ? $item->valor_original : $item->valor_integral) - $item->valor_recebido) }}
        </span>
        <div class="clearfix"></div>
    </div>
    @endif

    <div class="line"></div>

    <div class="row">
        <span class="label">Vencimento:</span>
        <span class="value">{{ __data_pt($item->data_vencimento, 0) }}</span>
        <div class="clearfix"></div>
    </div>

    <div class="row">
        <span class="label">Recebimento:</span>
        <span class="value">{{ __data_pt($item->data_recebimento, 0) }}</span>
        <div class="clearfix"></div>
    </div>

    <div class="row">
        <span class="label">Cadastro:</span>
        <span class="value">{{ __data_pt($item->created_at) }}</span>
        <div class="clearfix"></div>
    </div>

    <div class="line"></div>

    <div class="footer">
        Documento gerado pelo sistema {{ env("APP_NAME") }}<br>
        {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</body>
</html>
