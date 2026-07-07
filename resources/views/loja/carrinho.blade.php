@extends('loja.default', ['title' => 'Carrinho'])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/ecommerce_carrinho.css">
@endsection

@section('content')
<div class="cart-page">
	<div class="container">
		<input type="hidden" id="carrinho_id" value="{{ $item->id }}">

		<div class="cart-header">
			<div>
				<!-- <span class="cart-badge">Meu carrinho</span> -->
				<h2>Carrinho de compras</h2>
				<p>{{ sizeof($item->itens) }} item(ns) no seu pedido</p>
			</div>
			<a href="{{ route('loja.index', ['link='.$config->loja_id]) }}" class="btn-cart-outline">
				<i class="fa fa-shopping-cart"></i> Continuar comprando
			</a>
		</div>

		<div class="row">
			<div class="col-md-8">
				@forelse($item->itens as $i)
				<div class="cart-product">
					<div class="cart-product-img">
						<img src="{{ $i->produto->img }}">
					</div>

					<div class="cart-product-info">
						<h4>{{ $i->produto->nome }} {{ $i->variacao ? $i->variacao->descricao : '' }}</h4>
						<p>Valor unitário: <strong>R$ {{ __moeda($i->valor_unitario) }}</strong></p>

						<form action="{{ route('loja.atualiza-quantidade', [$i->id, 'link='.$config->loja_id]) }}" method="post" id="form-update-{{$i->id}}">
							@csrf
							@method('put')
							<input type="hidden" name="link" value="{{ $config->loja_id }}">
							<input type="hidden" name="produto_variacao_id" value="{{ $i->variacao_id }}">

							<div class="cart-qty">
								<span>Quantidade</span>
								<div class="input-number">
									<input class="qtd" name="quantidade" type="number" min="1" value="{{ number_format($i->quantidade, 0) }}">
									<span class="qty-up">+</span>
									<span class="qty-down">-</span>
								</div>
							</div>
						</form>
					</div>

					<div class="cart-product-total">
						<span>Subtotal</span>
						<strong>R$ {{ __moeda($i->sub_total) }}</strong>

						<form action="{{ route('loja.remove-item', [$i->id, 'link='.$config->loja_id]) }}" method="post" id="form-{{$i->id}}">
							@csrf
							@method('delete')
							<button class="btn-remove btn-delete" title="Remover item">
								<i class="fa fa-trash"></i>
							</button>
						</form>
					</div>
				</div>
				@empty
				<div class="cart-empty">
					<i class="fa fa-shopping-cart"></i>
					<h3>Seu carrinho está vazio</h3>
					<p>Adicione produtos para continuar sua compra.</p>
					<a href="{{ route('loja.index', ['link='.$config->loja_id]) }}" class="btn-cart-primary">Ver produtos</a>
				</div>
				@endforelse
			</div>

			@if(sizeof($item->itens) > 0)
			<div class="col-md-4">
				<div class="cart-summary">
					<h3>Resumo do pedido</h3>

					<div class="summary-line">
						<span>Subtotal</span>
						<strong>R$ {{ __moeda($item->valor_total) }}</strong>
					</div>

					<form method="post" action="{{ route('loja.carrinho-setar-frete', ['link='.$config->loja_id]) }}">
						@csrf

						<div class="frete-box">
							<label>Calcular entrega</label>
							<div class="frete-input">
								<input data-mask="00000-000" class="form-control" name="cep" id="cep" type="tel" placeholder="Digite seu CEP">
								<button type="button" class="btn-frete">
									<i class="fa fa-truck"></i>
								</button>
							</div>
						</div>

						<div class="data-frete">
							@if($dataFrete != null)
							<h5>Seus endereços cadastrados</h5>
							{!! $dataFrete !!}
							@else
							@if($config->habilitar_retirada)
							<label class="frete-option">
								<input class="radio-frete" type="radio" name="tipo_frete" value="0" data-valor="0">
								<span>Retirar na loja</span>
								<strong>Grátis</strong>
							</label>
							@endif

							@if($config->frete_gratis_valor > 0 && $config->frete_gratis_valor <= $item->valor_total)
								<label class="frete-option">
									<input class="radio-frete" type="radio" name="tipo_frete" value="gratis" data-valor="0">
									<span>Frete grátis</span>
									<strong>R$ 0,00</strong>
								</label>
								@endif
								@endif
							</div>

							<input type="hidden" name="valor_frete" id="valor_frete">
							<input type="hidden" name="endereco_id" id="endereco_id">

							<button class="btn-cart-primary btn-ir-pagamento" disabled>
								<i class="fa fa-money"></i> Ir para pagamento
							</button>
						</form>
					</div>
				</div>
				@endif
			</div>
		</div>
	</div>
	@endsection
	@section('js')
	<script type="text/javascript">
		$(document).on("click", ".qty-up", function (e) {
			e.preventDefault();

			let input = $(this).closest(".input-number").find(".qtd");
			let valor = parseInt(input.val()) || 1;

			input.val(valor + 1);

			let form = $(this).closest("form").attr("id");
			document.getElementById(form).submit();
		});

		$(document).on("click", ".qty-down", function (e) {
			e.preventDefault();

			let input = $(this).closest(".input-number").find(".qtd");
			let valor = parseInt(input.val()) || 1;

			if(valor > 1){
				input.val(valor - 1);

				let form = $(this).closest("form").attr("id");
				document.getElementById(form).submit();
			}
		});

		$(function(){
			$('#valor_frete').val('');
		});

		$(".btn-delete").on("click", function (e) {
			e.preventDefault();

			var form = $(this).parents("form").attr("id");

			swal({
				title: "Você está certo?",
				text: "Deseja realmente remover este item do carrinho?",
				icon: "warning",
				buttons: true,
				buttons: ["Cancelar", "Excluir"],
				dangerMode: true,
			}).then((isConfirm) => {

				if (isConfirm) {
					document.getElementById(form).submit();
				}else{
					swal("", "Item mantido no carrinho!", "info");
				}
			});
		});

		$('.qtd').on("blur", function () {
			let form = $(this).closest("form").attr("id");
			document.getElementById(form).submit();
		});

		$('.btn-frete').on("click", function () {

			let carrinho_id = $('#carrinho_id').val();
			let cep = $('#cep').val();

			if(cep.length != 9){
				swal("Alerta", "CEP inválido", "error");
				return;
			}

			$('.btn-frete').html('<i class="fa fa-spinner fa-spin"></i>');

			$.get('/api/ecommerce/calcular-frete', {
				carrinho_id: carrinho_id,
				cep: cep
			})

			.done((res) => {

				$('.data-frete').html(res);

				$('.btn-frete').html(`
					<i class="fa fa-truck"></i>
					`);
			})

			.fail((err) => {

				console.log(err);

				$('.data-frete').html('');

				$('.btn-frete').html(`
					<i class="fa fa-truck"></i>
					`);

				swal(
					"Erro",
					"Algo deu errado ao calcular o frete",
					"error"
					);
			});
		});

		$(document).on("click", ".radio-frete", function () {

			$('#endereco_id').val('');

			let valorFrete = $(this).data('valor');
			let enderecoId = $(this).data('endereco-id');

			$('#valor_frete').val(valorFrete);
			$('#endereco_id').val(enderecoId);

			$('.btn-ir-pagamento').removeAttr('disabled');

			$('.frete-option').removeClass('active');
			$(this).closest('.frete-option').addClass('active');
		});
	</script>
	@endsection