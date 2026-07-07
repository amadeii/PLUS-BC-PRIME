<div class="row align-items-end">
	<div class="col-md-12 mb-2">
		<div style="display: flex;align-items: center;justify-content: space-between;background: #f3e8ff;border: 1px solid #e9d5ff;padding: 10px 14px;border-radius: 10px;">
			<h5 style="margin: 0; font-weight: 700; color: #4c1d95;">
				{{ $itemPedido->produto->nome }}
			</h5>

			<span style="background: #6A1B9A;color: #fff;font-size: 11px;padding: 4px 10px;border-radius: 20px;">
				Item
			</span>
		</div>
	</div>
	<div class="col-md-3">
		<div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #e5e7eb;">
			{!!Form::tel('valor_unitario_item', 'Valor unitário')
			->attrs(['class' => 'moeda'])
			->value(__moeda($itemPedido->valor_unitario))
			!!}
		</div>
	</div>

	<div class="col-md-9">
		<div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #e5e7eb;">
			{!!Form::text('observacao_item', 'Observação')
			->value($itemPedido->observacao)
			!!}
		</div>
	</div>

	<input type="hidden" id="valor_original" value="{{ $produto->valor_unitario }}">
</div>
@if(sizeof($produto->adicionaisAtivos) > 0)
<hr style="border-top: 1px solid #e5e7eb; margin: 18px 0;">

<div class="row">
	<div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden; background: linear-gradient(180deg, #ffffff 0%, #fcfcfd 100%);">
		<div class="card-body" style="padding: 16px;">
			<div class="d-flex align-items-center justify-content-between mb-2">
				<h5 class="mb-0" style="font-weight: 700; color: #111827; letter-spacing: .3px;">
					ADICIONAIS
				</h5>
				<span class="badge badge-light" style="font-size: 11px; padding: 6px 10px; border-radius: 999px; background: #eef2ff; color: #4338ca;">
					Personalize
				</span>
			</div>

			@foreach($categoriasAdicional as $c)
			<div class="row" style="margin-bottom: 14px;">
				<div class="col-12 mb-1">
					<h5 style="font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 0;">
						{{ $c->nome }}
					</h5>
					<div style="width: 40px; height: 2px; border-radius: 10px; background: #8b5cf6; margin-top: 4px;"></div>
				</div>

				@foreach($c->adicionais as $a)
				@if(in_array($a->id, $produto->adicionaisAtivos->pluck(['adicional_id'])->toArray()))
				<div class="col-md-3 col-sm-6 mb-2">
					<label class="w-100 h-100" style="margin: 0; cursor: pointer;">
						<div style="
						border: 1px solid #e5e7eb;
						border-radius: 10px;
						padding: 10px;
						background: #fff;
						box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
						transition: .15s ease;
						min-height: 70px;
						">
						<div class="d-flex align-items-start">
							<input style="margin-left: 6px; margin-top: 3px;" type="checkbox" class="checkbox_adicional" adicional-id="{{ $a->id }}" adicional-valor="{{ $a->valor }}" @if(in_array($a->id, $itemPedido->adicionais->pluck(['adicional_id'])->toArray())) checked @endif>

							<div style="margin-left: 10px; line-height: 1.25;">
								<div style="font-weight: 600; color: #111827; font-size: 13px;">
									{{ $a->nome }}
								</div>

								<div style="margin-top: 3px; font-size: 12px; font-weight: 600; color: #6A1B9A;">
									@if($a->valor > 0)
									R$ {{ __moeda($a->valor) }}
									@else
									Grátis
									@endif
								</div>
							</div>
						</div>
					</div>
				</label>
			</div>
			@endif
			@endforeach
		</div>
		@endforeach
	</div>
</div>
</div>
@endif

@if($produto->categoria && $produto->categoria->tipo_pizza == 1)

<div class="row mt-2">
	<div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
		<div class="card-body" style="padding: 14px;">
			
			<div class="d-flex align-items-center justify-content-between mb-2">
				<h5 class="mb-0" style="font-weight: 700; color: #111827; font-size: 15px;">
					TAMANHO / SABORES
				</h5>
				<span style="font-size: 11px; padding: 5px 9px; border-radius: 999px; background: #f3e8ff; color: #6A1B9A;">
					Monte sua pizza
				</span>
			</div>

			<div class="row">
				<div class="col-md-4">
					<div class="p-1 rounded" style="background: #f8fafc; border: 1px solid #e5e7eb;">
						<label style="font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase;">
							Tamanho
						</label>
						<select id="inp-tamanho_id" class="form-control form-select" style="height: 36px; border-radius: 8px; font-size: 13px;">
							<option value="">Selecione</option>
							@foreach($tamanhosDePizza as $t)
							<option @if($itemPedido->tamanho_id == $t->id) selected @endif max-sabores="{{ $t->maximo_sabores }}" value="{{ $t->id }}">{{ $t->info }}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>

			<div class="row pizzas mt-2" style="
			background: #fafafa;
			border: 1px dashed #e5e7eb;
			border-radius: 10px;
			min-height: 70px;
			padding: 10px;
			">
		</div>

	</div>
</div>
</div>
@endif