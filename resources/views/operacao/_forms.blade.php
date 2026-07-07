<div class="row g-2">

    <div class="col-md-2">
        {!!Form::text('codigo', 'Código')->required()!!}
    </div>

    <div class="col-md-3">
        {!!Form::text('nome', 'Nome da operação')->required()!!}
    </div>

    <div class="col-md-7">
        {!!Form::text('descricao', 'Descrição')->required() !!}
    </div>

    <div class="col-md-3">
        {!!Form::select('setor_id', 'Setor', ['' => 'Selecione'] + $setores->pluck('nome', 'id')->all())
        ->attrs(['class' => 'select2'])->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('tempo_padrao', 'Tempo padrão (min)')
        ->attrs(['class' => 'number'])
        ->required()
        !!}
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>

</div>