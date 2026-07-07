@extends('layouts.app', ['title' => isset($isCompra) ? 'Nova Compra' : (isset($isOrcamento) && $isOrcamento == 1 ? 'Novo orçamento' : 'Nova Venda')])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        @isset($isCompra)
        <h4>Nova Compra</h4>
        @else
        @if(isset($isOrcamento) && $isOrcamento == 1)
        <h4>Novo Orçamento</h4>
        @else
        <h4>Nova Venda - NFe</h4>
        @endif
        @endif

        @isset($isReserva)
        <div class="info-destaque info-reserva">
            <i class="fa fa-calendar"></i>
            Consumo da reserva <strong>#{{ $item->numero_sequencial }}</strong>
        </div>
        @endisset

        @isset($orcamentosId)
        <div class="info-destaque info-orcamento">
            <i class="fa fa-file-text"></i>
            Gerando venda de orçamentos
        </div>
        @endisset

        @isset($isPedidoVendiZap)
        <div class="info-destaque info-vendizap">
            <i class="fa fa-whatsapp"></i>
            Gerando venda pedido VendiZap <strong>#{{ $item->_id }}</strong>
        </div>
        @endisset

        @isset($isPedidoNuvemShop)
        <div class="info-destaque info-nuvemshop">
            <i class="fa fa-cloud"></i>
            Gerando venda pedido NuvemShop <strong>#{{ $item->pedido_id }}</strong>
        </div>
        @endisset

        @if(isset($isOrcamento) && $isOrcamento == 1)
        <input type="hidden" id="is_orcamento" value="1">
        @else
        <input type="hidden" id="is_orcamento" value="0">
        @endif

        <div style="text-align: right; margin-top: -35px;">
            @if(__countLocalAtivo() > 1 && isset($caixa) && !__escolheLocalidade())
            <h5 class="mt-2">Local: <strong class="text-danger">{{ $caixa->localizacao ? $caixa->localizacao->descricao : '' }}</strong></h5>
            @endif

            @if(isset($isOrcamento) && $isOrcamento == 1)
            <a href="{{ route('orcamentos.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
            @else
            <a href="{{ !isset($isCompra) ? route('nfe.index') : route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">

        {!!Form::open()
        ->post()
        ->id('form-nfe')
        ->route('nfe.store')
        !!}

        <div class="pl-lg-4">
            @include('nfe._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@isset($isCompra)
@include('modals._novo_fornecedor')
@include('compras.partials._importacao_di')
@else
@include('modals._novo_cliente')
@endif

@include('modals._dimensao_item_nfe')
@include('modals._descricao_item')

@section('js')
<script type="text/javascript" src="/js/busca_cep.js"></script>

<script type="text/javascript"> 
    $(".tipo_pagamento").change(() => {
        let tipo = $(".tipo_pagamento").val();
        if (tipo == "03" || tipo == "04") {
            $('#cartao_credito').modal('show')
        }
    })
</script>

<script src="/js/nfe.js"></script>
@isset($isCompra)
<script src="/js/novo_fornecedor.js"></script>
<script src="/js/compra_importacao.js"></script>
@else
<script src="/js/novo_cliente.js"></script>
@endif
@endsection
@endsection
