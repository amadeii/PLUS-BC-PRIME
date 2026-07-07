@extends('layouts.app', ['title' => 'Histórico do cliente'])
@section('content')
<div class="mt-3">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <hr class="mt-3">
                <h5>Histórico do cliente: <strong class="text-primary">{{ $item->info }}</strong></h5>

                <div id="basicwizard">
                    <ul class="nav nav-pills nav-justified form-wizard-header mb-4 m-2">
                        <li class="nav-item">
                            <a href="#tab-vendas" data-bs-toggle="tab" data-toggle="tab"  class="nav-link rounded-0 py-1"> 
                                <i class="ri-stack-fill fw-normal fs-18 align-middle me-1"></i>
                                <span class="d-none d-sm-inline">Vendas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-produtos" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 py-1">
                                <i class="ri-box-3-line fs-18 align-middle me-1"></i>
                                <span class="d-none d-sm-inline">Produtos vendidos (totalizador)</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-produtos-pesquisa" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 py-1">
                                <i class="ri-search-line fs-18 align-middle me-1 pesquisa"></i>
                                <span class="d-none d-sm-inline">Produtos vendidos (busca)</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tab-faturas" data-bs-toggle="tab" data-toggle="tab" class="nav-link rounded-0 py-1">
                                <i class="ri-wallet-line fs-18 align-middle me-1"></i>
                                <span class="d-none d-sm-inline">Faturas</span>
                            </a>
                        </li>
                    </ul>
                    <!--  -->
                    <div class="tab-content b-0 mb-0">
                        <div class="tab-pane" id="tab-vendas">

                            <div class="col-lg-12">
                                {!!Form::open()->fill(request()->all())
                                ->get()
                                !!}
                                <div class="row mt-3 g-1">

                                    <div class="col-md-2">
                                        {!!Form::date('start_date', 'Data inicial')
                                        !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!!Form::date('end_date', 'Data final')
                                        !!}
                                    </div>


                                    <div class="col-lg-4 col-12">
                                        <br>

                                        <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                                        <a id="clear-filter" class="btn btn-danger" href="{{ route('clientes.historico', [$item->id]) }}"><i class="ri-eraser-fill"></i>Limpar</a>
                                    </div>
                                </div>
                                {!!Form::close()!!}
                            </div>

                            <div class="col-md-12 mt-3">
                                <button id="btnUnificar" class="btn btn-primary mb-2 disabled">
                                    Unificar vendas
                                </button>
                                <div class="table-responsive">
                                    <table class="table table-centered mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="selecionarTodos">
                                                </th>
                                                <th>#</th>
                                                <th>Data</th>
                                                <th>Valor total</th>
                                                <th>Estado</th>
                                                <th>Chave</th>
                                                <th>Número documento</th>
                                                <th>Tipo</th>
                                                <th>Açoes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $total = 0; @endphp
                                            @forelse($data as $c)
                                            <tr class="linha-venda" data-estado="{{ $c->estado }}" data-total="{{ $c->total }}" data-id="{{ $c->id }}">
                                                <td>

                                                    <input type="checkbox"
                                                    name="input_unificar"
                                                    class="check-unificar"
                                                    {{ !in_array($c->estado, ['novo','rejeitado']) ? 'disabled' : '' }}>
                                                </td>
                                                <td>{{ $c->numero_sequencial }}</td>
                                                <td>{{ __data_pt($c->created_at) }}</td>
                                                <td>{{ __moeda($c->total) }}</td>
                                                <td>
                                                    @if($c->estado == 'aprovado')
                                                    <span class="badge bg-success text-white p-1">Aprovado</span>
                                                    @elseif($c->estado == 'cancelado')
                                                    <span class="badge bg-danger text-white p-1">Cancelado</span>
                                                    @elseif($c->estado == 'rejeitado')
                                                    <span class="badge bg-warning text-white p-1">Rejeitado</span>
                                                    @else
                                                    <span class="badge bg-info text-white p-1">Novo</span>
                                                    @endif
                                                </td>
                                                <td>{{ $c->estado == 'aprovado' ? $c->chave : '--' }}</td>
                                                <td>{{ $c->estado == 'aprovado' ? $c->numero : '--' }}</td>
                                                <td>{{ $c->tipo == 'nfce' ? 'PDV NFCe' : 'NFe' }}</td>
                                                <td>
                                                    @if(__isPlanoFiscal())
                                                    @if($c->estado == 'novo' || $c->estado == 'rejeitado')
                                                    @can('nfe_transmitir')
                                                    <button title="Transmitir NFe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$c->id}}')">
                                                        <i class="ri-send-plane-fill"></i>
                                                    </button>
                                                    @endcan
                                                    @endif
                                                    @endif

                                                    <a class="btn btn-ligth btn-sm" title="Detalhes" href="{{ route('nfe.show', $c->id) }}">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                </td>
                                            </tr>

                                            @php $total += $c->total; @endphp

                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">Nada encontrado</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-dark">
                                                <td class="text-white">Total</td>
                                                <td class="text-white">{{ __moeda($total) }}</td>
                                                <td colspan="4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- modal unnificar -->

                    <div class="modal fade" id="modalUnificar" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title">Unificar vendas</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">

                                    <p>
                                        Foram selecionadas 
                                        <strong id="qtdVendas"></strong> vendas.
                                    </p>

                                    <p>
                                        Total: <strong id="valorTotal" class="text-primary"></strong>
                                    </p>
                                    <hr>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="gerarFatura">
                                        <label class="form-check-label fw-bold">
                                            Informar fatura / parcelas
                                        </label>
                                    </div>

                                    <div id="areaFatura" style="display:none;">

                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tipo Pagamento</th>
                                                    <th>Data Vencimento</th>
                                                    <th>Valor</th>
                                                    <th width="50"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyFatura">
                                            </tbody>
                                        </table>

                                        <button type="button" class="btn btn-sm btn-outline-primary" id="addParcela">
                                            + Adicionar Parcela
                                        </button>

                                        <div class="mt-2 text-end">
                                            Total parcelas: 
                                            <strong id="totalParcelas">R$ 0,00</strong>
                                        </div>

                                    </div>

                                    <hr>

                                    <p>Deseja realmente unificar essas vendas?</p>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button id="confirmarUnificacao" class="btn btn-success">
                                        Sim, unificar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  -->

                    <div class="tab-content b-0 mb-0">
                        <div class="tab-pane" id="tab-produtos">
                            <div class="col-md-12 mt-3">
                                <div class="table-responsive">
                                    <table class="table table-striped table-centered mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th></th>
                                                <th>Produto</th>
                                                <th>Quantidade</th>
                                                <th>Valor unitário</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($produtos as $p)
                                            <tr>
                                                <td><img class="img-60" src="{{ $p->produto->img }}"></td>
                                                <td>{{ $p->produto->nome }}</td>
                                                <td>
                                                    @if(!$p->produto->unidadeDecimal())
                                                    {{ number_format($p->quantidade, 0, '.', '') }}
                                                    @else
                                                    {{ number_format($p->quantidade, 3, '.', '') }}
                                                    @endif

                                                </td>
                                                <td>{{ __moeda($p->valor_unitario) }}</td>
                                                <td>{{ __moeda($p->quantidade*$p->valor_unitario) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content b-0 mb-0">
                        <div class="tab-pane" id="tab-produtos-pesquisa">
                            <div class="col-md-12 mt-3">
                                <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-6">
                                        <label>Pesquise o produto</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="ri-search-line"></i>
                                            </span>
                                            <input 
                                            type="text" 
                                            id="inp-pesquisa" 
                                            class="form-control border-start-0"
                                            placeholder="Digite o nome do produto">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Código da venda</th>
                                                    <th>Data da venda</th>
                                                    <th>Descrição</th>
                                                    <th>Código de barras</th>
                                                    <th>Referência</th>
                                                    <th>Quantidade</th>
                                                    <th>Valor unitário</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content b-0 mb-0">
                        <div class="tab-pane" id="tab-faturas">
                            <div class="col-md-12 mt-3">
                                <div class="table-responsive">
                                    <table class="table table-striped table-centered mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Descrição</th>
                                                <th>Data de cadastro</th>
                                                <th>Data de vencimento</th>
                                                <th>Data de recebimento</th>
                                                <th>Valor</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($faturas as $c)
                                            <tr>
                                                <td>{{ $c->descricao }}</td>
                                                <td>{{ __data_pt($c->created_at) }}</td>
                                                <td>{{ __data_pt($c->data_vencimento, 0) }}</td>
                                                <td>{{ $c->status ? __data_pt($c->data_recebimento, 0) : '--' }}</td>
                                                <td>{{ __moeda($c->valor_integral) }}</td>
                                                <td>
                                                    @if($c->status)
                                                    <span class="btn btn-success position-relative me-lg-5 btn-sm">
                                                        <i class="ri-checkbox-line"></i> Recebido
                                                    </span>
                                                    @else
                                                    <span class="btn btn-warning position-relative me-lg-5 btn-sm">
                                                        <i class="ri-alert-line"></i> Pendente
                                                    </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="/assets/vendor/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="/assets/js/pages/demo.form-wizard.js"></script>
<script src="/js/nfe_transmitir.js"></script>
<script type="text/javascript">
    setTimeout(() => {
        // $('.pesquisa').trigger('click')
        $('#inp-pesquisa').val('')
    }, 1000)

    $('body').on('keyup', '#inp-pesquisa', function (e) {
        let pesquisa = $(this).val()
        if(pesquisa.length >= 1){
            $.get(path_url + "api/clientes/produtos-historico", 
            {
                pesquisa: pesquisa,
                cliente_id: '{{ $item->id }}'
            })
            .done((res) => {
                // console.log(res)
                $('#tab-produtos-pesquisa tbody').html(res)

            })
            .fail((err) => {
                console.log(err)
            })
        }
    })

    // unificar


    $(document).ready(function(){

        const btn = $('#btnUnificar');
        const modal = $('#modalUnificar');
        const btnConfirmar = $('#confirmarUnificacao');


        $('#selecionarTodos').on('change', function(){

            let checked = $(this).is(':checked');

            $('.check-unificar:not(:disabled)')
            .prop('checked', checked)
            .trigger('change');
        });


        $(document).on('change', '.check-unificar', function(){

            destacarLinha($(this));
            atualizarBotao();
        });


        function destacarLinha(checkbox){

            let tr = checkbox.closest('tr');

            if(checkbox.is(':checked')){
                tr.addClass('table-warning');
            } else {
                tr.removeClass('table-warning');
            }
        }


        function atualizarBotao(){

            let qtd = $('.check-unificar:checked').length;

            if(qtd >= 2){
                btn.removeClass('disabled');
            } else {
                btn.addClass('disabled');
            }
        }


        btn.on('click', function(){

            if($(this).hasClass('disabled')){
                return;
            }

            let total = 0;
            let qtd = 0;
            let ids = [];
            let tipos = [];

            $('.check-unificar:checked').each(function(){

                let tr = $(this).closest('tr');

                let valor = parseFloat(tr.data('total'));
                let id = tr.data('id');
                let tipoTexto = tr.find('td:last').text().trim();

                let tipo = (tipoTexto === 'PDV NFCe') ? 'nfce' : 'nfe';

                total += valor;
                qtd++;
                ids.push(id);
                tipos.push(tipo);
            });


            let tipoUnico = [...new Set(tipos)];

            // if(tipoUnico.length > 1){
            //     alert('Não é permitido unificar NFe com NFCe.');
            //     return;
            // }

            if(qtd < 2){
                alert('Selecione pelo menos 2 vendas.');
                return;
            }

            $('#qtdVendas').text(qtd);
            $('#valorTotal').text(
                total.toLocaleString('pt-BR', {style:'currency', currency:'BRL'})
                );

            btnConfirmar.data('ids', ids);
            btnConfirmar.data('tipos', tipos);

            modal.modal('show');
        });



        btnConfirmar.on('click', function(){

            let ids   = $(this).data('ids');
            let tipos = $(this).data('tipos');

            if(!ids || ids.length < 2){
                return;
            }



            let faturas = [];
            let somaParcelas = 0;

            if($('#gerarFatura').is(':checked')){

                $('#tbodyFatura tr').each(function(){

                    let tipoPagamento = $(this).find('.tipo-pagamento').val();
                    let dataVencimento = $(this).find('.data-vencimento').val();
                    let valor = convertMoedaToFloat($(this).find('.valor-parcela').val());

                    if(valor > 0){

                        faturas.push({
                            tipo_pagamento: tipoPagamento,
                            data_vencimento: dataVencimento,
                            valor: valor
                        });

                        somaParcelas += valor;
                    }
                });


                if(faturas.length === 0){
                    swal("Atenção", "Informe pelo menos uma parcela válida.", "warning");
                    return;
                }

                let totalTexto = $('#valorTotal').text();
                let totalNumerico = parseFloat(totalTexto.replace('R$', '').replace(/\./g,'').replace(',', '.')) || 0;
                console.log(somaParcelas)
                console.log(totalNumerico)
                if(Math.abs(somaParcelas - totalNumerico) > 0.01){
                    swal("Atenção", "A soma das parcelas deve ser igual ao total da venda.", "warning");
                    return;
                }
            }

            btnConfirmar.prop('disabled', true).text('Unificando...');

            $.ajax({
                url: "{{ route('nfe.unificar') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: ids,
                    tipos: tipos,
                    faturas: faturas
                },
                success: function(response){

                    modal.modal('hide');

                    swal("Sucesso", "Vendas unificadas com sucesso.", "success")
                    .then(() => {
                        location.reload();
                    })

                    $('.check-unificar:not(:disabled)')
                    .prop('checked', false)
                    .trigger('change');

                },
                error: function(xhr){

                    swal("Algo deu errado", "Erro ao unificar vendas.", "error");

                    btnConfirmar.prop('disabled', false).text('Sim, unificar');
                }
            });

        });


        modal.on('hidden.bs.modal', function(){

            btnConfirmar.prop('disabled', false).text('Sim, unificar');
        });

        let totalUnificacao = 0;

        $('#gerarFatura').on('change', function(){
            $('#areaFatura').toggle(this.checked);

            if(this.checked && $('#tbodyFatura tr').length === 0){
                adicionarParcela();
            }
        });

        $('#addParcela').on('click', function(){
            adicionarParcela();
        });

        const TIPOS_PAGAMENTO = @json(
            collect(\App\Models\ContaReceber::tiposPagamento())
            ->map(function($label, $value){
                return [
                'value' => $value,
                'label' => $label
                ];
            })
            ->values()
            );

        function adicionarParcela(){

            let options = '';

            TIPOS_PAGAMENTO.forEach(function(tipo){
                options += `<option value="${tipo.value}">${tipo.label}</option>`;
            });

            let row = `
            <tr>
            <td>
            <select class="form-select tipo-pagamento">
            ${options}
            </select>
            </td>
            <td>
            <input type="date" class="form-control data-vencimento">
            </td>
            <td>
            <input type="tel" class="form-control valor-parcela moeda">
            </td>
            <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger removerParcela">X</button>
            </td>
            </tr>
            `;

            $('#tbodyFatura').append(row);
        }

        $(document).on('click', '.removerParcela', function(){
            $(this).closest('tr').remove();
            calcularTotalParcelas();
        });

        $(document).on('input', '.valor-parcela', function(){
            calcularTotalParcelas();
        });

        function calcularTotalParcelas(){

            let total = 0;

            $('.valor-parcela').each(function(){
                total += convertMoedaToFloat($(this).val());
            });

            $('#totalParcelas').text(convertFloatToMoeda(total));
        }

    });


</script>
@endsection
