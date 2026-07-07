@extends('layouts.app', ['title' => 'Lista de Vendas PDV'])
@section('css')
<link rel="stylesheet" type="text/css" href="/css/front_box_index.css">
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    @can('pdv_create')
                    <a href="{{ route('frontbox.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        PDV
                    </a>
                    @endcan
                </div>
                <hr>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-4">
                        {!!Form::select('cliente_id', 'Cliente')
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::select('estado', 'Estado',
                        ['novo' => 'Novas',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        '' => 'Todos'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    
                    @if($adm)
                    <div class="col-md-2">
                        {!!Form::select('user_id', 'Usuário', ['' => 'Selecione'] + $usuarios->pluck('name', 'id')->all())
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    @endif
                    <div class="col-md-4 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('frontbox.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                    </div>
                    <div class="col-md-6"></div>

                    <div class="card shadow-sm border-0 mt-2 col-12 col-md-2 bg-light text-end">
                        <div class="card-body d-flex align-items-center justify-content-end" style="height: 50px;">
                            <div>
                                <small class="text-muted">Valor Total das Vendas</small>
                                <h4 class="mb-0 fw-bold text-success">
                                    R$ {{ __moeda($somaGeral) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}

                @if($contigencia != null)
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="text-danger">Contigência ativada</h4>
                                <p class="text-danger">Tipo: <strong>{{$contigencia->tipo}}</strong></p>
                                <p class="text-danger">Data de ínicio: <strong>{{ __data_pt($contigencia->created_at) }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-lg-12 mt-4">

                    <div class="table-responsive pedidos-dark-wrap d-none d-lg-block">
                        <table class="table pedidos-dark-table mb-0">
                            <thead class="">
                                <tr>
                                    <th style="width: 35px;">
                                        <input type="checkbox">
                                    </th>
                                    <th>Ações</th>
                                    <th>Pedido</th>
                                    <th>Valores</th>
                                    @if(__isPlanoFiscal())
                                    <th>Status</th>
                                    @endif
                                    <th>Usuário/Vendedor</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                @php
                                $cliente = $item->cliente ? $item->cliente->info : ($item->cliente_nome != "" ? $item->cliente_nome : "--");

                                $rowClass = '';
                                if($item->fiscal_status === 'erro' || $item->fiscal_status === 'alerta'){
                                    $rowClass = 'row-alerta';
                                }
                                @endphp

                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input">
                                    </td>

                                    <td>
                                        <div class="">

                                            @if($usarDropdown)
                                            @include('front_box.partials.dropdown_acoes', ['item' => $item])
                                            @else
                                            @include('front_box.partials.botoes_acoes', ['item' => $item])
                                            @endif

                                        </div>

                                    </td>

                                    <td>
                                        <div class="pedido-info">
                                            <strong>#{{ $item->numero_sequencial }}</strong>
                                            <span>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</span>
                                        </div>

                                        <div class="cliente-nome">
                                            {{ $cliente }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="valor-principal">
                                            R$ {{ __moeda($item->total) }}
                                        </div>

                                        <div class="valor-detalhe">
                                            Desc. {{ __moeda($item->desconto) }} • Frete {{ __moeda($item->valor_frete) }} • Acrésc. {{ __moeda($item->acrescimo) }}
                                        </div>

                                        @if($item->valor_cashback > 0)
                                        <div class="cashback-line">
                                            Cashback R$ {{ __moeda($item->valor_cashback) }}
                                        </div>
                                        @endif
                                    </td>

                                    @if(__isPlanoFiscal())
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($item->fiscal_status === 'erro')
                                            <span class="status-pill danger" onclick="consultarFiscal({{ $item->id }}, 'nfce')">
                                                <i class="ri-alert-line"></i> Erro fiscal
                                            </span>
                                            @elseif($item->fiscal_status === 'alerta')
                                            <span class="status-pill warning" onclick="consultarFiscal({{ $item->id }}, 'nfce')">
                                                <i class="ri-alert-line"></i> Alerta fiscal
                                            </span>
                                            @else
                                            <span class="status-pill success">
                                                <i class="ri-check-line"></i> Fiscal OK
                                            </span>
                                            @endif

                                            @if($item->estado == 'aprovado')
                                            <span class="status-pill success">Aprovado</span>
                                            @elseif($item->estado == 'cancelado')
                                            <span class="status-pill danger">Cancelado</span>
                                            @elseif($item->estado == 'rejeitado')
                                            <span class="status-pill warning">Rejeitado</span>
                                            @else
                                            <span class="status-pill muted">Novo</span>
                                            @endif
                                        </div>
                                    </td>
                                    @endif

                                    <td>
                                        <div class="d-flex flex-column gap-2">

                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ri-user-3-line fs-5 text-primary"></i>

                                                <span class="fw-semibold text-dark">
                                                    {{ $item->user ? $item->user->name : '--' }}
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <i class="ri-arrow-right-circle-line fs-5 text-success"></i>

                                                <span class="fw-semibold text-dark">
                                                    {{ $item->vendedor() ? $item->vendedor() : '--' }}
                                                </span>
                                            </div>

                                        </div>
                                    </td>

                                </tr>

                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Nada encontrado
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mobile-vendas-list d-lg-none">
                        @forelse($data as $item)
                        @php
                        $cliente = $item->cliente ? $item->cliente->info : ($item->cliente_nome != "" ? $item->cliente_nome : "--");
                        @endphp

                        <div class="mobile-venda-card">
                            <div class="mobile-venda-top">
                                <div>
                                    <strong>#{{ $item->numero_sequencial }}</strong>
                                    <span>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</span>
                                </div>

                                <div class="mobile-venda-valor">
                                    R$ {{ __moeda($item->total) }}
                                </div>
                            </div>

                            <div class="mobile-venda-cliente">
                                <i class="ri-user-line"></i>
                                {{ $cliente }}
                            </div>

                            <div class="mobile-venda-status">
                                @if($item->fiscal_status === 'erro')
                                <span class="status-pill danger" onclick="consultarFiscal({{ $item->id }}, 'nfce')">
                                    <i class="ri-alert-line"></i> Erro fiscal
                                </span>
                                @elseif($item->fiscal_status === 'alerta')
                                <span class="status-pill warning" onclick="consultarFiscal({{ $item->id }}, 'nfce')">
                                    <i class="ri-alert-line"></i> Alerta fiscal
                                </span>
                                @else
                                <span class="status-pill success">
                                    <i class="ri-check-line"></i> Fiscal OK
                                </span>
                                @endif

                                @if($item->estado == 'aprovado')
                                <span class="status-pill success">Aprovado</span>
                                @elseif($item->estado == 'cancelado')
                                <span class="status-pill danger">Cancelado</span>
                                @elseif($item->estado == 'rejeitado')
                                <span class="status-pill warning">Rejeitado</span>
                                @else
                                <span class="status-pill muted">Novo</span>
                                @endif
                            </div>

                            <div class="mobile-venda-users">
                                <div>
                                    <i class="ri-user-3-line text-primary"></i>
                                    {{ $item->user ? $item->user->name : '--' }}
                                </div>

                                <div>
                                    <i class="ri-arrow-right-circle-line text-success"></i>
                                    {{ $item->vendedor() ? $item->vendedor() : '--' }}
                                </div>
                            </div>

                            <div class="mobile-venda-actions">
                                @if($usarDropdown)
                                @include('front_box.partials.dropdown_acoes', ['item' => $item])
                                @else
                                @include('front_box.partials.botoes_acoes', ['item' => $item])
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="mobile-empty">
                            Nada encontrado
                        </div>
                        @endforelse
                    </div>


                    <br>
                    {!! $data->appends(request()->all())->links() !!}
                </div>

            </div>
        </div>
    </div>
</div>
@include('nfe.partials.modal_envio_wpp')

<div id="fiscalLoader" class="fiscal-loader d-none">
    <div class="fiscal-box">
        <div id="fiscalIcon" class="fiscal-icon error">✖</div>

        <h4 id="fiscalTitle">Erro Fiscal</h4>
        <p id="fiscalSubtitle">
            Foram encontrados problemas fiscais que impedem a transmissão.
        </p>

        <div id="fiscalContent" class="fiscal-content"></div>

        <div class="fiscal-actions">
            <button id="btnFiscalCancel" class="btn btn-secondary">
                Fechar
            </button>

        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript" src="/js/nfce_transmitir.js"></script>
<script type="text/javascript">
    function imprimir(id){
        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

        var docprint=window.open(path_url+"frontbox/imprimir-nao-fiscal/"+id, "",disp_setting);

        docprint.focus();
    }

    function imprimirA4(id){
        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

        var docprint=window.open(path_url+"frontbox/imprimir-a4/"+id, "",disp_setting);

        docprint.focus();
    }
</script>
<script type="text/javascript" src="/js/enviar_fatura_wpp.js"></script>
<script type="text/javascript" src="/js/consulta_fiscal.js"></script>

@endsection
