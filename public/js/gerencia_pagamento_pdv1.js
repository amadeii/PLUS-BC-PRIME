var obrigaCliente = 0;
$(document).ready(function() {

    const hoje = new Date();
    const dataVencimentoPadrao = new Date();
    dataVencimentoPadrao.setDate(hoje.getDate() + 30);

    $('.data_atual').val(formatarData(hoje));
    $('.data_vencimento_padrao').val(formatarData(dataVencimentoPadrao));
    $('#data_primeiro_pagamento').val(formatarData(dataVencimentoPadrao));

    const tiposPagamento = {

        '01': { nome: 'Dinheiro', tipo: 'vista' },
        '17': { nome: 'PIX', tipo: 'vista' },
        '10': { nome: 'Vale Alimentação', tipo: 'vista' },
        '11': { nome: 'Vale Refeição', tipo: 'vista' },
        '02': { nome: 'Cheque', tipo: 'vista' },
        '13': { nome: 'Vale Presente', tipo: 'vista' },
        '14': { nome: 'Vale Combustível', tipo: 'vista' },
        '16': { nome: 'Depósito Bancário', tipo: 'vista' },

        '04': { nome: 'Cartão de Débito', tipo: 'cartao' },
        '03': { nome: 'Cartão de Crédito', tipo: 'cartao' },

        '06': { nome: 'Crediário', tipo: 'prazo' },
        '05': { nome: 'Crédito Loja', tipo: 'prazo' },
        '15': { nome: 'Boleto', tipo: 'prazo' },
        '14': { nome: 'Duplicata Mercantil', tipo: 'prazo' },

        '07': { nome: 'Cartão de Crédito TEF', tipo: 'tef' },
        '08': { nome: 'Cartão de Débito TEF', tipo: 'tef' },
        '12': { nome: 'PIX TEF', tipo: 'tef' }
    };

    let pagamentoId = 0;

    function getValorTotal() {
        // var totalProdutos = convertMoedaToFloat($('.total-venda').text())
        // let total = totalProdutos + parseFloat(VALORACRESCIMO) + parseFloat(VALORFRETE) - parseFloat(DESCONTO)

        let total = convertMoedaToFloat($('#painel-total-venda').text())
        return total;
    }

    $(document).ready(function() {
        setTimeout(function() {
            const valorTotal = getValorTotal();
            if (valorTotal > 0) {
                $('#valor-restante').text(`R$ ${convertFloatToMoeda(valorTotal)}`);
                $('#mensagem-pagamento').html('<span class="text-warning">Adicione pagamentos para cobrir o valor total da venda.</span>');
            }
        }, 500);
    });

    // $('#cliente').on('hidden.bs.modal', function () {
    //     console.log($('.cliente_selecionado').text().trim())
    //     if(obrigaCliente == 1 && $('.cliente_selecionado').text().trim() == 'selecionar cliente'){
    //         toastr.warning("Selecione o cliente, obrigatório!");
    //         $('.cliente_selecionado').trigger('click')
    //     }
    // });

    $('#tipo_pagamento_atual').on('change', function() {
        const tipoPagamento = $(this).val();

        $('.campos-especificos').addClass('d-none');

        if (!tipoPagamento) return;

        if (tiposPagamento[tipoPagamento]) {
            const tipo = tiposPagamento[tipoPagamento].tipo;

            // if(tipo == 'prazo'){
            //     toastr.warning("Selecione o cliente para finalizar a venda!");
            //     $('.cliente_selecionado').trigger('click')
            //     obrigaCliente = 1
            // }

            switch(tipo) {
                case 'cartao':
                $('.campos-cartao').removeClass('d-none');
                break;
                case 'prazo':
                $('.campos-prazo').removeClass('d-none');
                $('#tem_parcelamento').prop('checked', false);
                $('.campos-parcelamento').addClass('d-none');
                break;
                case 'tef':

                break;
            }
        }

        setTimeout(function() {
            const valorRestante = calcularValorRestante();
            if (valorRestante > 0) {
                $('#valor_pagamento_atual').val(convertFloatToMoeda(valorRestante));
            }
        }, 100);
    });

    $('#tem_parcelamento').on('change', function() {
        if ($(this).is(':checked')) {
            $('.div-data-vencimento').addClass('d-none');
            $('.campos-parcelamento').removeClass('d-none');
        } else {
            $('.div-data-vencimento').removeClass('d-none');
            $('.campos-parcelamento').addClass('d-none');
        }
    });

    window.calcularValorRestante = function() {
        let valorTotal = getValorTotal();
        let totalPago = pagamentosRegistrados.reduce((sum, item) => sum + parseFloat(item.valor), 0);

        totalPago = Math.round(totalPago * 100) / 100;
        valorTotal = Math.round(valorTotal * 100) / 100;

        let valorRestante = valorTotal - totalPago;

        if (valorRestante > -0.01 && valorRestante < 0) {
            valorRestante = 0;
        }

        // Atualizar os valores exibidos
        $('#valor-total-venda').text('R$ ' + convertFloatToMoeda(valorTotal));
        $('#valor-pago').text('R$ ' + convertFloatToMoeda(totalPago));
        $('#valor-restante').text('R$ ' + convertFloatToMoeda(valorRestante));

        // Calcular e exibir o troco se aplicável
        const troco = totalPago > valorTotal ? totalPago - valorTotal : 0;
        $('#valor-troco').text('R$ ' + convertFloatToMoeda(troco));

        // console.log("Calculando valor restante. Total da venda:", valorTotal, "Total pago:", totalPago, "Restante:", valorRestante);

        return valorRestante;
    }

    $('.btn-gerar-fatura').click(() => {
        let valorRestante = calcularValorRestante();

        if(valorRestante <= 0){
            swal("Atenção", "Não existe valor restante para gerar fatura!", "warning");
            return;
        }

        $('#pagamento_multiplo').modal('hide');
        $('#modal_fatura_venda').modal('show');

        $('.lbl-total_fatura').text("R$ " + convertFloatToMoeda(valorRestante));
        $('#inp-entrada_fatura').val('');
    });

    $('#adicionar-pagamento').on('click', function() {
        // console.log("Handler original do botão adicionar-pagamento acionado");

        // $('.btn-gerar-fatura')
        // .prop('disabled', true)
        // .addClass('disabled btn-secondary')
        // .removeClass('btn-dark');

        const tipoPagamento = $('#tipo_pagamento_atual').val();
        const valorPagamento = $('#valor_pagamento_atual').val();

        if (!tipoPagamento) {
            swal('Atenção', 'Selecione um tipo de pagamento', 'warning');
            return;
        }

        if (!valorPagamento || parseFloat(valorPagamento.replace(/\./g, '').replace(',', '.')) <= 0) {
            swal('Atenção', 'Informe um valor válido para o pagamento', 'warning');
            return;
        }

        if ((tipoPagamento == '06' || tipoPagamento == '05' || tipoPagamento == '15') && $("#inp-cliente_id").val() == null) {
            swal('Atenção', 'Selecione um cliente para pagamento a prazo', 'warning');
            return;
        }

        const valorNumerico = parseFloat(valorPagamento.replace(/\./g, '').replace(',', '.'));
        let detalhes = '';
        let pagamento = {
            id: pagamentoId++,
            tipo: tipoPagamento,
            tipoNome: $('#tipo_pagamento_atual option:selected').text(),
            valor: valorNumerico,
            valorFormatado: valorPagamento
        };

        if (tiposPagamento[tipoPagamento]) {
            const tipo = tiposPagamento[tipoPagamento].tipo;

            switch(tipo) {
                case 'cartao':
                const bandeira = $('#bandeira_cartao').val();
                const codAutorizacao = $('#cod_autorizacao').val();

                pagamento.bandeira = bandeira;
                pagamento.codAutorizacao = codAutorizacao;

                if (bandeira) {
                    detalhes += `Bandeira: ${bandeira}`;
                }
                if (codAutorizacao) {
                    detalhes += detalhes ? ` | Autorização: ${codAutorizacao}` : `Autorização: ${codAutorizacao}`;
                }
                break;

                case 'prazo':
                const temParcelamento = $('#tem_parcelamento').is(':checked');

                if (temParcelamento) {
                    const qtdParcelas = $('#qtd_parcelas').val();
                    const dataPrimeiroPagamento = $('#data_primeiro_pagamento').val();
                    const periodicidade = $('#periodicidade').val();

                    pagamento.parcelado = true;
                    pagamento.parcelas = qtdParcelas;
                    pagamento.dataPrimeiroPagamento = dataPrimeiroPagamento;
                    pagamento.periodicidade = periodicidade;

                    detalhes = `${qtdParcelas}x de ${convertFloatToMoeda(valorNumerico / qtdParcelas)} | Início: ${formatarDataBR(dataPrimeiroPagamento)}`;
                } else {
                    const dataVencimento = $('#data_vencimento_atual').val();

                    pagamento.parcelado = false;
                    pagamento.dataVencimento = dataVencimento;

                    detalhes = `Vencimento: ${formatarDataBR(dataVencimento)}`;
                }
                break;
            }
        }

        pagamento.detalhes = detalhes;
        console.log("pagamento", pagamento)
        pagamentosRegistrados.push(pagamento);

        window.pagamentosRegistradosPDV = pagamentosRegistrados;

        const $form = $('#form-pdv');

        $form.find('input[name^="pagamentos"]').remove();


        window.pagamentosRegistradosPDV.forEach((pag, idx) => {

            $form.append(`<input type="hidden" name="pagamentos[${idx}][tipo]" value="${pag.tipo}">`);
            $form.append(`<input type="hidden" name="pagamentos[${idx}][valor]" value="${pag.valor}">`);

            if (pag.bandeira) {
                $form.append(`<input type="hidden" name="pagamentos[${idx}][bandeira]" value="${pag.bandeira}">`);
            }

            if (pag.codAutorizacao) {
                $form.append(`<input type="hidden" name="pagamentos[${idx}][cod_autorizacao]" value="${pag.codAutorizacao}">`);
            }

            if (pag.dataVencimento) {
                $form.append(`<input type="hidden" name="pagamentos[${idx}][data_vencimento]" value="${pag.dataVencimento}">`);
            }

            if (pag.parcelado) {
                $form.append(`<input type="hidden" name="pagamentos[${idx}][parcelado]" value="1">`);
                $form.append(`<input type="hidden" name="pagamentos[${idx}][parcelas]" value="${pag.parcelas}">`);
                $form.append(`<input type="hidden" name="pagamentos[${idx}][data_primeiro_pagamento]" value="${pag.dataPrimeiroPagamento}">`);
                $form.append(`<input type="hidden" name="pagamentos[${idx}][periodicidade]" value="${pag.periodicidade}">`);
            }
        });



        atualizarListaPagamentos();
        atualizarResumoPagamentos()

        $('#tipo_pagamento_atual').val('');
        $('#valor_pagamento_atual').val('');
        $('.campos-especificos').addClass('d-none');
        $('#bandeira_cartao').val('');
        $('#cod_autorizacao').val('');
        $('#tem_parcelamento').prop('checked', false);
    });

$(document).on("click", ".btn-pagamento-rapido", function(){

    let tipo = $(this).data("tipo");

    if(tipo == '06'){

        if(!$('#inp-cliente_id').val()){
            swal("Atenção", "Selecione um cliente para pagamento no crediário!", "warning").then(() => {
                $('.cliente_selecionado').trigger('click');
            });
            return;
        }

        let valorRestante = calcularValorRestante();

        if(valorRestante <= 0){
            swal("Atenção", "Não existe valor restante para lançar no crediário!", "warning");
            return;
        }

        $.get(path_url + "api/frenteCaixa/opcoes-crediario", {
            empresa_id: $('#empresa_id').val(),
            valor: convertFloatToMoeda(valorRestante)
        })
        .done((res) => {

            let html = `<div class="row g-2">`;

            res.map((x) => {
                html += `
                <div class="col-md-4">
                <div class="card shadow-sm border">
                <div class="card-body">
                <h5 class="mb-1">${x.parcelas}x de R$ ${convertFloatToMoeda(x.valor_parcela)}</h5>
                <small class="text-muted d-block">Total: R$ ${convertFloatToMoeda(x.valor_total)}</small>
                <small class="text-muted d-block">Juros: ${x.juros}%</small>
                <small class="text-muted d-block">1º vencimento: ${formatarDataBR(x.primeiro_vencimento)}</small>

                <button type="button" class="btn btn-success btn-sm w-100 mt-2 btn-selecionar-crediario"
                data-parcelas="${x.parcelas}"
                data-valor-total="${x.valor_total}"
                data-primeiro-vencimento="${x.primeiro_vencimento}"
                data-intervalo="${x.intervalo}">
                Selecionar
                </button>
                </div>
                </div>
                </div>
                `;
            });

            html += `</div>`;

            $('#lista-opcoes-crediario').html(html);
            $('#modal_opcoes_crediario').modal('show');
        })
        .fail((err) => {
            swal("Atenção", err.responseJSON, "warning");
        });

        return;
    }

    $('#tipo_pagamento_atual').val(tipo).change();

    setTimeout(() => {
        $('#adicionar-pagamento').trigger('click');
        setTimeout(() => {
            $('#finalizar_venda_tab').trigger('click');
        }, 50);
    }, 200);
});

$(document).on('click', '.btn-selecionar-crediario', function(){

    let parcelas = parseInt($(this).data('parcelas'));
    let valorTotal = parseFloat($(this).data('valor-total'));
    let primeiroVencimento = $(this).data('primeiro-vencimento');
    let intervalo = parseInt($(this).data('intervalo'));

    let valorParcela = valorTotal / parcelas;

    $('#modal_opcoes_crediario').modal('hide');

    for(let i = 1; i <= parcelas; i++){

        let vencimento = new Date(primeiroVencimento + 'T00:00:00');
        vencimento.setDate(vencimento.getDate() + ((i - 1) * intervalo));

        let dataVencimento = vencimento.toISOString().split('T')[0];

        pagamentosRegistrados.push({
            id: pagamentoId++,
            tipo: '06',
            tipoNome: 'Crediário',
            valor: valorParcela,
            valorFormatado: convertFloatToMoeda(valorParcela),
            dataVencimento: dataVencimento,
            detalhes: `${i}/${parcelas} - Vencimento: ${formatarDataBR(dataVencimento)}`
        });
    }

    window.pagamentosRegistradosPDV = pagamentosRegistrados;

    atualizarListaPagamentos();
    atualizarResumoPagamentos();

    $('#finalizar_venda_tab').trigger('click');
});

$('.btn-store-fatura').click(() => {
    console.clear()

    if (!$('#inp-cliente_id').val()) {
        swal("Atenção", "Selecione um cliente para gerar fatura a prazo!", "warning").then(() => {
            $('#modal_fatura_venda').modal('hide');
            $('.cliente_selecionado').trigger('click');
        });
        return;
    }

    if(!$('#inp-parcelas_fatura').val()){
        swal("Erro", "Informe a quantidade de parcelas!", "error")
        return;
    }
    if(!$('#inp-intervalo_fatura').val()){
        swal("Erro", "Informe o intervalo!", "error")
        return;
    }
    let data = {
        entrada_fatura: $('#inp-entrada_fatura').val(),
        parcelas_fatura: $('#inp-parcelas_fatura').val(),
        intervalo_fatura: $('#inp-intervalo_fatura').val(),
        primeiro_vencimento_fatura: $('#inp-primeiro_vencimento_fatura').val(),
        tipo_pagamento_fatura: $('#inp-tipo_pagamento_fatura').val(),
        total: calcularValorRestante()
    }
    // console.log(data)
    $.get(path_url + "api/frenteCaixa/gerar-fatura-pdv", data)
    .done((res) => {
        console.log(res)
        setTimeout(() => {

            $('#modal_fatura_venda').modal('hide')
            pagamentosRegistrados = []
            let pagamentoId = 0
            res.map((x) => {
                let pagamento = {
                    id: pagamentoId++,
                    tipo: $('#inp-tipo_pagamento_fatura').val(),
                    tipoNome: $('#inp-tipo_pagamento_fatura option:selected').text(),
                    valor: x.valor,
                    valorFormatado: convertFloatToMoeda(x.valor),
                    dataVencimento: x.vencimento,
                    detalhes: `Vencimento: ${formatarDataBR(x.vencimento)}`
                };
                // console.log(pagamento)
                pagamentosRegistrados.push(pagamento);
                window.pagamentosRegistradosPDV = pagamentosRegistrados;
                atualizarListaPagamentos();
                atualizarResumoPagamentos()
            })

        }, 100)
        

    })
    .fail((err) => {
        console.log(err);
    });
})

$(function(){
    let fatura = $('#fatura_venda').val()
    if(fatura){
        fatura = JSON.parse(fatura)
        pagamentosRegistrados = []
        let pagamentoId = 0
        fatura.map((f) => {
            $('#inp-tipo_pagamento_fatura').val(f.tipo_pagamento).change()
            let pagamento = {
                id: pagamentoId++,
                tipo: f.tipo_pagamento,
                tipoNome: $('#inp-tipo_pagamento_fatura option:selected').text(),
                valor: parseFloat(f.valor),
                valorFormatado: convertFloatToMoeda(parseFloat(f.valor)),
                dataVencimento: f.data_vencimento,
                detalhes: `Vencimento: ${formatarDataBR(f.data_vencimento)}`
            };
            pagamentosRegistrados.push(pagamento);

        })

        setTimeout(() => {
            window.pagamentosRegistradosPDV = pagamentosRegistrados;
            atualizarListaPagamentos();
            atualizarResumoPagamentos()
        }, 100)
    }
})

function formatarData(data) {
    const dia = data.getDate().toString().padStart(2, '0');
    const mes = (data.getMonth() + 1).toString().padStart(2, '0');
    const ano = data.getFullYear();
    return `${ano}-${mes}-${dia}`;
}

function formatarDataBR(dataIso) {
    if (!dataIso) return '';
    const [ano, mes, dia] = dataIso.split('-');
    return `${dia}/${mes}/${ano}`;
}

function atualizarListaPagamentos() {
    const $lista = $('#lista-pagamentos');
    const $semPagamentos = $('#sem-pagamentos');
    const $tabelaPagamentos = $('#tabela-pagamentos');
    const $finalizarBtn = $('#finalizar_venda_tab');


    $lista.empty();

    const valorTotal = getValorTotal();
    // console.log('Valor total para cálculo do restante:', valorTotal);

    if (pagamentosRegistrados.length === 0) {
        $semPagamentos.removeClass('d-none');
        $tabelaPagamentos.addClass('d-none');
        $finalizarBtn.prop('disabled', true);

        $('#valor-recebido-total').text(`R$ 0,00`);
        $('#valor-restante').text(`R$ ${convertFloatToMoeda(valorTotal)}`);

        const $valorRestanteBadge = $('#valor-restante-badge');
        $valorRestanteBadge.removeClass('bg-success text-white').addClass('bg-warning text-dark');

        $('#mensagem-pagamento').html('<span class="text-warning">Adicione pagamentos para cobrir o valor total da venda.</span>');

        return;
    } else {
        $semPagamentos.addClass('d-none');
        $tabelaPagamentos.removeClass('d-none');

        pagamentosRegistrados.forEach(pagamento => {
            const $row = $('<tr>');
            $row.append(`<td>${pagamento.tipoNome}</td>`);
            $row.append(`<td>R$ ${pagamento.valorFormatado}</td>`);
            $row.append(`<td>${pagamento.detalhes || '-'}</td>`);

            const $acoes = $('<td>');

            const $btnEditar = $('<button class="btn btn-sm btn-table-action btn-warning me-1" type="button">').html('<i class="ri-edit-line"></i>');

            $btnEditar.on('click', function() {
                abrirEdicaoPagamento(pagamento.id);
            });

            const $btnRemover = $('<button class="btn btn-sm btn-table-action btn-danger" type="button">').html('<i class="ri-delete-bin-line"></i>');

            $btnRemover.on('click', function() {
                removerPagamento(pagamento.id);
            });

            $acoes.append($btnEditar);
            $acoes.append($btnRemover);
            $row.append($acoes);

            $lista.append($row);
        });
    }

    const valorPago = pagamentosRegistrados.reduce((total, p) => total + p.valor, 0);
    const valorRestante = calcularValorRestante();

    $('#valor-recebido-total').text(`R$ ${convertFloatToMoeda(valorPago)}`);
    $('#valor-restante').text(`R$ ${convertFloatToMoeda(valorRestante)}`);


    const valorTotalVenda = getValorTotal();
    const troco = valorPago > valorTotalVenda ? valorPago - valorTotalVenda : 0;
    $('#valor-troco').text(`R$ ${convertFloatToMoeda(troco)}`);


    if (troco > 0) {
        $('#valor-troco').removeClass('text-gray-800').addClass('text-success');

        if (valorRestante <= 0.01) {
            $('#mensagem-pagamento').html('<span class="text-success">Pagamento completo. Troco disponível: R$ ' + convertFloatToMoeda(troco) + '. Você já pode finalizar a venda.</span>');
        }
    } else {
        $('#valor-troco').removeClass('text-success').addClass('text-gray-800');
    }

    const $valorRestanteBadge = $('#valor-restante-badge');

    if (valorRestante <= 0.01) {
        $valorRestanteBadge.removeClass('bg-warning').addClass('bg-success text-white');
        $finalizarBtn.prop('disabled', false);
        $('#mensagem-pagamento').html('<span class="text-success">Pagamento completo. Você já pode finalizar a venda.</span>');
    } else {
        $valorRestanteBadge.removeClass('bg-success text-white').addClass('bg-warning text-dark');
        $finalizarBtn.prop('disabled', true);
        $('#mensagem-pagamento').html('<span class="text-warning">Adicione pagamentos suficientes para cobrir o valor total antes de finalizar.</span>');
    }
}

function abrirEdicaoPagamento(id) {
    const pagamento = pagamentosRegistrados.find(p => p.id == id);

    if(!pagamento){
        return;
    }

    $('#edit-pagamento-id').val(pagamento.id);
    $('#edit-tipo-pagamento').val(pagamento.tipo).change();
    $('#edit-valor-pagamento').val(pagamento.valorFormatado);
    $('#edit-data-vencimento').val(pagamento.dataVencimento || '');

    $('#modal_editar_pagamento').modal('show');
}

$('#btn-salvar-edicao-pagamento').on('click', function () {

    let id = $('#edit-pagamento-id').val();
    let tipo = $('#edit-tipo-pagamento').val();
    let tipoNome = $('#edit-tipo-pagamento option:selected').text();
    let valor = convertMoedaToFloat($('#edit-valor-pagamento').val());
    let dataVencimento = $('#edit-data-vencimento').val();

    // valida cliente para pagamentos a prazo
    if ((tipo == '06' || tipo == '05' || tipo == '15') && !$('#inp-cliente_id').val()) {

        swal(
            "Atenção",
            "Selecione um cliente para pagamentos a prazo!",
            "warning"
            ).then(() => {
                $('#modal_editar_pagamento').modal('hide');
                $('.cliente_selecionado').trigger('click');
            });

            return;
        }

        if(!tipo){
            swal("Atenção", "Selecione o tipo de pagamento!", "warning");
            return;
        }

        if(valor <= 0){
            swal("Atenção", "Informe um valor maior que zero!", "warning");
            return;
        }

        pagamentosRegistrados = pagamentosRegistrados.map(p => {

            if(p.id == id){

                p.tipo = tipo;
                p.tipoNome = tipoNome;
                p.valor = valor;
                p.valorFormatado = convertFloatToMoeda(valor);
                p.dataVencimento = dataVencimento;
                p.detalhes = dataVencimento
                ? `Vencimento: ${formatarDataBR(dataVencimento)}`
                : '-';
            }

            return p;
        });

        window.pagamentosRegistradosPDV = pagamentosRegistrados;

        atualizarListaPagamentos();
        atualizarResumoPagamentos();

        $('#modal_editar_pagamento').modal('hide');
    });

function removerPagamento(id) {
    pagamentosRegistrados = pagamentosRegistrados.filter(p => p.id !== id);
    window.pagamentosRegistradosPDV = pagamentosRegistrados;
    atualizarListaPagamentos();
    atualizarResumoPagamentos();
}

function atualizarResumoPagamentos() {

    try {


        if (typeof window.pagamentosRegistradosPDV === 'undefined' || !window.pagamentosRegistradosPDV || window.pagamentosRegistradosPDV.length === 0) {
            // console.log("Nenhum pagamento encontrado na variável global");
            $('#lista-resumo-pagamentos-modal').html('<div class="alert alert-warning mb-0 text-center"><i class="bx bx-error me-1"></i>Nenhum pagamento encontrado! Por favor, registre ao menos uma forma de pagamento antes de finalizar a venda.</div>');
            return;
        }

        // console.log("Pagamentos encontrados: " + window.pagamentosRegistradosPDV.length);

        let valorTotalVenda = getValorTotal();

        let htmlPagamentos = '';
        let valorTotalPago = 0;

        window.pagamentosRegistradosPDV.forEach(pagamento => {
            valorTotalPago += parseFloat(pagamento.valor);

            const valorFormatado = pagamento.valorFormatado || pagamento.valor.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            htmlPagamentos += `<div class="mb-1"><strong>${pagamento.tipoNome}:</strong> R$ ${valorFormatado}`;

            if (pagamento.detalhes) {
                htmlPagamentos += ` <small class="text-muted">(${pagamento.detalhes})</small>`;
            }

            htmlPagamentos += '</div>';
        });


        htmlPagamentos += `<div class="mt-2 pt-2 border-top"><strong>Total Pago:</strong> R$ ${valorTotalPago.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}</div>`;


        if (valorTotalVenda > 0 && valorTotalPago > valorTotalVenda) {
            const troco = valorTotalPago - valorTotalVenda;
            htmlPagamentos += `<div class="text-success"><strong>Troco:</strong> R$ ${troco.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })}</div>`;
        }

        $('#lista-resumo-pagamentos-modal').html(htmlPagamentos);

        if (valorTotalVenda > 0) {
            $('.total-venda-modal').text('R$ ' + valorTotalVenda.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        } else {

            $('.total-venda-modal').text('R$ ' + valorTotalPago.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }

        // console.log("Resumo de pagamentos atualizado com sucesso");
    } catch (error) {
        console.log("Erro ao atualizar resumo: " + error.message);

    }

}


//  para fatura padrão
// fatura padrão do cliente


$(document).on('click', '.btn-fatura-padrao', function(){
    console.log(faturaPadraoCliente)
    if(!faturaPadraoCliente || faturaPadraoCliente.length == 0){
        swal("Atenção", "Cliente sem fatura padrão!", "warning");
        return;
    }

    let valorRestante = calcularValorRestante();

    if(valorRestante <= 0){
        swal("Atenção", "Não existe valor restante!", "warning");
        return;
    }

    let quantidade = faturaPadraoCliente.length;
    let valorParcela = valorRestante / quantidade;
    let pagamentoId = 0;

    faturaPadraoCliente.forEach((f) => {

        let vencimento = new Date();
        vencimento.setDate(vencimento.getDate() + parseInt(f.dias_vencimento));

        let dataVencimento = vencimento.toISOString().split('T')[0];

        pagamentosRegistrados.push({
            id: pagamentoId++,
            tipo: f.tipo_pagamento,
            tipoNome: f.tipo_pagamento_nome,
            valor: valorParcela,
            valorFormatado: convertFloatToMoeda(valorParcela),
            dataVencimento: dataVencimento,
            detalhes: `Vencimento: ${formatarDataBR(dataVencimento)}`
        });
    });

    atualizarListaPagamentos();
    atualizarResumoPagamentos();
});

});

let faturaPadraoCliente = null;

function verificarFaturaPadraoCliente(clienteId){

    faturaPadraoCliente = null;
    $('.btn-fatura-padrao').addClass('d-none');

    if(!clienteId){
        return;
    }

    $.get(path_url + "api/frenteCaixa/fatura-cliente/" + clienteId)
    .done((res) => {
        console.log("res", res)

        if(!res.tem_fatura || !res.faturas){
            return;
        }
        console.log("res", res)

        faturaPadraoCliente = res.faturas;
        $('.btn-fatura-padrao').removeClass('d-none');
    })
    .fail(() => {
        faturaPadraoCliente = null;
        $('.btn-fatura-padrao').addClass('d-none');
    });
}
