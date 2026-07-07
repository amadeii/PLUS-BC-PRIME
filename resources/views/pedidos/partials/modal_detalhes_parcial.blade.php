<div class="modal fade pedido-modal" id="modal-detalhes-parciais" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header border-0 pb-0">
				<div>
					<h4 class="modal-title fw-bold mb-1">
						<i class="ri-bank-card-line text-warning me-1"></i>
						Finalizações parciais
					</h4>
					<small class="modal-subtitle">Pagamentos parciais já lançados nesta comanda</small>
				</div>

				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body pt-3">
				@php
					$totalComandaParcial = $item->total + $item->acrescimo;
					$totalPagoParcial = $item->finalizacoesParciais ? $item->finalizacoesParciais->sum('valor_pago') : 0;
					$saldoRestanteParcial = $totalComandaParcial - $totalPagoParcial;
				@endphp

				<div class="pedido-parcial-modal-resumo">
					<div>
						<small>Total da comanda</small>
						<strong>R$ {{ __moeda($totalComandaParcial) }}</strong>
					</div>

					<div>
						<small>Pago parcial</small>
						<strong>R$ {{ __moeda($totalPagoParcial) }}</strong>
					</div>

					<div>
						<small>Saldo restante</small>
						<strong>R$ {{ __moeda($saldoRestanteParcial) }}</strong>
					</div>

					<div>
						<small>Parciais</small>
						<strong>{{ $item->finalizacoesParciais ? $item->finalizacoesParciais->count() : 0 }}</strong>
					</div>
				</div>

				@if($item->finalizacoesParciais && $item->finalizacoesParciais->count() > 0)
				<div class="pedido-parciais-list mt-3">
					@foreach($item->finalizacoesParciais as $parcial)
					<div class="pedido-parcial-card">
						<div class="pedido-parcial-card-head">
							<div>
								<small>{{ $parcial->created_at ? $parcial->created_at->format('d/m/Y H:i') : '--' }}</small>
							</div>

							<span>R$ {{ __moeda($parcial->valor_pago) }}</span>
						</div>

						<div class="pedido-parcial-card-grid">
							<div>
								<small>Saldo antes</small>
								<strong>R$ {{ __moeda($parcial->saldo_antes) }}</strong>
							</div>

							<div>
								<small>Saldo depois</small>
								<strong>R$ {{ __moeda($parcial->saldo_depois) }}</strong>
							</div>

							<div>
								<small>Status</small>
								<strong>{{ ucfirst($parcial->status) }}</strong>
							</div>

							<div>
								<small>Venda</small>
								@if($parcial->nfce_id)
								<a href="{{ route('nfce.edit', $parcial->nfce_id) }}" target="_blank">#{{ $parcial->nfce_id }}</a>
								@else
								<strong>--</strong>
								@endif
							</div>
						</div>

						@if($parcial->cpf_nota)
						<div class="pedido-parcial-info-line">
							<small>CPF na nota:</small>
							<strong>{{ $parcial->cpf_nota }}</strong>
						</div>
						@endif

						@if($parcial->observacao)
						<div class="pedido-parcial-info-line">
							<small>Obs:</small>
							<strong>{{ $parcial->observacao }}</strong>
						</div>
						@endif

						@if($parcial->itens && $parcial->itens->count() > 0)
						<div class="pedido-parcial-itens">
							<small>Itens vinculados a esta parcial</small>

							@foreach($parcial->itens as $pItem)
							<div class="pedido-parcial-item-line">
								<div>
									<strong>{{ $pItem->itemPedido->produto->nome ?? 'Produto' }}</strong>
									<small>{{ __moeda($pItem->quantidade) }} x R$ {{ __moeda($pItem->valor_unitario) }}</small>
								</div>

								<span>R$ {{ __moeda($pItem->sub_total) }}</span>
							</div>
							@endforeach
						</div>
						@else
						<div class="pedido-parcial-info-line">
							<small>Itens:</small>
							<strong>Gerado proporcionalmente pelo valor informado</strong>
						</div>
						@endif
					</div>
					@endforeach
				</div>
				@else
				<div class="pedido-empty mt-3">
					<i class="ri-bank-card-line"></i>
					<strong>Nenhuma finalização parcial</strong>
					<small>Este pedido ainda não possui pagamentos parciais.</small>
				</div>
				@endif
			</div>
		</div>
	</div>
</div>