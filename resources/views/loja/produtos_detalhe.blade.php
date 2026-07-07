@extends('loja.default', ['title' => $produto->nome])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/ecommerce_produto.css">
@endsection

@section('content')

<div class="product-page-premium">
    <div class="container">

        <div class="product-detail-premium">

            <div class="product-gallery-area">

                <div class="product-main-photo">
                    <img id="main-product-image" src="{{ $produto->img }}" alt="{{ $produto->nome }}">
                </div>

                <div class="product-thumb-list">
                    <button type="button" class="product-thumb-item active" data-img="{{ $produto->img }}">
                        <img src="{{ $produto->img }}" alt="{{ $produto->nome }}">
                    </button>

                    @foreach($produto->galeria as $g)
                    <button type="button" class="product-thumb-item" data-img="{{ $g->img }}">
                        <img src="{{ $g->img }}" alt="{{ $produto->nome }}">
                    </button>
                    @endforeach
                </div>

            </div>

            <div class="product-info-area">

                @if($produto->categoria)
                <span class="product-category-badge">{{ $produto->categoria->nome }}</span>
                @endif

                <h1>{{ $produto->nome }}</h1>

                <div class="product-price-area">
                    <strong class="product-price">R$ {{ __moeda($produto->valor_ecommerce) }}</strong>

                    @if($produto->percentual_desconto > 0)
                    <del>R$ {{ __moeda($produto->valor_ecommerce + ($produto->valor_ecommerce*$produto->percentual_desconto/100)) }}</del>
                    <span>-{{ $produto->percentual_desconto }}%</span>
                    @endif
                </div>

                <div class="product-stock-line">
                    <span></span>
                    <strong>Em estoque</strong>

                    @if($produto->gerenciar_estoque && $produto->estoque)
                    <small>{{ number_format($produto->estoque->quantidade, 0) }} disponível</small>
                    @endif
                </div>

                @if($produto->descricao_ecommerce)
                <p class="product-short-text">{{ $produto->descricao_ecommerce }}</p>
                @endif

                <form method="post" action="{{ route('loja.adicionar-carrinho') }}" class="product-cart-form">
                    @csrf

                    <input type="hidden" name="link" value="{{ $config->loja_id }}">
                    <input type="hidden" name="produto_id" value="{{ $produto->id }}">

                    @if(sizeof($produto->variacoes) > 0)
                    <div class="product-field">
                        <label>Selecione uma opção</label>
                        <select id="variacao_id" name="variacao_id">
                            @foreach($produto->variacoes as $v)
                            @if($v->valor > 0)
                            <option @if($v->estoqueNegativo() == 1) disabled @endif value="{{ $v->id }}">{{ $v->descricao }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="product-buy-row">
                        <div class="product-qty-box">
                            <label>Quantidade</label>

                            <div class="product-qty-control">
                                <button type="button" class="qty-minus">-</button>
                                <input name="quantidade" type="number" value="1" min="1">
                                <button type="button" class="qty-plus">+</button>
                            </div>
                        </div>

                        <button class="product-buy-button" type="submit">
                            <i class="fa fa-shopping-cart"></i>
                            Adicionar ao carrinho
                        </button>
                    </div>
                </form>

            </div>

        </div>

        @if($produto->texto_ecommerce)
        <div class="product-description-card">
            <div class="product-description-title">
                <span>Informações</span>
                <h2>Descrição do produto</h2>
            </div>

            <div class="product-description-content">
                {!! $produto->texto_ecommerce !!}
            </div>
        </div>
        @endif

    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    $(function(){
        getVariacao()
    })

    $(document).on("click", ".product-thumb-item", function () {
        let img = $(this).data('img')
        $('#main-product-image').attr('src', img)
        $('.product-thumb-item').removeClass('active')
        $(this).addClass('active')
    })

    $(document).on("click", ".qty-plus", function () {
        let input = $(this).closest('.product-qty-control').find('input')
        let value = parseInt(input.val() || 1)
        input.val(value + 1)
    })

    $(document).on("click", ".qty-minus", function () {
        let input = $(this).closest('.product-qty-control').find('input')
        let value = parseInt(input.val() || 1)

        if(value > 1){
            input.val(value - 1)
        }
    })

    $(document).on("change", "#variacao_id", function () {
        getVariacao()
    })

    function getVariacao(){
        let variacao_id = $('#variacao_id').val()

        if(!variacao_id){
            return
        }

        $.get(path_url+'api/ecommerce/variacao/', {variacao_id: variacao_id})
        .done((success) => {
            $('.product-price').html('R$ '+convertFloatToMoeda(success.valor))
        })
        .fail((err) => {
            console.log(err)
        })
    }
</script>
@endsection