<div class="row g-2">

    <div class="col-md-6">
        {!! Form::text('x_api_key', 'X API Key')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('codigo_beneficiario', 'Código Beneficiário')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('cooperativa', 'Cooperativa')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('posto', 'Posto')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::text('username', 'Username')
        ->required()
        !!}
    </div>

    <div class="col-md-5">
        {!! Form::text('password', 'Senha')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('tipo_cobranca', 'Tipo Cobrança', [
        'NORMAL' => 'NORMAL',
        'HIBRIDO' => 'HIBRIDO'
        ])->required()->attrs(['class' => 'form-select']) !!}
    </div>

    <div class="col-md-3">
        {!! Form::select('especie_documento', 'Espécie Documento', [
        'DUPLICATA_MERCANTIL_INDICACAO' => 'Duplicata Mercantil Indicação',
        'OUTROS' => 'Outros'
        ])->required()->attrs(['class' => 'form-select']) !!}
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

    <div class="col-md-12"></div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Access Token</label>
            <textarea class="form-control" rows="4" readonly>{{ $item->access_token ?? '' }}</textarea>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label>Token expira em</label>
            <input
            type="text"
            class="form-control"
            readonly
            value="{{ $item && $item->token_expires_at ? $item->token_expires_at->format('d/m/Y H:i:s') : '' }}"
            >
        </div>
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