@extends('loja.default', ['title' => 'Produtos'])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/ecommerce_produtos_categoria.css">
@endsection

@section('content')

<div class="section">
	<div class="container">

		<div class="store-strip">
			<div>
				<span>Loja</span>
				<h1>Produtos</h1>
			</div>
			<p>Confira todos os produtos disponíveis em nossa loja.</p>
		</div>

		<div class="section-card">
			<div class="section-title">
				<div>
					<span class="section-subtitle">Produtos</span>
					<h2>Todos os produtos</h2>
				</div>
			</div>

			@if(sizeof($produtos) > 0)

			<div class="products-grid">
				@foreach($produtos as $p)

				<a href="{{ route('loja.produto-detalhe', [$p->hash_ecommerce, 'link='.$config->loja_id]) }}" class="product-card-premium">

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
							@if($p->valor_ecommerce > 0)
							<strong class="product-price-premium">R$ {{ __moeda($p->valor_ecommerce) }}</strong>

							@if($p->percentual_desconto > 0)
							<del>
								R$ {{ __moeda($p->valor_ecommerce + ($p->valor_ecommerce * $p->percentual_desconto / 100)) }}
							</del>
							@endif
							@endif
						</div>

						<div class="btn-buy-premium">
							<i class="fa fa-shopping-cart"></i>
							Ver produto
						</div>
					</div>

				</a>

				@endforeach
			</div>

			@else

			<div class="empty-store">
				<div class="empty-icon">
					<i class="fa fa-shopping-bag"></i>
				</div>
				<h3>Nenhum produto encontrado</h3>
				<p>A loja ainda não possui produtos disponíveis.</p>
			</div>

			@endif

		</div>

	</div>
</div>

@endsection