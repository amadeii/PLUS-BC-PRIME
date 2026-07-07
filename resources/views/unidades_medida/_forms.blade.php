<div class="row g-2">

    <div class="alert alert-info mb-4">
        <div class="d-flex">
            <i class="fa fa-info-circle mr-2 mt-1"></i>
            <div>
                <strong>Conversão para compras/XML</strong><br>

                Ao importar um XML de compra, o sistema procura a unidade informada na nota fiscal e converte automaticamente para a unidade de estoque utilizando o fator informado.

                <br><br>

                <strong>Exemplos:</strong>

                <ul class="mb-0 mt-2">
                    <li><strong>CX12 → UN (12)</strong>: 5 caixas serão convertidas para 60 unidades.</li>
                    <li><strong>FARDO100 → UN (100)</strong>: 2 fardos serão convertidos para 200 unidades.</li>
                    <li><strong>KG → G (1000)</strong>: 1,5 KG será convertido para 1.500 G.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        {!! Form::text('nome', 'Origem')
        ->attrs(['class' => ''])
        ->required()
        !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('destino', 'Destino')
        ->attrs(['class' => ''])
        ->placeholder('Ex: UN')
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('fator_conversao', 'Fator Conversão')
        ->attrs([
        'class' => '',
        'data-mask' => '00000.0000', 'data-mask-reverse' => 1
        ])
        ->value(isset($item) ? number_format($item->fator_conversao, 4, '', '.') : 1)
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('status', 'Ativo', [
        '1' => 'Sim',
        '0' => 'Não'
        ])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100" id="btn-store">
            Salvar
        </button>
    </div>

</div>