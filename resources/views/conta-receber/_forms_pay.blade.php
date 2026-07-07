<div class="row g-2">

    <div class="col-md-2">
        {!!Form::tel('valor_multa', 'Valor multa')
        ->attrs(['class' => 'moeda'])
        ->value(__moeda($valorMulta))
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_juros', 'Valor juros')
        ->attrs(['class' => 'moeda'])
        ->value(__moeda($valorJuros))
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_pago', 'Valor Recebido')
        ->attrs(['class' => 'moeda'])
        ->required()
        ->value(__moeda($valorReceber))
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_recebimento', 'Data do Recebimento')
        ->required()
        ->value(date('Y-m-d'))
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo_pagamento', 'Tipo de Pagamento', ['' => 'Selecione'] + App\Models\ContaReceber::tiposPagamento())
        ->attrs(['class' => 'form-select'])
        ->required()
        ->value($item->tipo_pagamento)
        !!}
    </div>

    <div class="col-md-3 div-conta-empresa">
        {!!Form::select('conta_empresa_id', 'Conta empresa')
        ->required()
        !!}
    </div>

    @if($temPlanoConta)
    <div class="col-md-4">
        {!!Form::select('plano_conta_id', 'Plano de conta')
        ->attrs(['class' => 'form-select'])
        ->options(isset($item) && $item->plano_conta_id ? [$item->plano_conta_id => $item->planoConta->descricao] : [])
        !!}
    </div>
    @endif

    <div class="col-12 text-end mt-4">
        <button type="submit" class="btn btn-success text-white fw-bold">
            <i class="ri-check-double-line me-2"></i>
            Confirmar Recebimento
        </button>
    </div>
</div>
