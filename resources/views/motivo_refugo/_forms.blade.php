<div class="row g-2">

    <div class="col-md-2">
        {!!Form::text('codigo', 'Código')
        ->value(isset($item) ? $item->codigo : old('codigo'))
        !!}
    </div>

    <div class="col-md-6">
        {!!Form::text('nome', 'Motivo de refugo')
        ->value(isset($item) ? $item->nome : old('nome'))
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('ativo', 'Status', [
        1 => 'Ativo',
        0 => 'Inativo'
        ])
        ->value(isset($item) ? $item->ativo : 1)
        ->required()
        !!}
    </div>

    <div class="col-12">

        <div class="d-flex align-items-start gap-3 p-3 rounded border border-warning bg-warning-subtle mt-2">

            <div>
                <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center"
                style="width:42px; height:42px;">

                <i class="ri-error-warning-line text-white fs-5"></i>
            </div>
        </div>

        <div>
            <h6 class="mb-1 text-dark">
                Controle de refugo na produção
            </h6>

            <p class="mb-0 text-muted">
                Utilize os motivos de refugo para registrar perdas, falhas produtivas,
                problemas operacionais e desperdícios ocorridos durante a fabricação.
            </p>
        </div>

    </div>

</div>

<hr class="mt-4">

<div class="col-12" style="text-align: right;">
    <button type="submit" class="btn btn-success px-5" id="btn-store">

        <i class="ri-check-line"></i>
        Salvar
    </button>
</div>

</div>