@extends('layouts.app', ['title' => 'Regra de Comunicação'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-3">
                    <a href="{{ route('recorrencia-regra-comunicacao.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Regra
                    </a>
                </div>

                <hr class="mt-3">

                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())->get()!!}
                    <div class="row mt-3">
                        <div class="col-md-3">
                            {!!Form::text('nome', 'Pesquisar por nome')!!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('gatilho', 'Gatilho', [
                                '' => 'Todos',
                                'ao_gerar' => 'Ao gerar cobrança',
                                'antes_vencimento' => 'Antes do vencimento',
                                'no_vencimento' => 'No vencimento',
                                'apos_vencimento' => 'Após vencimento'
                            ])->attrs(['class' => 'form-select'])!!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('ativo', 'Status', [
                                '' => 'Todos',
                                '1' => 'Ativo',
                                '0' => 'Inativo'
                            ])->attrs(['class' => 'form-select'])!!}
                        </div>

                        <div class="col-md-4 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('recorrencia-regra-comunicacao.index') }}"><i class="ri-eraser-fill"></i> Limpar</a>
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
                                    <th>Gatilho</th>
                                    <th>Dias</th>
                                    <th>Canais</th>
                                    <th>Status</th>
                                    <th width="15%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ $item->nome ?? '--' }}</td>
                                    <td>
                                        @if($item->gatilho == 'ao_gerar')
                                        Ao gerar cobrança
                                        @elseif($item->gatilho == 'antes_vencimento')
                                        Antes do vencimento
                                        @elseif($item->gatilho == 'no_vencimento')
                                        No vencimento
                                        @elseif($item->gatilho == 'apos_vencimento')
                                        Após vencimento
                                        @else
                                        {{ $item->gatilho }}
                                        @endif
                                    </td>
                                    <td>{{ $item->dias ?? 0 }}</td>
                                    <td>
                                        @if($item->email_ativo)
                                        <span class="badge bg-primary">E-mail</span>
                                        @endif

                                        @if($item->whatsapp_ativo)
                                        <span class="badge bg-success">WhatsApp</span>
                                        @endif

                                        @if(!$item->email_ativo && !$item->whatsapp_ativo)
                                        <span class="badge bg-secondary">Nenhum</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->ativo)
                                        <span class="badge bg-success">Ativo</span>
                                        @else
                                        <span class="badge bg-danger">Inativo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('recorrencia-regra-comunicacao.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf

                                            <a class="btn btn-warning btn-sm text-white" href="{{ route('recorrencia-regra-comunicacao.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>

                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nada encontrado</td>
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