<div class="ap-list-item">

	<div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
		<strong>
			{{ $ap->funcionario->nome ?? 'Sem funcionário' }}
		</strong>

		<small class="text-muted">
			{{ __data_pt($ap->created_at) }}
		</small>
	</div>

	<div class="row g-2">

		<div class="col-6">
			<small class="text-muted d-block">Produzido</small>

			<strong class="text-success">
				{{ number_format($ap->quantidade_produzida, 3, ',', '.') }}
			</strong>
		</div>

		@if($ap->quantidade_refugada > 0)

		<div class="col-12 mt-2">

			<div class="border border-danger rounded p-3 bg-danger-subtle">

				<div class="d-flex align-items-start gap-3">

					<div class="rounded-circle bg-danger d-flex align-items-center justify-content-center"
					style="width:42px; height:42px; min-width:42px;">

					<i class="ri-close-circle-line text-white"></i>
				</div>

				<div class="w-100">

					<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">

						<div>
							<h6 class="mb-1 text-danger">
								Refugo registrado
							</h6>

							<div class="text-muted small">
								Quantidade refugada durante o processo produtivo
							</div>
						</div>

						<div class="text-end">
							<div class="small text-muted">
								Quantidade
							</div>

							<div class="fw-bold text-danger fs-5">
								{{ number_format($ap->quantidade_refugada, 3, ',', '.') }}
							</div>
						</div>

					</div>

					@if($ap->motivoRefugo)

					<hr class="my-2">

					<div>
						<div class="small text-muted mb-1">
							Motivo do refugo
						</div>

						<div class="fw-semibold text-dark">
							{{ $ap->motivoRefugo->nome }}
						</div>
					</div>

					@endif

				</div>

			</div>

		</div>

	</div>

	@endif

	<div class="col-6">
		<small class="text-muted d-block">Tempo</small>

		<strong>
			{{ $ap->tempo_real_minutos ?? 0 }} min
		</strong>
	</div>

	<div class="col-6">
		<small class="text-muted d-block">Status</small>

		@if($ap->status == 'finalizado')
		<span class="badge bg-success">Finalizado</span>
		@else
		<span class="badge bg-warning text-dark">Aberto</span>
		@endif
	</div>

</div>

@if($ap->observacao)
<div class="alert alert-warning mb-0 mt-3 py-2">
	<i class="ri-information-line"></i>

	<span class="ms-1">
		{{ $ap->observacao }}
	</span>
</div>
@endif

</div>