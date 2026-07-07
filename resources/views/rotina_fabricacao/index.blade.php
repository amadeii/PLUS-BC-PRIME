@extends('layouts.app', ['title' => 'Rotinas de Fabricação'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-3">
                    @can('rotina_fabricacao_create')
                    <a href="{{ route('rotina-fabricacao.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Rotina
                    </a>
                    @endcan
                </div>

                <hr class="mt-3">

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
                            <a class="btn btn-danger" href="{{ route('rotina-fabricacao.index') }}">
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
                                    <th>Lote mínimo</th>
                                    <th>Atualizado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->nome }}</td>
                                    <td>{{ $item->rotinaFabricacao->lote_minimo ? number_format($item->rotinaFabricacao->lote_minimo, 0) : '--' }}</td>
                                    <td>{{ $item->rotinaFabricacao ? __data_pt($item->rotinaFabricacao->updated_at, 1) : '--' }}</td>
                                    <td>
                                        <form style="width: 110px;" action="{{ route('rotina-fabricacao.destroy', $item->rotinaFabricacao->id) }}" method="post" id="form-{{ $item->rotinaFabricacao->id }}">
                                            @method('delete')
                                            @csrf

                                            @can('rotina_fabricacao_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('rotina-fabricacao.edit', $item->rotinaFabricacao->id) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @can('rotina_fabricacao_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan

                                            <a href="{{ route('produtos.rotina.projecao-custo', $item->rotinaFabricacao->id) }}" class="btn btn-info btn-sm">
                                                <i class="ri-calculator-line"></i> Projeção de Custo
                                            </a>
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
                        {!! $data->appends(request()->all())->links() !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection