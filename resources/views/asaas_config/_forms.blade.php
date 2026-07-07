<div class="row g-2">

    <div class="col-md-10">
        {!! Form::text('token', 'Token')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('sandbox', 'Ambiente', [1 => 'Sandbox', 0 => 'Produção'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('ultimo_numero_boleto', 'Último Número Boleto')
        ->required()
        ->min(1)
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('juros_padrao', '% Juros padrão')
        ->attrs(['class' => 'percentual'])
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('multa_padrao', '% Multa padrão')
        ->attrs(['class' => 'percentual'])
        !!}
    </div>

    <div class="col-md-6">
        {!! Form::text('observacao_padrao', 'Observação padrão')
        ->attrs(['class' => ''])
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])
        ->attrs(['class' => 'form-select']) !!}
    </div>

</div>
<hr>
<div class="row mt-3">
    <div class="col-md-12 text-end">
        <button class="btn btn-success">
            <i class="ri-check-line"></i> Salvar Configuração
        </button>
    </div>
</div>