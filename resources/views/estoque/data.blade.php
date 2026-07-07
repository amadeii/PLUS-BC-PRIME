@extends('layouts.app', ['title' => 'Estoque por data'])

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="row align-items-center">
                    <div class="col-md-8 col-12">
                        <h4 class="mb-1">
                            <i class="ri-calendar-check-line text-primary"></i>
                            Estoque por data
                        </h4>
                        <p class="text-muted mb-0">
                            Consulte a posição do estoque em uma data específica
                        </p>
                    </div>

                    <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                        <a href="{{ route('estoque.index') }}" class="btn btn-danger btn-sm">
                            <i class="ri-arrow-left-line"></i> Voltar
                        </a>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-xl-4 col-md-6">
                        <div class="card card-stats">
                            <div class="card-body">
                                <h4 class="fw-bold text-primary mb-0">{{ $totalProdutos }}</h4>
                                <small class="text-muted">Produtos encontrados</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-stats">
                            <div class="card-body">
                                <h4 class="fw-bold text-danger mb-0">R$ {{ __moeda($totalCompra) }}</h4>
                                <small class="text-muted">Total compra na data</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card card-stats">
                            <div class="card-body">
                                <h4 class="fw-bold text-success mb-0">R$ {{ __moeda($totalVenda) }}</h4>
                                <small class="text-muted">Total venda na data</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row mt-3">

                        <div class="col-md-2">
                            {!! Form::date('data_consulta', 'Data do estoque')->value($dataConsulta) !!}
                        </div>

                        <div class="col-md-3">
                            {!! Form::text('produto', 'Pesquisar por nome') !!}
                        </div>

                        <div class="col-md-3">
                            {!! Form::text('codigo_barras', 'Código de barras') !!}
                        </div>

                        <div class="col-md-2">
                            {!! Form::select('categoria_id', 'Categoria', ['' => 'Selecione'] + $categorias->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                        </div>

                        <div class="col-md-2 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
                            <a class="btn btn-danger" href="{{ route('estoque.data') }}"><i class="ri-eraser-fill"></i> Limpar</a>
                        </div>

                    </div>
                    {!! Form::close() !!}
                </div>

                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Código de barras</th>
                                    <th class="text-end">Estoque na data</th>
                                    <th class="text-end">Valor compra</th>
                                    <th class="text-end">Valor venda</th>
                                    <th class="text-end">Total compra</th>
                                    <th class="text-end">Total venda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->nome }}</strong>
                                        <br>
                                        <small class="text-muted">#{{ $item->id }}</small>
                                    </td>

                                    <td>{{ $item->categoria->nome ?? '--' }}</td>

                                    <td>{{ $item->codigo_barras ?? '--' }}</td>

                                    <td class="text-end">
                                        <strong>{{ number_format($item->estoque_data, 4, ',', '.') }}</strong>
                                    </td>

                                    <td class="text-end">
                                        R$ {{ __moeda($item->valor_compra) }}
                                    </td>

                                    <td class="text-end">
                                        R$ {{ __moeda($item->valor_unitario) }}
                                    </td>

                                    <td class="text-end text-danger">
                                        R$ {{ __moeda($item->estoque_data * $item->valor_compra) }}
                                    </td>

                                    <td class="text-end text-success">
                                        R$ {{ __moeda($item->estoque_data * $item->valor_unitario) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="ri-inbox-line"></i>
                                        Nenhum produto encontrado para esta data.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <br>
                {!! $data->appends(request()->all())->links() !!}

            </div>
        </div>
    </div>
</div>
@endsection