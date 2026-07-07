
@extends('layouts.app', ['title' => 'Detalhes da Cobrança'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Detalhes da Cobrança</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('recorrencias.show', $item->recorrencia_id) }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>

    <div class="card-body">

        <div class="row g-2">
            <div class="col-md-4">
                <label>Cliente</label>
                <input type="text" class="form-control" value="{{ $item->cliente->razao_social ?? $item->recorrencia->cliente->razao_social ?? '' }}" disabled>
            </div>

            <div class="col-md-4">
                <label>Recorrência</label>
                <input type="text" class="form-control" value="{{ $item->recorrencia->descricao ?? '' }}" disabled>
            </div>

            <div class="col-md-2">
                <label>Valor</label>
                <input type="text" class="form-control" value="R$ {{ __moeda($item->valor) }}" disabled>
            </div>

            <div class="col-md-2">
                <label>Status</label>
                <br>
                @if($item->status == 'pendente')
                <span class="badge bg-warning mt-2">Pendente</span>
                @elseif($item->status == 'pago')
                <span class="badge bg-success mt-2">Pago</span>
                @elseif($item->status == 'vencido')
                <span class="badge bg-danger mt-2">Vencido</span>
                @else
                <span class="badge bg-secondary mt-2">Cancelado</span>
                @endif
            </div>

            <div class="col-md-2 mt-2">
                <label>Vencimento</label>
                <input type="text" class="form-control" value="{{ $item->data_vencimento ? __data_pt($item->data_vencimento, 0) : '--' }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Forma pagamento</label>
                <input type="text" class="form-control" value="{{ strtoupper($item->forma_pagamento ?? '') }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Pago em</label>
                <input type="text" class="form-control" value="{{ $item->pago_em ? __data_pt($item->pago_em) : '--' }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Gerado em</label>
                <input type="text" class="form-control" value="{{ $item->created_at ? __data_pt($item->created_at) : '--' }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>Atualizado em</label>
                <input type="text" class="form-control" value="{{ $item->updated_at ? __data_pt($item->updated_at) : '--' }}" disabled>
            </div>

            <div class="col-md-2 mt-2">
                <label>ID</label>
                <input type="text" class="form-control" value="#{{ $item->id }}" disabled>
            </div>
        </div>

        <hr class="mt-4">

        <div class="d-flex justify-content-between align-items-center">
            <h5>Serviços da cobrança</h5>
        </div>

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
                    @forelse($item->recorrencia->servicos ?? [] as $servico)
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
                @if(optional($item->recorrencia)->gerar_automatico)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>

            <div class="col-md-2">
                <label>Enviar WhatsApp</label><br>
                @if(optional($item->recorrencia)->enviar_whatsapp)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>

            <div class="col-md-2">
                <label>Enviar e-mail</label><br>
                @if(optional($item->recorrencia)->enviar_email)
                <span class="badge bg-success">Sim</span>
                @else
                <span class="badge bg-danger">Não</span>
                @endif
            </div>
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
            <h5>Ações</h5>

            <div>
                @if($item->status == 'pendente')
                <form action="{{ route('recorrencia-cobrancas.marcar-pago', $item->id) }}" method="post" id="form-marcar-pago" style="display:inline;">
                    @csrf
                    <button type="button" class="btn btn-success btn-sm" onclick="confirmarMarcarPago()">
                        <i class="ri-check-line"></i>
                        Marcar como pago
                    </button>
                </form>

                <form action="{{ route('recorrencia-cobrancas.cancelar', $item->id) }}" method="post" id="form-cancelar-cobranca" style="display:inline;">
                    @csrf
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmarCancelarCobranca()">
                        <i class="ri-close-line"></i>
                        Cancelar cobrança
                    </button>
                </form>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@section('js')
<script>
    function confirmarMarcarPago(){
        swal({
            title: "Marcar como pago?",
            text: "Deseja realmente marcar esta cobrança como paga?",
            icon: "warning",
            buttons: ["Cancelar", "Sim, marcar"],
            dangerMode: false,
        }).then((willConfirm) => {
            if(willConfirm){
                $('#form-marcar-pago').submit();
            }
        });
    }

    function confirmarCancelarCobranca(){
        swal({
            title: "Cancelar cobrança?",
            text: "Deseja realmente cancelar esta cobrança?",
            icon: "warning",
            buttons: ["Cancelar", "Sim, cancelar"],
            dangerMode: true,
        }).then((willCancel) => {
            if(willCancel){
                $('#form-cancelar-cobranca').submit();
            }
        });
    }
</script>
@endsection
