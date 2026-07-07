@extends('loja.default', ['title' => 'Pagamento'])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/ecommerce_pagamento.css">
@endsection

@section('content')
<div class="section checkout-page">
	<div class="container">

		<div class="checkout-hero">
			<div>
				<span><i class="fa fa-lock"></i> Finalizar compra</span>
				<h1>Pagamento do pedido</h1>
			</div>
			<p>Confira os dados do pedido e escolha a melhor forma de pagamento.</p>
		</div>

		<div class="checkout-grid">

			<aside class="checkout-summary">
				<div class="summary-card">
					<div class="summary-header">
						<div>
							<span>Resumo</span>
							<h2>Seu pedido</h2>
						</div>
						<div class="header-icon"><i class="fa fa-shopping-bag"></i></div>
					</div>

					<div class="summary-products">
						@foreach($carrinho->itens as $i)
						<div class="summary-product">
							<div>
								<strong>{{ number_format($i->quantidade, 0) }}x {{ $i->produto->nome }}</strong>
								<small>Produto do pedido</small>
							</div>
							<span>R$ {{ __moeda($i->sub_total) }}</span>
						</div>
						@endforeach
					</div>

					<div class="summary-line">
						<span>Entrega</span>
						<strong>{{ $carrinho->tipo_frete != 0 ? $carrinho->tipo_frete : '' }} R$ {{ __moeda($carrinho->valor_frete) }}</strong>
					</div>

					<div class="summary-total">
						<span>Total</span>
						<strong>R$ {{ __moeda($carrinho->valor_total) }}</strong>
					</div>

					@if($carrinho->endereco)
					<div class="summary-address">
						<div class="summary-address-icon"><i class="fa fa-map-marker"></i></div>
						<div>
							<span>Endereço de entrega</span>
							<strong>{{ $carrinho->endereco->info }}</strong>
						</div>
					</div>
					@endif

					<div class="summary-note">
						<label>Observação do pedido</label>
						<textarea class="form-control" id="observacao" rows="4" placeholder="Ex: entregar após as 18h, chamar no WhatsApp..."></textarea>
					</div>
				</div>
			</aside>

			<section class="checkout-payment">
				<div class="payment-card">
					<div class="payment-header">
						<div>
							<span>Pagamento</span>
							<h2>Escolha uma forma de pagamento</h2>
						</div>
						<div class="header-icon"><i class="fa fa-credit-card"></i></div>
					</div>

					<div class="payment-methods">
						@if(in_array('Pix', $tiposPagamento))
						<button type="button" class="payment-method select-pix select-pay" onclick="selectPay('pix')">
							<i class="fa fa-qrcode"></i>
							<span>PIX</span>
							<small>Aprovação rápida</small>
						</button>
						@endif

						@if(in_array('Boleto', $tiposPagamento))
						<button type="button" class="payment-method select-boleto select-pay" onclick="selectPay('boleto')">
							<i class="fa fa-barcode"></i>
							<span>Boleto</span>
							<small>Pagamento bancário</small>
						</button>
						@endif

						@if(in_array('Cartão de credito', $tiposPagamento))
						<button type="button" class="payment-method select-cartao select-pay" onclick="selectPay('cartao')">
							<i class="fa fa-credit-card"></i>
							<span>Cartão</span>
							<small>Crédito parcelado</small>
						</button>
						@endif

						@if(in_array('Depósito bancário', $tiposPagamento))
						<button type="button" class="payment-method select-deposito select-pay" onclick="selectPay('deposito')">
							<i class="fa fa-exchange"></i>
							<span>Depósito</span>
							<small>Transferência</small>
						</button>
						@endif
					</div>

					<div class="payment-content">

						<div class="payment-body body body-pix d-none">
							<div class="payment-body-title">
								<i class="fa fa-qrcode"></i>
								<div>
									<h3>Pagamento com PIX</h3>
									<p>Informe os dados do pagador para gerar o pagamento.</p>
								</div>
							</div>

							<form method="post" id="paymentFormPix" action="{{ route('loja.pagamento-pix', ['link='.$config->loja_id]) }}">
								@csrf
								<input type="hidden" name="observacao" class="observacao">

								<div class="form-grid">
									<div class="form-group-premium"><label>Nome</label><input required name="payerFirstName" data-checkout="payerFirstName" type="text" class="form-control" placeholder="Nome"></div>
									<div class="form-group-premium"><label>Sobrenome</label><input required name="payerLastName" data-checkout="payerLastName" type="text" class="form-control" placeholder="Sobrenome"></div>
									<div class="form-group-premium"><label>Email</label><input required name="payerEmail" data-checkout="payerEmail" id="payerEmail" type="email" class="form-control" placeholder="email@exemplo.com"></div>
									<div class="form-group-premium"><label>Tipo de documento</label><select required name="docType" id="docType" data-checkout="docType" class="form-control"></select></div>
									<div class="form-group-premium"><label>Número do documento</label><input required name="docNumber" data-checkout="docNumber" type="tel" class="form-control cpf_cnpj" placeholder="CPF ou CNPJ"></div>
								</div>

								<div class="payment-footer">
									<button id="btn-pix" class="btn-pay" type="submit"><i class="fa fa-qrcode"></i> Pagar com PIX</button>
								</div>
							</form>
						</div>

						<div class="payment-body body body-boleto d-none">
							<div class="payment-body-title">
								<i class="fa fa-barcode"></i>
								<div>
									<h3>Pagamento com boleto</h3>
									<p>Informe os dados do pagador para gerar o boleto.</p>
								</div>
							</div>

							<form method="post" id="paymentFormBoleto" action="{{ route('loja.pagamento-boleto', ['link='.$config->loja_id]) }}">
								@csrf
								<input type="hidden" name="observacao" class="observacao">

								<div class="form-grid">
									<div class="form-group-premium"><label>Nome</label><input required name="payerFirstName" data-checkout="payerFirstName" type="text" class="form-control" placeholder="Nome"></div>
									<div class="form-group-premium"><label>Sobrenome</label><input required name="payerLastName" data-checkout="payerLastName" type="text" class="form-control" placeholder="Sobrenome"></div>
									<div class="form-group-premium"><label>Email</label><input required name="payerEmail" data-checkout="payerEmail" id="payerEmail" type="email" class="form-control" placeholder="email@exemplo.com"></div>
									<div class="form-group-premium"><label>Tipo de documento</label><select required name="docType" id="docType2" data-checkout="docType" class="form-control"></select></div>
									<div class="form-group-premium"><label>Número do documento</label><input required name="docNumber" data-checkout="docNumber" type="tel" class="form-control cpf_cnpj" placeholder="CPF ou CNPJ"></div>
								</div>

								<div class="payment-footer">
									<button id="btn-boleto" class="btn-pay" type="submit"><i class="fa fa-barcode"></i> Pagar com boleto</button>
								</div>
							</form>
						</div>

						<div class="payment-body body body-cartao d-none">
							<div class="payment-body-title">
								<i class="fa fa-credit-card"></i>
								<div>
									<h3>Pagamento com cartão de crédito</h3>
									<p>Preencha os dados do cartão com segurança.</p>
								</div>
							</div>

							<form method="post" id="paymentFormCartao" action="{{ route('loja.pagamento-cartao', ['link='.$config->loja_id]) }}">
								@csrf
								<input type="hidden" name="observacao" class="observacao">

								<div class="form-grid">
									<div class="form-group-premium form-col-2"><label>Titular do cartão</label><input required id="cardholderName" data-checkout="cardholderName" type="text" class="form-control" placeholder="Nome impresso no cartão"></div>
									<div class="form-group-premium"><label>Tipo de documento</label><select required name="docType" id="docType3" data-checkout="docType" class="form-control"></select></div>
									<div class="form-group-premium"><label>Número do documento</label><input required name="docNumber" data-checkout="docNumber" type="tel" class="form-control cpf_cnpj cpf-cartao" placeholder="CPF ou CNPJ"></div>
									<div class="form-group-premium"><label>Email</label><input required name="email" data-checkout="email" id="email" type="email" class="form-control" placeholder="email@exemplo.com"></div>
									<div class="form-group-premium"><label>Número do cartão</label><div class="card-number-box"><input required data-checkout="cardNumber" id="cardNumber" type="tel" class="form-control" data-mask="0000000000000000" placeholder="0000 0000 0000 0000"><img id="band-img"></div></div>
									<div class="form-group-premium"><label>Parcelas</label><select required name="installments" data-checkout="installments" id="installments" type="tel" class="form-control"></select></div>
									<div class="form-group-premium"><label>Código de segurança</label><input required data-checkout="securityCode" id="securityCode" type="tel" class="form-control" placeholder="CVV"></div>
									<div class="form-group-premium"><label>Data de vencimento</label><div class="expiration-grid"><input required placeholder="MM" data-checkout="cardExpirationMonth" id="cardExpirationMonth" type="tel" class="form-control" data-mask="00"><input required placeholder="AA" data-checkout="cardExpirationYear" id="cardExpirationYear" type="tel" class="form-control" data-mask="00"></div></div>
								</div>

								<div class="hidden-fields">
									<select class="custom-select" id="issuer" name="issuer" data-checkout="issuer"></select>
									<input name="paymentMethodId" id="paymentMethodId"/>
									<input name="transactionAmount" id="transactionAmount" value="{{$carrinho->valor_total}}" />
								</div>

								<div class="payment-footer">
									<button id="btn-cartao" class="btn-pay" type="submit"><i class="fa fa-credit-card"></i> Pagar com cartão</button>
								</div>
							</form>
						</div>

						<div class="payment-body body body-deposito d-none">
							<div class="payment-body-title">
								<i class="fa fa-exchange"></i>
								<div>
									<h3>Depósito bancário / transferência</h3>
									<p>Informe seu documento e confira os dados para pagamento.</p>
								</div>
							</div>

							<form method="post" id="paymentFormDeposito" action="{{ route('loja.pagamento-deposito', ['link='.$config->loja_id]) }}">
								@csrf
								<input type="hidden" name="observacao" class="observacao">

								<div class="form-grid">
									<div class="form-group-premium"><label>CPF/CNPJ</label><input required name="cpf_cnpj" type="tel" class="form-control cpf_cnpj" placeholder="CPF ou CNPJ"></div>
								</div>

								<div class="deposit-box">
									{!! $config->dados_deposito !!}
								</div>

								<div class="payment-footer">
									<button id="btn-deposito" class="btn-pay" type="submit"><i class="fa fa-check-circle"></i> Confirmar depósito</button>
								</div>
							</form>
						</div>

					</div>
				</div>
			</section>

		</div>
	</div>
</div>
@endsection

@section('js')
<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script type="text/javascript">
	$(function(){
		$('.select-pay').first().trigger('click')
		window.Mercadopago.setPublishableKey('{{ $config->mercadopago_public_key }}');
		window.Mercadopago.getIdentificationTypes();

		setTimeout(() => {
			let s = $('#docType').html()
			$('#docType2').html(s)
			$('#docType3').html(s)
		}, 2000)
	})

	function selectPay(tipo){
		$('.select-pay').removeClass('select')
		$('.select-'+tipo).addClass('select')

		$('.body').addClass('d-none')
		$('.body-'+tipo).removeClass('d-none')
	}

	$('#cardNumber').keyup(() => {
		let cardnumber = $('#cardNumber').val().replaceAll(" ", "");
		if (cardnumber.length >= 6) {
			let bin = cardnumber.substring(0,6);

			window.Mercadopago.getPaymentMethod({
				"bin": bin
			}, setPaymentMethod);
		}
	})

	function setPaymentMethod(status, response) {
		if (status == 200) {
			let paymentMethod = response[0];
			document.getElementById('paymentMethodId').value = paymentMethod.id;

			$('#band-img').attr("src", paymentMethod.thumbnail);
			getIssuers(paymentMethod.id);
		} else {
			alert(`payment method info error: ${response}`);
		}
	}

	function getIssuers(paymentMethodId) {
		window.Mercadopago.getIssuers(
			paymentMethodId,
			setIssuers
		);
	}

	function setIssuers(status, response) {
		if (status == 200) {
			let issuerSelect = document.getElementById('issuer');
			$('#issuer').html('');
			response.forEach( issuer => {
				let opt = document.createElement('option');
				opt.text = issuer.name;
				opt.value = issuer.id;
				issuerSelect.appendChild(opt);
			});

			getInstallments(
				document.getElementById('paymentMethodId').value,
				document.getElementById('transactionAmount').value,
				issuerSelect.value
			);
		} else {
			alert(`issuers method info error: ${response}`);
		}
	}

	function getInstallments(paymentMethodId, transactionAmount, issuerId){
		window.Mercadopago.getInstallments({
			"payment_method_id": paymentMethodId,
			"amount": parseFloat(transactionAmount),
			"issuer_id": parseInt(issuerId)
		}, setInstallments);
	}

	function setInstallments(status, response){
		if (status == 200) {
			document.getElementById('installments').options.length = 0;
			response[0].payer_costs.forEach( payerCost => {
				let opt = document.createElement('option');
				opt.text = payerCost.recommended_message;
				opt.value = payerCost.installments;
				document.getElementById('installments').appendChild(opt);
			});
		} else {
			alert(`installments method info error: ${response}`);
		}
	}

	doSubmit = false;
	document.getElementById('paymentFormCartao').addEventListener('submit', getCardToken);
	function getCardToken(event){
		event.preventDefault();
		if(!doSubmit){
			let docNumber = $('.cpf-cartao').val().replace(/[^0-9]/g,'')
			$('.cpf-cartao').val(docNumber)
			setTimeout(() => {
				let $form = document.getElementById('paymentFormCartao');
				window.Mercadopago.createToken($form, setCardTokenAndPay);
				return false;
			}, 50)
		}
	};

	function setCardTokenAndPay(status, response) {

		if (status == 200 || status == 201) {
			let form = document.getElementById('paymentFormCartao');
			let card = document.createElement('input');
			card.setAttribute('name', 'token');
			card.setAttribute('type', 'hidden');
			card.setAttribute('value', response.id);
			form.appendChild(card);
			doSubmit=true;
			$('button').attr('disabled', true)
			form.submit();
		} else {
			alert("Verify filled data!\n"+JSON.stringify(response, null, 4));
		}
	};

	$('#observacao').on('input focusout', () => {
		$('.observacao').val($('#observacao').val())
	})
</script>
@endsection