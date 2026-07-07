<div class="row g-2">
    <div class="col-md-2">
        {!! Form::text('codigo', 'Código')
        ->attrs(['maxlength' => 10])
        ->required()
        !!}
    </div>

    <div class="col-md-4">
        {!! Form::text('nome', 'Nome')
        ->attrs(['maxlength' => 100])
        ->required()
        !!}
    </div>

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>
</div>
