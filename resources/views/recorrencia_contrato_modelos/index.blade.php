@extends('layouts.app', ['title' => 'Contratos'])

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-3">
                    @can('recorrencia_contrato_create')
                    <a href="{{ route('recorrencia-contrato-modelos.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Contrato
                    </a>
                    @endcan
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('titulo', 'Pesquisar por título')!!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('status', 'Status', [
                                '' => 'Todos',
                                'gerado' => 'Gerado',
                                'assinado' => 'Assinado',
                                'cancelado' => 'Cancelado'
                            ])!!}
                        </div>

                        <div class="col-md-4 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i> Pesquisar
                            </button>

                            <a id="clear-filter" class="btn btn-danger" href="{{ route('recorrencia-contrato-modelos.index') }}">
                                <i class="ri-eraser-fill"></i> Limpar
                            </a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número</th>
                                    <th>Título</th>
                                    <th>Recorrência</th>
                                    <th>Modelo</th>
                                    <th>Status</th>
                                    <th>Geração</th>
                                    <th width="18%">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->numero }}</td>
                                    <td>{{ $item->titulo ?? '--' }}</td>
                                    <td>
                                        @if($item->recorrencia)
                                            #{{ $item->recorrencia->id }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>{{ optional($item->modelo)->nome ?? '--' }}</td>
                                    <td>
                                        @if($item->status == 'assinado')
                                            <span class="badge bg-success">Assinado</span>
                                        @elseif($item->status == 'cancelado')
                                            <span class="badge bg-danger">Cancelado</span>
                                        @else
                                            <span class="badge bg-primary">Gerado</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->data_geracao ? dateBr($item->data_geracao) : '--' }}</td>

                                    <td>
                                        <form action="{{ route('recorrencia-contrato-modelos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf

                                            @can('recorrencia_contrato_edit')
                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('recorrencia-contrato-modelos.edit', $item->id) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @can('recorrencia_contrato_delete')
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