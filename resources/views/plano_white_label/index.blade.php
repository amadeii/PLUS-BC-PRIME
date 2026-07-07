@extends('layouts.app', ['title' => 'Planos White Label'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-2">
                    <a href="{{ route('planos-white-label.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Plano
                    </a>
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('nome', 'Pesquisar por nome')!!}
                        </div>

                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('planos-white-label.index') }}"><i class="ri-eraser-fill"></i> Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                <div class="col-12 mt-3">
                    <div class="table-responsive">
                        <table class="table table-centered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>Valor mensal</th>
                                    <th>Valor por empresa</th>
                                    <th>Limite empresas</th>
                                    <th>Status</th>
                                    <th>Data de cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ __moeda($item->valor_mensal) }}</td>
                                    <td>{{ __moeda($item->valor_por_empresa) }}</td>
                                    <td>{{ $item->limite_empresas ?? 'Ilimitado' }}</td>
                                    <td>
                                        @if($item->ativo)
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                        <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </td>
                                    <td>{{ __data_pt($item->created_at, 1) }}</td>
                                    <td>
                                        <form action="{{ route('planos-white-label.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 100px">
                                            @method('delete')
                                            @csrf

                                            <a class="btn btn-warning btn-sm" href="{{ route('planos-white-label.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>

                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {!! $data->appends(request()->all())->links() !!}

            </div>
        </div>
    </div>
</div>
@endsection