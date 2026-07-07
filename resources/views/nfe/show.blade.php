@extends('layouts.app', ['title' => $data->tpNF == 0 ? 'Detalhes da Compra' : 'Detalhes da Venda'])
@section('content')

@php
$isCompra = ($data->tpNF == 0);
$titulo = $isCompra ? 'Detalhes da Compra' : 'Detalhes da Venda';
@endphp

<div class="page-content">
    <div class="card border-top border-0 mt-1">
        <div class="card-body p-4 p-lg-5">

            {{-- Header --}}
            <div class="d-flex align-items-start align-items-lg-center justify-content-between gap-3 flex-wrap">
                <div>
                    <h4 class="mb-1 text-primary">{{ $titulo }}</h4>

                    <div class="text-muted small">
                        <span class="me-3">
                            Cadastro: <strong class="text-dark">{{ __data_pt($data->created_at) }}</strong>
                        </span>

                        @if($data->chave)
                        <span class="me-3">
                            Emissão: <strong class="text-dark">{{ __data_pt($data->data_emissao) }}</strong>
                        </span>
                        <span class="me-3">
                            Nº: <strong class="text-dark">{{ $data->numero }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i> Voltar
                    </a>

                    <a class="btn btn-dark btn-sm px-3" title="Imprimir Pedido" target="_blank"
                    href="{{ route('nfe.imprimirVenda', [$data->id]) }}">
                    <i class="ri-printer-line"></i> Imprimir Pedido
                </a>
            </div>
        </div>

        <hr class="my-4">

        {{-- Top info cards --}}
        <div class="row g-3">

            <div class="col-12 col-lg-8">
                <div class="p-3 rounded border bg-light">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="mb-0">
                                {{ $isCompra ? 'Fornecedor' : 'Cliente' }}:
                                <strong class="text-primary">
                                    {{ $isCompra
                                    ? ($data->fornecedor_id ? $data->fornecedor->razao_social : 'Consumidor Final')
                                    : ($data->cliente_id ? $data->cliente->razao_social : 'Consumidor Final')
                                }}
                            </strong>
                        </h5>

                        <h5 class="mb-0">
                            Total:
                            <strong class="text-success">R$ {{ __moeda($data->total) }}</strong>
                        </h5>
                    </div>

                    @if($data->user)
                    <div class="small text-muted">
                        Usuário: <strong class="text-dark">{{ $data->user->name }}</strong>
                    </div>
                    @endif

                    @if($data->chave_importada)
                    <div class="small text-muted">
                        Chave importada: <strong class="text-dark">{{ $data->chave_importada }}</strong>
                    </div>
                    @endif

                    {{-- OBSERVAÇÃO --}}
                    <div class="mt-2">
                        <div class="small text-muted mb-1">Observação</div>
                        @php
                        $obs = $data->observacao ?? $data->obs ?? $data->observacoes ?? null;
                        @endphp

                        @if($obs)
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="ri-information-line"></i>
                            <span class="ms-1">{{ $obs }}</span>
                        </div>
                        @else
                        <div class="text-muted small fst-italic">Sem observações.</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="p-3 rounded border">
                <h6 class="mb-3">Estado fiscal</h6>

                @if(__isPlanoFiscal())
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        @if($data->estado == 'aprovado')
                        <span class="badge bg-success">Aprovado</span>
                        @elseif($data->estado == 'cancelado')
                        <span class="badge bg-danger">Cancelado</span>
                        @elseif($data->estado == 'rejeitado')
                        <span class="badge bg-warning text-white">Rejeitado</span>
                        @else
                        <span class="badge bg-info text-white">Novo</span>
                        @endif
                    </div>

                    @if($data->estado == 'rejeitado')
                    <div class="alert alert-warning border-0 d-flex align-items-start mb-1">
                        <div class="me-3">
                            <i class="ri-alert-line fs-4"></i>
                        </div>

                        <div>
                            <small>
                                {{ $data->motivo_rejeicao }}
                            </small>
                        </div>
                    </div>
                    @endif

                    @if($data->estado == 'aprovado')
                    <div class="d-flex gap-2">
                        <a href="{{ route('nfe.download-xml', [$data->id]) }}" class="btn btn-dark btn-sm">
                            <i class="ri-file-download-line"></i> XML
                        </a>

                        <button type="button"
                        onclick="imprimir('{{ $data->id }}', '{{ $data->numero }}')"
                        class="btn btn-primary btn-sm" title="Imprimir NFe">
                        <i class="ri-printer-line"></i> DANFE
                    </button>
                </div>
                @endif
            </div>

            @if($data->chave)
            <div class="mt-3">
                <div class="small text-muted mb-1">Chave</div>
                <div class="text-break small">
                    <strong>{{ $data->chave }}</strong>
                </div>
            </div>
            @endif
            @else
            <div class="text-muted small">Plano fiscal desabilitado.</div>
            @endif
        </div>
    </div>

</div>

{{-- Itens --}}
<div class="mt-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="mb-0">Itens da NFe</h5>
    </div>

    <div class="table-responsive-sm mt-2">
        <table class="table table-striped table-centered mb-0 align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Produto</th>
                    <th class="text-center" style="width: 140px;">Quantidade</th>
                    <th class="text-end" style="width: 140px;">Valor</th>
                    <th class="text-end" style="width: 140px;">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data->itens as $item)
                <tr>
                    <td>{{ $item->descricao() }}</td>
                    <td class="text-center">
                        @if(!$item->produto->unidadeDecimal())
                        {{ number_format($item->quantidade, 0, '.', '') }}
                        @else
                        {{ $item->quantidade }}
                        @endif
                    </td>
                    <td class="text-end">{{ __moeda($item->valor_unitario) }}</td>
                    <td class="text-end">{{ __moeda($item->sub_total) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">Nada encontrado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Fatura --}}
<div class="row mt-4">
    <div class="col-12 col-lg-8">
        <h5 class="mb-2">Fatura</h5>

        <div class="table-responsive-sm">
            <table class="table table-striped table-centered mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Pagamento</th>
                        <th class="text-center" style="width: 170px;">Data Vencimento</th>
                        <th class="text-end" style="width: 140px;">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data->fatura as $item)
                    <tr>
                        <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                        <td class="text-center">{{ __data_pt($item->data_vencimento, 0) }}</td>
                        <td class="text-end">{{ __moeda($item->valor) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">NFe sem informações de pagamento</td>
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

{{-- Modal Print --}}
<div class="modal fade" id="modal-print" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Imprimir NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-12 col-lg-4">
                        <button type="button" class="btn btn-success w-100" onclick="gerarDanfe('danfe')">
                            <i class="ri-printer-line"></i> DANFE
                        </button>
                    </div>

                    <div class="col-12 col-lg-4">
                        <button type="button" class="btn btn-primary w-100" onclick="gerarDanfe('simples')">
                            <i class="ri-printer-line"></i> Simples
                        </button>
                    </div>

                    <div class="col-12 col-lg-4">
                        <button type="button" class="btn btn-dark w-100" onclick="gerarDanfe('etiqueta')">
                            <i class="ri-printer-line"></i> Etiqueta
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript">
    var IDNFE = null;

    function imprimir(id, numero){
        IDNFE = id;
        $('.ref-numero').text(numero);
        $('#modal-print').modal('show');
    }

    function gerarDanfe(tipo){
        if(tipo === 'danfe'){
            window.open('/nfe/imprimir/' + IDNFE);
        }else if(tipo === 'simples'){
            window.open('/nfe/danfe-simples/' + IDNFE);
        }else{
            window.open('/nfe/danfe-etiqueta/' + IDNFE);
        }
        $('#modal-print').modal('hide');
    }
</script>
@endsection
