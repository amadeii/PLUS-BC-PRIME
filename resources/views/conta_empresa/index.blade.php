@extends('layouts.app', ['title' => 'Contas da empresa'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12 d-flex gap-2 flex-wrap">
                    @can('contas_empresa_create')
                    <a href="{{ route('contas-empresa.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Nova Conta
                    </a>
                    @endcan

                    @can('contas_empresa_create')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTransferencia">
                        <i class="ri-exchange-funds-line"></i>
                        Transferência entre contas
                    </button>
                    @endcan
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!!Form::select('cliente_id', 'Pesquisar por nome')->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data Final')
                            !!}
                        </div>

                        @if(__countLocalAtivo() > 1)
                        <div class="col-md-2">
                            {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        @endif
                        <div class="col-md-2 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('conta-receber.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nome</th>
                                    <th>Plano de conta</th>
                                    <th>Banco</th>
                                    <th>Agência</th>
                                    <th>Conta</th>
                                    <th>Status</th>
                                    @if(__countLocalAtivo() > 1)
                                    <th>Local</th>
                                    @endif
                                    <th>Saldo</th>
                                    <th width="10%">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td data-label="Nome">{{ $item->nome }}</td>
                                    <td data-label="Plano de conta">{{ $item->plano->descricao }}</td>
                                    <td data-label="Banco">{{ $item->banco }}</td>
                                    <td data-label="Agência">{{ $item->agencia }}</td>
                                    <td data-label="Conta">{{ $item->conta }}</td>
                                    <td data-label="Status">
                                        @if($item->status)
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        @else
                                        <i class="ri-close-circle-fill text-danger"></i>
                                        @endif
                                    </td>
                                    @if(__countLocalAtivo() > 1)
                                    <td data-label="Local" class="text-danger">{{ $item->localizacao->descricao }}</td>
                                    @endif
                                    <td data-label="Saldo">{{ __moeda($item->saldo) }}</td>
                                    <td>
                                        <form action="{{ route('contas-empresa.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 150px;">
                                            @csrf
                                            @method('delete')
                                            @can('contas_empresa_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('contas-empresa.edit', [$item->id]) }}">
                                                <i class="ri-pencil-fill"></i>
                                            </a>
                                            @endcan
                                            @can('contas_empresa_delete')
                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                            <a href="{{ route('contas-empresa.show', $item) }}" class="btn btn-dark btn-sm text-white">
                                                <i class="ri-list-indefinite"></i>
                                            </a>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTransferencia" tabindex="-1" aria-labelledby="modalTransferenciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form method="POST" action="{{ route('contas-empresa.transferencia-store') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTransferenciaLabel">
                        <i class="ri-exchange-funds-line"></i>
                        Nova Transferência
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            {!! Form::select('conta_saida_id', 'Conta de saída',
                            ['' => 'Selecione'] + $data->pluck('nome', 'id')->all()
                            )->attrs(['class' => 'form-select select2'])->required() !!}
                        </div>

                        <div class="col-md-12 mb-3">
                            {!! Form::select('conta_entrada_id', 'Conta de entrada',
                            ['' => 'Selecione'] + $data->pluck('nome', 'id')->all()
                            )->attrs(['class' => 'form-select select2'])->required() !!}
                        </div>

                        <div class="col-md-6 mb-3">
                            {!! Form::text('valor', 'Valor')->attrs([
                            'class' => 'form-control moeda',
                            'placeholder' => '0,00'
                            ])->required() !!}
                        </div>

                        <div class="col-md-6 mb-3">
                            {!! Form::select('tipo_pagamento', 'Tipo de pagamento', [
                            '' => 'Selecione'] + \App\Models\Nfe::tiposPagamento())->attrs(['class' => 'form-select'])->required() !!}
                        </div>

                        <!-- <div class="col-md-6 mb-3">
                            {!! Form::date('data_transferencia', 'Data')->attrs([
                            'class' => 'form-control'
                            ]) !!}
                        </div>
 -->
                        <div class="col-md-12 mb-2">
                            {!! Form::textarea('descricao', 'Descrição')
                            ->attrs([
                            'class' => 'form-control',
                            'rows' => 3,
                            'placeholder' => 'Digite uma descrição se necessário'
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-line"></i> Salvar Transferência
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')

@endsection
