@extends('layouts.app', ['title' => 'Imprimir Etiquetas'])

@section('content')

<div class="card mt-1">
	<div class="card-header">
		<h4>Imprimir Etiquetas - {{ $item->nome }}</h4>

		<div style="text-align:right;margin-top:-35px;">
			<a href="{{ route('etiqueta-modelos.index') }}" class="btn btn-danger btn-sm">
				<i class="ri-arrow-left-double-fill"></i>
				Voltar
			</a>
		</div>
	</div>

	<div class="card-body">

		<form method="post" action="{{ route('etiqueta-modelos.imprimir-produtos', $item->id) }}" target="_blank">
			@csrf

			<div class="row">

				<div class="col-md-8">
					<label>Produto</label>

					<select class="form-control" id="inp-produto_id" style="width:100%">
						<option value="">Digite para buscar o produto</option>
					</select>
				</div>

				<div class="col-md-2">
					<label>&nbsp;</label>

					<button type="button" class="btn btn-primary w-100" id="btnAddProduto">
						<i class="ri-add-line"></i>
						Adicionar
					</button>
				</div>

			</div>

			<hr>

			<div class="table-responsive">

				<table class="table table-striped table-centered" id="table-produtos">
					<thead class="table-dark">
						<tr>
							<th>Produto</th>
							<th width="120">Qtd.</th>
							<th width="80">Ação</th>
						</tr>
					</thead>

					<tbody>

					</tbody>

				</table>

			</div>

			<div class="text-end mt-3">

				<button type="submit" class="btn btn-success">
					<i class="ri-printer-fill"></i>
					Gerar Impressão
				</button>

			</div>

		</form>

	</div>
</div>

@endsection

@section('js')

<script>

	let produtosSelecionados = [];

	$('#btnAddProduto').click(function(){

		let produtoId = $('#inp-produto_id').val();
		let produtoNome = $('#inp-produto_id option:selected').text();

		if(!produtoId){
			swal("Atenção", "Selecione um produto", "warning");
			return;
		}

		if(produtosSelecionados.includes(produtoId)){
			swal("Atenção", "Produto já adicionado", "warning");
			return;
		}

		produtosSelecionados.push(produtoId);

		let html = `
		<tr data-produto-id="${produtoId}">
		<td>
		${produtoNome}

		<input
		type="hidden"
		name="produtos[${produtoId}][id]"
		value="${produtoId}">
		</td>

		<td>
		<input
		type="number"
		class="form-control"
		name="produtos[${produtoId}][qtd]"
		value="1"
		min="1">
		</td>

		<td>

		<button
		type="button"
		class="btn btn-danger btn-sm btn-remove-produto">

		<i class="ri-delete-bin-line"></i>

		</button>

		</td>
		</tr>
		`;

		$('#table-produtos tbody').append(html);

		$('#inp-produto_id').val('').trigger('change');
	});

	$(document).on('click', '.btn-remove-produto', function(){

		let tr = $(this).closest('tr');

		let produtoId = tr.data('produto-id').toString();

		produtosSelecionados = produtosSelecionados.filter(
			item => item != produtoId
			);

		tr.remove();
	});

</script>

@endsection