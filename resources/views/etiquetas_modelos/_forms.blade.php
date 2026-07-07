<div class="row g-2">

    <div class="col-md-4">
        {!!Form::text('nome', 'Nome do modelo')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('largura', 'Largura (mm)')
        ->value(isset($item) ? $item->largura : 60)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('altura', 'Altura (mm)')
        ->value(isset($item) ? $item->altura : 40)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('etiquetas_por_linha', 'Etiquetas por linha')
        ->value(isset($item) ? $item->etiquetas_por_linha : 3)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('fonte_padrao', 'Tamanho da fonte')
        ->value(isset($item) ? $item->fonte_padrao : 10)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('largura_codigo_barras', 'Largura cód. barras')
        ->value(isset($item) ? $item->largura_codigo_barras : '38')
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('altura_codigo_barras', 'Altura cód. barras')
        ->value(isset($item) ? $item->altura_codigo_barras : 10)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('espaco_horizontal', 'Espaço horizontal')
        ->value(isset($item) ? $item->espaco_horizontal : 2)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('espaco_vertical', 'Espaço vertical')
        ->value(isset($item) ? $item->espaco_vertical : 2)
        ->required()
        !!}
    </div>

    <div class="col-md-2">
        <label class="form-label">Status</label>

        <select name="ativo" class="form-select">
            <option value="1" {{ isset($item) && !$item->ativo ? '' : 'selected' }}>
                Ativo
            </option>
            <option value="0" {{ isset($item) && !$item->ativo ? 'selected' : '' }}>
                Inativo
            </option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">Mostrar numeração código de barras</label>

        <div class="form-check form-switch mt-2">
            <input
            class="form-check-input"
            type="checkbox"
            name="mostrar_numero_codigo_barras"
            value="1"
            {{ !isset($item) || $item->mostrar_numero_codigo_barras ? 'checked' : '' }}
            >
            <label class="form-check-label">
                Exibir número abaixo do código de barras
            </label>
        </div>
    </div>

    <div class="col-12 mt-3">
        <div class="alert alert-info">
            <i class="ri-information-line"></i>
            Após salvar o modelo você poderá montar o layout utilizando o editor visual drag & drop.
        </div>
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Salvar
        </button>
    </div>

</div>