@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
	<thead>
		<tr>
			<th>Produto</th>
			<th>Quantidade</th>
			<th>Estoque</th>
			<th>Tipo</th>
			<th>Usuário</th>
			<th>Data</th>
			
		</tr>
	</thead>
	<tbody>
		@foreach($data as $key => $item)
		<tr class="@if($key%2 == 0) pure-table-odd @endif">
			<td style="width: 400px;">{{ $item->produto->nome }}</td>
			<td>
				@if(!$item->produto->unidadeDecimal())
				{{ number_format($item->quantidade, 0, '.', '') }}
				@else
				{{ number_format($item->quantidade, 3, '.', '') }}
				@endif
			</td>
			<td>
				@if(!$item->produto->unidadeDecimal())
				{{ number_format($item->estoque_atual, 0, '.', '') }}
				@else
				{{ number_format($item->estoque_atual, 3, '.', '') }}
				@endif
			</td>
			<td>{{ $item->tipoTransacao() }}</td>
			<td>{{ $item->user->name }}</td>
			<td>{{ __data_pt($item->created_at) }}</td>
			
		</tr>
		@endforeach
	</tbody>
</table>

@endsection
