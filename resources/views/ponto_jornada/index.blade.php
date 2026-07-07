@extends('layouts.app', ['title' => 'Jornadas de Trabalho'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    @can('ponto_jornada_create')
                    <a href="{{ route('ponto-jornada.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Jornada
                    </a>
                    @endcan
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('descricao', 'Pesquisar por descrição')!!}
                        </div>
                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i> Pesquisar
                            </button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('ponto-jornada.index') }}">
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
                                    <th>Descrição</th>
                                    <th>Intervalo</th>
                                    <th>Tolerância Atraso</th>
                                    <th>Hora Extra Após</th>
                                    <th>Status</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->descricao }}</td>
                                    <td>{{ $item->intervalo_minutos }} min</td>
                                    <td>{{ $item->tolerancia_atraso }} min</td>
                                    <td>{{ $item->hora_extra_apos_minutos }} min</td>
                                    <td>
                                        @if($item->ativo)
                                        <span class="badge bg-success">Ativo</span>
                                        @else
                                        <span class="badge bg-danger">Inativo</span>
                                        @endif
                                    </td>

                                    <td>
                                        <form action="{{ route('ponto-jornada.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')

                                            @can('ponto_jornada_edit')
                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('ponto-jornada.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @csrf

                                            @can('ponto_jornada_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan

                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nada encontrado</td>
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