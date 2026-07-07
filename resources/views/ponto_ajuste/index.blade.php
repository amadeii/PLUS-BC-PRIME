@extends('layouts.app', ['title' => 'Ajustes de Ponto'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

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

                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i> Pesquisar
                            </button>
                            <a class="btn btn-danger" href="{{ route('ponto-ajuste.index') }}">
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
                                    <th>Motivo</th>
                                    <th>Usuário</th>
                                    <th>Data Ajuste</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->registro->funcionario->nome ?? '' }}</td>
                                    <td>{{ $item->motivo }}</td>
                                    <td>{{ $item->usuario->name ?? '' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a class="btn btn-dark btn-sm text-white" href="{{ route('ponto-ajuste.show', [$item->id]) }}">
                                            <i class="ri-eye-fill"></i>
                                        </a>
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