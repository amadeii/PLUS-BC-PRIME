@extends('layouts.app', ['title' => 'Contas a Receber'])
@section('css')
<style type="text/css">
    .badge:hover{
        cursor: pointer;
    }

    .descricao{
        width: 220px;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .modal-premium .modal-content{
        border-radius: 18px;
        border: 1px solid #EEF0F6;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }

    .modal-premium .modal-header{
        border-bottom: 1px solid #EEF0F6;
        padding: 16px 20px;
    }

    .modal-premium .modal-title{
        font-weight: 600;
        font-size: 16px;
    }

    .resumo-card{
        background: #fff;
        border-radius: 16px;
        padding: 14px;
        border: 1px solid #EEF0F6;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: .2s;
    }

    .resumo-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    }

    .resumo-icon{
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .icon-warning{ background: #FFF7E6; color: #f59e0b; }
    .icon-danger{ background: #FFECEC; color: #ef4444; }
    .icon-success{ background: #ECFDF5; color: #10b981; }
    .icon-primary{ background: #F3F0FF; color: #6d28d9; }

    .resumo-info small{
        color: #8a94a6;
        font-size: 12px;
    }

    .resumo-info h5{
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }

    .resumo-header{
        background: #F6F7FB;
        border-radius: 14px;
        padding: 12px 16px;
        margin-bottom: 15px;
    }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="d-flex gap-2 flex-wrap">

                    @can('conta_receber_create')
                    <a href="{{ route('conta-receber.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Conta Receber
                    </a>
                    @endcan

                    @if(request('cliente_id') && isset($resumoCliente))
                    <button type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalResumoCliente">

                    <i class="ri-bar-chart-box-line"></i>
                    Resumo do Cliente
                </button>
                @endif

            </div>

            <hr class="mt-3">
            <div class="col-lg-12">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3 g-2">
                    <div class="col-md-4">
                        {!!Form::select('cliente_id', 'Pesquisar por nome')->attrs(['class' => 'select2'])
                        ->options($cliente != null ? [$cliente->id => $cliente->info] : [])
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::select('filtro_data', 'Filtro de data', ['data_vencimento' => 'Data de vencimento', 'data_recebimento' => 'Data de recebimento', 'created_at' => 'Data de cadastro'])
                        ->attrs(['class' => 'form-select'])
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
                    @if(__countLocalAtivo() > 1)
                    <div class="col-md-2">
                        {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    @endif
                    <div class="col-md-2">
                        {!!Form::select('status', 'Status', ['' => 'Todas', 1 => 'Recebidas', 0 => 'Pendentes', -1 => 'Vencidas'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('ordem', 'Ordenar por', ['' => 'Data de cadastro', 1 => 'Data de vencimento'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::select('categoria_conta_id', 'Categoria', ['' => 'Todas']+$categorias->pluck('nome', 'id')->all())
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    @if($temPlanoConta)
                    <div class="col-md-4">
                        {!!Form::select('plano_conta_id', 'Plano de conta')
                        ->attrs(['class' => 'form-select'])
                        ->options($planoSelecionado ? [$planoSelecionado->id => $planoSelecionado->descricao] : [])
                        !!}
                    </div>
                    @endif

                    <div class="col-md-2">
                        {!!Form::text('numero_documento', 'Número documento venda')
                        ->attrs(['class' => ''])
                        !!}
                    </div>
                    <div class="col-md-4 col-xl-2 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('conta-receber.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>
            <div class="col-md-12 mt-3">
                <div class="table-responsive">
                    <div class="tabela-scroll" style="overflow-x:auto;">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>

                                    <th>
                                        <div class="form-check form-checkbox-danger">
                                            <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                        </div>
                                    </th>

                                    <th>#</th>
                                    <th data-label="Cliente">Cliente</th>
                                    <th data-label="Descrição">Descrição</th>
                                    @if(__countLocalAtivo() > 1)
                                    <th data-label="Local">Local</th>
                                    @endif
                                    <th data-label="Categoria">Categoria</th>
                                    <th data-label="Valor Integral">Valor Integral</th>
                                    <th data-label="Valor Recebido">Valor Recebido</th>
                                    <th data-label="Data Cadastro">Data Cadastro</th>
                                    <th data-label="Data Vencimento">Data Vencimento</th>
                                    <th data-label="Data Recebimento">Data Recebimento</th>
                                    <th data-label="Estado">Estado</th>
                                    <th data-label="Venda">Venda</th>
                                    @if($temPlanoConta)
                                    <th data-label="Plano de Conta">Plano de Conta</th>
                                    @endif
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>

                                    <td>
                                        <div class="form-check form-checkbox-danger mb-2">
                                            <input class="form-check-input check-delete" type="checkbox" name="item_delete[]" value="{{ $item->id }}">
                                        </div>
                                    </td>

                                    <td data-label="#">{{ $item->numero_sequencial }}</td>

                                    <td data-label="Cliente"><label style="width:400px">{{ $item->cliente ? $item->cliente->razao_social : '--' }}</label></td>
                                    <td data-label="Descrição">
                                        <div class="descricao">{{ $item->descricao }}</div>
                                    </td>

                                    @if(__countLocalAtivo() > 1)
                                    <td data-label="Local" class="text-danger">{{ $item->localizacao->descricao }}</td>
                                    @endif

                                    <td data-label="Categoria">{{ $item->categoria ? $item->categoria->nome : '--' }}</td>
                                    <td data-label="Valor Integral">{{ __moeda($item->valor_integral) }}</td>
                                    <td data-label="Valor Recebido">{{ __moeda($item->valor_recebido) }}</td>
                                    <td data-label="Data Cadastro">{{ __data_pt($item->created_at, 0) }}</td>

                                    <td data-label="Data Vencimento">
                                        {{ __data_pt($item->data_vencimento, 0) }}
                                        @if(!$item->status)
                                        <br><span class="text-danger" style="font-size:10px">{{ $item->diasAtraso() }}</span>
                                        @endif
                                    </td>

                                    <td data-label="Data Recebimento">{{ $item->status ? __data_pt($item->data_recebimento, false) : '--' }}</td>

                                    <td data-label="Estado">
                                        @if($item->status)
                                        <span class="badge bg-success p-1 fs-12" style="width:120px"><i class="ri-checkbox-line"></i> Recebido</span>
                                        @else
                                        @if(strtotime($item->data_vencimento) < strtotime(date('Y-m-d')))
                                        <span class="badge bg-danger p-1 fs-12" style="width:120px"><i class="ri-alert-line"></i> Em atraso</span>
                                        @else
                                        <span class="badge bg-warning p-1 fs-12" style="width:120px"><i class="ri-alert-line"></i> Pendente</span>
                                        @endif
                                        @if($item->motivo_estorno)
                                        <span onclick="motivoEstorno('{{ $item->motivo_estorno }}')" class="badge bg-primary">estornada</span>
                                        @endif
                                        @endif
                                    </td>

                                    <td data-label="Venda">
                                        @if($item->nfce)
                                        <a href="{{ route('nfce.show', [$item->nfce->id]) }}" class="btn btn-sm btn-primary">PDV</a>
                                        #{{ $item->nfce->numero_sequencial }}
                                        @elseif($item->nfe)
                                        <a href="{{ route('nfe.show', [$item->nfe->id]) }}" class="btn btn-sm btn-dark">Pedido</a>
                                        #{{ $item->nfe->numero_sequencial }}

                                        @elseif($item->ordemServico)
                                        <a href="{{ route('ordem-servico.show', [$item->ordemServico->id]) }}" class="btn btn-sm btn-dark">OS</a>
                                        #{{ $item->ordemServico->codigo_sequencial }}
                                        @else
                                        --
                                        @endif
                                    </td>
                                    @if($temPlanoConta)
                                    <td data-label="Plano de Conta">
                                        @if($item->planoConta)
                                        {{ $item->planoConta->descricao }}
                                        @else
                                        --
                                        @endif
                                    </td>
                                    @endif

                                    <td>
                                        <form action="{{ route('conta-receber.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width:250px">
                                            @csrf
                                            @if(!$item->status)
                                            @method('delete')
                                            @can('conta_receber_edit')
                                            @if(!$item->cobrancaBancaria)
                                            <a class="btn btn-warning btn-sm" href="{{ route('conta-receber.edit', [$item->id]) }}"><i class="ri-pencil-fill"></i></a>
                                            @endif
                                            @endcan
                                            @can('conta_receber_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger"><i class="ri-delete-bin-line"></i></button>
                                            @endcan
                                            @can('conta_receber_edit')
                                            <a title="Receber conta" href="{{ route('conta-receber.pay', $item) }}" class="btn btn-success btn-sm text-white">
                                                <i class="ri-money-dollar-box-line"></i>
                                            </a>
                                            @endcan
                                            @else
                                            @if(!$item->motivo_estorno)
                                            <a title="Estornar conta" href="{{ route('conta-receber.estornar', $item) }}" class="btn btn-info btn-sm text-white">
                                                <i class="ri-arrow-go-back-fill"></i>
                                            </a>
                                            @endif
                                            @endif

                                            @if(!$item->boleto && !$item->status)
                                            @can('boleto_create')
                                            @if(!$item->cobrancaBancaria)
                                            <a title="Gerar boleto" class="btn btn-dark btn-sm" href="{{ route('boleto.create', [$item->id]) }}">
                                                <i class="ri-file-list-2-line"></i>
                                            </a>
                                            @else
                                            <a href="{{ route('cobrancas.show', $item->cobrancaBancaria->id) }}" class="btn btn-sm btn-outline-primary" title="Ver cobrança">
                                                <i class="ri-file-fill"></i>
                                            </a>
                                            @endif
                                            @endcan
                                            @elseif($item->boleto)
                                            @can('boleto_view')
                                            <a title="Visualizar boleto" class="btn btn-dark btn-sm" href="{{ route('boleto.show', [$item->id]) }}">
                                                <i class="ri-file-list-3-fill"></i>
                                            </a>
                                            @endcan
                                            @endif

                                            @if($item->status)
                                            <a title="Imprimir comprovante" class="btn btn-dark btn-sm" target="_blank" href="{{ route('conta-receber.imprimir-comprovante', [$item->id]) }}">
                                                <i class="ri-printer-line"></i>
                                            </a>
                                            <a title="Ver conta" class="btn btn-light btn-sm" href="{{ route('conta-receber.show', [$item->id]) }}">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @endif

                                            @if($item->arquivo)
                                            <a title="Baixar arquivo" class="btn btn-dark btn-sm" href="{{ route('conta-receber.download-file', [$item->id]) }}">
                                                <i class="ri-attachment-line"></i>
                                            </a>
                                            @endif

                                            @if($item->historicoRecebimento())
                                            <button data-id="{{ $item->conta_receber_origem_id ?? $item->id }}" title="Histórico de recebimento" type="button" class="btn btn-sm btn-secondary btnHistoricoRecebimento">
                                                <i class="ri-inbox-archive-fill"></i>
                                            </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="4">Soma da página</td>
                                    <td>{{ __moeda($data->sum('valor_integral')) }}</td>
                                    <td>{{ __moeda($data->sum('valor_recebido')) }}</td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
                <button type="button" id="scrollToggle2" class="scroll-btn-jidox hidden">
                    <i class="ri-arrow-right-circle-line"></i>
                </button>
                <br>
                <div class="row">
                    @can('conta_receber_delete')
                    <div class="col-md-2">
                        <form action="{{ route('conta-receber.destroy-select') }}" method="post" id="form-delete-select">
                            @method('delete')
                            @csrf
                            <div></div>
                            <button type="button" class="btn btn-danger btn-sm btn-delete-all w-100" disabled>
                                <i class="ri-close-circle-line"></i> Remover selecionados
                            </button>
                        </form>
                    </div>
                    @endcan
                    
                    @can('conta_receber_edit')
                    <div class="col-md-2">
                        <form action="{{ route('conta-receber.recebe-select') }}" method="post" id="form-recebe-paga-select">
                            @csrf
                            <div></div>
                            <button class="btn btn-success btn-sm w-100 btn-recebe-paga-all" disabled>
                                <i class="ri-check-line"></i> Receber selecionados
                            </button>
                        </form>
                    </div>
                    @endcan

                    <div class="col-md-2 text-end">
                        @if(request()->has('categoria_conta_id'))
                        <form action="{{ route('conta-receber.export-excel') }}" method="get">
                            @foreach(request()->all() as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" class="btn btn-dark btn-sm w-100">
                                <i class="ri-file-excel-line "></i> Exportar para Excel
                            </button>
                        </form>
                        @endif

                    </div>

                    <div class="col-md-6 text-end">
                        @can('boleto_create')
                        <form action="{{ route('boleto.create-several') }}" method="get" id="form-gerar-boletos">
                            <div></div>
                            <button type="submit" class="btn btn-dark btn-sm btn-boleto" disabled>
                                <i class="ri-file-line"></i> Gerar boletos
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            </div>
            <br>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="modalHistoricoRecebimento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Histórico de Recebimentos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Valor original</th>
                            <th>Valor recebido</th>
                            <th>Data vencimento</th>
                            <th>Data recebimento</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="tbodyHistoricoRecebimento"></tbody>

                </table>

            </div>

        </div>
    </div>
</div>

@if(request('cliente_id') && isset($resumoCliente))
<div class="modal fade modal-premium" id="modalResumoCliente" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-bar-chart-box-line me-1 text-primary"></i>
                    Resumo Financeiro
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- CLIENTE -->
                <div class="resumo-header">
                    <small class="text-muted">Cliente</small>
                    <div class="fw-semibold">
                        {{ $cliente->razao_social ?? $cliente->nome_fantasia ?? '--' }}
                    </div>
                </div>

                <!-- CARDS -->
                <div class="row g-3">

                    <!-- PENDENTE -->
                    <div class="col-md-6 col-lg-3">
                        <div class="resumo-card">
                            <div class="resumo-icon icon-warning">
                                <i class="ri-time-line"></i>
                            </div>
                            <div class="resumo-info">
                                <small>Pendente</small>
                                <h5>R$ {{ __moeda($resumoCliente['pendente']) }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- ATRASO -->
                    <div class="col-md-6 col-lg-3">
                        <div class="resumo-card">
                            <div class="resumo-icon icon-danger">
                                <i class="ri-alert-line"></i>
                            </div>
                            <div class="resumo-info">
                                <small>Em atraso</small>
                                <h5>R$ {{ __moeda($resumoCliente['atraso']) }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- RECEBIDO -->
                    <div class="col-md-6 col-lg-3">
                        <div class="resumo-card">
                            <div class="resumo-icon icon-success">
                                <i class="ri-check-line"></i>
                            </div>
                            <div class="resumo-info">
                                <small>Recebido</small>
                                <h5>R$ {{ __moeda($resumoCliente['recebido']) }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- TOTAL -->
                    <div class="col-md-6 col-lg-3">
                        <div class="resumo-card">
                            <div class="resumo-icon icon-primary">
                                <i class="ri-money-dollar-circle-line"></i>
                            </div>
                            <div class="resumo-info">
                                <small>Total geral</small>
                                <h5>R$ {{ __moeda($resumoCliente['geral']) }}</h5>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
@endif

@endsection
@section('js')
@if(session('imprimir_ids'))
<script>
    window.addEventListener('load', function () {
        window.open(
            path_url + 'conta-receber-imprimir-lote?ids={{ session('imprimir_ids') }}',
            '_blank'
            );
    });
</script>
@endif
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
<script type="text/javascript" src="/js/boleto.js"></script>
<script type="text/javascript" src="/js/recebe_paga_selecionados.js"></script>

<script type="text/javascript">
    function motivoEstorno(motivo) {
        swal("", motivo, 'info')
    }

    $(document).on('click', '.btnHistoricoRecebimento', function(){

        let id = $(this).data('id');

        $.get(path_url+'api/conta-receber/historico-recebimento/' + id, function(html){

            $('#tbodyHistoricoRecebimento').html(html);
            $('#modalHistoricoRecebimento').modal('show');

        });

    });
</script>

@endsection
