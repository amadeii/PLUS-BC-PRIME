@extends('layouts.app', ['title' => 'Detalhes da Recorrência'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Detalhes da Recorrência</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('recorrencias.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>

    <div class="card-body">

        <div class="row g-2">
            <div class="col-md-4">
                <label>Cliente</label>
                <input type="text" class="form-control" value="{{ $item->cliente->razao_social ?? '' }}" disabled>
            </div>

            <div class="col-md-4">
                <label>Descrição</label>
                <input type="text" class="form-control" value="{{ $item->descricao }}" disabled>
            </div>

            <div class="col-md-2">
                <label>Valor</label>
                <input type="text" class="form-control" value="R$ {{ __moeda($item->valor) }}" disabled>
            </div>

            <div class="col-md-2">
                <label>Status</label>
                <br>
                @if($item->status == 'ativa')
                <span class="badge bg-success mt-2">Ativa</span>
                @elseif($item->status == 'pausada')
                <span class="badge bg-warning mt-2">Pausada</span>
                @elseif($item->status == 'cancelada')
                <span class="badge bg-danger mt-2">Cancelada</span>
                @else
                <span class="badge bg-secondary mt-2">Finalizada</span>
                @endif
            </div>

            <div class="col-md-2 mt-2">
                <label>Periodicidade</label>
                <input type="text" class="form-control" value="{{ ucfirst($item->periodicidade) }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Forma pagamento</label>
                <input type="text" class="form-control" value="{{ strtoupper($item->forma_pagamento) }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Dia vencimento</label>
                <input type="text" class="form-control" value="{{ $item->dia_vencimento }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Data início</label>
                <input type="text" class="form-control" value="{{ $item->data_inicio ? __data_pt($item->data_inicio) : '--' }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Próxima cobrança</label>
                <input type="text" class="form-control" value="{{ $item->proxima_cobranca ? __data_pt($item->proxima_cobranca) : '--' }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Data fim</label>
                <input type="text" class="form-control" value="{{ $item->data_fim ? __data_pt($item->data_fim) : '--' }}" disabled>
            </div>
        </div>

        <hr class="mt-4">

        <h5>Serviços</h5>

        <div class="table-responsive-sm mt-3">
            <table class="table table-striped table-centered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Serviço</th>
                        <th>Quantidade</th>
                        <th>Valor unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($item->servicos as $servico)
                    <tr>
                        <td>{{ $servico->servico->nome ?? '' }}</td>
                        <td>{{ __moeda($servico->quantidade) }}</td>
                        <td>R$ {{ __moeda($servico->valor_unitario) }}</td>
                        <td>R$ {{ __moeda($servico->subtotal) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">Nenhum serviço informado</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr class="mt-4">

        <div class="row g-2">
            <div class="col-md-2">
                <label>Gerar automático</label><br>
                @if($item->gerar_automatico)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>

            <div class="col-md-2">
                <label>Enviar WhatsApp</label><br>
                @if($item->enviar_whatsapp)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>

            <div class="col-md-2">
                <label>Enviar e-mail</label><br>
                @if($item->enviar_email)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>

          <!--   <div class="col-md-2">
                <label>Gerar NFSe</label><br>
                @if($item->gera_nfse)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>

            <div class="col-md-2">
                <label>Gerar NFe</label><br>
                @if($item->gera_nfe)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div> -->
        </div>

        @if($item->observacao)
        <hr class="mt-4">
        <h5>Observação</h5>
        <div class="alert alert-light border">
            {{ $item->observacao }}
        </div>
        @endif

        <hr class="mt-4">

        <div class="d-flex justify-content-between align-items-center">
            <h5>Cobranças geradas</h5>

            @can('recorrencia_create')
            <form action="{{ route('recorrencias.gerar-cobranca', $item->id) }}" method="post" id="form-gerar-cobranca">
                @csrf

                <button type="button" class="btn btn-primary btn-sm" onclick="confirmarGeracaoCobranca()">
                    <i class="ri-money-dollar-circle-line"></i>
                    Gerar cobrança
                </button>
            </form>
            @endcan
        </div>

        <div class="table-responsive-sm mt-3">
            <table class="table table-striped table-centered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th>Pago em</th>
                        <th width="18%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($item->cobrancas as $c)
                    <tr>
                        <td>{{ __data_pt($c->data_vencimento, 0) }}</td>
                        <td>R$ {{ __moeda($c->valor) }}</td>
                        <td>
                            @if($c->status == 'pendente')
                            <span class="badge bg-warning">Pendente</span>
                            @elseif($c->status == 'pago')
                            <span class="badge bg-success">Pago</span>
                            @elseif($c->status == 'vencido')
                            <span class="badge bg-danger">Vencido</span>
                            @else
                            <span class="badge bg-danger">Cancelado</span>
                            @endif
                        </td>
                        <td>{{ strtoupper($c->forma_pagamento) }}</td>
                        <td>{{ $c->pago_em ? __data_pt($c->pago_em) : '--' }}</td>
                        <td>
                            <a class="btn btn-dark btn-sm text-white" href="{{ route('recorrencia-cobrancas.show', $c->id) }}">
                                <i class="ri-eye-line"></i>
                            </a>

                            @if($c->status == 'pendente')

                            <form action="{{ route('recorrencia-cobrancas.marcar-pago', $c->id) }}" method="post" style="display:inline;" id="form-pagar-{{ $c->id }}">
                                @csrf

                                <button type="button" class="btn btn-success btn-sm" onclick="confirmarPagamento({{ $c->id }})">
                                    <i class="ri-check-line"></i>
                                </button>
                            </form>

                            <form action="{{ route('recorrencia-cobrancas.cancelar', $c->id) }}" method="post" style="display:inline;" id="form-cancelar-{{ $c->id }}">
                                @csrf

                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarCancelamento({{ $c->id }})">
                                    <i class="ri-close-line"></i>
                                </button>
                            </form>

                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Nenhuma cobrança gerada</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
@section('js')
<script>
    function confirmarGeracaoCobranca(){
        swal({
            title: "Gerar cobrança?",
            text: "Deseja realmente gerar uma nova cobrança para esta recorrência?",
            icon: "warning",
            buttons: ["Cancelar", "Sim, gerar"],
            dangerMode: false,
        }).then((willGenerate) => {
            if(willGenerate){
                $('#form-gerar-cobranca').submit();
            }
        });
    }

    function confirmarPagamento(id){
        swal({
            title: "Marcar como pago?",
            text: "Deseja realmente marcar esta cobrança como paga?",
            icon: "warning",
            buttons: ["Cancelar", "Sim, marcar"],
            dangerMode: false,
        }).then((willConfirm) => {
            if(willConfirm){
                $('#form-pagar-' + id).submit();
            }
        });
    }

    function confirmarCancelamento(id){
        swal({
            title: "Cancelar cobrança?",
            text: "Deseja realmente cancelar esta cobrança?",
            icon: "warning",
            buttons: ["Voltar", "Sim, cancelar"],
            dangerMode: true,
        }).then((willConfirm) => {
            if(willConfirm){
                $('#form-cancelar-' + id).submit();
            }
        });
    }

</script>
@endsection