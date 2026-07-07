<div class="row">
	@foreach($somaTiposPagamento as $key => $valor)
	@if($valor > 0)
	<div class="row mb-3">
		<div class="card shadow-sm border-0" style="border-radius: 16px;">
			
			<div class="card-body">

				<div class="row align-items-center mb-2">
					<div class="col-12 col-md-6">
						<h5 class="text-success mb-0" style="font-weight: 600;">
							{{ App\Models\Nfce::getTipoPagamento($key) }}
						</h5>
					</div>

					<div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
						<h5 class="mb-0">
							<strong class="text-dark" style="font-weight: 700;">
								R$ {{ __moeda($valor) }}
							</strong>
						</h5>
					</div>
				</div>

				<div class="row line-row border-top pt-3">
					<div class="col-12 appends">

						<div class="dynamic-form row mt-2 g-2 align-items-end">
							<input type="hidden" value="{{ $key }}" name="tipo_pagamento[]">
							
							<div class="col-7 col-md-3">
								<label class="form-label" style="font-size: 12px; font-weight: 600; color: #6c757d;">
									Conta
								</label>
								<select required name="conta_empresa_id[]" class="form-select select2">
									<option value=""></option>
									@foreach($contasEmpresa as $c)
									<option value="{{ $c->id }}">
										{{ $c->nome }}
									</option>
									@endforeach
								</select>
							</div>

							<div class="col-5 col-md-2">
								<label class="form-label" style="font-size: 12px; font-weight: 600; color: #6c757d;">
									Valor
								</label>
								<input required type="tel" class="form-control moeda valor_linha" name="valor[]" placeholder="0,00">
							</div>

							<div class="col-12 col-md-7">
								<label class="form-label" style="font-size: 12px; font-weight: 600; color: #6c757d;">
									Descrição
								</label>
								<input type="text" class="form-control ignore descricao" name="descricao[]" placeholder="Opcional">
							</div>

						</div>

					</div>
				</div>

				<div class="row mt-4 pt-3 border-top align-items-center">
					<div class="col-6 col-md-6">
						<button type="button" class="btn btn-dark btn-clone-caixa" style="border-radius: 10px;">
							<i class="ri-add-line me-1"></i> Adicionar linha
						</button>
					</div>

					<div class="col-6 col-md-6 text-end">
						<input type="hidden" class="valor_total" value="{{ $valor }}">
						<h5 class="mb-0" style="font-size: 14px;">
							<span class="text-muted">Valor restante</span><br>
							<strong class="total-restante text-danger" style="font-size: 20px;">R$ 0,00</strong>
						</h5>
					</div>
				</div>

			</div>
		</div>
	</div>
	@endif
	@endforeach

	<div class="col-md-9"></div>

	<div class="col-md-3">
		<button disabled class="btn btn-success w-100 btn-store" style="border-radius: 12px; height: 48px; font-weight: 700;">
			<i class="ri-check-line me-1"></i>
			Salvar
		</button>
	</div>
</div>