@extends('layouts.app', ['title' => 'Logs Cron Nuvem Shop'])
@section('content')
<div class="card mt-1">
    <div class="card-header">
        <h4>Logs Cron Nuvem Shop</h4>

    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Processados</th>
                        <th>Novos</th>
                        <th>Atualizados</th>
                        <th>OS Criadas</th>
                        <th>OS Erro</th>
                        <th>Status</th>
                        <th>Mensagem</th>
                        <th>Início</th>
                        <th>Fim</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                    <tr>
                        <td>{{ $item->pedidos_processados }}</td>
                        <td>{{ $item->pedidos_novos }}</td>
                        <td>{{ $item->pedidos_atualizados }}</td>
                        <td>{{ $item->ordens_separacao_criadas }}</td>
                        <td>{{ $item->ordens_separacao_erro }}</td>
                        <td>
                            @if($item->status == 'sucesso')
                            <span class="badge bg-success">Sucesso</span>
                            @elseif($item->status == 'erro')
                            <span class="badge bg-danger">Erro</span>
                            @else
                            <span class="badge bg-warning">Processando</span>
                            @endif
                        </td>
                        <td>{{ $item->mensagem }}</td>
                        <td>{{ __data_pt($item->iniciado_em) }}</td>
                        <td>{{ __data_pt($item->finalizado_em) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $data->links() }}
        </div>
    </div>
</div>
@endsection
