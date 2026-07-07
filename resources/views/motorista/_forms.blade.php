<div class="row g-2">
    <div class="col-md-3">
        {!!Form::text('nome', 'Nome')->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('cpf', 'CPF')
        ->attrs(['class' => 'cpf'])->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('padrao', 'Padrão', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])->required()
        !!}
    </div>
    <hr>
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>
