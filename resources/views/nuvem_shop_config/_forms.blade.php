<div class="row g-2">

    <div class="col-md-2">
        {!! Form::text('client_id', 'APP ID')
        ->required()
        !!}
    </div>

    <div class="col-md-4">
        {!! Form::text('client_secret', 'Client Secret')
        ->required()
        !!}
    </div>

    <div class="col-md-3">
        {!! Form::text('email', 'Email')
        ->required()
        !!}
    </div>


    <div class="col-md-3">
        <label>Status Nuvemshop</label>
        @if(isset($item) && $item->autenticado)
        <div class="form-control bg-light-success text-success">
            Conectado - Loja {{ $item->store_id }}
        </div>
        @else
        <div class="form-control bg-light-danger text-danger">
            Não conectado
        </div>
        @endif
    </div>

    <div class="col-md-2">
        {!! Form::select('cron_para_separacao', 'Cron para Ordem Separação', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="row  mt-3">
        <div class="col-md-2">
            <button type="submit" class="btn btn-success px-5">
                Salvar Configuração
            </button>
        </div>
        
        <div class="col-md-6">
        </div>
        @if(isset($item))
        <div class="col-md-4 text-end">
            <a href="{{ route('nuvem-shop-auth.index', ['empresa_id' => $item->empresa_id]) }}" class="btn btn-primary ">
                Conectar Nuvemshop
            </a>

            @if($item->cron_para_separacao)
            <a href="{{ route('nuvem-shop.logs-cron', ['empresa_id' => $item->empresa_id]) }}" class="btn btn-dark ">
                Logs Cron
            </a>
            @endif
        </div>
        @endif
    </div>

</div>