@extends('layouts.app', ['title' => 'Cobranças Recorrentes'])
@section('content')
<div class="mt-1">
	<div class="row">
		<div class="card">
			<div class="card-body">
				<div class="col-md-3">
					@can('recorrencia_create')
					<a href="{{ route('recorrencias.create') }}" class="btn btn-success">
						<i class="ri-add-circle-fill"></i>
						Nova Recorrência
					</a>
					@endcan
				</div>

				<hr class="mt-3">

				<div class="col-lg-12">
					{!!Form::open()->fill(request()->all())->get()!!}
					<div class="row mt-3">
						<div class="col-md-3">
							{!!Form::text('descricao', 'Pesquisar por descrição')!!}
						</div>

						<div class="col-md-3">
							{!!Form::select('cliente_id', 'Cliente', ['' => 'Todos'] + $clientes->pluck('razao_social', 'id')->all())->attrs(['class' => 'select2'])!!}
						</div>

						<div class="col-md-2">
							{!!Form::select('status', 'Status', [
							'' => 'Todos',
							'ativa' => 'Ativa',
							'pausada' => 'Pausada',
							'cancelada' => 'Cancelada',
							'finalizada' => 'Finalizada'
							])!!}
						</div>

						<div class="col-md-4 text-left">
							<br>
							<button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
							<a id="clear-filter" class="btn btn-danger" href="{{ route('recorrencias.index') }}"><i class="ri-eraser-fill"></i> Limpar</a>
						</div>
					</div>
					{!!Form::close()!!}
				</div>

				<div class="col-md-12 mt-3">
					<div class="table-responsive-sm">
						<table class="table table-striped table-centered mb-0">
							<thead class="table-dark">
								<tr>
									<th>Descrição</th>
									<th>Cliente</th>
									<th>Valor</th>
									<th>Periodicidade</th>
									<th>Próxima cobrança</th>
									<th>Status</th>
									<th width="22%">Ações</th>
								</tr>
							</thead>
							<tbody>
								@forelse($data as $item)
								<tr>
									<td>{{ $item->descricao }}</td>
									<td>{{ $item->cliente->razao_social ?? '' }}</td>
									<td>R$ {{ __moeda($item->valor) }}</td>
									<td>{{ ucfirst($item->periodicidade) }}</td>
									<td>{{ $item->proxima_cobranca ? __data_pt($item->proxima_cobranca, 0) : '--' }}</td>
									<td>
										@if($item->status == 'ativa')
										<span class="badge bg-success">Ativa</span>
										@elseif($item->status == 'pausada')
										<span class="badge bg-warning">Pausada</span>
										@elseif($item->status == 'cancelada')
										<span class="badge bg-danger">Cancelada</span>
										@else
										<span class="badge bg-secondary">Finalizada</span>
										@endif
									</td>
									<td>
										<form action="{{ route('recorrencias.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
											@method('delete')
											@csrf

											@can('recorrencia_edit')
											<a class="btn btn-warning btn-sm text-white" href="{{ route('recorrencias.edit', [$item->id]) }}">
												<i class="ri-pencil-fill"></i>
											</a>
											@endcan

											@can('recorrencia_view')
											<a class="btn btn-dark btn-sm text-white" href="{{ route('recorrencias.show', [$item->id]) }}">
												<i class="ri-eye-line"></i>
											</a>
											@endcan

											@can('recorrencia_delete')
											<button type="button" class="btn btn-delete btn-sm btn-danger">
												<i class="ri-delete-bin-line"></i>
											</button>
											@endcan
										</form>
									</td>
								</tr>
								@empty
								<tr>
									<td colspan="7" class="text-center">Nada encontrado</td>
								</tr>
								@endforelse
							</tbody>
						</table>
						<br>
					</div>
				</div>

				{!! $data->appends(request()->all())->links() !!}
			</div>
		</div>
	</div>
</div>
@endsection