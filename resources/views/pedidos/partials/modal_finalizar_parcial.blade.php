<div class="modal fade pedido-modal" id="modal-finalizar-parcial" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<form id="form-finalizar-parcial" method="post" action="{{ route('pedidos-cardapio.finalizar-parcial', $item->id) }}">
			@csrf
			<input type="hidden" name="pedido_id" value="{{ $item->id }}">
			<input type="hidden" name="pagamentos" id="pagamentos-parciais-json">
			<input type="hidden" id="pedido-total-parcial" value="{{ $item->total + $item->acrescimo }}">

			<div class="modal-content">
				<div class="modal-header border-0 pb-0">
					<div>
						<h4 class="modal-title fw-bold mb-1">
							<i class="ri-bank-card-line text-warning me-1"></i>
							Finalização parcial
						</h4>
						<small class="modal-subtitle">Finalize por valor livre ou selecionando itens da comanda</small>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>

				<div class="modal-body pt-3">
					<div class="row g-3">

						<div class="col-12">
							@php
							$totalComandaParcial = $item->total + $item->acrescimo;
							$totalPagoParcial = $item->finalizacoesParciais ? $item->finalizacoesParciais->sum('valor_pago') : 0;
							$saldoRestanteParcial = $totalComandaParcial - $totalPagoParcial;
							@endphp

							<div class="finalizar-parcial-total">
								<div>
									<small>Total da comanda</small>
									<strong>R$ {{ __moeda($totalComandaParcial) }}</strong>
								</div>

								<div>
									<small>Já pago parcial</small>
									<strong>R$ {{ __moeda($totalPagoParcial) }}</strong>
								</div>

								<div>
									<small>Saldo restante</small>
									<strong>R$ {{ __moeda($saldoRestanteParcial) }}</strong>
								</div>
							</div>
						</div>

						<div class="col-12">
							<label class="form-label finalizar-label-modal">Como deseja finalizar?</label>
							<div class="tipo-parcial-grid">
								<label class="tipo-parcial-card">
									<input type="radio" name="tipo_parcial" value="valor">
									<div>
										<strong>Por valor</strong>
										<small>Ex: cliente irá pagar R$ 100,00</small>
									</div>
								</label>

								<label class="tipo-parcial-card">
									<input type="radio" name="tipo_parcial" value="itens">
									<div>
										<strong>Por itens</strong>
										<small>Seleciona os produtos pagos</small>
									</div>
								</label>
							</div>
						</div>

						<div class="col-12 d-none" id="area-itens-parcial">
							<label class="form-label finalizar-label-modal">Itens da comanda</label>

							<div class="lista-itens-parcial">
								@foreach($item->itens as $pedidoItem)
								@php
								$totalItem = $pedidoItem->sub_total ?? (($pedidoItem->quantidade ?? 0) * ($pedidoItem->valor_unitario ?? 0));
								@endphp

								<label class="item-parcial-card @if($pedidoItem->finalizado_pdv) item-parcial-card-disabled @endif">
									<input type="checkbox"
									class="check-item-parcial"
									name="itens_parciais[]"
									value="{{ $pedidoItem->id }}"
									data-total="{{ $totalItem }}"
									@if($pedidoItem->finalizado_pdv) disabled @endif>

									<div class="item-parcial-info">
										<strong>{{ $pedidoItem->produto->nome ?? $pedidoItem->nome ?? 'Produto' }}</strong>
										<small>{{ __moeda($pedidoItem->quantidade ?? 1) }} x R$ {{ __moeda($pedidoItem->valor_unitario ?? 0) }}</small>

										@if($pedidoItem->finalizado_pdv)
										<span class="item-parcial-pago">Já finalizado parcial</span>
										@endif
									</div>

									<span>R$ {{ __moeda($totalItem) }}</span>
								</label>
								@endforeach
							</div>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label finalizar-label-modal">Valor parcial</label>
							<input type="tel" name="valor_parcial" id="inp-valor-parcial" class="form-control moeda" placeholder="0,00" required>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label finalizar-label-modal">CPF na nota</label>
							<input type="tel" name="cpf_nota" id="inp-cpf-nota-parcial" class="form-control cpf" placeholder="CPF opcional">
						</div>

						<div class="col-12">
							<div class="alert alert-warning py-2 px-3 d-none" id="alerta-finalizacao-parcial"></div>
						</div>

						<div class="col-12">
							<div id="area-pagamentos-parcial" class="area-pagamentos-bloqueada">
								<div class="row g-3">
									<div class="col-12">
										<h6 class="fw-bold mb-0 finalizar-title-modal">Formas de pagamento</h6>
										<small class="modal-subtitle">Pode adicionar mais de uma forma de pagamento</small>
									</div>

									<div class="col-12 col-md-5">
										<label class="form-label finalizar-label-modal">Tipo de pagamento</label>
										<select id="inp-tipo-pagamento-parcial" class="form-select">
											<option value="">Selecione</option>
											@foreach($tiposPagamento as $key => $tp)
											<option value="{{ $key }}">{{ $tp }}</option>
											@endforeach
										</select>
									</div>

									<div class="col-12 col-md-4">
										<label class="form-label finalizar-label-modal">Valor</label>
										<input type="tel" id="inp-valor-pagamento-parcial" class="form-control moeda" placeholder="0,00">
									</div>

									<div class="col-12 col-md-3 d-flex align-items-end">
										<button type="button" class="btn btn-dark w-100" id="btn-add-pagamento-parcial">
											<i class="ri-add-line"></i>
											Adicionar
										</button>
									</div>

									<div class="col-12">
										<div id="lista-pagamentos-parciais"></div>
									</div>

									<div class="col-12">
										<div class="finalizar-parcial-resumo">
											<div>
												<small>Total parcial</small>
												<strong id="resumo-total-parcial">R$ 0,00</strong>
											</div>
											<div>
												<small>Total pago</small>
												<strong id="resumo-total-pago">R$ 0,00</strong>
											</div>
											<div>
												<small>Falta</small>
												<strong id="resumo-total-falta">R$ 0,00</strong>
											</div>
										</div>
									</div>

									<div class="col-12">
										<label class="form-label finalizar-label-modal">Observação</label>
										<textarea name="observacao" rows="3" class="form-control" placeholder="Observação da finalização parcial"></textarea>
									</div>
								</div>

								<div class="bloqueio-pagamento-msg">Informe o valor parcial primeiro</div>
							</div>
						</div>

					</div>
				</div>

				<div class="modal-footer border-0 pt-0">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-warning text-white fw-semibold" id="btn-confirmar-finalizacao-parcial">
						<i class="ri-check-line"></i>
						Confirmar finalização parcial
					</button>
				</div>
			</div>
		</form>
	</div>
</div>