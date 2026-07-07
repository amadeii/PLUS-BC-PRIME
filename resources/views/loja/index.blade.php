@extends('loja.default', ['title' => 'Home'])
@section('content')

<div class="container">

    <!-- <div class="store-strip">
        <div>
            <span>Loja online</span>
            <h1>{{ $config->nome }}</h1>
        </div>

        @if($config->descricao_breve)
        <p>{{ $config->descricao_breve }}</p>
        @endif
    </div> -->

    <div class="section-card">
        <div class="section-title">
            <div>
                <span class="section-subtitle">Confira nossas ofertas</span>
                <h2>Produtos em Destaque</h2>
            </div>
        </div>

        @if(sizeof($produtosEmDestaque) > 0)
        <div class="products-grid">

            @foreach($produtosEmDestaque as $p)
            <a class="product-card-premium" href="{{ route('loja.produto-detalhe', [$p->hash_ecommerce, 'link='.$config->loja_id]) }}">

                <div class="product-image-box">
                    <img src="{{ $p->img }}" alt="{{ $p->nome }}">

                    <div class="product-tags">
                        @if($p->percentual_desconto > 0)
                        <span class="tag-sale">-{{ $p->percentual_desconto }}%</span>
                        @endif

                        <span class="tag-featured">Destaque</span>
                    </div>
                </div>

                <div class="product-info">
                    <span class="product-category-premium">
                        {{ $p->categoria ? $p->categoria->nome : 'Geral' }}
                    </span>

                    <h3>{{ $p->nome }}</h3>

                    <div class="product-price-line">
                        @if(sizeof($p->variacoes) > 0)
                        <strong class="product-price-premium">{{ $p->valorPrimeiraVariacao() }}</strong>
                        @else
                            @if($p->valor_ecommerce > 0)
                            <strong class="product-price-premium">R$ {{ __moeda($p->valor_ecommerce) }}</strong>

                            @if($p->percentual_desconto > 0)
                            <del>R$ {{ __moeda($p->valor_ecommerce + ($p->valor_ecommerce*$p->percentual_desconto/100)) }}</del>
                            @endif
                            @endif
                        @endif
                    </div>

                    <span class="btn-buy-premium">
                        Ver produto
                    </span>
                </div>

            </a>
            @endforeach

        </div>
        @else
        <div class="empty-store">
            <div class="empty-icon">
                <i class="fa fa-shopping-bag"></i>
            </div>
            <h3>Nenhum produto em destaque</h3>
            <p>Em breve novos produtos estarão disponíveis nesta loja.</p>
        </div>
        @endif
    </div>

</div>

@endsection