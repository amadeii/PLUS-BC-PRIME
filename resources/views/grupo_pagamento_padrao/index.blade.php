@extends('layouts.app', ['title' => 'Grupo Pagamento Padrão'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-3">

                    <a href="{{ route('grupo-pagamento-padrao.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Grupo
                    </a>

                </div>

                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Pagador</th>
                                    <th>Documento</th>
                                    <th>Transporte</th>
                                    <th>Parcela</th>
                                    <th>Banco</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->nome_pagador }}</td>
                                    <td>{{ $item->documento_pagador }}</td>
                                    <td>R$ {{ __moeda($item->valor_transporte) }}</td>

                                    <td>R$ {{ __moeda($item->valor_parcela) }}</td>
                                    <td>{{ $item->codigo_banco }}</td>

                                    <td>
                                        <form action="{{ route('grupo-pagamento-padrao.destroy', $item->id) }}" method="post">
                                            @method('delete')
                                            @csrf
                                            <a href="{{ route('grupo-pagamento-padrao.edit', $item->id) }}" class="btn btn-warning btn-sm text-white">
                                                <i class="ri-pencil-fill"></i>
                                            </a>

                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
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
                    </div>
                </div>

                {!! $data->appends(request()->all())->links() !!}

            </div>
        </div>
    </div>
</div>
@endsection