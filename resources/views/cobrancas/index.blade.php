@extends('layouts.app', ['title' => 'Cobranças Geradas'])

@section('content')

<div class="mt-1">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8 col-12">
                    <h3 class="mb-0">
                        <i class="ri-bank-line"></i> Cobranças Geradas
                    </h3>
                    <small class="text-muted">
                        Relação de boletos e cobranças bancárias emitidas
                    </small>
                </div>

                <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('cobranca-bancaria.index') }}" class="btn btn-danger btn-sm">
                        <i class="ri-arrow-left-line"></i> Voltar para geração
                    </a>
                </div>
            </div>

            <hr class="mt-3">

            <div class="col-lg-12 mb-2">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-3 g-2">
                    <div class="col-md-4">
                        {!! Form::select('cliente_id', 'Pesquisar por nome')->attrs(['class' => 'select2'])
                        ->options($cliente != null ? [$cliente->id => $cliente->info] : []) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('status', 'Status')
                        ->options([
                        '' => 'Todos',
                        'pendente' => 'Pendentes',
                        'pago' => 'Pagos',
                        'vencido' => 'Vencidos',
                        'erro' => 'Com erro',
                        ])->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('banco', 'Banco', $contasBancarias)
                        ->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="col-md-3 col-xl-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i> Pesquisar
                        </button>

                        <a class="btn btn-danger" href="{{ route('cobrancas.index') }}">
                            <i class="ri-eraser-fill"></i> Limpar
                        </a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            @if($data->count() > 0)

            <div class="table-responsive mt-3">
                <table class="table align-middle table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Nosso número</th>
                            <th>Vencimento</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Banco</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        @php
                        $pago = !empty($item->data_pagamento);
                        $vencido = !$pago && \Carbon\Carbon::parse($item->data_vencimento)->isPast();
                        $pendente = !$pago && !$vencido;
                        @endphp

                        <tr>
                            <td>
                                <strong>#{{ $item->id }}</strong>
                                @if($item->conta_receber_id)
                                <br>
                                <small class="text-muted">CR: {{ $item->conta_receber_id }}</small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $item->cliente->razao_social ?? $item->cliente->nome ?? '--' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $item->cliente->cpf_cnpj ?? '--' }}
                                </small>
                            </td>

                            <td>
                                <span>{{ $item->nosso_numero ?? '--' }}</span>
                                @if($item->seu_numero)
                                <br>
                                <small class="text-muted">Seu número: {{ $item->seu_numero }}</small>
                                @endif
                            </td>

                            <td>
                                <span class="{{ $vencido ? 'text-danger fw-bold' : '' }}">
                                    {{ __data_pt($item->data_vencimento, 0) }}
                                </span>

                                @if($item->data_pagamento)
                                <br>
                                <small class="text-success">
                                    Pago em {{ __data_pt($item->data_pagamento, 0) }}
                                </small>
                                @endif
                            </td>

                            <td>
                                <strong class="text-success">
                                    R$ {{ __moeda($item->valor) }}
                                </strong>

                                @if($item->valor_recebido)
                                <br>
                                <small class="text-primary">
                                    Recebido: R$ {{ __moeda($item->valor_recebido) }}
                                </small>
                                @endif
                            </td>

                            <td>
                                @if($pago)
                                <span class="badge bg-success">Pago</span>
                                @elseif($vencido)
                                <span class="badge bg-danger">Vencido</span>
                                @else
                                <span class="badge bg-warning">Pendente</span>
                                @endif

                                @if($item->mensagem_erro)
                                <br>
                                <span class="badge bg-danger mt-1">Erro</span>
                                @endif

                                @if($item->status_banco)
                                <br>
                                <small class="text-muted">{{ $item->status_banco }}</small>
                                @endif
                            </td>

                            <td>
                                {{ strtoupper($item->banco) ?? '--' }}
                            </td>

                            <td>
                                <div class="">
                                    <a href="{{ route('cobrancas.ver-boleto', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Abrir PDF">
                                        <i class="ri-printer-line"></i>
                                    </a>

                                    <a href="{{ route('cobrancas.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                        <i class="ri-file-fill"></i>
                                    </a>

                                    @if($item->linha_digitavel)
                                    <button type="button" class="btn btn-sm btn-outline-dark btn-copiar-linha" data-linha="{{ $item->linha_digitavel }}" title="Copiar linha digitável">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                    @endif

                                    @if($item->mensagem_erro)
                                    <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="{{ $item->mensagem_erro }}">
                                        <i class="ri-error-warning-line"></i>
                                    </button>
                                    @endif

                                    <button type="button" class="btn btn-sm btn-outline-info btn-consultar-status" data-id="{{ $item->id }}"
                                        title="Consultar status no banco">
                                        <i class="ri-refresh-line"></i>
                                    </button>

                                    @if(!$pago)
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-baixar-titulo" data-id="{{ $item->id }}" title="Baixar título">
                                        <i class="ri-close-circle-line"></i>
                                    </button>
                                    @endif

                                </div>
                            </td>
                        </tr>

                        @if($item->linha_digitavel || $item->codigo_barras)
                        <tr>
                            <td colspan="8" class="bg-light">
                                @if($item->linha_digitavel)
                                <div>
                                    <strong>Linha digitável:</strong>
                                    <span class="text-muted">{{ $item->linha_digitavel }}</span>
                                </div>
                                @endif

                                @if($item->codigo_barras)
                                <div class="mt-1">
                                    <strong>Código de barras:</strong>
                                    <span class="text-muted">{{ $item->codigo_barras }}</span>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endif

                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $data->links() }}
            </div>

            @else

            <div class="text-center py-5">
                <i class="ri-bank-card-line text-primary" style="font-size: 40px;"></i>
                <h5 class="mt-3">Nenhuma cobrança gerada</h5>
                <p class="text-muted">Ainda não há registros de cobrança bancária para os filtros informados.</p>
            </div>

            @endif

        </div>
    </div>
</div>

@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const botoesCopiar = document.querySelectorAll('.btn-copiar-linha');

        botoesCopiar.forEach(botao => {
            botao.addEventListener('click', function () {
                const linha = this.dataset.linha || '';

                if (!linha) return;

                navigator.clipboard.writeText(linha).then(() => {
                    toastr.success('Linha digitável copiada com sucesso.')

                }).catch(() => {
                    toastr.error('Não foi possível copiar a linha digitável.')
                });
            });
        });

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Tooltip(tooltipTriggerEl);
            }
        });
    });

    $('.btn-consultar-status').on('click', function () {

        let id = $(this).data('id');
        let btn = $(this);

        btn.prop('disabled', true);
        btn.html('<i class="ri-loader-4-line ri-spin"></i>');

        $.post(`/cobrancas/${id}/consultar-status`, {
            _token: '{{ csrf_token() }}'
        })
        .done(function (res) {

            if (res.success) {
                toastr.success('Status atualizado: ' + (res.status ?? 'OK'));
                location.reload();
            } else {
                toastr.error(res.msg ?? 'Erro ao consultar');
            }

        })
        .fail(function (err) {
            toastr.error('Erro ao consultar status');
            console.log(err.responseText);
        })
        .always(function () {
            btn.prop('disabled', false);
            btn.html('<i class="ri-refresh-line"></i>');
        });

    });

    $(document).on('click', '.btn-baixar-titulo', function () {

        let id = $(this).data('id');
        let btn = $(this);

        swal({
            title: "Confirma?",
            text: "Deseja realmente baixar este título?",
            icon: "warning",
            buttons: ["Cancelar", "Sim"],
            dangerMode: true
        }).then((willConfirm) => {

            if (!willConfirm) return;

            btn.prop('disabled', true);
            btn.html('<i class="ri-loader-4-line ri-spin"></i>');

            $.ajax({
                url: '/cobrancas/' + id + '/baixar-titulo',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            })
            .done(function (res) {

                if (res.success) {
                    swal("Sucesso!", res.msg || "Título baixado com sucesso.", "success");
                    setTimeout(() => location.reload(), 1200);
                } else {
                    swal("Erro!", res.msg || "Erro ao baixar título.", "error");
                }

            })
            .fail(function (xhr) {

                let msg = xhr.responseJSON?.msg ?? 'Erro ao baixar título.';
                swal("Erro!", msg, "error");

            })
            .always(function () {

                btn.prop('disabled', false);
                btn.html('<i class="ri-close-circle-line"></i>');

            });

        });

    });

</script>
@endsection