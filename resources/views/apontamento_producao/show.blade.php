@extends('layouts.app', ['title' => 'Apontamento da OP'])

@section('css')
<style>
	.op-info{ background:#f8f9fb; border:1px solid #eef0f4; border-radius:12px; padding:14px; height:100%; }
	.op-info span{ display:block; font-size:12px; color:#6c757d; margin-bottom:4px; }
	.op-info strong{ font-size:16px; color:#212529; }
	.op-section-title{ font-size:17px; font-weight:700; color:#212529; margin-bottom:2px; }
	.op-section-sub{ font-size:12px; color:#6c757d; }
	.operacao-box{ border:1px solid #eef0f4; border-radius:14px; background:#fff; overflow:hidden; margin-bottom:18px; }
	.operacao-head{ background:#fafbfd; border-bottom:1px solid #eef0f4; padding:16px; }
	.operacao-body{ padding:16px; }
	.ap-list-item{ border:1px solid #eef0f4; border-radius:12px; padding:12px; background:#fff; margin-bottom:10px; }
	.ap-form{ border:1px solid #eef0f4; border-radius:12px; background:#fafbfd; padding:16px; }
	.ap-form label{ font-size:12px; color:#6c757d; margin-bottom:4px; font-weight:600; }
	.ap-empty{ border:1px dashed #dfe3ea; border-radius:12px; padding:24px; text-align:center; color:#6c757d; background:#fafbfd; }
	.badge-soft-op{ background:#f1f4ff; color:#0d6efd; border-radius:30px; padding:7px 12px; font-size:12px; font-weight:700; }
</style>
@endsection

@section('content')

@php
$produtoNome = optional(optional($item->itens->first())->produto)->nome ?? '-';
$clienteNome = optional(optional($item->itens->first())->cliente)->razao_social ?? optional(optional($item->itens->first())->cliente)->nome_fantasia ?? 'Não informado';

$planejado = (float) $item->itens->sum('quantidade');
$produzido = (float) ($item->quantidade_produzida ?? 0);
$refugo = (float) ($item->quantidade_refugada ?? 0);

$operacoes = $item->operacoes ? $item->operacoes->sortBy('sequencia') : collect();
$apontamentosGerais = $item->apontamentos ?? collect();
@endphp

<div class="page-content">
	<div class="card border-top border-0 mt-1">
		<div class="card-body p-4 p-lg-5">

			<div class="d-flex align-items-start align-items-lg-center justify-content-between gap-3 flex-wrap">
				<div>
					<h4 class="mb-1 text-primary">Apontamento da OP #{{ $item->codigo_sequencial }}</h4>
					<div class="text-muted small">
						<span class="me-3">Registro operacional da ordem de produção</span>
						@if($item->created_at)
						<span>Cadastro: <strong class="text-dark">{{ __data_pt($item->created_at) }}</strong></span>
						@endif
					</div>
				</div>

				<div class="d-flex gap-2">
					<a href="{{ route('apontamento-producao.index') }}" class="btn btn-danger btn-sm px-3">
						<i class="ri-arrow-left-double-fill"></i> Voltar
					</a>
				</div>
			</div>

			<hr class="my-4">

			<div class="row g-3 mb-4">
				<div class="col-12 col-lg-4">
					<div class="op-info">
						<span>Produto</span>
						<strong>{{ $produtoNome }}</strong>
					</div>
				</div>

				<div class="col-12 col-lg-4">
					<div class="op-info">
						<span>Cliente</span>
						<strong>{{ $clienteNome }}</strong>
					</div>
				</div>

				<div class="col-6 col-lg-2">
					<div class="op-info">
						<span>Planejado</span>
						<strong>{{ number_format($planejado, 3, ',', '.') }}</strong>
					</div>
				</div>

				<div class="col-6 col-lg-2">
					<div class="op-info">
						<span>Produzido</span>
						<strong class="text-success">{{ number_format($produzido, 3, ',', '.') }}</strong>
					</div>
				</div>

				<div class="col-6 col-lg-2">
					<div class="op-info">
						<span>Refugo</span>
						<strong class="text-danger">{{ number_format($refugo, 3, ',', '.') }}</strong>
					</div>
				</div>
			</div>

			<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
				<div>
					<h5 class="mb-0">Apontamentos de produção</h5>
					<div class="text-muted small">
						@if($operacoes->count() > 0)
						Informe os apontamentos por etapa produtiva
						@else
						Esta OP não possui roteiro/operações, faça o apontamento geral
						@endif
					</div>
				</div>

				<span class="badge-soft-op">
					@if($operacoes->count() > 0)
					{{ $operacoes->count() }} operações
					@else
					Sem roteiro
					@endif
				</span>
			</div>

			@if($operacoes->count() > 0)


			@foreach($operacoes as $operacao)

			@php
			$apontamentos = $operacao->apontamentos ?? collect();
			@endphp

			<div class="operacao-box">
				<div class="operacao-head d-flex align-items-start align-items-lg-center justify-content-between gap-3 flex-wrap">
					<div>
						<div class="op-section-title">{{ $operacao->sequencia }} - {{ $operacao->nome_operacao }}</div>
						<div class="op-section-sub">Setor: <strong>{{ $operacao->nome_setor ?? '-' }}</strong></div>
					</div>

					<div class="d-flex gap-2 flex-wrap">
						<span class="badge bg-primary">Previsto: {{ $operacao->tempo_previsto_minutos ?? 0 }} min</span>
						<span class="badge bg-dark">Real: {{ $operacao->tempo_real_minutos ?? 0 }} min</span>

						<span class="badge bg-success">
							Eficiência: {{ number_format($operacao->eficiencia ?? 0, 1, ',', '.') }}%
						</span>

						@if($operacao->status == 'finalizada')
						<span class="badge bg-success">Finalizada</span>
						@elseif($operacao->status == 'parcial')
						<span class="badge bg-warning text-dark">Parcial</span>
						@else
						<span class="badge bg-secondary">Pendente</span>
						@endif

						@if($operacao->status != 'finalizada')
						<a href="javascript:void(0)" class="btn btn-success btn-sm btn-finalizar-operacao" data-url="{{ route('apontamento-producao.finalizar-operacao', $operacao->id) }}">
							<i class="ri-checkbox-circle-line"></i> Finalizar Operação
						</a>
						@endif
					</div>
				</div>

				<div class="operacao-body">
					<div class="row g-4">
						<div class="col-12 col-lg-5">
							<h6 class="mb-2">Apontamentos realizados</h6>

							@forelse($apontamentos as $ap)
							@include('apontamento_producao.partials.card_apontamento', ['ap' => $ap])
							@empty
							<div class="ap-empty">
								<i class="ri-timer-line fs-3 d-block mb-2"></i>
								Nenhum apontamento realizado nesta operação.
							</div>
							@endforelse
						</div>

						<div class="col-12 col-lg-7">
							<h6 class="mb-2">Novo apontamento</h6>

							<form method="post" action="{{ route('apontamento-producao.store') }}" class="ap-form">
								@csrf

								<input type="hidden" name="ordem_producao_id" value="{{ $item->id }}">
								<input type="hidden" name="ordem_producao_operacao_id" value="{{ $operacao->id }}">

								@include('apontamento_producao.partials.form_apontamento')
							</form>
						</div>
					</div>
				</div>
			</div>

			@endforeach

			@else

			<div class="operacao-box">
				<div class="operacao-head d-flex align-items-start align-items-lg-center justify-content-between gap-3 flex-wrap">
					<div>
						<div class="op-section-title">Apontamento geral da OP</div>
						<div class="op-section-sub">Use este formulário quando a ordem não possuir roteiro ou operações cadastradas</div>
					</div>

					<span class="badge bg-warning text-dark">Sem operação vinculada</span>
				</div>

				<div class="operacao-body">
					<div class="row g-4">
						<div class="col-12 col-lg-5">
							<h6 class="mb-2">Apontamentos realizados</h6>

							@forelse($apontamentosGerais as $ap)
							@include('apontamento_producao.partials.card_apontamento', ['ap' => $ap])
							@empty
							<div class="ap-empty">
								<i class="ri-timer-line fs-3 d-block mb-2"></i>
								Nenhum apontamento realizado nesta OP.
							</div>
							@endforelse
						</div>

						<div class="col-12 col-lg-7">
							<h6 class="mb-2">Novo apontamento geral</h6>

							<form method="post" action="{{ route('apontamento-producao.store') }}" class="ap-form">
								@csrf

								<input type="hidden" name="ordem_producao_id" value="{{ $item->id }}">
								<input type="hidden" name="ordem_producao_operacao_id" value="">

								@include('apontamento_producao.partials.form_apontamento')
							</form>
						</div>
					</div>
				</div>
			</div>

			@endif

		</div>
	</div>
</div>

@endsection

@section('js')
<script>

	$(function(){

		$(document).on('click', '.btn-finalizar-operacao', function(){

			let url = $(this).data('url');

			swal({
				title: "Finalizar operação?",
				text: "Após finalizar, a operação será bloqueada para novos apontamentos.",
				icon: "warning",
				buttons: {
					cancel: {
						text: "Cancelar",
						visible: true,
						className: "btn btn-danger"
					},
					confirm: {
						text: "Sim, finalizar",
						className: "btn btn-success"
					}
				}
			}).then((confirmar) => {

				if(confirmar){
					window.location.href = url;
				}

			});

		});

	});

</script>
@endsection