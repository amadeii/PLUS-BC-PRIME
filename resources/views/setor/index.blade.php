@extends('layouts.app', ['title' => 'Setores'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-2">
                    @can('setor_create')
                    <a href="{{ route('setor.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Setor
                    </a>
                    @endcan
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-2">
                            {!!Form::text('nome', 'Pesquisar por nome')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!! Form::text('codigo', 'Pesquisar por código') !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i>Pesquisar
                            </button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('setor.index') }}">
                                <i class="ri-eraser-fill"></i>Limpar
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
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Horas/Dia</th>
                                    <th>Custo Hora</th>
                                    <th>Eficiência</th>
                                    <th>Centro de Custo</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->descricao }}</td>
                                    <td>{{ $item->horas_dia }}</td>
                                    <td>R$ {{ __moeda($item->custo_hora) }}</td>
                                    <td>{{ __moeda($item->eficiencia) }}%</td>
                                    <td>{{ $item->centroCusto->nome }}</td>

                                    <td>
                                        <form action="{{ route('setor.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')

                                            @can('setor_edit')
                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('setor.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @csrf

                                            @can('setor_delete')
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