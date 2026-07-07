<div class="mb-3">
	<h4 class="mb-1">{{ $produto->nome }}</h4>
	<small class="text-muted">Estrutura completa da composição com recursão</small>
</div>

<div class="table-responsive">
	<table class="table table-striped table-hover">
		<thead class="table-light">
			<tr>
				<th width="100">Código</th>
				<th>Descrição</th>
				<th width="120">Qtde</th>
				<th width="90">UM</th>
				<th width="150">Categoria</th>
				<th width="140">Custo Unit.</th>
				<th width="140">Total</th>
			</tr>
		</thead>
		<tbody>
			@forelse($lista as $linha)
			<tr>
				<td>{{ $linha['codigo'] }}</td>
				<td>
					<div style="padding-left: {{ $linha['nivel'] * 28 }}px;">
						@if($linha['nivel'] > 0)
						<span class="text-muted me-1">↳</span>
						@endif
						<strong>{{ $linha['nome'] }}</strong>
					</div>
				</td>
				<td>{{ __moeda($linha['quantidade']) }}</td>
				<td>{{ $linha['unidade'] }}</td>
				<td>{{ $linha['categoria'] }}</td>
				<td>R$ {{ __moeda($linha['valor_compra']) }}</td>
				<td>R$ {{ __moeda($linha['total']) }}</td>
			</tr>
			@empty
			<tr>
				<td colspan="7" class="text-center text-muted">
					Nenhuma composição encontrada.
				</td>
			</tr>
			@endforelse
		</tbody>
	</table>
</div>