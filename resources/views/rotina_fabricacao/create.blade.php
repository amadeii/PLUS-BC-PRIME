@extends('layouts.app', ['title' => 'Criar Rotina de Fabricação'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-header">
                <h4>Selecionar Produto para Criar Rotina</h4>

                <div style="text-align: right; margin-top: -35px;">
                    <a href="{{ route('rotina-fabricacao.index') }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>
            </div>

            <div class="card-body">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!!Form::text('nome', 'Pesquisar produto')!!}
                        </div>

                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i>Pesquisar
                            </button>
                            <a class="btn btn-danger" href="{{ route('rotina-fabricacao.create') }}">
                                <i class="ri-eraser-fill"></i>Limpar
                            </a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                <div class="col-12 mt-4">
                    <div class="table-responsive">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Produto</th>
                                    <th>Data de cadastro</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ __data_pt($item->created_at) }}</td>
                                    <td>
                                        <a class="btn btn-success btn-sm" href="{{ route('rotina-fabricacao.create-form', $item->id) }}">
                                            <i class="ri-add-circle-fill"></i> Criar Rotina
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum produto pendente para criar rotina</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <br>
                        {!! $data->appends(request()->all())->links() !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection