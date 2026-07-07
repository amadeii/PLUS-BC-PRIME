<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8">
	<title>Acompanhamento da Ordem de Produção - {{ $empresa->nome }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">

	<style>
		body {
			background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
			font-family: 'Nunito', sans-serif;
			color: #1f2937;
		}

		.navbar-brand img {
			max-height: 60px;
		}

		.card-producao {
			border: none;
			border-radius: 18px;
			box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
			margin-top: 24px;
			overflow: hidden;
		}

		.topo-card {
			background: linear-gradient(135deg, #4254BA 0%, #5B5BD6 100%);
			color: #fff;
			padding: 24px;
		}

		.topo-card h3 {
			margin: 0;
			font-weight: 800;
		}

		.status-badge {
			font-size: 13px;
			padding: 8px 14px;
			border-radius: 999px;
			font-weight: 700;
			display: inline-block;
		}

		.info-box {
			background: #fff;
			border: 1px solid #e5e7eb;
			border-radius: 14px;
			padding: 16px;
			height: 100%;
			box-shadow: 0 4px 14px rgba(0,0,0,0.04);
		}

		.info-box .label {
			font-size: 12px;
			text-transform: uppercase;
			color: #6b7280;
			font-weight: 700;
			letter-spacing: .4px;
			margin-bottom: 6px;
		}

		.info-box .value {
			font-size: 16px;
			font-weight: 800;
			color: #111827;
		}

		.section-title {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 20px;
			font-weight: 800;
			color: #1f2937;
			margin-bottom: 14px;
		}

		.table thead th {
			background: #111827;
			color: #fff;
			border: none;
			font-size: 13px;
			vertical-align: middle;
		}

		.table tbody td {
			vertical-align: middle;
		}

		.item-status {
			padding: 6px 10px;
			border-radius: 999px;
			font-size: 12px;
			font-weight: 700;
			display: inline-block;
		}

		.status-pendente {
			background: #fff7ed;
			color: #c2410c;
		}

		.status-producao {
			background: #eff6ff;
			color: #1d4ed8;
		}

		.status-finalizado {
			background: #ecfdf5;
			color: #047857;
		}

		.obs-box {
			background: #f8fafc;
			border: 1px dashed #cbd5e1;
			border-radius: 14px;
			padding: 16px;
			color: #334155;
		}

		.table-card {
			background: #fff;
			border-radius: 16px;
			border: 1px solid #e5e7eb;
			padding: 14px;
			box-shadow: 0 4px 14px rgba(0,0,0,0.04);
		}

		@media (max-width: 768px) {
			table.table thead {
				display: none !important;
			}

			table.table,
			table.table tbody,
			table.table tr,
			table.table td {
				display: block !important;
				width: 100% !important;
			}

			table.table tr {
				background: #fff;
				border: 1px solid #e5e7eb;
				border-radius: 14px;
				margin-bottom: 14px;
				padding: 10px;
				box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
			}

			table.table td {
				text-align: right !important;
				padding: 10px 12px;
				border: none !important;
				position: relative;
			}

			table.table td::before {
				content: attr(data-label);
				position: absolute;
				left: 12px;
				width: 48%;
				font-weight: 700;
				text-align: left;
				color: #64748b;
			}
		}
	</style>
</head>
<body>

	<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
		<div class="container">
			<a class="navbar-brand d-flex align-items-center gap-2">
				@if($empresa->logo)
				<img src="{{ $empresa->img }}" width="120">
				@else
				<strong>{{ $empresa->nome }}</strong>
				@endif
			</a>
		</div>
	</nav>

	<main class="container py-4">
		<div class="card card-producao">
			<div class="topo-card d-flex flex-wrap justify-content-between align-items-center gap-3">
				<div>
					<h3><i class="ri-settings-3-line"></i> Ordem de Produção #{{ $ordem->codigo_sequencial }}</h3>
					<div class="mt-2 opacity-75">
						Acompanhe o andamento da sua produção em tempo real
					</div>
				</div>

				@php
				$estado = [
				'novo' => ['Novo', 'secondary'],
				'producao' => ['Em produção', 'primary'],
				'expedicao' => ['Em expedição', 'warning'],
				'entregue' => ['Entregue', 'success']
				][$ordem->estado] ?? ['Não definido', 'dark'];
				@endphp

				<span class="status-badge bg-{{ $estado[1] }}">
					{{ $estado[0] }}
				</span>
			</div>

			<div class="card-body p-4">
				<div class="row g-3 mb-4">
					<div class="col-md-3 col-6">
						<div class="info-box">
							<div class="label">Código</div>
							<div class="value">#{{ $ordem->codigo_sequencial }}</div>
						</div>
					</div>

					<div class="col-md-3 col-6">
						<div class="info-box">
							<div class="label">Responsável</div>
							<div class="value">{{ $ordem->funcionario->nome ?? '--' }}</div>
						</div>
					</div>

					<div class="col-md-3 col-6">
						<div class="info-box">
							<div class="label">Data prevista</div>
							<div class="value">
								{{ $ordem->data_prevista_entrega ? __data_pt($ordem->data_prevista_entrega, 0) : '--' }}
							</div>
						</div>
					</div>

					<div class="col-md-3 col-6">
						<div class="info-box">
							<div class="label">Status atual</div>
							<div class="value">{{ $estado[0] }}</div>
						</div>
					</div>
				</div>

				@if($ordem->observacao)
				<div class="mb-4">
					<div class="section-title">
						<i class="ri-file-text-line text-primary"></i> Observações da produção
					</div>
					<div class="obs-box">
						{!! nl2br(e($ordem->observacao)) !!}
					</div>
				</div>
				@endif

				<div class="section-title">
					<i class="ri-list-check-3 text-primary"></i> Itens da ordem de produção
				</div>

				<div class="table-card">
					<div class="table-responsive">
						<table class="table table-striped align-middle mb-0">
							<thead>
								<tr>
									<th>Produto</th>
									<th>Qtd</th>
									<th>Cliente</th>
									<th>Nº Pedido</th>
									<th>Status</th>
									<th>Observação</th>
								</tr>
							</thead>
							<tbody>
								@forelse($ordem->itens as $item)
								@php
								$statusItem = $item->status == 0 ? 'pendente' : 'finalizado';

								$classeStatus = match($statusItem) {
									'pendente', 'novo' => 'status-pendente',
									'producao', 'em_producao', 'fazendo' => 'status-producao',
									'finalizado', 'pronto', 'concluido', 'concluído' => 'status-finalizado',
									default => 'status-pendente'
								};
								@endphp

								<tr>
									<td data-label="Produto">
										<strong>{{ $item->produto->nome ?? '--' }}</strong>
										@if($item->itemProducao)
										<div class="text-muted small">
											Item produção: {{ $item->itemProducao->id }}
										</div>
										@endif
									</td>

									<td data-label="Qtd">
										<strong>{{ $item->quantidade }}</strong>
									</td>

									<td data-label="Cliente">
										{{ $item->cliente->razao_social ?? $item->cliente->nome ?? $item->cliente->info ?? '--' }}
									</td>

									<td data-label="Nº Pedido">
										{{ $item->numero_pedido ?? '--' }}
									</td>

									<td data-label="Status">
										<span class="item-status {{ $classeStatus }}">
											{{ strtoupper($statusItem) }}
										</span>
									</td>

									<td data-label="Observação">
										{{ $item->observacao ?? '--' }}
									</td>
								</tr>
								@empty
								<tr>
									<td colspan="6" class="text-center text-muted py-4">
										Nenhum item vinculado a esta ordem de produção
									</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>

			</div>
		</div>
	</main>

	<footer class="text-center text-muted mt-4 mb-3">
		<small>© {{ date('Y') }} {{ $empresa->nome }} — Todos os direitos reservados</small>
	</footer>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>