@extends('layouts.app', ['title' => 'Agendamento'])

@section('css')
<style>
    @page{ size:auto; margin:0mm; }
    @media print{
        .print{ margin:10px; }
        .d-print-none{ display:none!important; }
    }
    .info-line{ margin-bottom:6px; font-size:14px; }
    .info-line b{ color:#6c757d; }
    .total-box{ background:#f8f9fa; border-radius:10px; padding:14px; }

    .cliente-avatar{
        width:70px;
        height:70px;
        border-radius:18px;
        object-fit:cover;
        border:3px solid #fff;
        box-shadow:0 4px 12px rgba(0,0,0,.10);
        background:#fff;
    }

    .cliente-avatar-box{
        background:rgba(var(--bs-primary-rgb), .10);
        padding:6px;
        border-radius:20px;
    }
</style>
@endsection

@section('content')
<div class="mt-1 print">

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h4 class="text-primary mb-1">Agendamento #{{ $item->numero_sequencial }}</h4>
                    <p class="text-muted mb-0">Detalhes do atendimento e serviços</p>
                </div>

                @can('clientes_edit')
                <a class="btn btn-sm btn-warning d-print-none" href="{{ route('clientes.edit', [$item->cliente_id]) }}">
                    <i class="ri-edit-line"></i> Editar cliente
                </a>
                @endcan
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-md-8">

                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <div class="cliente-avatar-box me-3">
                                    <img class="cliente-avatar" 
                                    src="{{ $item->cliente->img }}" 
                                    onerror="this.src='/imgs/no-image.png'">
                                </div>

                                <div>
                                    <h5 class="mb-1 fw-bold">{{ $item->cliente->razao_social }}</h5>
                                    <small class="text-muted">Dados do cliente</small>
                                </div>

                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 p-2">
                                        <small class="text-muted d-block">CPF/CNPJ</small>
                                        <strong>{{ $item->cliente->cpf_cnpj }}</strong>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="bg-light rounded-3 p-2">
                                        <small class="text-muted d-block">Telefone</small>
                                        <strong>{{ $item->cliente->telefone }}</strong>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block">Serviços</small>
                                        <strong class="text-primary fs-5">{{ sizeof($item->itens) }}</strong>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block">Desconto</small>
                                        <strong class="text-danger fs-6">{{ __moeda($item->desconto) }}</strong>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="bg-light rounded-3 p-2 text-center">
                                        <small class="text-muted d-block">Atendente</small>
                                        <strong>{{ $item->funcionario ? $item->funcionario->nome : '--' }}</strong>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>
                <div class="col-md-4">
                    @can('agendamento_edit')
                    <form method="POST" action="{{ route('agendamentos.update', [$item->id]) }}" class="d-print-none">
                        @method('put')
                        @csrf

                        <div class="row g-2">
                            <div class="col-md-6">
                                {!! Form::tel('inicio', 'Início')->attrs(['class' => 'timer'])->value(\Carbon\Carbon::parse($item->inicio)->format('H:i')) !!}
                            </div>

                            <div class="col-md-6">
                                {!! Form::tel('termino', 'Término')->attrs(['class' => 'timer'])->value(\Carbon\Carbon::parse($item->termino)->format('H:i')) !!}
                            </div>

                            <div class="col-12">
                                {!! Form::date('data', 'Data')->attrs(['class' => 'date'])->value($item->data) !!}
                            </div>

                            <div class="col-12">
                                <button class="btn btn-success w-100">
                                    <i class="ri-check-line"></i> Salvar alterações
                                </button>
                            </div>
                        </div>
                    </form>
                    @endcan
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Serviço</th>
                            <th class="text-center">Quantidade</th>
                            <th class="text-end">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->itens as $i)
                        <tr>
                            <td class="fw-semibold">{{ $i->servico->nome }}</td>
                            <td class="text-center">{{ number_format($i->quantidade, 0) }}</td>
                            <td class="text-end">{{ __moeda($i->valor) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-end fw-bold">Total</td>
                            <td class="text-end text-success fw-bold">R$ {{ __moeda($item->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-print-none mt-4 d-flex justify-content-between flex-wrap gap-2">

                @can('agendamento_delete')
                <form action="{{ route('agendamentos.destroy', $item->id) }}" method="post" id="form-{{ $item->id }}">
                    @method('delete')
                    @csrf
                    <button type="button" class="btn btn-danger btn-delete">
                        <i class="ri-delete-bin-line"></i> Remover
                    </button>
                </form>
                @endcan

                <form method="post" action="{{ route('agendamentos.update-status', [$item->id]) }}" id="form-confirm-{{ $item->id }}" class="d-flex flex-wrap gap-2">
                    @method('PUT')
                    @csrf

                    @if($item->nfce_id == null)
                    @can('pdv_create')
                    <a href="{{ route('agendamentos.pdv', [$item->id]) }}" class="btn btn-dark">
                        <i class="ri-price-tag-3-fill"></i> Finalizar no PDV
                    </a>
                    @endcan
                    @endif

                    @if($item->status == 0)
                    @can('agendamento_edit')
                    <button type="button" class="btn btn-success btn-confirm">
                        <i class="ri-check-line"></i> Alterar para Finalizado
                    </button>
                    @endcan
                    @endif

                    <a href="javascript:window.print()" class="btn btn-primary">
                        <i class="ri-printer-line"></i> Imprimir
                    </a>
                </form>

            </div>

        </div>
    </div>

</div>
@endsection