@extends('layouts.app', ['title' => 'Pedido Ecommerce'])
@section('css')
<style type="text/css">
    @page { size: auto;  margin: 0mm; }

    @media print {
        .print{
            margin: 10px;
        }
    }

    .info-box {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .label {
        font-size: 12px;
        color: #888;
        display: block;
    }
</style>
@endsection
@section('content')

<div class="card mt-1 print">
    <div class="card-body">
        <div class="pl-lg-4">

            <div class="ms">
                {!! $item->_estado() !!}
                <div class="mt-3 d-print-none" style="text-align: right;">
                    <a href="{{ route('pedidos-ecommerce.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
                <div class="row mb-3">

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">

                                <div class="row g-3">

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Pedido</span>
                                            <strong class="text-danger">#{{ $item->hash_pedido }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Data Pedido</span>
                                            <strong>{{ __data_pt($item->created_at) }}</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Entrega</span>
                                            <strong>
                                                {{ $item->data_entrega ? __data_pt($item->data_entrega, 0) : '--' }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Rastreamento</span>
                                            <strong>
                                                {{ $item->codigo_rastreamento ?? '--' }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Total</span>
                                            <strong class="text-success">
                                                R$ {{ __moeda($item->valor_total) }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Frete</span>
                                            <strong>
                                                R$ {{ __moeda($item->valor_frete) }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <div class="info-box">
                                            <span class="label">Pagamento</span>

                                            @if($item->status_pagamento == 'approved')
                                            <span class="badge bg-success">Aprovado</span>
                                            @elseif($item->status_pagamento == 'pending')
                                            <span class="badge bg-danger">Pendente</span>
                                            @else
                                            <span class="badge bg-warning text-dark">Depósito</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($item->comprovante)
                                    <div class="col-md-3 col-6 d-flex align-items-end">
                                        <a class="btn btn-dark w-100" target="_blank" href="/uploads/comprovantes/{{ $item->comprovante }}">
                                            <i class="ri-eye-line"></i> Ver comprovante
                                        </a>
                                    </div>
                                    @endif

                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <a href="{{ route('pedidos-ecommerce.alterar-estado', $item->id) }}" class="btn btn-info btn-sm d-print-none" href=""><i class="ri-refresh-line"></i>
                    Alterar estado
                </a>
                <a class="btn btn-primary btn-sm d-print-none" href="javascript:window.print()" ><i class="ri-printer-line d-print-none"></i>
                    Imprimir
                </a>
                @if($item->nfe_id == 0)
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('pedidos-ecommerce.gerar-nfe', $item->id) }}">
                    <i class="ri-file-text-line"></i>
                    Gerar Venda
                </a>
                @else
                <a class="btn btn-success btn-sm d-print-none" href="{{ route('nfe.show', $item->nfe_id) }}">
                    <i class="ri-file-text-line"></i>
                    Ver Venda
                </a>
                @endif

                <div class="d-print-none mt-2">
                    @if($item->tipo_pagamento == 'boleto')
                    <a href="{{ $item->link_boleto }}" target="_blank">
                        <i class="ri-links-fill"></i> Link do boleto
                    </a>
                    @endif

                    @if($item->tipo_pagamento == 'pix')
                    <p>PIX: {{ $item->qr_code }}</p>
                    @endif
                </div>

                <div class="info-box">
                    <span class="label">Transação ID</span>
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="text-primary">{{ $item->transacao_id ?? '--' }}</strong>

                        @if($item->transacao_id)
                        <i class="ri-file-copy-line text-muted" style="cursor:pointer;" onclick="navigator.clipboard.writeText('{{ $item->transacao_id }}')">
                        </i>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <label>Itens do pedido</label>
                <div class="table-responsive-sm">
                    <table class="table table-striped table-centered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Valor unitário</th>
                                <th>Sub total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->itens as $i)
                            <tr>
                                <td>{{ $i->descricao() }}</td>
                                <td>{{ number_format($i->quantidade, 0) }}</td>
                                <td>{{ __moeda($i->valor_unitario) }}</td>
                                <td>{{ __moeda($i->sub_total) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-3">

                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">

                            <div class="row">

                                <!-- CLIENTE -->
                                <div class="col-md-6 col-12 mb-3">
                                    <h5 class="mb-3 text-primary">
                                        <i class="ri-user-3-line"></i> Dados do Cliente
                                    </h5>

                                    <div class="info-line">
                                        <span class="label">Nome</span>
                                        <strong>{{ $item->cliente->info }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Email</span>
                                        <strong>{{ $item->cliente->email }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Telefone</span>
                                        <strong>{{ $item->cliente->telefone }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Cadastro</span>
                                        <strong>{{ __data_pt($item->cliente->created_at) }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Documento</span>
                                        <strong>{{ $item->numero_documento }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Frete</span>
                                        <strong>
                                            {{ $item->tipo_frete != 0 ? $item->tipo_frete : 'Sem frete' }}
                                        </strong>
                                    </div>
                                </div>

                                <!-- ENDEREÇO -->
                                <div class="col-md-6 col-12">
                                    <h5 class="mb-3 text-success">
                                        <i class="ri-map-pin-line"></i> Endereço
                                    </h5>

                                    <div class="info-line">
                                        <span class="label">Rua</span>
                                        <strong>{{ $item->rua_entrega ?? '--' }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Número</span>
                                        <strong>{{ $item->numero_entrega ?? '--' }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Bairro</span>
                                        <strong>{{ $item->bairro_entrega ?? '--' }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">CEP</span>
                                        <strong>{{ $item->cep_entrega ?? '--' }}</strong>
                                    </div>

                                    <div class="info-line">
                                        <span class="label">Cidade</span>
                                        <strong>{{ $item->cidade_entrega ?? '--' }}</strong>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection

@section('js')

@endsection
