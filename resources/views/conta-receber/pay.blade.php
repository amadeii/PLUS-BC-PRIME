@extends('layouts.app', ['title' => 'Receber Conta'])

@section('content')
<div class="page-content mt-1">

	<div class="card shadow-sm border-0 rounded-4">
		<div class="card-body p-4">

			<!-- HEADER -->
			<div class="d-flex justify-content-between align-items-center mb-4">
				<div>
					<h4 class="mb-1 text-primary fw-bold">
						<i class="ri-money-dollar-circle-line me-2"></i>
						Receber Conta
					</h4>
					<small class="text-muted">Confirme o recebimento do lançamento financeiro</small>
				</div>

				<a href="{{ url()->previous() }}" type="button" class="btn btn-danger btn-sm">
					<i class="ri-arrow-left-double-fill"></i>Voltar
				</a>
			</div>

			<hr class="mb-4">

			{!! Form::open()
			->put()
			->route('conta-receber.pay-put', [$item->id])
			!!}


			<div class="row mb-4 g-3">

				<div class="col-md">
					<div class="p-3 rounded-4 bg-light border h-100">
						<small class="text-muted d-block">Data de cadastro</small>
						<h6 class="mb-0 fw-semibold">
							{{ __data_pt($item->created_at) }}
						</h6>
					</div>
				</div>

				<div class="col-md">
					<div class="p-3 rounded-4 bg-light border h-100">
						<small class="text-muted d-block">Data de vencimento</small>
						<h6 class="mb-0 fw-semibold">
							{{ __data_pt($item->data_vencimento, false) }}
						</h6>
					</div>
				</div>

				<div class="col-md">
					<div class="p-3 rounded-4 bg-light border h-100">
						<small class="text-muted d-block">Referência</small>
						<h6 class="mb-0 fw-semibold">
							{{ $item->referencia ?? '--' }}
						</h6>
					</div>
				</div>

				<div class="col-md">
					<div class="p-3 rounded-4 bg-primary border h-100">
						<small class="text-white d-block">Valor integral</small>
						<h6 class="mb-0 fw-semibold text-white">
							R$ {{ __moeda($item->valor_integral) }}
						</h6>
					</div>
				</div>

				<div class="col-md">
					<div class="p-3 rounded-4 bg-success border h-100">
						<small class="text-white d-block">Total com juros/multa</small>
						<h6 class="mb-0 fw-semibold text-white">
							R$ {{ __moeda($valorReceber) }}
						</h6>
					</div>
				</div>

			</div>

			<input type="hidden" name="criar_saldo_devedor" id="criar_saldo_devedor" value="0">
			<input type="hidden" name="data_saldo_devedor" id="data_saldo_devedor">
			<input type="hidden" name="valor_saldo_devedor" id="valor_saldo_devedor">
			<input type="hidden" id="valor_base_receber" value="{{ __moeda($item->valor_integral) }}">

			<!-- FORMULÁRIO DE PAGAMENTO -->
			<div class="bg-white p-4 rounded-4 border">
				@include('conta-receber._forms_pay')
			</div>

			{!! Form::close() !!}

		</div>
	</div>

</div>
@endsection

@section('js')
<script type="text/javascript" src="/js/controla_conta_empresa.js"></script>
<script>
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

		let valorBase = moedaToFloat($('#valor_base_receber').val());
		let valorRecebido = moedaToFloat($('input[name="valor_pago"]').val());

		let saldoDevedor = valorBase - valorRecebido;

		if(saldoDevedor <= 0){
			return true;
		}

		e.preventDefault();

		swal({
			title: "Existe saldo devedor",
			text: "O valor recebido é menor que o valor da conta. Deseja criar uma nova conta com o saldo devedor?",
			icon: "warning",
			buttons: ["Não criar", "Criar nova conta"],
			dangerMode: false,
		}).then((confirmou) => {
			if(confirmou){

				$('#criar_saldo_devedor').val(1);
				$('#valor_saldo_devedor').val(saldoDevedor.toFixed(2));

				swal({
					title: "Criar saldo devedor",
					text: `O sistema irá gerar uma nova conta no valor de ${saldoDevedor.toLocaleString('pt-BR', {
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
