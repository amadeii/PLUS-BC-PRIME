@extends('layouts.app', ['title' => 'Detalhes da Venda'])

@section('css')
<style>
    .page-header-box{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .page-title-wrap h3{
        margin-bottom:4px;
        font-weight:700;
        color:#1f2937;
    }

    .page-title-wrap p{
        margin-bottom:0;
        color:#6b7280;
        font-size:13px;
    }

    .info-card{
        border:none;
        border-radius:14px;
        box-shadow:0 4px 14px rgba(15, 23, 42, 0.06);
        transition:all .2s ease;
        overflow:hidden;
        height:100%;
    }

    .info-card:hover{
        transform:translateY(-2px);
        box-shadow:0 10px 24px rgba(15, 23, 42, 0.10);
    }

    .info-card .card-body{
        padding:16px;
    }

    .info-label{
        font-size:11px;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:.4px;
        color:#94a3b8;
        margin-bottom:6px;
        display:block;
    }

    .info-value{
        font-size:16px;
        font-weight:700;
        color:#1e293b;
        margin:0;
        line-height:1.3;
    }

    .info-value.primary{
        color:#2563eb;
    }

    .info-value.success{
        color:#49526B;
    }

    .info-value.danger{
        color:#dc2626;
    }

    .section-card{
        border:none;
        border-radius:16px;
        box-shadow:0 4px 14px rgba(15, 23, 42, 0.06);
        margin-bottom:18px;
        overflow:hidden;
    }

    .section-card .card-body{
        padding:18px;
    }

    .section-title{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        margin-bottom:16px;
    }

    .section-title h5{
        margin:0;
        font-weight:700;
        color:#1f2937;
        display:flex;
        align-items:center;
        gap:8px;
    }

    .section-title .badge{
        font-size:11px;
        padding:7px 10px;
        border-radius:999px;
    }

    .table-modern tbody tr:hover{
        background:#f8fafc;
    }

    .produto-title{
        font-weight:700;
        color:#0f172a;
    }

    .qtd-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:34px;
        padding:6px 10px;
        border-radius:999px;
        background:#eef2ff;
        color:#4338ca;
        font-weight:700;
        font-size:12px;
    }

    .money{
        font-weight:700;
        color:#49526B;
        white-space:nowrap;
    }

    .subtotal-box{
        margin-top:14px;
        display:flex;
        justify-content:flex-end;
    }

    .subtotal-content{
        min-width:260px;
        background:#f8fafc;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:14px 16px;
    }

    .subtotal-line{
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-size:14px;
        margin-bottom:6px;
        color:#475569;
    }

    .subtotal-line:last-child{
        margin-bottom:0;
    }

    .subtotal-line.total{
        font-size:18px;
        font-weight:700;
        color:#0f172a;
        padding-top:8px;
        margin-top:8px;
        border-top:1px dashed #cbd5e1;
    }

    .empty-row{
        text-align:center;
        color:#94a3b8;
        padding:20px 10px !important;
        font-weight:600;
    }

    .btn-top{
        border-radius:10px;
        padding:8px 14px;
        font-weight:600;
    }

    @media (max-width: 768px){
        .info-value{
            font-size:14px;
        }

        .subtotal-content{
            width:100%;
            min-width:100%;
        }
    }
</style>
@endsection

@section('content')
<div class="card mt-1">
    <div class="card-body">

        <div class="row">
            <div class="col-12">
                <div class="section-card">
                    <div class="card-body">

                        <div class="page-header-box">
                            <div class="page-title-wrap">
                                <h3>Detalhes da Venda</h3>
                                <p>Visualize cliente, itens, serviços e pagamentos da venda.</p>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">

                                @if($data->estado == 'aprovado')
                                <a class="btn btn-success btn-top" title="Imprimir" target="_blank" href="{{ route('nfce.imprimir', [$data->id]) }}">
                                    <i class="ri-printer-line"></i> Imprimir Fiscal
                                </a>
                                @endif

                                <a class="btn btn-dark btn-top" title="Imprimir" target="_blank" href="{{ route('frontbox.imprimir-nao-fiscal', [$data->id]) }}">
                                    <i class="ri-printer-line"></i> Imprimir
                                </a>

                                <a href="{{ route('frontbox.index') }}" class="btn btn-danger btn-top">
                                    <i class="ri-arrow-left-double-fill"></i> Voltar
                                </a>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="info-card">
                                    <div class="card-body">
                                        <span class="info-label">Cliente</span>
                                        <h6 class="info-value primary">
                                            {{ $data->cliente_id ? $data->cliente->razao_social : 'Consumidor Final' }}
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="info-card">
                                    <div class="card-body">
                                        <span class="info-label">Usuário</span>
                                        <h6 class="info-value">
                                            {{ $data->user->name ?? '--' }}
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="info-card">
                                    <div class="card-body">
                                        <span class="info-label">Qtd. Produtos</span>
                                        <h6 class="info-value">
                                            {{ $data->itens->count() }}
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 col-12">
                                <div class="info-card">
                                    <div class="card-body">
                                        <span class="info-label">Total Geral</span>
                                        <h5 class="info-value success">
                                            R$ {{ __moeda($data->itens->sum('sub_total') + $data->itensServico->sum('sub_total')) }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($data->estado == 'aprovado')
                        <div class="section-card">
                            <div class="card-body">
                                <div class="section-title">
                                    <h5>
                                        <i class="ri-file-list-3-line text-success"></i>
                                        Dados Fiscais
                                    </h5>
                                    <span class="badge bg-success">Aprovado</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-6 col-12">
                                        <div class="info-card">
                                            <div class="card-body">
                                                <span class="info-label">Ambiente</span>
                                                <h6 class="info-value">
                                                    {{ $data->ambiente == 1 ? 'Produção' : 'Homologação' }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12">
                                        <div class="info-card">
                                            <div class="card-body">
                                                <span class="info-label">Série</span>
                                                <h6 class="info-value">
                                                    {{ $data->numero_serie ?? '--' }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12">
                                        <div class="info-card">
                                            <div class="card-body">
                                                <span class="info-label">Número</span>
                                                <h6 class="info-value">
                                                    {{ $data->numero ?? '--' }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-6 col-12">
                                        <div class="info-card">
                                            <div class="card-body">
                                                <span class="info-label">Chave</span>
                                                <h6 class="info-value" style="font-size:13px; word-break:break-all;">
                                                    {{ $data->chave ?? '--' }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        {{-- PRODUTOS --}}
                        <div class="section-card">
                            <div class="card-body">
                                <div class="section-title">
                                    <h5>
                                        <i class="ri-shopping-cart-line text-primary"></i>
                                        Produtos
                                    </h5>
                                    <span class="badge bg-primary">
                                        {{ $data->itens->count() }} item(ns)
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 90px">#</th>
                                                <th>Produto</th>
                                                <th class="text-center" style="width: 120px">Quantidade</th>
                                                <th class="text-end" style="width: 160px">Valor</th>
                                                <th class="text-end" style="width: 180px">Sub Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data->itens as $item)
                                            <tr>
                                                <td>{{ $item->produto->numero_sequencial }}</td>
                                                <td>
                                                    <div class="produto-title">{{ $item->produto->nome }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="qtd-badge">{{ $item->quantidade }}</span>
                                                </td>
                                                <td class="text-end">R$ {{ __moeda($item->valor_unitario) }}</td>
                                                <td class="text-end">
                                                    <span class="money">R$ {{ __moeda($item->sub_total) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="empty-row">Nada encontrado</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="subtotal-box">
                                    <div class="subtotal-content">
                                        <div class="subtotal-line total">
                                            <span>Total Produtos</span>
                                            <span class="money">R$ {{ __moeda($data->itens->sum('sub_total')) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SERVIÇOS --}}
                        @if($data->itensServico->count() > 0)
                        <div class="section-card">
                            <div class="card-body">
                                <div class="section-title">
                                    <h5>
                                        <i class="ri-tools-line text-warning"></i>
                                        Serviços
                                    </h5>
                                    <span class="badge bg-warning text-dark">
                                        {{ $data->itensServico->count() }} serviço(s)
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 90px">#</th>
                                                <th>Serviço</th>
                                                <th class="text-center" style="width: 120px">Quantidade</th>
                                                <th class="text-end" style="width: 160px">Valor</th>
                                                <th class="text-end" style="width: 180px">Sub Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data->itensServico as $item)
                                            <tr>
                                                <td>{{ $item->servico->numero_sequencial }}</td>
                                                <td>
                                                    <div class="produto-title">{{ $item->servico->nome }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="qtd-badge">{{ $item->quantidade }}</span>
                                                </td>
                                                <td class="text-end">R$ {{ __moeda($item->valor_unitario) }}</td>
                                                <td class="text-end">
                                                    <span class="money">R$ {{ __moeda($item->sub_total) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="empty-row">Nada encontrado</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="subtotal-box">
                                    <div class="subtotal-content">
                                        <div class="subtotal-line total">
                                            <span>Total Serviços</span>
                                            <span class="money">R$ {{ __moeda($data->itensServico->sum('sub_total')) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PAGAMENTOS --}}
                        <div class="section-card">
                            <div class="card-body">
                                <div class="section-title">
                                    <h5>
                                        <i class="ri-money-dollar-circle-line text-success"></i>
                                        Forma de Pagamento
                                    </h5>
                                    <span class="badge bg-success">
                                        {{ $data->fatura->count() }} pagamento(s)
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-modern align-middle">
                                        <thead>
                                            <tr>
                                                <th>Pagamento</th>
                                                <th>Data Vencimento</th>
                                                <th class="text-end" style="width: 180px">Valor</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($data->fatura as $item)
                                            <tr>
                                                <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                                                <td>{{ __data_pt($item->data_vencimento, 0) }}</td>
                                                <td class="text-end">
                                                    <span class="money">R$ {{ __moeda($item->valor) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="3" class="empty-row">Nada encontrado</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="subtotal-box">
                                    <div class="subtotal-content">
                                        <div class="subtotal-line total">
                                            <span>Total Pagamentos</span>
                                            <span class="money">R$ {{ __moeda($data->fatura->sum('valor')) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- fim card-body principal --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection