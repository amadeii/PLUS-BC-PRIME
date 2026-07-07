@extends('layouts.app', ['title' => 'Receber Contas'])

@section('content')
<div class="page-content mt-1">

	<div class="card shadow-sm border-0 rounded-4">
		<div class="card-body p-4">

			<!-- HEADER -->
			<div class="d-flex justify-content-between align-items-center mb-4">
				<div>
					<h4 class="mb-1 text-primary fw-bold">
						<i class="ri-money-dollar-circle-line me-2"></i>
						Receber Contas
					</h4>
					<small class="text-muted">
						Confirme os recebimentos abaixo
					</small>
				</div>

				<a href="{{ route('conta-pagar.index')}}" type="button" class="btn btn-danger btn-sm">
					<i class="ri-arrow-left-double-fill"></i>Voltar
				</a>
			</div>

			<hr class="mb-4">

			{!!Form::open()
			->put()
			->route('conta-receber.receive-select')
			!!}

			@isset($redirectPdv)
			<input type="hidden" value="1" name="redirect_pdv">
			@endif

			<input type="hidden" name="criar_saldo_devedor" id="criar_saldo_devedor" value="0">
			<input type="hidden" name="data_saldo_devedor" id="data_saldo_devedor">
			<input type="hidden" name="valor_saldo_devedor" id="valor_saldo_devedor">

			@foreach($data as $key => $item)

			<!-- BLOCO DA CONTA -->
			<div class="mb-4 p-4 bg-light rounded-4 border shadow-sm">

				<div class="row align-items-center mb-3">

					<div class="col-md-6">
						<small class="text-muted d-block">Data de Cadastro</small>
						<strong>{{ __data_pt($item->created_at) }}</strong>

						<br>

						<small class="text-muted d-block mt-2">Categoria</small>
						<strong>{{ $item->categoria->nome ?? '--' }}</strong>
					</div>

					<div class="col-md-6 text-md-end">
						<small class="text-muted d-block">Vencimento</small>
						<strong>{{ __data_pt($item->data_vencimento, false) }}</strong>

						<br>

						<small class="text-muted d-block mt-2">Referência</small>
						<strong>{{ $item->referencia ?? '--' }}</strong>
					</div>

				</div>

				<!-- VALOR EM DESTAQUE -->

				
				<div class="row">
					<div class="col-2 p-3 rounded-3 text-white mb-2" style="background: linear-gradient(135deg, #5B5BD6, #7C4DFF);">
						<div class="d-flex justify-content-between align-items-center">
							<span class="">Valor Integral</span>
							<h5 class="mb-0 fw-bold">
								R$ {{ __moeda($item->valor_integral) }}
							</h5>
						</div>
					</div>

					<div class="col-2 p-3 rounded-3 text-white mb-2" style="background: linear-gradient(135deg, #16A34A, #22C55E); margin-left: 5px;">
						<div class="d-flex justify-content-between align-items-center">
							<span class="">Valor a Receber</span>
							<h5 class="mb-0 fw-bold">
								R$ {{ __moeda($item->valor_receber) }}
							</h5>
						</div>
					</div>
				</div>

				@include('conta-receber._forms_pay_select')

			</div>

			@endforeach

			<!-- BOTÃO FINAL -->
			<div class="text-end mt-4">
				<button type="submit" class="btn btn-success text-white fw-bold">

					<i class="ri-check-double-line me-2"></i>
					Confirmar Recebimentos
				</button>
			</div>

			{!!Form::close()!!}

		</div>
	</div>
</div>
@endsection


@section('js')
<script type="text/javascript" src="/js/controla_conta_empresa.js"></script>
<script type="text/javascript">
	$(function(){
		$('#valor_saldo_devedor').val('');
	})
	setTimeout(() => {
		$(".conta_empresa").each(function (e, v) {
			$(this).select2({
				minimumInputLength: 2,
				language: "pt-BR",
				placeholder: "Digite para buscar a conta",
				width: "100%",
				ajax: {
					cache: true,
					url: path_url + "api/contas-empresa",
					dataType: "json",
					data: function (params) {
						console.clear();
						let empresa_id = $('#empresa_id').val()
						var query = {
							pesquisa: params.term,
							empresa_id: empresa_id
						};
						return query;
					},
					processResults: function (response) {
						var results = [];

						$.each(response, function (i, v) {
							var o = {};
							o.id = v.id;

							o.text = v.nome;
							o.value = v.id;
							results.push(o);
						});
						return {
							results: results,
						};
					},
				},
			});
		});
	}, 100);

	function moedaToFloat(valor){
		if(!valor) return 0;
		return parseFloat(
			valor.toString()
			.replace('R$', '')
			.replace(/\./g, '')
			.replace(',', '.')
			.trim()
			) || 0;
	}

	$('form').on('submit', function(e){
		let form = this;
		let temSaldo = false;
		let totalSaldo = 0;
		let valorTotal = 0;

		$('.valor-recebido').each(function(){
			let valorOriginal = parseFloat($(this).data('valor-original')) || 0;
			let valorRecebido = moedaToFloat($(this).val());

			if(valorRecebido < valorOriginal){
				temSaldo = true;
				totalSaldo += (valorOriginal - valorRecebido);
				valorTotal += (valorOriginal - valorRecebido);
			}
		});

		if(!temSaldo){
			return true;
		}

		e.preventDefault();

		swal({
			title: "Existe saldo devedor",
			text: "Alguma conta foi recebida com valor menor. Deseja criar uma nova conta com o saldo devedor?",
			icon: "warning",
			buttons: ["Não criar", "Criar nova conta"],
			dangerMode: false,
		}).then((confirmou) => {
			if(confirmou){
				$('#criar_saldo_devedor').val(1);
				$('#valor_saldo_devedor').val(valorTotal);

				swal({
					title: "Criar saldo devedor",
					text: `O sistema irá gerar uma nova conta no valor de ${valorTotal.toLocaleString('pt-BR', {
						style: 'currency',
						currency: 'BRL'
					})}. Informe a data de vencimento da nova parcela.`,
					icon: "info",
					content: {
						element: "input",
						attributes: {
							type: "date"
						}
					},
					buttons: ["Cancelar", "Criar conta"],
				}).then((data) => {

					if(!data){
						swal(
							"Data obrigatória",
							"Informe a data de vencimento para criar o saldo devedor.",
							"warning"
							);
						return;
					}
					$('#data_saldo_devedor').val(data);

					form.submit();
				});
			}else{
				$('#criar_saldo_devedor').val(0);
				$('#data_saldo_devedor').val('');
				$('#valor_saldo_devedor').val('');
				form.submit();
			}
		});
	});
</script>
@endsection