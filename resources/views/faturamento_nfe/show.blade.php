{{-- resources/views/faturamento_nfe/show.blade.php --}}

@extends('layouts.app', ['title' => 'Detalhes do Faturamento'])

@section('content')

<div class="page-content">

    <div class="card border-top border-0 mt-1">

        <div class="card-body p-4 p-lg-5">

            <div class="d-flex align-items-start align-items-lg-center justify-content-between gap-3 flex-wrap">

                <div>

                    <h4 class="mb-1 text-primary">
                        Detalhes do Faturamento
                    </h4>

                    <div class="text-muted small">

                        <span class="me-3">
                            Cadastro:
                            <strong class="text-dark">
                                {{ __data_pt($item->created_at) }}
                            </strong>
                        </span>

                        @if($item->data_faturamento)

                        <span class="me-3">
                            Faturamento:
                            <strong class="text-dark">
                                {{ __data_pt($item->data_faturamento, 0) }}
                            </strong>
                        </span>

                        @endif

                        <span class="me-3">
                            Nº:
                            <strong class="text-dark">
                                {{ $item->numero }}
                            </strong>
                        </span>

                    </div>

                </div>

                <div class="d-flex gap-2">

                    <a href="{{ route('faturamento-nfe.index') }}" class="btn btn-danger btn-sm px-3">

                        <i class="ri-arrow-left-double-fill"></i>
                        Voltar
                    </a>

                    @if($item->estado == 'aprovado')

                    <a class="btn btn-dark btn-sm px-3" target="_blank" href="{{ route('nfe.imprimir', [$item->id]) }}">
                        <i class="ri-printer-line"></i>
                        DANFE
                    </a>

                    <a class="btn btn-primary btn-sm px-3" target="_blank" href="{{ route('nfe.download-xml', [$item->id]) }}">
                        <i class="ri-file-download-line"></i>
                        XML
                    </a>

                    @endif

                </div>

            </div>

            <hr class="my-4">
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="p-3 rounded border bg-light">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <h5 class="mb-0">
                                    Cliente:

                                    <strong class="text-primary">

                                        {{ $item->cliente_id
                                        ? $item->cliente->razao_social
                                        : 'Consumidor Final' }}

                                    </strong>

                                </h5>

                                <h5 class="mb-0">
                                    Total:
                                    <strong class="text-success">
                                        R$ {{ __moeda($item->total) }}
                                    </strong>

                                </h5>

                            </div>

                            @if($item->user)

                            <div class="small text-muted">
                                Usuário:
                                <strong class="text-dark">
                                    {{ $item->user->name }}
                                </strong>
                            </div>
                            @endif

                            @if($item->observacao_faturamento)
                            <div class="mt-2">
                                <div class="small text-muted mb-1">
                                    Observação do faturamento
                                </div>

                                <div class="alert alert-warning mb-0 py-2">
                                    <i class="ri-information-line"></i>
                                    <span class="ms-1">
                                        {{ $item->observacao_faturamento }}
                                    </span>

                                </div>

                            </div>

                            @endif

                        </div>

                    </div>

                </div>

                <div class="col-12 col-lg-4">

                    <div class="p-3 rounded border">

                        <h6 class="mb-3">
                            Estado Fiscal
                        </h6>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                            <div>

                                @if($item->estado == 'aprovado')

                                <span class="badge bg-success">
                                    Aprovado
                                </span>

                                @elseif($item->estado == 'cancelado')

                                <span class="badge bg-danger">
                                    Cancelado
                                </span>

                                @elseif($item->estado == 'rejeitado')

                                <button type="button" class="btn btn-warning btn-sm text-white btn-ver-rejeicao" data-motivo="{{ $item->motivo_rejeicao }}" data-numero="{{ $item->numero }}">
                                    Rejeitado
                                </button>

                                @else

                                <span class="badge bg-info text-white">
                                    Novo
                                </span>

                                @endif

                            </div>

                            <div>

                                <div class="small text-muted mb-1">
                                    Estado da Fatura
                                </div>

                                @if($item->estado_fatura == 'finalizado')

                                <span class="badge bg-success">
                                    Faturado
                                </span>

                                @elseif($item->estado_fatura == 'aprovado')

                                <span class="badge bg-primary">
                                    Fatura Aprovada
                                </span>

                                @else

                                <span class="badge bg-warning text-dark">
                                    Fatura Pendente
                                </span>

                                @endif

                            </div>

                        </div>

                        @if($item->chave && $item->estado == 'aprovado')
                        <div class="mt-3">
                            <div class="small text-muted mb-1">
                                Chave de acesso
                            </div>

                            <div class="text-break small">

                                <strong>
                                    {{ $item->chave }}
                                </strong>
                            </div>

                        </div>

                        @endif

                        @if($item->recibo)

                        <div class="mt-3">

                            <div class="small text-muted mb-1">
                                Recibo
                            </div>

                            <div class="text-break small">

                                <strong>
                                    {{ $item->recibo }}
                                </strong>

                            </div>

                        </div>

                        @endif

                    </div>

                </div>

                <div class="mt-4">

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                        <h5 class="mb-0">
                            Itens do Faturamento
                        </h5>

                    </div>

                    <div class="table-responsive-sm mt-2">

                        <table class="table table-striped table-centered mb-0 align-middle">

                            <thead class="table-dark">

                                <tr>
                                    <th>Produto</th>

                                    <th class="text-center" style="width:140px;">
                                        Quantidade
                                    </th>

                                    <th class="text-end" style="width:140px;">
                                        Valor
                                    </th>

                                    <th class="text-end" style="width:140px;">
                                        Sub Total
                                    </th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($item->itens as $i)

                                <tr>

                                    <td>
                                        {{ $i->descricao() }}
                                    </td>

                                    <td class="text-center">

                                        @if(!$i->produto->unidadeDecimal())

                                        {{ number_format($i->quantidade, 0, '.', '') }}

                                        @else

                                        {{ $i->quantidade }}

                                        @endif

                                    </td>

                                    <td class="text-end">
                                        {{ __moeda($i->valor_unitario) }}
                                    </td>

                                    <td class="text-end">
                                        {{ __moeda($i->sub_total) }}
                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td colspan="4"
                                    class="text-center text-muted py-4">

                                    Nada encontrado

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            @if($item->fatura && count($item->fatura) > 0)

            <div class="row mt-4">

                <div class="col-12 col-lg-8">

                    <h5 class="mb-2">
                        Fatura
                    </h5>

                    <div class="table-responsive-sm">

                        <table class="table table-striped table-centered mb-0 align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th>Pagamento</th>

                                    <th class="text-center" style="width:170px;">
                                        Data Vencimento
                                    </th>

                                    <th class="text-end" style="width:140px;">
                                        Valor
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($item->fatura as $f)

                                <tr>

                                    <td>
                                        {{ $f->getTipoPagamento($f->tipo_pagamento) }}
                                    </td>

                                    <td class="text-center">
                                        {{ __data_pt($f->data_vencimento, 0) }}
                                    </td>

                                    <td class="text-end">
                                        {{ __moeda($f->valor) }}
                                    </td>

                                </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            @endif

        </div>

    </div>

</div>

@endsection

@section('js')

<script>

    $(document).on('click', '.btn-ver-rejeicao', function(){

        swal({
            title: "NF-e Rejeitada",
            text: $(this).data('motivo'),
            icon: "warning"
        });

    });

</script>

@endsection