<div class="row g-2">

    <div class="col-12">
        <h5 class="mb-1">Dados do Pagador</h5>
        <hr class="mt-1">
    </div>

    <div class="col-md-3">
        {!! Form::text('nome_pagador', 'Nome do pagador')->required() !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('documento_pagador', 'Documento do pagador')
        ->attrs(['class' => 'cpf_cnpj'])
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('valor_transporte', 'Valor transporte')
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_transporte) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('indicador_pagamento', 'Indicador do pagamento', App\Models\Mdfe::indicadoresDePagamento())
        ->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-12 mt-3">
        <h5 class="mb-1">Componente</h5>
        <hr class="mt-1">
    </div>

    <div class="col-md-2">
        {!! Form::select('tipo_componente', 'Tipo componente', App\Models\Mdfe::tiposDeComponentes())
        ->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('valor_componente', 'Valor componente')
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_componente) : '')
        !!}
    </div>

    <div class="col-md-4">
        {!! Form::text('descricao', 'Descrição do componente') !!}
    </div>

    <div class="col-12 mt-3">
        <h5 class="mb-1">Parcelamento</h5>
        <hr class="mt-1">
    </div>

    <div class="col-md-2">
        {!! Form::tel('valor_parcela', 'Valor parcela')
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_parcela) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::date('vencimento', 'Vencimento') !!}
    </div>

    <div class="col-12 mt-3">
        <h5 class="mb-1">Dados Bancários</h5>
        <hr class="mt-1">
    </div>

    <div class="col-md-2">
        {!! Form::text('codigo_banco', 'Código banco') !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('codigo_agencia', 'Código agência') !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('cnpj_iof', 'CNPJ IOF')
        ->attrs(['class' => 'cnpj'])
        !!}
    </div>

    <div class="col-12 mt-4" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>

</div>