<div class="row g-2">

    <div class="col-md-4">
        {!!Form::select('funcionario_id', 'Funcionário',
        ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
        ->attrs(['class' => 'select2'])
        ->required()
        ->id('func')
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::select('jornada_id', 'Jornada',
        ['' => 'Selecione'] + $jornadas->pluck('descricao', 'id')->all())
        ->attrs(['class' => 'select2'])
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_inicio', 'Data início')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_fim', 'Data fim')
        !!}
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>

</div>