@extends('layouts.app', ['title' => 'Planejamento #' . $item->numero_sequencial])

@section('css')
<link rel="stylesheet" href="/css/planejamento.css">
@endsection

@section('content')
@php
$totalProdutos = $item->produtos->sum('sub_total');
$totalServicos = $item->servicos->sum('sub_total');
$totalTerceiros = $item->servicosTerceiro->sum('sub_total');
$totalAdm = $item->custosAdm->sum('sub_total');
$totalCusto = $totalProdutos + $totalServicos + $totalTerceiros + $totalAdm - $item->desconto;
@endphp

<div class="card">
	<div class="card-body">
		<div class="planejamento-page mt-1">

			<div class="planejamento-header">
				<div>
					<div class="planejamento-eyebrow">Planejamento de custo</div>
					<h3>Planejamento #{{ $item->numero_sequencial }}</h3>
					<p>{{ $item->descricao ?: 'Sem descrição informada' }}</p>
				</div>

				<div class="planejamento-actions">
					<a href="{{ route('planejamento-custo.index') }}" class="btn-planejamento btn-light-planejamento"><i class="ri-arrow-left-line"></i> Voltar</a>
					<button type="button" class="btn-planejamento btn-dark-planejamento" data-bs-toggle="modal" data-bs-target="#modal_estado"><i class="ri-refresh-fill"></i> Alterar estado</button>
					<a href="{{ route('planejamento-custo.edit', [$item->id]) }}" class="btn-planejamento btn-warning-planejamento"><i class="ri-pencil-fill"></i> Editar</a>
				</div>
			</div>

			<div class="row g-3">
				<div class="col-lg-4">

					<div class="planejamento-card">
						<div class="planejamento-card-head">
							<div>
								<span class="section-label">Dados principais</span>
								<h5>Informações do projeto</h5>
							</div>
							{!! $item->_estado() !!}
						</div>

						<div class="info-block">
							<label>Descrição</label>
							<p>{{ $item->descricao ?: '--' }}</p>
						</div>

						<div class="info-block">
							<label>Observação</label>
							<p>{{ $item->observacao ?: '--' }}</p>
						</div>

						<div class="client-box">
							<div class="client-avatar">
								<i class="ri-user-3-line"></i>
							</div>
							<div>
								<small>Cliente</small>
								<strong>{{ $item->projeto->cliente->razao_social }}</strong>
							</div>
						</div>

						<div class="info-grid">
							<div><label>CPF/CNPJ</label><strong>{{ $item->projeto->cliente->cpf_cnpj ?: '--' }}</strong></div>
							<div><label>Telefone</label><strong>{{ $item->projeto->cliente->telefone ?: '--' }}</strong></div>
							<div><label>Email</label><strong>{{ $item->projeto->cliente->email ?: '--' }}</strong></div>
							<div><label>Cidade</label><strong>{{ $item->projeto->cliente->cidade ? $item->projeto->cliente->cidade->info : '--' }}</strong></div>
						</div>

						@if(sizeof($item->itensProposta) > 0)
						<div class="proposal-value">
							<span>Valor final proposta</span>
							<strong>R$ {{ __moeda($item->total_final) }}</strong>
						</div>
						@endif

						<div class="d-flex flex-wrap gap-2 mt-3">
							@if($item->estado == 'proposta')
							<a class="btn-planejamento btn-primary-planejamento" href="{{ route('planejamento-custo.proposta', [$item->id]) }}" target="_blank"><i class="ri-bring-forward"></i> {{ sizeof($item->itensProposta) == 0 ? 'Criar Proposta' : 'Nova Proposta' }}</a>
							@endif

							@if($item->estado != 'cotacao' && sizeof($item->itensProposta) > 0)
							<a class="btn-planejamento btn-light-planejamento" href="{{ route('planejamento-custo.imprimir-proposta', [$item->id]) }}" target="_blank"><i class="ri-printer-line"></i> Imprimir Proposta</a>
							@endif

							@if($item->arquivo)
							<a target="_blank" class="btn-planejamento btn-light-planejamento" href="{{ route('planejamento-custo.preview', [$item->id]) }}"><i class="ri-file-fill"></i> Visualizar Arquivo</a>
							@endif
						</div>
					</div>

					<div class="planejamento-card mt-3">
						<div class="planejamento-card-head">
							<div>
								<span class="section-label">Timeline</span>
								<h5>Histórico de alteração</h5>
							</div>
						</div>

						<div class="timeline-planejamento">
							@forelse($item->logs as $l)
							<div class="timeline-item">
								<div class="timeline-dot"></div>
								<div class="timeline-content">
									<strong>{{ $l->usuario->name }}</strong>
									<small>{{ __data_pt($l->created_at) }}</small>

									@if($l->estado_anterior == '' && $l->estado_alterado == '')
									<span class="badge bg-light text-dark mt-1">PROPOSTA</span>
									@else
									<div class="mt-1">{!! $l->_estadoAnterior() !!} / {!! $l->_estadoAlterado() !!}</div>
									@endif

									@if($l->observacao)
									<p>{{ $l->observacao }}</p>
									@endif
								</div>
							</div>
							@empty
							<div class="empty-planejamento">
								<i class="ri-time-line"></i>
								<p>Nenhum histórico encontrado</p>
							</div>
							@endforelse
						</div>
					</div>

				</div>

				<div class="col-lg-8">

					<div class="summary-grid">
						<div class="summary-card">
							<span>Produtos</span>
							<strong>R$ {{ __moeda($totalProdutos) }}</strong>
						</div>
						<div class="summary-card">
							<span>Mão de obra</span>
							<strong>R$ {{ __moeda($totalServicos) }}</strong>
						</div>
						<div class="summary-card">
							<span>Terceiros</span>
							<strong>R$ {{ __moeda($totalTerceiros) }}</strong>
						</div>
						<div class="summary-card active">
							<span>Total custo</span>
							<strong>R$ {{ __moeda($totalCusto) }}</strong>
						</div>
					</div>

					<div class="planejamento-card mt-3">
						<div class="planejamento-card-head">
							<div>
								<span class="section-label">Composição</span>
								<h5>Valores de custo</h5>
							</div>
						</div>

						<ul class="nav planejamento-tabs mb-3" role="tablist">
							<li class="nav-item"><a href="#produtos" data-bs-toggle="tab" class="nav-link active">Produtos</a></li>
							<li class="nav-item"><a href="#servicos" data-bs-toggle="tab" class="nav-link">Mão de Obra</a></li>
							<li class="nav-item"><a href="#servicos-terceiro" data-bs-toggle="tab" class="nav-link">Serviços de Terceiros</a></li>
							<li class="nav-item"><a href="#custos-adm" data-bs-toggle="tab" class="nav-link">Custos Administrativos</a></li>
						</ul>

						<div class="tab-content">
							<div class="tab-pane show active" id="produtos">
								@include('planejamento_custo.partials.tabela_itens', ['itens' => $item->produtos, 'tipo' => 'produto'])
								<a class="btn-planejamento btn-primary-planejamento mt-3" href="{{ route('planejamento-custo.cotacao', [$item->id]) }}"><i class="ri-price-tag-3-line"></i> Cotações</a>
							</div>

							<div class="tab-pane" id="servicos">
								@include('planejamento_custo.partials.tabela_itens', ['itens' => $item->servicos, 'tipo' => 'servico'])
							</div>

							<div class="tab-pane" id="servicos-terceiro">
								@include('planejamento_custo.partials.tabela_itens', ['itens' => $item->servicosTerceiro, 'tipo' => 'terceiro'])
							</div>

							<div class="tab-pane" id="custos-adm">
								@include('planejamento_custo.partials.tabela_itens', ['itens' => $item->custosAdm, 'tipo' => 'adm'])
							</div>
						</div>

						<div class="total-box">
							<div>
								<span>Total de produtos</span>
								<strong>R$ {{ __moeda($totalProdutos) }}</strong>
							</div>
							<div>
								<span>Mão de obra</span>
								<strong>R$ {{ __moeda($totalServicos) }}</strong>
							</div>
							<div>
								<span>Serviços terceiro</span>
								<strong>R$ {{ __moeda($totalTerceiros) }}</strong>
							</div>
							<div>
								<span>Custos administrativos</span>
								<strong>R$ {{ __moeda($totalAdm) }}</strong>
							</div>
							<div class="grand-total">
								<span>Total custo</span>
								<strong>R$ {{ __moeda($totalCusto) }}</strong>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

@include('planejamento_custo.partials.modal_estado')
@endsection