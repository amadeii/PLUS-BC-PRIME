@extends('layouts.app', ['title' => 'Faturamento em Lote'])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/fatura_nfe.css">
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="fat-page">

            <div class="fat-header">
                <div class="fat-title">
                    <h4>Faturamento em Lote</h4>
                    <p>Selecione os pedidos pendentes para transmitir NF-e em lote.</p>
                </div>

                <div class="fat-actions">
                    <a href="{{ route('faturamento-nfe.index') }}" class="btn btn-danger">
                        <i class="ri-arrow-left-line"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="fat-filter">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-3">
                    <div class="col-md-3">
                        {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>

                    <div class="col-md-3 d-flex align-items-end mb-1">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i> Pesquisar
                        </button>

                        <a href="{{ route('faturamento-nfe.lote') }}" class="btn btn-outline">
                            <i class="ri-eraser-fill"></i> Limpar
                        </a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            <form id="formLote">
                @csrf

                <div class="fat-panel">
                    <div class="d-flex justify-content-between align-items-center m-2">
                        <div>
                            <strong>{{ $items->total() }}</strong> pedidos disponíveis para faturamento
                        </div>

                        <button type="button" class="btn-fat-green btn-lote-resumo" id="btnProcessarLote" disabled>
                            <span class="btn-lote-icon">
                                <i class="ri-send-plane-fill"></i>
                            </span>
                            <span>
                                Faturar Selecionados
                                <small>
                                    <b id="qtdSelecionadosBtn">0</b> venda(s) · <b id="valorSelecionadoBtn">R$ 0,00</b>
                                </small>
                            </span>
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-dark">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="checkTodosLote">
                                    </th>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>CPF/CNPJ</th>
                                    <th>Valor</th>
                                    <th>Data Pedido</th>
                                    <th>Situação</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($items as $item)
                                <tr id="linha-lote-{{ $item->id }}">
                                    <td>
                                        <input type="checkbox" name="ids[]" class="check-lote" value="{{ $item->id }}" data-valor="{{ $item->total }}">
                                    </td>

                                    <td>
                                        <strong>{{ $item->numero_sequencial ?? $item->id }}</strong>
                                    </td>

                                    <td>
                                        {{ $item->cliente->razao_social ?? 'Consumidor Final' }}
                                    </td>

                                    <td>
                                        {{ $item->cliente->cpf_cnpj ?? '--' }}
                                    </td>

                                    <td class="valor text-success">
                                        R$ {{ __moeda($item->total) }}
                                    </td>

                                    <td>
                                        {{ __data_pt($item->created_at, 0) }}
                                        <br>
                                        <strong class="text-primary">{{ $item->created_at->format('H:i') }}</strong>
                                    </td>

                                    <td>
                                        <span class="status-pill status-pendente" id="status-lote-{{ $item->id }}">
                                            Pendente
                                        </span>
                                    </td>

                                    <td>
                                        <button type="button" class="btn-ver-venda-lote" data-bs-toggle="modal" data-bs-target="#modalVendaLote{{ $item->id }}">
                                            <i class="ri-eye-line"></i>
                                            Ver venda
                                        </button>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        Nenhum pedido pendente para faturamento em lote
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="fat-footer">
                        <div>
                            Selecionados: <strong id="qtdSelecionados">0</strong>
                            |
                            Total: <strong class="text-success" id="valorSelecionado">R$ 0,00</strong>
                        </div>
                    </div>

                    <div class="fat-pagination m-1">
                        {{ $items->appends(request()->all())->links() }}
                    </div>
                </div>
            </form>

            @foreach($items as $item)
            @include('faturamento_nfe.partials.modal_detalhes_venda', ['item' => $item])
            @endforeach

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function(){
        atualizaResumoLote()
    })
    $(document).on('change', '#checkTodosLote', function(){
        $('.check-lote').prop('checked', $(this).is(':checked'));
        atualizaResumoLote();
    });

    $(document).on('change', '.check-lote', function(){
        atualizaResumoLote();
    });

    function atualizaResumoLote(){
        let qtd = 0;
        let total = 0;

        $('.check-lote:checked').each(function(){
            qtd++;
            total += parseFloat($(this).data('valor')) || 0;
        });

        let totalFormatado = total.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });

        $('#qtdSelecionados').text(qtd);
        $('#valorSelecionado').text(totalFormatado);

        $('#qtdSelecionadosBtn').text(qtd);
        $('#valorSelecionadoBtn').text(totalFormatado);

        $('#btnProcessarLote').prop('disabled', qtd == 0);
    }

    $(document).on('click', '.btn-selecionar-modal', function(){
        let id = $(this).data('id');
        $('.check-lote[value="' + id + '"]').prop('checked', true);
        atualizaResumoLote();
    });

    $(document).on('click', '#btnProcessarLote', function(e){
        e.preventDefault();

        let ids = [];

        $('.check-lote:checked').each(function(){
            ids.push($(this).val());
        });

        if(ids.length == 0){
            swal('Atenção', 'Selecione ao menos um pedido.', 'warning');
            return;
        }

        let processandoLote = false;

        swal({
            title: 'Transmitir NF-es?',
            text: 'Deseja transmitir os pedidos selecionados?',
            icon: 'warning',
            buttons: true,
            dangerMode: false,
        }).then((ok) => {

            if(!ok) return;

            if(processandoLote){
                return;
            }

            processandoLote = true;

            processarLote(ids);
        });
    });

    function processarLote(ids){

        $('#btnProcessarLote')
        .prop('disabled', true)
        .html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Processando...
            `);

        $('.check-lote:checked').each(function(){
            let id = $(this).val();

            $('#status-lote-' + id)
            .removeClass()
            .addClass('status-pill status-pendente')
            .html('Processando...');
        });

        $.ajax({
            url: '/api/faturamento-nfe-processar-lote',
            method: 'POST',
            data: {
                ids: ids,
                empresa_id: "{{ request()->empresa_id }}",
                _token: "{{ csrf_token() }}"
            },
            success: function(res){

                let html = '';

                res.resultados.forEach((item) => {

                    if(item.erro == 0){

                        $('#status-lote-' + item.id)
                        .removeClass()
                        .addClass('status-pill status-ok')
                        .html('Autorizada');

                        $('#linha-lote-' + item.id).addClass('table-success');

                        html += `
                        <div style="text-align:left; margin-bottom:8px;">
                        ✅ Pedido #${item.pedido}<br>
                        <small>${item.mensagem}</small>
                        </div>
                        `;

                    }else{

                        $('#status-lote-' + item.id)
                        .removeClass()
                        .addClass('status-pill status-pendente')
                        .html('Rejeitada');

                        $('#linha-lote-' + item.id).addClass('table-warning');

                        html += `
                        <div style="text-align:left; margin-bottom:8px;">
                        ❌ Pedido #${item.pedido}<br>
                        <small>${item.mensagem}</small>
                        </div>
                        `;
                    }
                });

                swal({
                    title: 'Processamento concluído',
                    content: {
                        element: "div",
                        attributes: {
                            innerHTML: `
                            <div class="text-start">
                            <strong>Total:</strong> ${res.total}<br>
                            <strong>Sucesso:</strong> ${res.sucesso}<br>
                            <strong>Erros:</strong> ${res.erros}<br><br>
                            ${html}
                            </div>
                            `
                        }
                    }
                }).then(() => {
                    location.reload();
                })

                $('#btnProcessarLote')
                .prop('disabled', false)
                .html(`
                    <i class="ri-send-plane-fill"></i>
                    Faturar Selecionados
                    `);

                $('.check-lote:checked').prop('checked', false);
                $('#checkTodosLote').prop('checked', false);
                atualizaResumoLote();

            // setTimeout(() => {
            //     location.reload();
            // }, 3000);
        },
        error: function(xhr){

            let msg = 'Erro ao processar lote';

            if(xhr.responseJSON && xhr.responseJSON.message){
                msg = xhr.responseJSON.message;
            }

            swal('Erro', msg, 'error');

            $('#btnProcessarLote')
            .prop('disabled', false)
            .html(`
                <i class="ri-send-plane-fill"></i>
                Faturar Selecionados
                `);
        }
    });
    }
</script>
@endsection