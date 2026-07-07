@extends('layouts.app', ['title' => 'Pedido Delivery #' . $item->numero_sequencial])
@section('css')
<style type="text/css">
	.card-hover:hover{
		cursor: pointer;
	}

	.pedido-info-ifood{
    background: #fff;
    border: 1px solid #f1f1f1;
}

.pedido-info-ifood .info-box{
    background: #fafafa;
    border: 1px solid #ededed;
    border-radius: 18px;
    padding: 14px 16px;
}

.pedido-info-ifood .icon-box{
    width: 42px;
    height: 42px;
    min-width: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.pedido-info-ifood .mini-info{
    background: #fcfcfc;
    border: 1px solid #efefef;
    border-radius: 16px;
    padding: 12px 14px;
    height: 100%;
}

.pedido-info-ifood .mini-info .label{
    display: block;
    font-size: 12px;
    color: #8c8c8c;
    margin-bottom: 4px;
}

.pedido-info-ifood .mini-info .value{
    font-size: 15px;
    font-weight: 700;
}

.pedido-info-ifood .mini-highlight{
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 16px;
    padding: 12px 14px;
    font-size: 15px;
    border: 1px solid #ededed;
}
</style>
@endsection
@section('content')
<div class="mt-3">
	<div class="row">
		<input value="{{ $item->id }}" type="hidden" id="pedido_id">
		@if($item->estado == 'novo' || $item->estado == 'aprovado')
		<div class="col-12 col-lg-4">
			<div class="card">
				<div class="card-body">
					<form class="row" method="post" action="{{ route('pedidos-delivery.store-item', [$item->id]) }}">
						@csrf

						<input type="hidden" id="tipo_divisao_pizza" value="{{ $config != null ? $config->valor_pizza : 'divide' }}">
						<div class="col-md-12">
							{!!Form::select('produto_delivery', 'Produto')->required()
							->attrs(['class' => 'produto_delivery'])
							!!}
						</div>

						<div class="col-md-6 col-12 mt-2">
							{!!Form::tel('quantidade', 'Quantidade')
							->required()
							->attrs(['class' => 'moeda'])
							!!}
						</div>

						<div class="col-md-6 col-12 mt-2">
							{!!Form::tel('valor_unitario', 'Valor unitário')
							->required()
							->attrs(['class' => 'moeda'])
							!!}
						</div>

						<div class="col-md-12 mt-2">
							<button type="button" class="btn w-100 btn-dark" id="btn-adicionais">
								<i class="ri-shopping-basket-fill"></i>
								Definir adicionais
							</button>
						</div>

						<div class="col-md-12 mt-2 adicionaisescolhidos">
						</div>

						<div class="col-md-12 col-12 mt-2">
							{!!Form::text('observacao', 'Observação')
							!!}
						</div>

						<div class="col-12 mt-2 div-tp-carne d-none">
							{!!Form::select('ponto_carne', 'Ponto da carne', ['' => 'Selecione'] +  App\Models\Produto::pontosDaCarne())
							->attrs(['class' => 'form-select'])
							!!}
						</div>

						<div class="col-md-6 col-12 mt-2">
							{!!Form::tel('sub_total', 'Subtotal')
							->required()
							->readonly()
							->attrs(['class' => 'moeda'])
							!!}
						</div>

						<div class="col-md-6 col-12 mt-2">
							{!!Form::select('estado', 'Estado', 
							[
							'novo' => 'Novo', 
							'pendente' => 'Pendente', 
							'preparando' => 'Preparando', 
							'finalizado' => 'Finalizando'
							])
							->attrs(['class' => 'form-select'])
							->required()
							!!}
						</div>

						<input type="hidden" id="adicionais-hidden" name="adicionais">
						<input type="hidden" id="pizzas-hidden" name="pizzas">
						<input type="hidden" id="tamanho_id-hidden" name="tamanho_id">
						<div class="col-md-12 col-12 mt-4">
							<button type="submit" class="btn w-100 btn-success">
								<i class="ri-checkbox-circle-fill"></i>
								Adicionar
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		@endif

		<div class="col-12 @if($item->estado == 'novo' || $item->estado == 'aprovado') col-lg-8 @else col-lg-12 @endif">
			<div class="card">
				<div class="card-body">
					<div class="col-12">

						<a href="{{ route('pedidos-delivery.enviar-wpp', [$item->id]) }}" class="btn btn-sm btn-success">
							<i class="ri-whatsapp-line"></i> Enviar mensagem
						</a>

						@if($item->agendamento)
						<a href="{{ route('agendamentos.show', [$item->agendamento->id]) }}" class="btn btn-sm btn-dark">
							<i class="ri-calendar-line"></i> Ver agendamento
						</a>
						@endif
						<div style="text-align: right; ">
							<a href="{{ route('pedidos-delivery.index') }}" class="btn btn-danger btn-sm px-3">
								<i class="ri-arrow-left-double-fill"></i>Voltar
							</a>
						</div>
						<div class="row">
							<div class="col-12">
								{!! $item->_estado() !!}
							</div>
							@if($item->finalizado == 0)
							<form method="post" action="{{ route('pedidos-delivery.update', [$item->id]) }}" class="row">
								<div class="col-8 col-lg-4">
									@csrf
									@method('put')

									{!!Form::select('estado', 'Estado', 
									[
									'' => 'Selecione',
									'novo' => 'Novo',
									'aprovado' => 'Aprovado',
									'cancelado' => 'Cancelado',
									'finalizado' => 'Finalizado',
									] 
									)->required()
									->attrs(['class' => 'form-select'])
									->value($item->estado)
									!!}
								</div>
								<div class="col-4 col-lg-4">
									<br>
									<button class="btn btn-dark">
										Alterar estado
										<i class="ri-checkbox-circle-fill"></i>
									</button>
								</div>
							</form>
							@endif

						</div>
					</div>
					<br>
					<h3>ITENS <strong class="text-success">#{{ $item->numero_sequencial }}</strong></h3>

					<a target="_blank" class="float-end btn btn-dark mb-1" href="{{ route('pedidos-delivery.print', [$item->id])}}">
						<i class="ri-printer-line"></i>
						Imprimir
					</a>
					

					<div class="table-responsive col-12" style="min-height: 300px;">
						<table class="table">
							<thead class="table-light">
								<tr>
									<th>Produto/Serviço</th>
									<th>Quantidade</th>
									<th>Valor unitário</th>
									<th>Subtotal</th>
									<th>Observação</th>
									<th>Ações</th>
								</tr>
							</thead>
							<tbody>
								@foreach($item->itens as $i)

								<tr class="bg-{{ $i->estado }}">
									<td>{{ $i->produto ? $i->produto->nome : $i->servico->nome }}</td>
									<td>{{ __moeda($i->quantidade) }}</td>
									<td>{{ __moeda($i->valor_unitario) }}</td>
									<td>{{ __moeda($i->sub_total) }}</td>
									<td>
										@if($i->observacao == '')
										<button class="btn btn-sm">
											<i class="ri-sticky-note-line"></i>
										</button>
										@else
										<button class="btn btn-sm btn-dark" onclick="noteSwal('{{ $i->observacao }}')">
											<i class="ri-sticky-note-line"></i>
										</button>
										@endif
									</td>
									<td>
										@if($item->estado == 'novo' || $item->estado == 'aprovado')
										<form action="{{ route('pedidos-delivery.destroy-item', $i->id) }}" method="post" id="form-item-{{$item->id}}">
											@csrf
											@method('delete')
											<button type="submit" title="Deletar" class="btn btn-danger btn-delete btn-sm"><i class="ri-delete-bin-2-line"></i></button>
										</form>
										@endif
									</td>
								</tr>
								@if(sizeof($i->adicionais) > 0)
								<tr>
									<td></td>
									<td colspan="5" style="font-weight: bold; font-size: 13px;">Adicionais: {{ $i->getAdicionaisStr() }}</td>
								</tr>
								@endif

								@if($i->ponto_carne)
								<tr>
									<td></td>
									<td colspan="5" style="font-weight: bold; font-size: 13px;">Ponto da carme: <strong class="text-success">{{ $i->ponto_carne }}</strong></td>
								</tr>
								@endif

								@if(sizeof($i->pizzas) > 0)
								<tr>
									<td></td>
									<td colspan="5" style="font-weight: bold; font-size: 13px;">Sabores: 
										<strong class="text-success">
											@foreach($i->pizzas as $s)
											1/{{ sizeof($i->pizzas) }} {{ $s->sabor->nome }}
											@if(!$loop->last)
											|
											@endif
											@endforeach
										</strong>

										<span> - Tamanho: <strong class="text-info">{{ $i->tamanho ? $i->tamanho->nome : '--' }}</strong></span>
									</td>
								</tr>
								@endif

								@endforeach
							</tbody>
							<tfoot>
								@if($item->desconto > 0)
								<tr>
									<td>Desconto</td>
									<td colspan="5">-R$ {{ __moeda($item->desconto) }}</td>
								</tr>
								@endif
							</tfoot>
						</table>
						
					</div>


					<div class="row">
						<h5>estado dos itens</h5>
						<div class="col-lg-3 col-6">
							<h6 class="text-novo">
								<i class="ri-flag-2-fill"></i> novo
							</h6>
						</div>

						<div class="col-lg-3 col-6">
							<h6 class="text-pendente">
								<i class="ri-flag-2-fill"></i> pendente
							</h6>
						</div>

						<div class="col-lg-3 col-6">
							<h6 class="text-preparando">
								<i class="ri-flag-2-fill"></i> preparando
							</h6>
						</div>

						<div class="col-lg-3 col-6">
							<h6 class="text-finalizado">
								<i class="ri-flag-2-fill"></i> finalizado
							</h6>
						</div>
					</div>
					<hr>

					@if($item->estado == 'novo' || $item->estado == 'aprovado')
					<div class="card border-0 shadow-sm rounded-4 mb-3 pedido-info-ifood">
						<div class="card-body p-3 p-md-4">

							<div class="d-flex align-items-center justify-content-between mb-3">
								<h5 class="mb-0 fw-bold text-dark">
									<i class="ri-shopping-bag-3-line me-1 text-danger"></i>
									Detalhes da entrega
								</h5>

								<button 
								data-bs-toggle="modal" 
								data-bs-target="#modal-enderecos" 
								class="btn btn-light btn-sm rounded-pill px-3 border"
								type="button"
								>
								<i class="ri-map-pin-2-fill text-danger me-1"></i>
								Alterar
							</button>
						</div>

						@if($item->endereco)
						<div class="info-box mb-3">
							<div class="d-flex align-items-start">
								<div class="icon-box bg-danger-subtle text-danger me-3">
									<i class="ri-map-2-fill"></i>
								</div>
								<div>
									<span class="text-muted d-block small">Endereço de entrega</span>
									<strong class="text-dark">{{ $item->endereco->info }}</strong>
								</div>
							</div>
						</div>

						<div class="row g-3 mb-2">
							<div class="col-md-4">
								<div class="mini-info">
									<span class="label">Entrega</span>
									<strong class="value text-danger">R$ {{ __moeda($item->valor_entrega) }}</strong>
								</div>
							</div>

							<div class="col-md-4">
								<div class="mini-info">
									<span class="label">Pagamento</span>
									<strong class="value text-dark">{{ $item->tipo_pagamento }}</strong>
								</div>
							</div>

							<div class="col-md-4">
								<div class="mini-info">
									<span class="label">Status do pagamento</span>
									<div class="mt-1">{!! $item->_estadoPagamento() !!}</div>
								</div>
							</div>
						</div>

						@else
						<div class="info-box mb-3">
							<div class="d-flex align-items-start">
								<div class="icon-box bg-warning-subtle text-warning me-3">
									<i class="ri-store-2-fill"></i>
								</div>
								<div>
									<span class="text-muted d-block small">Forma de retirada</span>
									<strong class="text-dark">Retirada no balcão</strong>
								</div>
							</div>
						</div>
						@endif

						@if($item->observacao)
						<div class="alert alert-light border rounded-4 mb-3">
							<span class="text-muted d-block small mb-1">Observação do cliente</span>
							<strong class="text-danger">{{ $item->observacao }}</strong>
						</div>
						@endif

						@if($item->troco_para)
						<div class="mini-highlight mb-3">
							<i class="ri-money-dollar-circle-line text-success me-2"></i>
							<span class="text-muted">Troco para</span>
							<strong class="text-dark ms-1">R$ {{ __moeda($item->troco_para) }}</strong>
						</div>
						@endif

						@if($item->motoboy)
						<div class="mini-highlight bg-primary bg-opacity-10 border-0">
							<i class="ri-bike-line text-primary me-2"></i>
							<span class="text-muted">Motoboy</span>
							<strong class="text-primary ms-1">
								{{ $item->motoboy->nome }} - R$ {{ __moeda($item->comissao_motoboy) }}
							</strong>
						</div>
						@endif

					</div>
				</div>
				@endif

				@if(!$item->agendamento)
				@if($item->estado == 'novo' || $item->estado == 'aprovado')
				<div class="col-12">
					<button data-bs-toggle="modal" data-bs-target="#modal-finalizar" class="btn btn-lg btn-primary pull-right @if($item->finalizado) disabled @endif">
						<i class="ri-shopping-cart-2-line"></i>
						Finalizar <strong style="font-size: 25px; margin-left: 15px">R$ {{ __moeda($item->valor_total) }}</strong>
					</button>

					<div style="float: right;">
						<form action="{{ route('pedidos-delivery.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
							@method('delete')
							@csrf
							<button type="button" class="btn btn-delete btn-sm btn-danger">
								<i class="ri-delete-bin-line"></i> Remover Pedido
							</button>
						</form>
					</div>
				</div>
				@endif

				@endif
			</div>
		</div>
	</div>
</div>
</div>

<div class="modal fade" id="modal-enderecos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">
					Endereços

					<button class="btn btn-sm btn-success ml-2 btn-novo-endereco" type="button">
						<i class="ri-add-circle-fill"></i>
						Novo endereço
					</button>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="table-responsive">
						<table class="table">
							<thead class="table-dark">
								<tr>
									<th>Endereço</th>
									<th>Padrão</th>
									<th>Valor de entrega</th>
									<th>Ação</th>
								</tr>
							</thead>
							<tbody>
								@foreach($cliente->enderecos as $e)
								<tr>
									<td>{{ $e->info }}</td>
									<td>
										@if($item->padrao)
										<i class="ri-checkbox-circle-fill text-success"></i>
										@else
										<i class="ri-close-circle-fill text-danger"></i>
										@endif
									</td>
									<td>{{ __moeda($e->bairro->valor_entrega) }}</td>

									<td>
										<form method="post" action="{{ route('pedidos-delivery.set-endereco', [$item->id]) }}">
											<input type="hidden" name="endereco_id" value="{{ $e->id }}">
											@csrf
											<button title="Selecionar endereço" class="btn btn-success btn-sm">
												<i class="ri-check-line"></i>
											</button>
										</form>
									</td>
								</tr>
								@endforeach

								<tr>
									<td>Retirar no balcão</td>
									<td></td>
									<td></td>
									<td>
										<form method="post" action="{{ route('pedidos-delivery.set-endereco', [$item->id]) }}">
											<input type="hidden" name="endereco_id" value="">
											@csrf
											<button title="Selecionar endereço" class="btn btn-success btn-sm">
												<i class="ri-check-line"></i>
											</button>
										</form>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

				</div>
			</div>
			<div class="modal-footer">
				<button id="" type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-novo-endereco" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<form method="post" action="{{ route('pedidos-delivery.store-endereco', [$item->id]) }}">
				@csrf
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Novo Endereço</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row g-2">

						<div class="col-md-3">
							{!!Form::select('bairro_id', 'Bairro', ['' => 'Selecione'] + $bairros->pluck('info', 'id')->all())->required()
							->attrs(['class' => 'form-select'])
							!!}
						</div>

						<div class="col-md-4">
							{!!Form::text('rua', 'Rua')->required()
							!!}
						</div>

						<div class="col-md-2">
							{!!Form::text('numero', 'Número')->required()
							!!}
						</div>

						<div class="col-md-2">
							{!!Form::select('tipo', 'Tipo', ['casa' => 'Casa', 'trabalho' => 'Trabalho'])->required()
							->attrs(['class' => 'form-select'])
							!!}
						</div>

						<div class="col-md-4">
							{!!Form::text('referencia', 'Referência')
							!!}
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success" data-bs-dismiss="modal">Salvar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-finalizar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Finalizar</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-8">
						{!!Form::select('motoboy_id', 'Motoboy', ['' => 'Selecione'] + $motoboys->pluck('info', 'id')->all())
						->attrs(['class' => 'form-select select2'])
						->value($item->motoboy ? $item->motoboy_id : '')
						!!}
					</div>

					<div class="col-md-4">
						{!!Form::tel('valor_comissao', 'Valor')->attrs(['class' => 'moeda'])
						->value($item->motoboy ? __moeda($item->comissao_motoboy) : '')
						!!}
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button id="btn-finalizar" type="button" class="btn btn-success" data-bs-dismiss="modal">Salvar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-adicionais" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Adicionais</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row adicionais">


				</div>

				<h4 class="mt-3">Subtotal: <strong class="subtotal_modal"></strong></h4>

			</div>
			<div class="modal-footer">
				<button id="btn-save-modal" type="button" class="btn btn-success" data-bs-dismiss="modal">Salvar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-pizza" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Selecione os sabores</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row m-2">
					<p class="text-danger">*selecione o tamanho para buscar os sabores</p>
					<div class="col-md-5 col-6">
						{!!Form::select('tamanho_id', 'Tamanho', ['' => 'Selecione'] + 
						$tamanhosPizza->pluck('info', 'id')->all())
						->attrs(['class' => 'form-select'])
						!!}
					</div>
				</div>
				<div class="row pizzas m-2 mt-4">
				</div>

				<div class="col-md-2 col-6 m-2 mt-3">
					{!!Form::tel('subtotal_modal', 'Subtotal')
					->required()
					->attrs(['class' => 'moeda'])
					!!}
				</div>
			</div>
			<div class="modal-footer">
				<button id="btn-save-sabores" type="button" class="btn btn-success">Salvar</button>
			</div>
		</div>
	</div>
</div>

<form method="get" action="{{ route('pedidos-delivery.finish', [$item->id]) }}" id="form-finish">
	<input type="hidden" name="motoboy_id" id="motoboy_id">
	<input type="hidden" name="valor_comissao" id="valor_comissao">
</form>

@endsection

@section('js')
<script type="text/javascript" src="/js/pedido_delivery.js"></script>
@endsection
