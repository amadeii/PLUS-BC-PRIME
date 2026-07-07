@extends('layouts.app', ['title' => 'Cotação #' . $item->referencia])

@section('css')
<style>
    @page { size: auto; margin: 0mm; }

    .print{
        margin:15px;
    }

    .header-cotacao{
        border-bottom:2px solid #eee;
        padding-bottom:10px;
        margin-bottom:20px;
    }

    .info-label{
        font-size:13px;
        color:#888;
    }

    .info-value{
        font-weight:600;
    }

    .resumo-box{
        background:#f8f9fa;
        border-radius:8px;
        padding:15px;
    }

    .table thead th{
        font-size:14px;
    }

    @media print {

        .d-print-none{
            display:none !important;
        }

        .print{
            margin:5px;
        }

    }
</style>
@endsection

@section('content')

<div class="print">

    <div class="card shadow-sm">
        <div class="card-body">

            <!-- HEADER -->

            <div class="row header-cotacao align-items-center">

                <div class="col-md-6">

                    <h4 class="mb-1">
                        Cotação
                        <span class="text-success">#{{ $item->referencia }}</span>
                    </h4>

                    <div class="text-muted">
                        Fornecedor: <strong>{{ $item->fornecedor->info }}</strong>
                    </div>

                </div>

                <div class="col-md-6 text-md-end">

                    <div class="info-label">Estado</div>

                    @if($item->estado == 'aprovada')
                    <span class="badge bg-success">Aprovada</span>
                    @elseif($item->estado == 'rejeitada')
                    <span class="badge bg-danger">Rejeitada</span>
                    @elseif($item->estado == 'respondida')
                    <span class="badge bg-primary">Respondida</span>
                    @else
                    <span class="badge bg-info">Nova</span>
                    @endif

                </div>

            </div>

            <!-- BOTÃO VOLTAR -->

            <div class="d-print-none mb-3">

                <a href="{{ route('cotacoes.index') }}" class="btn btn-danger btn-sm">
                    <i class="ri-arrow-left-line"></i> Voltar
                </a>

            </div>

            <!-- INFO -->

            <div class="row mb-4">

                <div class="col-md-4">

                    <div class="info-label">Responsável</div>
                    <div class="info-value">{{ $item->responsavel }}</div>

                </div>

                <div class="col-md-4">

                    <div class="info-label">Data cadastro</div>
                    <div class="info-value">{{ __data_pt($item->created_at,1) }}</div>

                </div>

                <div class="col-md-4">

                    <div class="info-label">Data resposta</div>
                    <div class="info-value">{{ __data_pt($item->data_resposta,1) }}</div>

                </div>

                <div class="col-md-4 mt-3">

                    <div class="info-label">Previsão entrega</div>
                    <div class="info-value">{{ __data_pt($item->previsao_entrega,0) }}</div>

                </div>

                <div class="col-md-4 mt-3">

                    <div class="info-label">Código barras</div>
                    <div class="info-value">{{ $item->codigo_barras }}</div>

                </div>

            </div>

            <!-- TABELA PRODUTOS -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th width="120">Quantidade</th>
                            <th width="150">Valor Unitário</th>
                            <th width="150">Subtotal</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($item->itens as $i)

                        @php
                        $casasDecimais = $i->produto->unidade == 'UN' ? 0 : 2;
                        @endphp

                        <tr>

                            <td>{{ $i->produto->nome }}</td>

                            <td>{{ number_format($i->quantidade,$casasDecimais) }}</td>

                            <td>{{ __moeda($i->valor_unitario) }}</td>

                            <td>{{ __moeda($i->sub_total) }}</td>

                            <td>{{ $i->observacao }}</td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            <!-- FATURA -->

            @if(sizeof($item->fatura) > 0)

            <div class="mt-4">

                <h5>Fatura</h5>

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead class="table-light">

                            <tr>
                                <th width="150">Vencimento</th>
                                <th width="200">Tipo pagamento</th>
                                <th width="150">Valor</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($item->fatura as $i)

                            <tr>

                                <td>{{ __data_pt($i->data_vencimento,0) }}</td>

                                <td>{{ $i->getTipoPagamento() }}</td>

                                <td>{{ __moeda($i->valor) }}</td>

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

            @endif


            <!-- RESUMO -->

            <div class="row mt-4">

                <div class="col-md-6"></div>

                <div class="col-md-6">

                    <div class="resumo-box">

                        <p class="mb-1">
                            Produtos
                            <span class="float-end">
                                R$ {{ __moeda($item->itens->sum('sub_total')) }}
                            </span>
                        </p>

                        <p class="mb-1">
                            Desconto
                            <span class="float-end">
                                R$ {{ __moeda($item->desconto) }}
                            </span>
                        </p>

                        <p class="mb-1">
                            Frete
                            <span class="float-end">
                                R$ {{ __moeda($item->valor_frete) }}
                            </span>
                        </p>

                        <hr>

                        <h5 class="mb-0">
                            Total
                            <span class="float-end text-success">
                                R$ {{ __moeda($item->valor_total) }}
                            </span>
                        </h5>

                    </div>

                </div>

            </div>


            <!-- OBSERVAÇÕES -->

            @if($item->observacao || $item->observacao_resposta || $item->observacao_frete)

            <div class="mt-4">

                @if($item->observacao)
                <p><strong>Observação:</strong> {{ $item->observacao }}</p>
                @endif

                @if($item->observacao_resposta)
                <p><strong>Observação resposta:</strong> {{ $item->observacao_resposta }}</p>
                @endif

                @if($item->observacao_frete)
                <p><strong>Observação frete:</strong> {{ $item->observacao_frete }}</p>
                @endif

            </div>

            @endif


            <!-- BOTÕES -->

            <div class="d-print-none mt-4 text-end">

                @if($cotacaoComCompra == null)

                @if($item->estado != 'aprovada')

                <a href="{{ route('cotacoes.purchase',[$item->id]) }}" class="btn btn-dark">
                    <i class="ri-bookmark-fill"></i> Gerar compra
                </a>

                @endif

                @endif


                @if($item->nfe_id)

                <a class="btn btn-success" href="{{ route('nfe.show',$item->nfe_id) }}">
                    <i class="ri-file-text-line"></i> Ver NFe
                </a>

                @endif


                <a href="javascript:window.print()" class="btn btn-primary">
                    <i class="ri-printer-line"></i> Imprimir
                </a>

            </div>


            @if($cotacaoComCompra != null)

            <p class="text-danger mt-3">
                Não é possível gerar compra para esta cotação,
                <strong>{{ $cotacaoComCompra->fornecedor->info }}</strong>
                já foi escolhido como fornecedor.
            </p>

            @endif


        </div>
    </div>

</div>

@endsection