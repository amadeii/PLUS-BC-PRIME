@extends('layouts.app', ['title' => 'Funcionários x Jornada'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-2">
                    @can('ponto_funcionario_create')
                    <a href="{{ route('ponto-funcionario.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Vínculo
                    </a>
                    @endcan
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}

                    <div class="row mt-3">

                        <div class="col-md-3">
                            {!!Form::select('funcionario_id', 'Funcionário',
                            ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            ->id('func')
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::select('jornada_id', 'Jornada',
                            ['' => 'Selecione'] + $jornadas->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>

                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i> Pesquisar
                            </button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('ponto-funcionario.index') }}">
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
                                    <th>Funcionário</th>
                                    <th>Jornada</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->funcionario->nome ?? '' }}</td>
                                    <td>{{ $item->jornada->descricao ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->data_inicio)->format('d/m/Y') }}</td>
                                    <td>
                                        @if($item->data_fim)
                                        {{ \Carbon\Carbon::parse($item->data_fim)->format('d/m/Y') }}
                                        @else
                                        <span class="badge bg-success">Ativo</span>
                                        @endif
                                    </td>

                                    <td>
                                        <form action="{{ route('ponto-funcionario.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')

                                            @can('ponto_funcionario_edit')
                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('ponto-funcionario.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @csrf

                                            @can('ponto_funcionario_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan

                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nada encontrado</td>
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