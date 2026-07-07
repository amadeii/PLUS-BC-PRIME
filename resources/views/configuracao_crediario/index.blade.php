@extends('layouts.app', ['title' => 'Configuração de Crediário'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-3">
                    @can('configuracao_crediario_create')
                    <a href="{{ route('configuracao-crediario.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Configuração
                    </a>
                    @endcan
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}
                    <div class="row mt-3">
                        <div class="col-md-2">
                            {!!Form::select('status', 'Status', ['' => 'Todos', '1' => 'Ativo', '0' => 'Inativo'])->attrs(['class' => 'form-select'])!!}
                        </div>

                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('configuracao-crediario.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Faixa de valor</th>
                                    <th>Máx. parcelas</th>
                                    <th>Sem juros até</th>
                                    <th>Juros</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>
                                        R$ {{ __moeda($item->valor_minimo) }}
                                        até
                                        {{ $item->valor_maximo ? 'R$ ' . __moeda($item->valor_maximo) : 'Sem limite' }}
                                    </td>
                                    <td>{{ $item->maximo_parcelas }}x</td>
                                    <td>{{ $item->parcelas_sem_juros }}x</td>
                                    <td>{{ __moeda($item->juros_percentual) }}%</td>
                                    <td>
                                        1ª em {{ $item->primeiro_vencimento_dias }} dias<br>
                                        <small class="text-muted">Intervalo {{ $item->intervalo_parcelas_dias }} dias</small>
                                    </td>
                                    <td>
                                        @if($item->ativo)
                                        <span class="badge bg-success">Ativo</span>
                                        @else
                                        <span class="badge bg-danger">Inativo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('configuracao-crediario.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf

                                            @can('configuracao_crediario_edit')
                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('configuracao-crediario.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan

                                            @can('configuracao_crediario_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
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
                        <br>
                    </div>
                </div>

                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection