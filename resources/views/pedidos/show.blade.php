@extends('layouts.app', ['title' => 'Comanda ' . $item->comanda])

@section('css')
<link rel="stylesheet" href="/css/pedido_cardapio.css">
@endsection

@section('content')
<div class="pedido-page mb-2">
	<div id="print"></div>

	<input type="hidden" id="impressao_sem_janela_cupom" value="{{ $configGeral ? $configGeral->impressao_sem_janela_cupom : 0 }}">

	<div class="pedido-topbar">
		<div>
			<span class="pedido-eyebrow">Pedido do cardápio</span>
			<h3>Comanda #{{ $item->comanda ?? $item->id }}</h3>

			<div class="pedido-meta">
				@if($item->_mesa)
				<span>{{ $item->_mesa->nome }}</span>
				@endif

				@if($item->cliente_nome)
				<span>{{ $item->cliente_nome }} - {{ $item->cliente_fone }}</span>
				@endif

				<span class="{{ $item->status ? 'status-aberto' : 'status-fechado' }}">
					{{ $item->status ? 'Aberto' : 'Fechado' }}
				</span>
			</div>
		</div>

		<div class="pedido-top-actions">
			<button type="button" data-bs-toggle="modal" data-bs-target="#modal-mesa" class="btn pedido-btn pedido-btn-light">
				<i class="ri-refresh-line"></i>
				Alterar mesa
			</button>

			<button class="btn pedido-btn pedido-btn-dark" onclick="print('{{ $item->id }}')">
				<i class="ri-printer-line"></i>
				Imprimir
			</button>

			<a href="{{ route('pedidos-cardapio.index') }}" class="btn pedido-btn pedido-btn-danger">
				<i class="ri-arrow-left-double-fill"></i>
				Voltar
			</a>
		</div>
	</div>

	<div class="row g-3">
		<div class="col-12 col-lg-4">
			<div class="pedido-panel">
				<div class="pedido-panel-header">
					<div>
						<h5>Adicionar produto</h5>
						<small>Inclua itens na comanda</small>
					</div>
				</div>

				<form class="row g-2" method="post" action="{{ route('pedidos-cardapio.store-item', [$item->id]) }}">
					@csrf

					<input type="hidden" id="tipo_divisao_pizza" value="{{ $config != null ? $config->valor_pizza : 'divide' }}">

					<div class="col-12">
						{!!Form::select('produto_cardapio', 'Produto')->required()->attrs(['class' => 'produto_cardapio'])!!}
					</div>

					<div class="col-6">
						{!!Form::tel('quantidade', 'Quantidade')->required()->attrs(['class' => 'moeda'])!!}
					</div>

					<div class="col-6">
						{!!Form::tel('valor_unitario', 'Valor unitário')->required()->attrs(['class' => 'moeda'])!!}
					</div>

					<div class="col-12">
						<button @if($item->status == 0) disabled @endif type="button" class="btn pedido-btn pedido-btn-light w-100" id="btn-adicionais">
							<i class="ri-shopping-basket-fill"></i>
							Definir adicionais
						</button>
					</div>

					<div class="col-12 adicionaisescolhidos"></div>

					<div class="col-12">
						{!!Form::text('observacao', 'Observação')!!}
					</div>

					<div class="col-12 div-tp-carne d-none">
						{!!Form::select('ponto_carne', 'Ponto da carne', ['' => 'Selecione'] + App\Models\Produto::pontosDaCarne())->attrs(['class' => 'form-select'])!!}
					</div>

					<div class="col-6">
						{!!Form::tel('sub_total', 'Subtotal')->required()->readonly()->attrs(['class' => 'moeda'])!!}
					</div>

					<div class="col-6">
						{!!Form::select('estado', 'Estado', [
						'novo' => 'Novo',
						'pendente' => 'Pendente',
						'preparando' => 'Preparando',
						'finalizado' => 'Finalizando'
						])->attrs(['class' => 'form-select'])->required()!!}
					</div>

					<input type="hidden" id="adicionais-hidden" name="adicionais">
					<input type="hidden" id="pizzas-hidden" name="pizzas">
					<input type="hidden" id="tamanho_id-hidden" name="tamanho_id">

					<div class="col-12 mt-3">
						<button @if($item->status == 0) disabled @endif type="submit" class="btn pedido-btn pedido-btn-primary w-100">
							<i class="ri-checkbox-circle-fill"></i>
							Adicionar produto
						</button>
					</div>
				</form>
			</div>

			@if($config != null && $config->incluir_servico)
			<div class="pedido-panel mt-3">
				<div class="pedido-panel-header">
					<div>
						<h5>Adicionar serviço</h5>
						<small>Inclua serviços na comanda</small>
					</div>
				</div>

				<form class="row g-2" method="post" action="{{ route('pedidos-cardapio.store-servico', [$item->id]) }}">
					@csrf

					<div class="col-12">
						{!!Form::select('servico_id', 'Serviço')->required()->attrs(['class' => ''])!!}
					</div>

					<div class="col-6">
						{!!Form::tel('quantidade', 'Quantidade')->required()->attrs(['class' => 'moeda qtd_servico'])!!}
					</div>

					<div class="col-6">
						{!!Form::tel('valor_unitario', 'Valor unitário')->required()->attrs(['class' => 'moeda valor_unitario_servico'])!!}
					</div>

					<div class="col-12">
						{!!Form::text('observacao', 'Observação')!!}
					</div>

					<div class="col-6">
						{!!Form::tel('sub_total', 'Subtotal')->required()->readonly()->attrs(['class' => 'moeda sub_total_servico'])!!}
					</div>

					<div class="col-6">
						{!!Form::select('estado', 'Estado', [
						'novo' => 'Novo',
						'pendente' => 'Pendente',
						'preparando' => 'Preparando',
						'finalizado' => 'Finalizando'
						])->attrs(['class' => 'form-select'])->required()!!}
					</div>

					<div class="col-12 mt-3">
						<button @if($item->status == 0) disabled @endif type="submit" class="btn pedido-btn pedido-btn-dark w-100">
							<i class="ri-checkbox-circle-fill"></i>
							Adicionar serviço
						</button>
					</div>
				</form>
			</div>
			@endif
		</div>

		<div class="col-12 col-lg-8">
			<div class="pedido-panel pedido-itens-panel">
				<div class="pedido-panel-header pedido-panel-header-between">
					<div>
						<h5>Itens da comanda</h5>
						<small>{{ sizeof($item->itens) + sizeof($item->itensServico) }} itens lançados</small>
					</div>

					<div class="pedido-total-mini">
						<small>Total</small>
						<strong>R$ {{ __moeda($item->total + $item->acrescimo) }}</strong>
					</div>
				</div>

				<div class="pedido-table-desktop">
					<div class="table-responsive">
						<table class="table pedido-table">
							<thead>
								<tr>
									<th>Produto</th>
									<th>Qtd</th>
									<th>Unitário</th>
									<th>Subtotal</th>
									<th>Obs</th>
									<th>Ações</th>
								</tr>
							</thead>

							<tbody>
								@forelse($item->itens as $i)
								<tr class="pedido-row bg-{{ $i->estado }}">
									<td>
										<strong>{{ $i->produto->nome }}</strong>

										@if($i->funcionario)
										<small>Garçom: {{ $i->funcionario->nome }}</small>
										@endif

										@if($i->nome_cardapio)
										<small>Cliente: {{ $i->nome_cardapio }}</small>
										@endif
									</td>

									<td>{{ __moeda($i->quantidade) }}</td>
									<td>R$ {{ __moeda($i->valor_unitario) }}</td>
									<td><strong>R$ {{ __moeda($i->sub_total) }}</strong></td>

									<td>
										@if($i->observacao == '')
										<button class="btn pedido-icon-btn pedido-icon-light">
											<i class="ri-sticky-note-line"></i>
										</button>
										@else
										<button class="btn pedido-icon-btn pedido-icon-dark" onclick="noteSwal('{{ $i->observacao }}')">
											<i class="ri-sticky-note-line"></i>
										</button>
										@endif
									</td>

									<td>
										@if(__isAdmin())
										<form action="{{ route('pedidos-cardapio.destroy-item', $i->id) }}" method="post" id="form-{{$i->id}}">
											@csrf
											@method('delete')
											<button @if($item->status == 0) disabled @endif type="submit" title="Deletar" class="btn pedido-icon-btn pedido-icon-danger btn-delete">
												<i class="ri-delete-bin-2-line"></i>
											</button>
										</form>
										@endif
									</td>
								</tr>

								@if(sizeof($i->adicionais) > 0)
								<tr class="pedido-detail-row">
									<td colspan="6">Adicionais: <strong>{{ $i->getAdicionaisStr() }}</strong></td>
								</tr>
								@endif

								@if($i->ponto_carne)
								<tr class="pedido-detail-row">
									<td colspan="6">Ponto da carne: <strong>{{ $i->ponto_carne }}</strong></td>
								</tr>
								@endif

								@if(sizeof($i->pizzas) > 0)
								<tr class="pedido-detail-row">
									<td colspan="6">
										Sabores:
										<strong>
											@foreach($i->pizzas as $s)
											1/{{ sizeof($i->pizzas) }} {{ $s->sabor->nome }}@if(!$loop->last) | @endif
											@endforeach
										</strong>
										<span> - Tamanho: <strong>{{ $i->tamanho ? $i->tamanho->nome : '--' }}</strong></span>
									</td>
								</tr>
								@endif
								@empty
								<tr>
									<td colspan="6">
										<div class="pedido-empty">
											<i class="ri-restaurant-2-line"></i>
											<strong>Nenhum item lançado</strong>
											<small>Adicione produtos para montar a comanda.</small>
										</div>
									</td>
								</tr>
								@endforelse

								@foreach($item->itensServico as $i)
								<tr class="pedido-row bg-{{ $i->estado }}">
									<td><strong>Serviço: {{ $i->servico->nome }}</strong></td>
									<td>{{ __moeda($i->quantidade) }}</td>
									<td>R$ {{ __moeda($i->valor_unitario) }}</td>
									<td><strong>R$ {{ __moeda($i->sub_total) }}</strong></td>

									<td>
										@if($i->observacao == '')
										<button class="btn pedido-icon-btn pedido-icon-light">
											<i class="ri-sticky-note-line"></i>
										</button>
										@else
										<button class="btn pedido-icon-btn pedido-icon-dark" onclick="noteSwal('{{ $i->observacao }}')">
											<i class="ri-sticky-note-line"></i>
										</button>
										@endif
									</td>

									<td>
										<form action="{{ route('pedidos-cardapio.destroy-item-servico', $i->id) }}" method="post" id="form-{{$i->id}}">
											@csrf
											@method('delete')
											<button type="submit" title="Deletar" class="btn pedido-icon-btn pedido-icon-danger btn-delete">
												<i class="ri-delete-bin-2-line"></i>
											</button>
										</form>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>

				<div class="pedido-mobile-list">
					@forelse($item->itens as $i)
					<div class="pedido-mobile-card bg-{{ $i->estado }}">
						<div class="pedido-mobile-head">
							<div>
								<strong>{{ $i->produto->nome }}</strong>

								@if($i->funcionario)
								<small>Garçom: {{ $i->funcionario->nome }}</small>
								@endif

								@if($i->nome_cardapio)
								<small>Cliente: {{ $i->nome_cardapio }}</small>
								@endif
							</div>

							<span>{{ $i->estado }}</span>
						</div>

						<div class="pedido-mobile-grid">
							<div>
								<small>Qtd</small>
								<strong>{{ __moeda($i->quantidade) }}</strong>
							</div>

							<div>
								<small>Unitário</small>
								<strong>R$ {{ __moeda($i->valor_unitario) }}</strong>
							</div>

							<div>
								<small>Subtotal</small>
								<strong>R$ {{ __moeda($i->sub_total) }}</strong>
							</div>
						</div>

						@if(sizeof($i->adicionais) > 0)
						<div class="pedido-mobile-extra">Adicionais: <strong>{{ $i->getAdicionaisStr() }}</strong></div>
						@endif

						@if($i->ponto_carne)
						<div class="pedido-mobile-extra">Ponto da carne: <strong>{{ $i->ponto_carne }}</strong></div>
						@endif

						@if(sizeof($i->pizzas) > 0)
						<div class="pedido-mobile-extra">
							Sabores:
							<strong>
								@foreach($i->pizzas as $s)
								1/{{ sizeof($i->pizzas) }} {{ $s->sabor->nome }}@if(!$loop->last) | @endif
								@endforeach
							</strong>
							<br>
							Tamanho: <strong>{{ $i->tamanho ? $i->tamanho->nome : '--' }}</strong>
						</div>
						@endif

						<div class="pedido-mobile-actions">
							@if($i->observacao == '')
							<button class="btn pedido-btn pedido-btn-light">
								<i class="ri-sticky-note-line"></i>
								Sem obs
							</button>
							@else
							<button class="btn pedido-btn pedido-btn-dark" onclick="noteSwal('{{ $i->observacao }}')">
								<i class="ri-sticky-note-line"></i>
								Ver obs
							</button>
							@endif

							@if(__isAdmin())
							<form action="{{ route('pedidos-cardapio.destroy-item', $i->id) }}" method="post" id="form-mobile-{{$i->id}}">
								@csrf
								@method('delete')
								<button @if($item->status == 0) disabled @endif type="submit" class="btn pedido-btn pedido-btn-danger btn-delete">
									<i class="ri-delete-bin-2-line"></i>
									Remover
								</button>
							</form>
							@endif
						</div>
					</div>
					@empty
					<div class="pedido-empty">
						<i class="ri-restaurant-2-line"></i>
						<strong>Nenhum item lançado</strong>
						<small>Adicione produtos para montar a comanda.</small>
					</div>
					@endforelse

					@foreach($item->itensServico as $i)
					<div class="pedido-mobile-card bg-{{ $i->estado }}">
						<div class="pedido-mobile-head">
							<div>
								<strong>Serviço: {{ $i->servico->nome }}</strong>
							</div>

							<span>{{ $i->estado }}</span>
						</div>

						<div class="pedido-mobile-grid">
							<div>
								<small>Qtd</small>
								<strong>{{ __moeda($i->quantidade) }}</strong>
							</div>

							<div>
								<small>Unitário</small>
								<strong>R$ {{ __moeda($i->valor_unitario) }}</strong>
							</div>

							<div>
								<small>Subtotal</small>
								<strong>R$ {{ __moeda($i->sub_total) }}</strong>
							</div>
						</div>

						<div class="pedido-mobile-actions">
							@if($i->observacao == '')
							<button class="btn pedido-btn pedido-btn-light">
								<i class="ri-sticky-note-line"></i>
								Sem obs
							</button>
							@else
							<button class="btn pedido-btn pedido-btn-dark" onclick="noteSwal('{{ $i->observacao }}')">
								<i class="ri-sticky-note-line"></i>
								Ver obs
							</button>
							@endif

							<form action="{{ route('pedidos-cardapio.destroy-item-servico', $i->id) }}" method="post" id="form-mobile-servico-{{$i->id}}">
								@csrf
								@method('delete')
								<button type="submit" class="btn pedido-btn pedido-btn-danger btn-delete">
									<i class="ri-delete-bin-2-line"></i>
									Remover
								</button>
							</form>
						</div>
					</div>
					@endforeach
				</div>

				<div class="pedido-status">
					<h5>Estado dos itens</h5>

					<div class="row g-2">
						<div class="col-6 col-lg-3">
							<div class="pedido-status-item text-novo">
								<i class="ri-flag-2-fill"></i>
								Novo
							</div>
						</div>

						<div class="col-6 col-lg-3">
							<div class="pedido-status-item text-pendente">
								<i class="ri-flag-2-fill"></i>
								Pendente
							</div>
						</div>

						<div class="col-6 col-lg-3">
							<div class="pedido-status-item text-preparando">
								<i class="ri-flag-2-fill"></i>
								Preparando
							</div>
						</div>

						<div class="col-6 col-lg-3">
							<div class="pedido-status-item text-finalizado">
								<i class="ri-flag-2-fill"></i>
								Finalizado
							</div>
						</div>
					</div>
				</div>

				@if($config->percentual_taxa_servico > 0)
				<div class="pedido-resumo row g-2">
					<div class="col-12 col-md-4">
						<div class="pedido-resumo-card">
							<small>Taxa de serviço</small>
							<strong>{{ $config->percentual_taxa_servico }}%</strong>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<div class="pedido-resumo-card">
							<small>Valor dos itens</small>
							<strong>R$ {{ __moeda($item->itensServico->sum('sub_total') + $item->itens->sum('sub_total')) }}</strong>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<div class="pedido-resumo-card">
							<small>Acréscimo</small>
							<strong>R$ {{ __moeda($item->acrescimo) }}</strong>
						</div>
					</div>
				</div>
				@endif

				@can('pdv_create')
				@if($item->status == 1)
				@if(sizeof($clientes) > 0)
				<div class="pedido-clientes">
					<h5>Finalizar por cliente</h5>

					<div class="row g-2">
						@foreach($clientes as $key => $valor)
						<div class="col-12 col-md-4 col-lg-3">
							<a href="{{ route('pedidos-cardapio.finish-client', ['pedido_id' => $item->id, 'nome' => $key, 'valor' => $valor])}}" class="btn pedido-btn pedido-btn-dark w-100 @if(!$item->status) disabled @endif">
								<i class="ri-user-6-fill"></i>
								{{ $key }} R$ {{ __moeda($valor) }}
							</a>
						</div>
						@endforeach
					</div>
				</div>
				@endif
				@php $saldoParcialRestante = 0; @endphp
				@php $totalParcialPago = 0; @endphp

				@if($item->finalizacoesParciais && $item->finalizacoesParciais->count() > 0)
				@php
				$totalParcialPago = $item->finalizacoesParciais->sum('valor_pago');
				$saldoParcialRestante = ($item->total + $item->acrescimo) - $totalParcialPago;
				@endphp

				<div class="pedido-parcial-alert mt-1">
					<div>
						<span>
							<i class="ri-bank-card-line"></i>
							Este pedido possui {{ $item->finalizacoesParciais->count() }} finalização parcial
						</span>

						<small>
							Pago: <strong>R$ {{ __moeda($totalParcialPago) }}</strong>
							&nbsp;|&nbsp;
							Restante: <strong>R$ {{ __moeda($saldoParcialRestante) }}</strong>
						</small>
					</div>

					<button type="button" class="btn pedido-btn pedido-btn-light" data-bs-toggle="modal" data-bs-target="#modal-detalhes-parciais">
						<i class="ri-eye-line"></i>
						Ver detalhes
					</button>
				</div>
				@endif

				<div class="pedido-finish-area d-flex gap-2">
					<button type="button" class="btn pedido-finish-btn pedido-finish-btn-parcial me-auto @if(!$item->status || ($saldoParcialRestante == 0 && $item->finalizacoesParciais->count() > 0)) disabled @endif" data-bs-toggle="modal" data-bs-target="#modal-finalizar-parcial">
						<span>
							<i class="ri-bank-card-line"></i>
							Finalizar parcial
						</span>

						<strong>R$ {{ __moeda($item->total + $item->acrescimo - $totalParcialPago) }}</strong>
					</button>

					<a class="btn pedido-finish-btn @if(!$item->status || ($item->finalizacoesParciais && $item->finalizacoesParciais->count() > 0)) disabled @endif" href="{{ route('pedidos-cardapio.finish', [$item->id])}}">
						<span>
							<i class="ri-shopping-cart-2-line"></i>
							@if(sizeof($clientes) > 0)
							Finalizar todos
							@else
							Finalizar pedido
							@endif
						</span>

						<strong>R$ {{ __moeda($item->total + $item->acrescimo) }}</strong>
					</a>
				</div>
				@endif
				@endcan
			</div>
		</div>
	</div>
</div>

<div class="modal fade pedido-modal" id="modal-mesa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<form action="{{ route('pedidos-cardapio.update-table', [$item->id]) }}" method="post">
			@csrf
			@method('put')

			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Alterar mesa/comanda</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div class="col-12 mb-2">
						{!!Form::select('mesa_id', 'Mesa', ['' => 'Selecione'] + $mesas->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])->value($item->mesa_id)!!}
					</div>

					<div class="col-12">
						{!!Form::tel('comanda', 'Comanda')->value($item->comanda)!!}
					</div>
				</div>

				<div class="modal-footer">
					<button type="submit" class="btn pedido-btn pedido-btn-primary" data-bs-dismiss="modal">Salvar</button>
				</div>
			</div>
		</form>
	</div>
</div>

<div id="overlay-emitindo-nfce">
    <div class="emitindo-box">
        <div class="spinner-border text-primary mb-4" style="width:70px;height:70px;" role="status"></div>

        <h2>Emitindo NFC-e...</h2>

        <p>
            Não feche esta tela.<br>
            Aguarde o processamento.
        </p>
    </div>
</div>

<div class="modal fade pedido-modal" id="modal-adicionais" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Adicionais</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="row adicionais"></div>

				<div class="pedido-modal-total">
					<small>Subtotal</small>
					<strong class="subtotal_modal"></strong>
				</div>
			</div>

			<div class="modal-footer">
				<button id="btn-save-modal" type="button" class="btn pedido-btn pedido-btn-primary" data-bs-dismiss="modal">Salvar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade pedido-modal" id="modal-pizza" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Selecione os sabores</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<div class="modal-body">
				<div class="row g-2">
					<div class="col-12">
						<p class="pedido-alert-text">Selecione o tamanho para buscar os sabores.</p>
					</div>

					<div class="col-12 col-md-5">
						{!!Form::select('tamanho_id', 'Tamanho', ['' => 'Selecione'] + $tamanhosPizza->pluck('info', 'id')->all())->attrs(['class' => 'form-select'])!!}
					</div>
				</div>

				<div class="row pizzas mt-3"></div>

				<div class="col-12 col-md-3 mt-3">
					{!!Form::tel('subtotal_modal', 'Subtotal')->required()->attrs(['class' => 'moeda'])!!}
				</div>
			</div>

			<div class="modal-footer">
				<button id="btn-save-sabores" type="button" class="btn pedido-btn pedido-btn-primary">Salvar</button>
			</div>
		</div>
	</div>
</div>

@include('pedidos.partials.modal_finalizar_parcial')
@include('pedidos.partials.modal_detalhes_parcial')

@endsection

@section('js')
<script>
	const PEDIDO_ID = {{ $item->id }};
</script>
<script type="text/javascript" src="/js/pedido.js"></script>
<script type="text/javascript" src="/js/pedido_finalizar_parcial.js"></script>
@endsection