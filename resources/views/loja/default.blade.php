<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{$title}} - {{ $config->nome }}</title>

	<link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700" rel="stylesheet">
	<link href="/assets/vendor/select2/css/select2.min.css" rel="stylesheet">

	<style>
		:root{
			--color-main: {{ $config->cor_principal ?? '#E11D48' }};
		}
	</style>

	<link rel="stylesheet" href="/ecommerce/css/bootstrap.min.css">
	<link rel="stylesheet" href="/ecommerce/css/slick.css">
	<link rel="stylesheet" href="/ecommerce/css/slick-theme.css">
	<link rel="stylesheet" href="/ecommerce/css/font-awesome.min.css">

	<link rel="stylesheet" href="/assets/css/toastr.min.css">

	<link rel="stylesheet" href="/css/loja-premium.css">

	@yield('css')
</head>

<body>

	<div class="modal-loading"></div>

	<header>

		<div class="top-header">
			<div class="container">
				<div class="top-header-content">

					<a href="{{ route('loja.minha-conta', ['link='.$config->loja_id]) }}">
						<i class="fa fa-user-o"></i>
						Minha conta
					</a>

				</div>
			</div>
		</div>

		<div class="main-header">
			<div class="container">

				<div class="header-content">

					<div class="logo-area">
						<a href="{{ route('loja.index', ['link='.$config->loja_id]) }}">
							<img src="{{ $config->logo_img }}" alt="Logo">
						</a>
					</div>

					<div class="search-area">

						<form class="search-form" method="get" action="{{ route('loja.pesquisa') }}">

							<input type="hidden" value="{{ $config->loja_id }}" name="link">

							<select name="categoria">
								<option value="">Categorias</option>

								@foreach($categorias as $c)
								@if($c->hash_ecommerce)
								<option @isset($categoria_pesquisa)@if($categoria_pesquisa == $c->hash_ecommerce) selected @endif
									@endisset
									value="{{ $c->hash_ecommerce }}">
									{{ $c->nome }}
								</option>
								@endif
								@endforeach

							</select>

							<input type="text" placeholder="O que você procura hoje?" name="pesquisa" @isset($pesquisa) value="{{ $pesquisa }}" @endisset>

							<button type="submit">
								<i class="fa fa-search"></i>
								Procurar
							</button>

						</form>

					</div>

					<div class="header-actions">

						<a href="{{ route('loja.minha-conta', ['link='.$config->loja_id]) }}" class="action-btn">
							<i class="fa fa-user-o"></i>
						</a>

						<a href="{{ route('loja.carrinho', ['link='.$config->loja_id]) }}" class="action-btn">

							<i class="fa fa-shopping-cart"></i>

							@if(isset($carrinho) && $carrinho != [])
							<div class="cart-badge">
								{{ sizeof($carrinho->itens) }}
							</div>
							@endif

						</a>

					</div>

				</div>

			</div>
		</div>

		<div class="navigation">
			<div class="container">

				<div class="navigation-content">

					<a class="nav-item-loja active" href="{{ route('loja.index', ['link='.$config->loja_id]) }}">
						Home
					</a>

					@foreach($categorias as $c)
					@if($c->hash_ecommerce)

					<a class="nav-item-loja" href="{{ route('loja.produtos-categoria', [$c->hash_ecommerce, 'link='.$config->loja_id]) }}">
						{{ $c->nome }}
					</a>

					@endif
					@endforeach

				</div>

			</div>
		</div>

	</header>

	<div class="page-content">
		@yield('content')
	</div>

	<footer class="footer">

		<div class="container">

			<div class="row">

				<div class="col-md-4">
					<h4 class="footer-title">Sobre a loja</h4>

					@if($config->descricao_breve)
					<p>{{ $config->descricao_breve }}</p>
					@endif
				</div>

				<div class="col-md-4">
					<h4 class="footer-title">Categorias</h4>

					<ul class="footer-links">

						@foreach($categorias as $c)
						@if($c->hash_ecommerce)

						<li>
							<a href="{{ route('loja.produtos-categoria', [$c->hash_ecommerce, 'link='.$config->loja_id]) }}">
								{{ $c->nome }}
							</a>
						</li>

						@endif
						@endforeach

					</ul>
				</div>

				<div class="col-md-4">
					<h4 class="footer-title">Contato</h4>

					<ul class="footer-links">

						<li>
							<a href="tel:{{ $config->telefone }}">
								<i class="fa fa-phone"></i>
								{{ $config->telefone }}
							</a>
						</li>

						<li>
							<a href="mailto:{{ $config->email }}">
								<i class="fa fa-envelope"></i>
								{{ $config->email }}
							</a>
						</li>

					</ul>
				</div>

			</div>

			<div class="footer-bottom">
				Copyright © {{ date('Y') }} - {{ $config->nome }}
			</div>

		</div>

	</footer>

	<script src="/ecommerce/js/jquery.min.js"></script>
	<script src="/ecommerce/js/bootstrap.min.js"></script>
	<script src="/ecommerce/js/slick.min.js"></script>

	<script src="/assets/js/toastr.min.js"></script>
	<script src="/assets/vendor/jquery-mask-plugin/jquery.mask.min.js"></script>
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	<script>

		@if(session()->has('flash_success'))
		toastr.success('{{ session()->get('flash_success') }}');
		@endif

		@if(session()->has('flash_error'))
		toastr.error('{{ session()->get('flash_error') }}');
		@endif

		$body = $("body");

		$(document).on({
			ajaxStart: function () {
				$body.addClass("loading");
			},
			ajaxStop: function () {
				$body.removeClass("loading");
			}
		});

	</script>

	@yield('js')

</body>
</html>