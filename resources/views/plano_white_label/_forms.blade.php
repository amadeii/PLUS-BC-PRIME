<div class="row g-2">
    <div class="col-md-4">
        {!!Form::text('nome', 'Nome')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_mensal', 'Valor mensal')
        ->required()
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_mensal) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_por_empresa', 'Valor por empresa')
        ->required()
        ->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_por_empresa) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('limite_empresas', 'Limite empresas')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('ativo', 'Ativo', ['1' => 'Sim', '0' => 'Não'])
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>