@extends('layouts.app', ['title' => 'Detalhes da venda PDV - NFCe'])

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="sale-detail-header">
                    <div class="sale-detail-title">
                        <div class="sale-detail-icon">
                            <i class="ri-shopping-cart-2-line"></i>
                        </div>
                        <div>
                            <h4>Detalhes da venda PDV - NFCe</h4>
                            <p>Resumo completo da venda, cliente, valores e situação fiscal</p>
                        </div>
                    </div>

                    <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i> Voltar
                    </a>
                </div>

                <div class="sale-summary-grid">
                    <div class="sale-summary-card">
                        <span>Cliente</span>
                        <strong class="text-primary">{{ $data->cliente_id ? $data->cliente->razao_social : 'Consumidor Final' }}</strong>
                    </div>

                    <div class="sale-summary-card">
                        <span>Total</span>
                        <strong class="text-success">R$ {{ __moeda($data->total) }}</strong>
                    </div>

                    <div class="sale-summary-card">
                        <span>Data de cadastro</span>
                        <strong>{{ __data_pt($data->created_at) }}</strong>
                    </div>

                    @if(__isPlanoFiscal())
                    <div class="sale-summary-card">
                        <span>Data de emissão</span>
                        <strong>{{ __data_pt($data->data_emissao) }}</strong>
                    </div>

                    <div class="sale-summary-card sale-summary-status">
                        <span>Estado</span>

                        @if($data->estado == 'aprovado')
                            <strong class="badge-sale badge-sale-success">Aprovado</strong>
                        @elseif($data->estado == 'cancelado')
                            <strong class="badge-sale badge-sale-danger">Cancelado</strong>
                        @elseif($data->estado == 'rejeitado')
                            <strong class="badge-sale badge-sale-warning">Rejeitado</strong>
                        @else
                            <strong class="badge-sale badge-sale-info">Novo</strong>
                        @endif
                    </div>
                    @endif
                </div>

                @if(__isPlanoFiscal() && $data->estado == 'aprovado')
                <div class="sale-actions">
                    <a href="{{ route('nfce.download-xml', [$data->id]) }}" class="btn btn-dark btn-sm">
                        <i class="ri-file-download-line"></i> Download XML
                    </a>

                    <a class="btn btn-primary btn-sm" title="Imprimir NFCe" target="_blank" href="{{ route('nfce.imprimir', [$data->id]) }}">
                        <i class="ri-printer-line"></i> Imprimir
                    </a>
                </div>
                @endif

                <hr>

                <div class="col-lg-12 mt-4">
                    <h5>Itens da NFCe</h5>
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Valor</th>
                                    <th>Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data->itens as $item)
                                <tr>
                                    <td>{{ $item->produto->nome }}</td>
                                    <td>{{ $item->quantidade }}</td>
                                    <td>{{ __moeda($item->valor_unitario) }}</td>
                                    <td>{{ __moeda($item->sub_total) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-8 col-12 mt-5">
                        <h5>Fatura</h5>
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Pagamento</th>
                                    <th>Data Vencimento</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data->fatura as $item)
                                <tr>
                                    <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                                    <td>{{ __data_pt($item->data_vencimento, 0) }}</td>
                                    <td>{{ __moeda($item->valor) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Nfe sem informações de pagamento</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@section('css')
<style>
.sale-detail-header{ display:flex; align-items:center; justify-content:space-between; gap:15px; padding:6px 0 18px; border-bottom:1px solid #eef0f6; }
.sale-detail-title{ display:flex; align-items:center; gap:14px; }
.sale-detail-icon{ width:48px; height:48px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:#f3f0ff; color:var(--bs-primary); font-size:25px; }
.sale-detail-title h4{ margin:0; font-weight:800; color:#1f2937; }
.sale-detail-title p{ margin:3px 0 0; color:#6b7280; font-size:13px; }
.sale-summary-grid{ display:grid; grid-template-columns:repeat(5, 1fr); gap:12px; margin-top:18px; margin-bottom:14px; }
.sale-summary-card{ background:#fff; border:1px solid #eef0f6; border-radius:18px; padding:14px 16px; box-shadow:0 6px 18px rgba(15,23,42,.04); min-height:86px; display:flex; flex-direction:column; justify-content:center; }
.sale-summary-card span{ color:#6b7280; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; margin-bottom:5px; }
.sale-summary-card strong{ font-size:16px; line-height:1.25; }
.sale-summary-status{ align-items:flex-start; }
.badge-sale{ display:inline-flex; align-items:center; justify-content:center; padding:6px 11px; border-radius:999px; font-size:12px !important; font-weight:800; }
.badge-sale-success{ background:#dcfce7; color:#15803d; }
.badge-sale-danger{ background:#fee2e2; color:#b91c1c; }
.badge-sale-warning{ background:#fef3c7; color:#b45309; }
.badge-sale-info{ background:#e0f2fe; color:#0369a1; }
.sale-actions{ display:flex; justify-content:flex-end; gap:8px; flex-wrap:wrap; margin:4px 0 18px; }

@media(max-width:1199px){
    .sale-summary-grid{ grid-template-columns:repeat(3, 1fr); }
}

@media(max-width:767px){
    .sale-detail-header{ align-items:flex-start; flex-direction:column; }
    .sale-detail-header .btn{ align-self:flex-end; }
    .sale-summary-grid{ grid-template-columns:1fr; }
    .sale-summary-card{ min-height:auto; }
}
</style>
@endsection
@endsection