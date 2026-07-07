@extends('layouts.app', ['title' => 'Pedido Nuvem Shop #'.$item->pedido_id])
@section('css')
<style type="text/css">
    @page { size: auto;  margin: 0mm; }

    @media print {
        .print{
            margin: 10px;
        }
    }

    .info-card {
        border-radius: 10px;
        transition: 0.2s;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.08);
    }

    .info-card small {
        font-size: 11px;
    }

    .info-card h6 {
        font-weight: 600;
    }

    .info-card h4 {
        font-weight: 700;
    }

    .table-itens thead {
        background: #f8fafc;
        font-size: 13px;
        text-transform: uppercase;
    }

    .table-itens tbody tr {
        border-bottom: 1px solid #f1f5f9;
    }

    .table-itens tbody tr:hover {
        background: #f8fafc;
    }

    .table-itens td {
        padding: 12px 10px;
        vertical-align: middle;
    }

    .badge.bg-light {
        border: 1px solid #e5e7eb;
    }
</style>
@endsection
@section('content')

<div class="card mt-1 print">
    <div class="card-body">
        <div class="pl-lg-4">

            <div class="ms">

                <div class="mt-3 d-print-none" style="text-align: right;">
                    <a href="{{ route('nuvem-shop-pedidos.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                <div class="row mb-3">

                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm info-card">
                            <div class="card-body p-2">
                                <small class="text-muted">Pedido</small>
                                <h4 class="text-danger mb-0">#{{ $item->pedido_id }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm info-card">
                            <div class="card-body p-2">
                                <small class="text-muted">Data do pedido</small>
                                <h6 class="text-primary mb-0">{{ __data_pt($item->data) }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm info-card">
                            <div class="card-body p-2">
                                <small class="text-muted">Cadastro</small>
                                <h6 class="text-primary mb-0">{{ __data_pt($item->created_at) }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm info-card">
                            <div class="card-body p-2">
                                <small class="text-muted">Valor Total</small>
                                <h6 class="text-success mb-0">R$ {{ __moeda($item->total) }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm info-card">
                            <div class="card-body p-2">
                                <small class="text-muted">Entrega</small>
                                <h6 class="text-primary mb-0">R$ {{ __moeda($item->valor_frete) }}</h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm info-card">
                            <div class="card-body p-2">
                                <small class="text-muted">Desconto</small>
                                <h6 class="text-danger mb-0">R$ {{ __moeda($item->desconto) }}</h6>
                            </div>
                        </div>
                    </div>

                </div>

                <a class="btn btn-primary btn-sm d-print-none" href="javascript:window.print()" ><i class="ri-printer-line d-print-none"></i>
                    Imprimir
                </a>
                @if($item->nfe_id == 0)
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('nuvem-shop-pedidos.gerar-nfe', $item->id) }}">
                    <i class="ri-file-text-line"></i>
                    Gerar NFe
                </a>
                @else
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('nfe.show', $item->nfe_id) }}">
                    <i class="ri-file-text-line"></i>
                    Ver NFe
                </a>
                @endif

                @if(!empty($item->log_pedido))
                <button type="button" class="btn btn-dark btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal-log-pedido">
                    <i class="ri-file-list-3-line"></i>
                    Ver log
                </button>
                @endif

            </div>

            <div class="row mt-3">
                <div class="col-12">

                    <div class="card shadow-sm border-0">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="mb-0">
                                    <i class="ri-shopping-cart-line"></i>
                                    Itens do pedido
                                </h4>
                                <span class="badge bg-primary">
                                    {{ count($item->itens) }} itens
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-itens">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th class="text-center">Qtd</th>
                                            <th class="text-end">Valor unitário</th>
                                            <th class="text-end">Sub total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item->itens as $i)
                                        <tr>
                                            <td>
                                                <strong>
                                                    {{ $i->nome }}
                                                    @if($i->produto->codigo_barras)
                                                    <span class="text-muted">[{{ $i->produto->codigo_barras }}]</span>
                                                    @endif
                                                </strong>
                                                <br>
                                                <span class="text-muted">
                                                    Nome interno: {{ $i->produto->nome }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">
                                                    {{ number_format($i->quantidade, 0) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                R$ {{ __moeda($i->valor_unitario) }}
                                            </td>
                                            <td class="text-end text-success">
                                                <strong>R$ {{ __moeda($i->sub_total) }}</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="row mt-4">

                <!-- Cliente -->
                <div class="col-md-6 col-12">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 text-muted">Cliente</h5>

                                @if($item->cliente)
                                <a href="{{ route('clientes.edit', [$item->cliente->id]) }}" class="btn btn-warning btn-sm d-print-none">
                                    <i class="ri-edit-line"></i>
                                </a>
                                @else
                                <button class="btn btn-dark btn-sm d-print-none" data-bs-toggle="modal" data-bs-target="#modal-cliente">
                                    Atribuir cliente
                                </button>
                                @endif
                            </div>

                            <h4 class="fw-bold mb-3">
                                {{ $item->cliente->razao_social ?? '--' }}
                            </h4>

                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Documento</small>
                                    <div class="fw-bold">{{ $item->cliente->cpf_cnpj ?? '--' }}</div>
                                </div>

                                <div class="col-6">
                                    <small class="text-muted">ID Nuvem Shop</small>
                                    <div class="fw-bold">{{ $item->cliente->nuvem_shop_id ?? '--' }}</div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted">Observação</small>
                                <div>{{ $item->observacao ?? '--' }}</div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Entrega -->
                <div class="col-md-6 col-12">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">

                            <h5 class="text-muted mb-3">Dados de entrega</h5>

                            <div class="row mb-2">
                                <div class="col-8">
                                    <small class="text-muted">Rua</small>
                                    <div class="fw-bold text-primary">{{ $item->rua }}</div>
                                </div>

                                <div class="col-4">
                                    <small class="text-muted">Número</small>
                                    <div class="fw-bold text-primary">{{ $item->numero }}</div>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-8">
                                    <small class="text-muted">Bairro</small>
                                    <div class="fw-bold text-primary">{{ $item->bairro }}</div>
                                </div>

                                <div class="col-4">
                                    <small class="text-muted">Cidade</small>
                                    <div class="fw-bold text-primary">{{ $item->cidade }}</div>
                                </div>
                            </div>

                            <div>
                                <small class="text-muted">CEP</small>
                                <div class="fw-bold text-primary">{{ $item->cep }}</div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-cliente" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ route('mercado-livre-pedidos.set-cliente', [$item->id]) }}">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Atribuir cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            {!!Form::select('cliente_id', 'Cliente')
                            ->required()

                            !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Atribuir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(!empty($item->log_pedido))
@php
    $logPedido = json_decode($item->log_pedido);
@endphp

<div class="modal fade" id="modal-log-pedido" tabindex="-1" aria-labelledby="modalLogPedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalLogPedidoLabel">
                    <i class="ri-file-list-3-line"></i>
                    Log do pedido #{{ $item->pedido_id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-3 col-6">
                        <small class="text-muted">Número</small>
                        <div class="fw-bold">#{{ $logPedido->number ?? $logPedido->id ?? '--' }}</div>
                    </div>

                    <div class="col-md-3 col-6">
                        <small class="text-muted">Status</small>
                        <div class="fw-bold">{{ $logPedido->status ?? '--' }}</div>
                    </div>

                    <div class="col-md-3 col-6">
                        <small class="text-muted">Pagamento</small>
                        <div class="fw-bold">{{ $logPedido->payment_status ?? '--' }}</div>
                    </div>

                    <div class="col-md-3 col-6">
                        <small class="text-muted">Envio</small>
                        <div class="fw-bold">{{ $logPedido->shipping_status ?? '--' }}</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Cliente</h6>
                                <strong>{{ $logPedido->customer->name ?? $logPedido->contact_name ?? '--' }}</strong><br>
                                <small>{{ $logPedido->customer->email ?? $logPedido->contact_email ?? '--' }}</small><br>
                                <small>Doc: {{ $logPedido->customer->identification ?? $logPedido->contact_identification ?? '--' }}</small><br>
                                <small>Telefone: {{ $logPedido->customer->phone ?? $logPedido->contact_phone ?? '--' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mt-2 mt-md-0">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Entrega</h6>
                                <strong>
                                    {{ $logPedido->shipping_address->address ?? $logPedido->billing_address ?? '--' }},
                                    {{ $logPedido->shipping_address->number ?? $logPedido->billing_number ?? '--' }}
                                </strong><br>
                                <small>
                                    {{ $logPedido->shipping_address->locality ?? $logPedido->billing_locality ?? '--' }}
                                </small><br>
                                <small>
                                    {{ $logPedido->shipping_address->city ?? $logPedido->billing_city ?? '--' }}
                                    /
                                    {{ $logPedido->shipping_address->province ?? $logPedido->billing_province ?? '--' }}
                                </small><br>
                                <small>CEP: {{ $logPedido->shipping_address->zipcode ?? $logPedido->billing_zipcode ?? '--' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mt-2 mt-md-0">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted">Valores</h6>
                                <small>Subtotal: <strong>{{ __moeda($logPedido->subtotal ?? 0) }}</strong></small><br>
                                <small>Frete: <strong>{{ __moeda($logPedido->shipping_cost_customer ?? 0) }}</strong></small><br>
                                <small>Desconto: <strong>{{ __moeda($logPedido->discount ?? 0) }}</strong></small><br>
                                <small>Total: <strong>{{ __moeda($logPedido->total ?? 0) }}</strong></small>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="text-muted mt-3">Produtos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th>SKU</th>
                                <th>Cód. barras</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($logPedido->products ?? []) as $prod)
                            <tr>
                                <td>{{ $prod->name ?? '--' }}</td>
                                <td>{{ $prod->sku ?? '--' }}</td>
                                <td>{{ $prod->barcode ?? '--' }}</td>
                                <td class="text-center">{{ $prod->quantity ?? 0 }}</td>
                                <td class="text-end">{{ __moeda($prod->price ?? 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum produto no log</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr>

                <button class="btn btn-outline-dark btn-sm mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#jsonCompletoPedido">
                    <i class="ri-code-line"></i>
                    Ver JSON completo
                </button>

                <div class="collapse" id="jsonCompletoPedido">
                    <pre class="bg-light p-3 rounded" style="max-height: 420px; overflow:auto; font-size: 12px;">{{ json_encode($logPedido, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('js')
<script type="text/javascript">
    $("#inp-cliente_id").select2({
        minimumInputLength: 2,
        language: "pt-BR",
        placeholder: "Digite para buscar o cliente",
        theme: "bootstrap4",
        dropdownParent: $('#modal-cliente'),
        ajax: {
            cache: true,
            url: path_url + "api/clientes/pesquisa",
            dataType: "json",
            data: function (params) {
                console.clear();
                var query = {
                    pesquisa: params.term,
                    empresa_id: $("#empresa_id").val(),
                };
                return query;
            },
            processResults: function (response) {
                var results = [];

                $.each(response, function (i, v) {
                    var o = {};
                    o.id = v.id;

                    o.text = v.razao_social + " - " + v.cpf_cnpj;
                    o.value = v.id;
                    results.push(o);
                });
                return {
                    results: results,
                };
            },
        },
    });
</script>

@endsection
