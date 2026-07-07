<div class="row g-2">
    <div class="col-md-2">
        {!!Form::tel('valor_minimo', 'Valor mínimo')->attrs(['class' => 'moeda'])->required()
        ->value(isset($item) ? __moeda($item->valor_minimo) : '') !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor_maximo', 'Valor máximo')->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->valor_maximo) : '')!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('maximo_parcelas', 'Máx. parcelas')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('parcelas_sem_juros', 'Sem juros até')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('juros_percentual', 'Juros %')->attrs(['class' => 'percentual'])!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('primeiro_vencimento_dias', '1º vencimento dias')->value(isset($item) ? $item->primeiro_vencimento_dias : 30)->required()!!}
    </div>

    <div class="col-md-2 mt-3">
        {!!Form::tel('intervalo_parcelas_dias', 'Intervalo dias')->value(isset($item) ? $item->intervalo_parcelas_dias : 30)->required()!!}
    </div>

    <div class="col-md-2 mt-3">
        {!!Form::select('ativo', 'Status', [1 => 'Ativo', 0 => 'Inativo'])->attrs(['class' => 'form-select'])->required()!!}
    </div>

    <div class="col-md-8 mt-4">
        <div class="alert alert-info mb-0">
            <i class="ri-information-line"></i>
            Exemplo: de R$ 100,00 até R$ 1.000,00 pode parcelar em até 6x, sendo 3x sem juros.
        </div>
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>
</div>