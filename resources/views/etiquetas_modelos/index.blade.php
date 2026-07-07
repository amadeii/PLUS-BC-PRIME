@extends('layouts.app', ['title' => 'Modelos de Etiqueta'])

@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-3">
                    <a href="{{ route('etiqueta-modelos.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Modelo
                    </a>
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}

                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('pesquisa', 'Pesquisar por nome')!!}
                        </div>

                        <div class="col-md-4 text-left">
                            <br>

                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i>
                                Pesquisar
                            </button>

                            <a id="clear-filter" class="btn btn-danger" href="{{ route('etiqueta-modelos.index') }}">
                                <i class="ri-eraser-fill"></i>
                                Limpar
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
                                    <th>Largura</th>
                                    <th>Altura</th>
                                    <th>Etiquetas/Linha</th>
                                    <th>Status</th>
                                    <th width="20%">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->nome }}</td>

                                    <td>
                                        {{ number_format($item->largura, 2, ',', '.') }} mm
                                    </td>

                                    <td>
                                        {{ number_format($item->altura, 2, ',', '.') }} mm
                                    </td>

                                    <td>
                                        {{ $item->etiquetas_por_linha }}
                                    </td>

                                    <td>
                                        @if($item->ativo)
                                        <span class="badge bg-success">
                                            Ativo
                                        </span>
                                        @else
                                        <span class="badge bg-danger">
                                            Inativo
                                        </span>
                                        @endif
                                    </td>

                                    <td>
                                        <form action="{{ route('etiqueta-modelos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @csrf
                                            @method('delete')

                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('etiqueta-modelos.edit', $item->id) }}" title="Editar">
                                                <i class="ri-pencil-fill"></i>
                                            </a>

                                            <a class="btn btn-dark btn-sm text-white" href="{{ route('etiqueta-modelos.editor', $item->id) }}"
                                                title="Editor Visual">
                                                <i class="ri-layout-grid-fill"></i>
                                            </a>

                                            <a class="btn btn-primary btn-sm text-white" href="{{ route('etiqueta-modelos.selecionar-produtos', $item->id) }}" title="Imprimir Etiquetas">
                                                <i class="ri-printer-fill"></i>
                                            </a>

                                            <button type="button" class="btn btn-delete btn-sm btn-danger" title="Excluir">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Nenhum modelo encontrado
                                    </td>
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