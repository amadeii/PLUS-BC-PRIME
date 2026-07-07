@extends('layouts.app', ['title' => 'Projeção de Custo Padrão'])

@section('content')
<div class="card mt-1 card-projecao">
    <div class="card-header">
        <h4>Simulação de Custo de Produção</h4>

        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Produto</label>
                <input type="text" class="form-control" readonly value="{{ $produto->nome }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Quantidade a produzir</label>
                <input type="number" min="1" step="1" id="quantidade" class="form-control" value="1">
            </div>

            <div class="col-md-3">
                <label class="form-label">Markup (%)</label>
                <input type="number" min="0" step="0.01" id="markup" class="form-control" value="50">
            </div>

            <div class="col-md-12 text-end mt-2">
                <button type="button" class="btn btn-primary" id="btn-recalcular">
                    <i class="ri-refresh-line"></i> Recalcular
                </button>

                <a href="javascript:void(0)" class="btn btn-danger" id="btn-pdf">
                    <i class="ri-file-pdf-line"></i> Gerar Simulação PDF
                </a>

                <a href="javascript:void(0)" class="btn btn-primary" id="btn-instrucao">
                    <i class="ri-printer-line"></i> Imprimir Instrução
                </a>
            </div>

            <div class="col-md-12">
                <div class="simulacao-bloco">
                    <div class="bloco-header">
                        <h5>Custo de Materiais</h5>
                        <div class="valor-destaque" id="total-materiais">R$ 0,00</div>
                    </div>

                    <button type="button" class="btn btn-primary" onclick="abrirModalComposicao()">
                        <i class="ri-list-check-2"></i> Ver composição completa
                    </button>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>Qtde Total</th>
                                    <th>UM</th>
                                    <th>Categoria</th>
                                    <th>Custo Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-materiais">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Informe a quantidade e clique em recalcular</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="simulacao-bloco">
                    <div class="bloco-header">
                        <h5>Custo de Processo</h5>
                        <div class="valor-destaque" id="subtotal-processo">R$ 0,00</div>
                    </div>

                    <div class="table-responsive">
                        <div class="col-1 text-end">
                            <div class="valor-destaque" id="tempo-total-processo">0 min</div>
                        </div>

                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Operação</th>
                                    <th>Tempo Total</th>
                                    <th>Custo Hora</th>
                                    <th>Setor</th>
                                    <th>C. Custo</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-processos">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Aguardando simulação</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="simulacao-bloco">
                    <h5>Custo Final</h5>

                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <div class="resumo-card">
                                <span>Custo Total Produção</span>
                                <strong id="custo-total">R$ 0,00</strong>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="resumo-card">
                                <span>Custo Unitário</span>
                                <strong id="custo-unitario">R$ 0,00</strong>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="resumo-card">
                                <span>Markup</span>
                                <strong id="markup-view">0%</strong>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="resumo-card resumo-card-success">
                                <span>Preço Sugerido</span>
                                <strong id="preco-sugerido">R$ 0,00</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="simulacao-bloco">
                    <h5>Prazo Estimado</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="resumo-card">
                                <span>Dias em fila</span>
                                <strong id="dias-fila-setores">0 dia(s)</strong>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="resumo-card">
                                <span>Tempo deste pedido</span>
                                <strong id="dias-producao-pedido">0 dia(s)</strong>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="resumo-card">
                                <span>Tempo total produção</span>
                                <strong id="tempo-total-pedido">0 min</strong>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="resumo-card resumo-card-success">
                                <span>Setor gargalo</span>
                                <strong id="gargalo-setor">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <div class="resumo-card">
                                <span>Dias estimados</span>
                                <strong id="prazo-dias">0 dia(s)</strong>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="resumo-card bg-warning">
                                <span>Data estimada entrega</span>
                                <strong id="data-entrega">--/--/----</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="simulacao-bloco">
                    <div class="bloco-header">
                        <h5>Capacidade dos Setores</h5>
                        <div class="valor-destaque">PCP / Gargalos</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Setor</th>
                                    <th>Horas/Dia</th>
                                    <th>Eficiência</th>
                                    <th>Capacidade Real</th>
                                    <th>Fila Atual</th>
                                    <th>Pedido Atual</th>
                                    <th>Total</th>
                                    <th>Previsão</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-capacidade-setores">
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Aguardando cálculo</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalComposicaoCompleta" tabindex="-1" aria-labelledby="modalComposicaoCompletaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalComposicaoCompletaLabel">
                    Composição completa do produto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="conteudo-modal-composicao">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2">Carregando composição...</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .card-projecao{
        border: 0;
        box-shadow: 0 8px 25px rgba(15, 23, 42, .06);
        border-radius: 16px;
    }

    .simulacao-bloco{
        background: #fff;
        border: 1px solid #E5E7EB;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .03);
    }

    .bloco-header{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .valor-destaque{
        background: #EEF2FF;
        color: #4254BA;
        font-weight: 700;
        padding: 10px 14px;
        border-radius: 12px;
    }

    .resumo-card{
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px;
        height: 100%;
    }

    .resumo-card span{
        display: block;
        color: #64748B;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .resumo-card strong{
        font-size: 22px;
        color: #0F172A;
    }

    .resumo-card-success{
        background: #ECFDF5;
        border-color: #BBF7D0;
    }

    .resumo-card-success strong{
        color: #15803D;
    }
</style>
@endsection

@section('js')
<script>
    function moeda(v){
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(parseFloat(v || 0));
    }

    function numero(v, casas = 2){
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: casas,
            maximumFractionDigits: casas
        }).format(parseFloat(v || 0));
    }

    function montarMateriais(lista){
        let html = '';

        if(!lista.length){
            html = `<tr><td colspan="7" class="text-center text-muted">Nenhum material encontrado</td></tr>`;
            $('#tbody-materiais').html(html);
            return;
        }

        lista.forEach(item => {
            html += `
            <tr>
            <td>${item.codigo ?? '--'}</td>
            <td>${item.descricao ?? ''}</td>
            <td>${numero(item.quantidade_total)}</td>
            <td>${item.unidade ?? 'un'}</td>
            <td>${item.categoria ?? ''}</td>
            <td>${moeda(item.custo_unitario)}</td>
            <td>${moeda(item.total)}</td>
            </tr>
            `;
        });

        $('#tbody-materiais').html(html);
    }

    function montarProcessos(lista){
        let html = '';
        let tempoTotal = 0;

        if(!lista.length){
            html = `<tr><td colspan="6" class="text-center text-muted">Nenhum processo encontrado</td></tr>`;
            $('#tbody-processos').html(html);
            $('#tempo-total-processo').text('0 min');
            return;
        }

        lista.forEach(item => {
            tempoTotal += parseFloat(item.tempo_total_min || 0);

            html += `
            <tr>
            <td>${item.operacao ?? ''}</td>
            <td>${numero(item.tempo_total_min, 0)} min</td>
            <td>${moeda(item.custo_hora)}/h</td>
            <td>${item.setor ?? '-'}</td>
            <td>${item.centro_custo ?? '-'}</td>
            <td>${moeda(item.total)}</td>
            </tr>
            `;
        });

        $('#tbody-processos').html(html);
        $('#tempo-total-processo').text(numero(tempoTotal, 0) + ' min');
    }

    function simularCusto(){
        let quantidade = $('#quantidade').val();
        let markup = $('#markup').val();

        if(!quantidade || parseFloat(quantidade) <= 0){
            swal("Atenção", "Informe uma quantidade válida", "warning");
            $('#quantidade').focus();
            return;
        }

        $.ajax({
            url: '{{ route("produtos.rotina.simular-custo", $item->id) }}',
            type: 'GET',
            dataType: 'json',
            data: {
                quantidade: quantidade,
                markup: markup
            },
            beforeSend: function(){
                $('#btn-recalcular').prop('disabled', true).html('<i class="ri-loader-4-line"></i> Calculando...');
            },
            success: function(res){
                if(res.success){
                    montarMateriais(res.materiais || []);
                    montarProcessos(res.processos || []);

                    $('#total-materiais').text(moeda(res.total_materiais));
                    $('#subtotal-processo').text(moeda(res.subtotal_processo));

                    $('#custo-total').text(moeda(res.custo_total));
                    $('#custo-unitario').text(moeda(res.custo_unitario));
                    $('#markup-view').text(numero(res.markup) + '%');
                    $('#preco-sugerido').text(moeda(res.preco_sugerido));

                    $('#prazo-dias').text((res.prazo_dias || 0) + ' dia(s)');
                    $('#data-entrega').text(res.data_entrega || '--/--/----');

                    $('#dias-fila-setores').text((res.dias_fila_setores || 0) + ' dia(s)');
                    $('#dias-producao-pedido').text((res.dias_producao_pedido || 0) + ' dia(s)');
                    $('#gargalo-setor').text(res.gargalo_setor || '-');

                    $('#tempo-total-pedido').text(numero(res.total_minutos_pedido || 0, 0) + ' min');

                    montarCapacidadeSetores(res.capacidade_setores || []);
                }
            },
            error: function(xhr){
                let msg = 'Erro ao simular custo';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                swal("Erro", msg, "error");
            },
            complete: function(){
                $('#btn-recalcular').prop('disabled', false).html('<i class="ri-refresh-line"></i> Recalcular');
            }
        });
    }

    $(function(){
        $('#btn-recalcular').on('click', function(){
            simularCusto();
        });

        $('#btn-pdf').on('click', function(){
            let quantidade = $('#quantidade').val();
            let markup = $('#markup').val();

            if(!quantidade || parseFloat(quantidade) <= 0){
                swal("Atenção", "Informe uma quantidade válida", "warning");
                return;
            }

            let url = `{{ route('produtos.rotina.simulacao-pdf', $item->id) }}?quantidade=${quantidade}&markup=${markup}`;
            window.open(url, '_blank');
        });

        $('#btn-instrucao').on('click', function(){
            let quantidade = $('#quantidade').val();
            let markup = $('#markup').val();

            if(!quantidade || parseFloat(quantidade) <= 0){
                swal("Atenção", "Informe uma quantidade válida", "warning");
                return;
            }

            let url = `{{ route('produtos.rotina.print-instrucao', $item->id) }}?quantidade=${quantidade}&markup=${markup}`;
            window.open(url, '_blank');
        });

        simularCusto();
    });

    function abrirModalComposicao() {
        let modal = new bootstrap.Modal(document.getElementById('modalComposicaoCompleta'));
        modal.show();

        $('#conteudo-modal-composicao').html(`
            <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Carregando composição...</div>
            </div>
            `);

        $.get("{{ route('produtos.composicao-completa', $item->produto->id) }}")
        .done(function(response) {
            $('#conteudo-modal-composicao').html(response);
        })
        .fail(function() {
            $('#conteudo-modal-composicao').html(`
                <div class="alert alert-danger mb-0">
                Não foi possível carregar a composição completa.
                </div>
                `);
        });
    }

    function montarCapacidadeSetores(lista){
        let html = '';

        if(!lista.length){
            html = `<tr><td colspan="8" class="text-center text-muted">Nenhum setor encontrado</td></tr>`;
            $('#tbody-capacidade-setores').html(html);
            return;
        }

        lista.forEach(item => {
            html += `
            <tr>
            <td>${item.setor ?? '-'}</td>
            <td>${numero(item.horas_dia, 2)}h</td>
            <td>${numero(item.eficiencia, 0)}%</td>
            <td><strong>${numero(item.capacidade_dia_horas, 2)}h</strong></td>
            <td>${item.dias_fila ?? 0} dia(s)</td>
            <td>${item.dias_pedido ?? 0} dia(s)</td>
            <td><strong>${item.dias_total ?? 0} dia(s)</strong></td>
            <td>${item.data_prevista ?? '--/--/----'}</td>
            </tr>
            `;
        });

        $('#tbody-capacidade-setores').html(html);
    }
</script>
@endsection