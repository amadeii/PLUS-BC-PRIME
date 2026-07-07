<div class="row g-2">

    <div class="col-md-2">
        {!!Form::text('codigo', 'Código')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::text('nome', 'Nome do setor')->required()!!}
    </div>

    <div class="col-md-6">
        {!!Form::text('descricao', 'Descrição')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::text('horas_dia', 'Horas por dia')
        ->attrs(['class' => 'number'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('custo_hora', 'Custo por hora')
        ->attrs(['class' => 'moeda'])
        ->required()
        ->value(isset($item) ? __moeda($item->custo_hora) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('eficiencia', 'Eficiência %')
        ->attrs(['class' => 'percentual'])
        ->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('centro_custo_id', 'Centro de custo', ['' => 'Selecione'] + $centrosDeCusto->pluck('nome', 'id')->all())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>

</div>