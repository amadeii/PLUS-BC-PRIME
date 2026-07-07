@extends('layouts.app', ['title' => 'Faturamento NFC-e em Lote'])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/fatura_nfe.css">
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="fat-page">

            <div class="fat-header">
                <div class="fat-title">
                    <h4>Faturamento NFC-e em Lote</h4>
                    <p>Selecione as vendas pendentes para transmitir NFC-e em lote.</p>
                </div>

                <div class="fat-actions">
                    <a href="{{ route('faturamento-nfce.index') }}" class="btn btn-danger">
                        <i class="ri-arrow-left-line"></i> Voltar
                    </a>
                </div>
            </div>

            <div class="fat-filter">
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row g-3">
                    <div class="col-md-4">
                        {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2']) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('tipo_pagamento', 'Tipo de pagamento')
                        ->attrs(['class' => 'form-select'])
                        ->options(['' => 'Todos'] + $tiposPagamento) !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::select('per_page', 'Registros por página')
                        ->attrs(['class' => 'form-select'])
                        ->options([
                        10 => '10',
                        20 => '20',
                        30 => '30',
                        50 => '50',
                        100 => '100'
                        ]) !!}
                    </div>

                    <div class="col-md-3 d-flex align-items-end mb-1">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i> Pesquisar
                        </button>

                        <a href="{{ route('faturamento-nfce.lote') }}" class="btn btn-outline">
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
                            <strong>{{ $items->total() }}</strong> vendas disponíveis para emissão NFC-e
                        </div>

                        <button type="button" class="btn-fat-green btn-lote-resumo" id="btnProcessarLote" disabled>
                            <span class="btn-lote-icon">
                                <i class="ri-send-plane-fill"></i>
                            </span>
                            <span>
                                Emitir NFC-e Selecionadas
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
                                    <th>Venda</th>
                                    <th>Cliente</th>
                                    <th>CPF/CNPJ</th>
                                    <th>Valor</th>
                                    <th>Data Venda</th>
                                    <th>Pagamento(s)</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($items as $item)
                                <tr id="linha-lote-{{ $item->id }}">
                                    <td>
                                        <input type="checkbox" name="ids[]" class="check-lote" value="{{ $item->id }}" data-valor="{{ $item->valor_total ?? $item->total }}">
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
                                        R$ {{ __moeda($item->valor_total ?? $item->total) }}
                                    </td>

                                    <td>
                                        {{ __data_pt($item->created_at, 0) }}
                                        <br>
                                        <strong class="text-primary">{{ $item->created_at->format('H:i') }}</strong>
                                    </td>

                                    <td>
                                        @if($item->fatura && $item->fatura->count())

                                        <div class="pagamentos-list">

                                            @foreach($item->fatura as $fat)
                                            <div class="pagamento-item">

                                                <span class="pagamento-badge">
                                                    {{ \App\Models\Nfce::getTipoPagamento($fat->tipo_pagamento) }}
                                                </span>

                                                <span class="pagamento-valor">
                                                    R$ {{ __moeda($fat->valor) }}
                                                </span>

                                            </div>
                                            @endforeach

                                        </div>

                                        @elseif($item->tipo_pagamento)

                                        <div class="pagamentos-list">

                                            <div class="pagamento-item">

                                                <span class="pagamento-badge pagamento-badge-primary">
                                                    {{ \App\Models\Nfce::getTipoPagamento($item->tipo_pagamento) }}
                                                </span>

                                                <span class="pagamento-valor">
                                                    R$ {{ __moeda($item->valor_total) }}
                                                </span>

                                            </div>

                                        </div>

                                        @else

                                        <span class="text-muted">--</span>

                                        @endif
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
                                    <td colspan="8" class="text-center py-5">
                                        Nenhuma venda pendente para emissão NFC-e em lote
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="fat-footer">
                        <div>
                            Selecionadas: <strong id="qtdSelecionados">0</strong>
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
            @include('faturamento_nfce.partials.modal_detalhes_venda', ['item' => $item])
            @endforeach

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    let processandoLote = false;

    $(function(){
        atualizaResumoLote();
    });

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
        $('#btnProcessarLote').prop('disabled', qtd == 0 || processandoLote);
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
            swal('Atenção', 'Selecione ao menos uma venda.', 'warning');
            return;
        }

        swal({
            title: 'Emitir NFC-e?',
            text: 'Deseja emitir NFC-e das vendas selecionadas?',
            icon: 'warning',
            buttons: true,
            dangerMode: false,
        }).then((ok) => {
            if(!ok) return;

            if(processandoLote) return;

            processandoLote = true;
            processarLote(ids);
        });
    });

    function processarLote(ids){

        $('#btnProcessarLote')
        .prop('disabled', true)
        .html(`
            <span class="spinner-border spinner-border-sm me-1"></span>
            Emitindo NFC-e...
            `);

        $('.check-lote:checked').each(function(){
            let id = $(this).val();

            $('#status-lote-' + id)
            .removeClass()
            .addClass('status-pill status-pendente')
            .html('Emitindo...');
        });

        $.ajax({
            url: '/api/faturamento-nfce-processar-lote',
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
                        ✅ Venda #${item.venda ?? item.pedido}<br>
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
                        ❌ Venda #${item.venda ?? item.pedido}<br>
                        <small>${item.mensagem}</small>
                        </div>
                        `;
                    }
                });

                swal({
                    title: 'Emissão concluída',
                    content: {
                        element: "div",
                        attributes: {
                            innerHTML: `
                            <div class="text-start">
                            <strong>Total:</strong> ${res.total}<br>
                            <strong>Autorizadas:</strong> ${res.sucesso}<br>
                            <strong>Rejeitadas:</strong> ${res.erros}<br><br>
                            ${html}
                            </div>
                            `
                        }
                    }
                }).then(() => {
                    location.reload();
                });

                processandoLote = false;

                $('#btnProcessarLote')
                .prop('disabled', false)
                .html(`
                    <span class="btn-lote-icon">
                    <i class="ri-send-plane-fill"></i>
                    </span>
                    <span>
                    Emitir NFC-e Selecionadas
                    <small>
                    <b id="qtdSelecionadosBtn">0</b> venda(s) · <b id="valorSelecionadoBtn">R$ 0,00</b>
                    </small>
                    </span>
                    `);

                $('.check-lote:checked').prop('checked', false);
                $('#checkTodosLote').prop('checked', false);
                atualizaResumoLote();
            },
            error: function(xhr){

                let msg = 'Erro ao emitir NFC-e em lote';

                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }

                swal('Erro', msg, 'error');

                processandoLote = false;

                $('#btnProcessarLote')
                .prop('disabled', false)
                .html(`
                    <span class="btn-lote-icon">
                    <i class="ri-send-plane-fill"></i>
                    </span>
                    <span>
                    Emitir NFC-e Selecionadas
                    <small>
                    <b id="qtdSelecionadosBtn">0</b> venda(s) · <b id="valorSelecionadoBtn">R$ 0,00</b>
                    </small>
                    </span>
                    `);

                atualizaResumoLote();
            }
        });
}
</script>
@endsection