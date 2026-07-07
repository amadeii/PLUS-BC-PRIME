@extends('layouts.app', ['title' => 'Registros de Ponto'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <div class="col-md-2">
                    @can('ponto_registro_create')
                    <a href="{{ route('ponto-registro.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Registrar Ponto
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
                            ->attrs(['class' => 'select2'])->id('func')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tipo', 'Tipo', [
                            '' => 'Todos',
                            'entrada' => 'Entrada',
                            'intervalo_inicio' => 'Intervalo Início',
                            'intervalo_fim' => 'Intervalo Fim',
                            'saida' => 'Saída'
                            ])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::date('data_inicial', 'Data inicial')!!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::date('data_final', 'Data final')!!}
                        </div>

                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="ri-search-line"></i> Pesquisar
                            </button>
                            <a class="btn btn-danger" href="{{ route('ponto-registro.index') }}">
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
                                    <th>Tipo</th>
                                    <th>Data/Hora</th>
                                    <th>Status</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->funcionario->nome ?? '' }}</td>

                                    <td>
                                        @php
                                        $tipos = [
                                        'entrada' => 'Entrada',
                                        'intervalo_inicio' => 'Intervalo Início',
                                        'intervalo_fim' => 'Intervalo Fim',
                                        'saida' => 'Saída'
                                        ];
                                        @endphp
                                        {{ $tipos[$item->tipo] ?? $item->tipo }}
                                    </td>

                                    <td>{{ \Carbon\Carbon::parse($item->data_hora)->format('d/m/Y H:i') }}</td>

                                    <td>
                                        @if($item->status == 'valido')
                                        <span class="badge bg-success">Válido</span>
                                        @elseif($item->status == 'suspeito')
                                        <span class="badge bg-warning">Suspeito</span>
                                        @else
                                        <span class="badge bg-dark">Ajustado</span>
                                        @endif
                                    </td>

                                    <td>
                                        <form action="{{ route('ponto-registro.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')

                                            @csrf

                                            @can('ponto_registro_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan

                                            <a class="btn btn-dark btn-sm text-white" href="{{ route('ponto-registro.show', [$item->id]) }}">
                                                <i class="ri-eye-fill"></i>
                                            </a>

                                            @can('ponto_ajuste_create')
                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('ponto-ajuste.create', [$item->id]) }}" title="Ajuste de ponto">
                                                <i class="ri-edit-2-line"></i>
                                            </a>
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