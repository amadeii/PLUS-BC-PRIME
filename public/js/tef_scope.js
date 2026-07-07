const ip = window.TEF_CONFIG?.ip || "127.0.0.1";
const porta = window.TEF_CONFIG?.porta || "8000";

var baseUrl = `http://${ip}:${porta}/api`;

// alert(baseUrl)

function verificarTef() {

    $.ajax({
        url: baseUrl + "/tefgp-status",
        method: "POST",
        contentType: "application/json",
        timeout: 3000,
        dataType: "json",
        success: function(res) {
            if (res.comando === "ATV" && res.erro === "" && res.regFinalizador === "0") {
                console.log("✅ TEF ativo");
            } else {
                console.log("⚠ TEF respondeu mas não está OK");
            }
        },
        error: function(err) {
            console.log("❌ TEF offline");
            $('#tipo_pagamento_atual option[value="00"]').remove();
        }
    });
}

$(function(){
    verificarTef()

    // carregarVendasTEF()

    // getProdutoCodBarras("7891000619162", (data) => {
    //     setTimeout(() => {
    //         addItem();
    //         $('#tipo-pagamento').val('00').change()
    //         setTimeout(() => {
    //             $('#finalizar-venda').trigger('click')
    //         }, 400)
    //     }, 400)
    // })
})

function reaisParaCentavos(valor) {
    if (valor === null || valor === undefined) return 0;

    const numero = Number(String(valor).trim().replace(/\./g, "").replace(",", "."));

    if (!Number.isFinite(numero)) {
        throw new Error("Valor inválido para conversão");
    }

    return Math.round((numero + Number.EPSILON) * 100);
}

function processarPagamentoScope(valor, tipo){

    const centavos = Math.round((Number(valor) + Number.EPSILON) * 100);

    let tipoTransacao = null
    if(tipo == 'debito'){
        tipoTransacao = '20'
    }
    if(tipo == 'credito'){
        tipoTransacao = '10'
    }
    $.ajax({
        url: baseUrl + "/tefgp-req",
        method: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({
            comando: "CRT",
            valorTotal: centavos,
            tipoTransacao: tipoTransacao,
        }),
        success: function(res) {
            console.log("Resposta CRT:", res);
            if (res.status === "APROVADA") {

                try {

                    salvarRetornoServidor(res);
                    confirmarTransacaoTEF(res);

                    swal("Sucesso", "Transação aprovada!", "success")
                    .then(() => {
                        $('#modal-tef').modal('hide');
                        SESSIONIDTEF = res.codigoAutorizacao;

                        finalizarVendaModal()
                    });

                } catch (e) {
                    swal("Erro", "Falha ao salvar transação no servidor", "error");
                }

            } else {

                console.warn("Transação não aprovada:", res);

                swal(
                    "Transação não aprovada",
                    res.erro || "Pagamento não autorizado",
                    "error"
                    );

                $('.tef-opcao').removeAttr('disabled').removeClass('disabled');

                tefStatus('err', 'Transação não aprovada');

            }
        },
        error: function(err) {
            console.error("Erro:", err.responseText);
        }
    });
}

function confirmarTransacaoTEF(raw) {

    const payload = {
        comando: "CNF", // ⚠️ confirmar no manual se é CNF mesmo
        identificacao: 1,
        docFiscal: 0,
        adquirente: raw.adquirente,
        controle: raw.controle,
        versaoInterface: 1,
        nomeAutomacao: "PDV_WEB",
        versaoAutomacao: "1.0.0",
        registroCertificacao: "000000"
    };

    $.ajax({
        url: baseUrl + "/tefgp-conf",
        method: "POST",
        contentType: "application/json",
        // ❗ NÃO colocar dataType: "json" para evitar erro de parse
        data: JSON.stringify(payload),

        success: function(response) {
            console.log("Confirmação TEF OK:", response);
        },

        error: function(err) {
            console.error("Erro ao confirmar TEF:", err.responseText);
        }
    });
}

async function salvarRetornoServidor(data) {

    if (!data) {
        console.error("Retorno TEF inválido");
        return;
    }

    // 🔥 Converte valor para centavos
    const valorCentavos = Math.round((Number(data.valor) + Number.EPSILON) * 100);

    let tipoTransacao = ''
    if(data.tipoTransacao == 20){
        tipoTransacao = 2
    }else if(data.tipoTransacao == 10){
        tipoTransacao = 3
    }else{
        tipoTransacao = 99
    }
    const payload = {
        tef_session_id: data.txId || null,
        tef_terminal_id: data.codigoPdv || null,
        tef_store_id: data.cnpjAdiquirente || null,

        tef_clisitef_status: data.status || null,
        tef_function_id: tipoTransacao,
        tef_controle: data.controle || null,
        tef_sitef_ip: null, // se você tiver essa info

        tef_nsu: data.nsu || null,
        tef_codigo_autorizacao: data.codigoAutorizacao || null,
        tef_bandeira: data.bandeira || null,
        tef_adquirente: data.adquirente || null,

        valor_centavos: valorCentavos,

        tef_raw: data, // salva JSON completo
        comprovantes: {
            cliente: data.impressoCliente || [],
            loja: data.impressoLoja || []
        }
    };

    console.log("payload", payload)

    try {
        const response = await fetch(getServerBase()+'/tef-store-log-scope', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error("Erro ao salvar retorno TEF");
        }

        console.log("Retorno TEF salvo com sucesso");
    } catch (error) {
        console.error("Erro ao enviar TEF para servidor:", error);
    }
}

function getServerBase() {
    return window.location.origin;
}

$('#tef-cancelar').on('click', async function () {
    $('#modal-tef').modal('hide')
})

$('.tef-opcao').on('click', async function () {
    const btn = $(this);
    const tipo = btn.data('tipo');

    let tipoStr = ''
    if(tipo == 'credito'){
        tipoStr = `Tipo de pagamento <strong>Crédito</strong>`
    }else if(tipo == 'debito'){
        tipoStr = `Tipo de pagamento <strong>Débito</strong>`
    }else if(tipo == 'voucher'){
        tipoStr = `Tipo de pagamento <strong>Voucher</strong>`
    }
    tefStatus('info', tipoStr)
    tefLog(tipoStr)
    $('.tef-opcao').attr('disabled', 'disabled').addClass('disabled')
    
    let valorTotal = convertMoedaToFloat($('#painel-total-venda').text())

    processarPagamentoScope(valorTotal, tipo)
})

$(document).ready(function () {

    // carregarVendasTEF()
    // adicionar produto
    // addProdutos(2)
    // setTimeout(() => {
    //     $('.efetuar_pagamento').trigger('click')
    //     setTimeout(() => {
    //         $('#tipo_pagamento_atual').val('00').change()
    //         setTimeout(() => {
    //             $('#adicionar-pagamento').trigger('click')
    //             setTimeout(() => {
    //                 $('#finalizar_venda_tab').trigger('click')
    //             }, 500)
    //         }, 500)
    //     }, 500)
    // }, 2000)

    // getProdutoCodBarras("7891000619162", (data) => {
        // setTimeout(() => {
    //         addItem();
    //         $('#tipo-pagamento').val('00').change()
    //         // setTimeout(() => {
    //         //     $('#finalizar-venda').trigger('click')
    //         // }, 400)
        // }, 400)
    // })

});

function tefStatus(tipo, msg) {

    const map = {
        info: 'alert-info',
        warn: 'alert-warning',
        ok: 'alert-success',
        err: 'alert-danger'
    };

    $('#tef-status')
    .removeClass()
    .addClass('alert ' + (map[tipo] || 'alert-secondary'))
    .html(msg);
}

let lastTefMsg = null;
function tefLog(msg) {
    if (msg === lastTefMsg) {
        return;
    }

    lastTefMsg = msg;
    const el = $('#tef-log');

    if (el.text().length > 5000) {
        el.text(el.text().slice(-3000));
    }

    el.append("<br>"+msg);
    el.scrollTop(el[0].scrollHeight);

    if(msg.includes('SELECIONADO')){
        tefLog('Processando pagamento, aguarde!')
    }
}
async function carregarVendasTEF() {

    const data = $('#tef-filtro-data').val() || new Date().toISOString().split('T')[0];
    $('#modal-tef-operacoes').modal('show')
    $('.btn-outline-warning').remove()

    $('#tbody-vendas-tef').html(`
        <tr>
        <td colspan="9" class="text-center py-4">
        <i class="la la-spinner la-spin la-2x"></i>
        <br>Carregando vendas TEF...
        </td>
        </tr>
        `);

    try {
        const response = await fetch(path+`tef/vendas?data=${data}`);
        const resultado = await response.json();

        if (resultado.success) {
            renderizarTabelaVendasTEF(resultado.vendas);
        } else {
            // console.log(resultado)
            $('#tbody-vendas-tef').html(`
                <tr>
                <td colspan="9" class="text-center text-danger py-4">
                <i class="la la-exclamation-circle la-2x"></i>
                <br>Erro ao carregar vendas: ${resultado.error || 'Erro desconhecido'}
                </td>
                </tr>
                `);
        }
    } catch (error) {
        console.error('[TEF] Erro ao carregar vendas:', error);
        $('#tbody-vendas-tef').html(`
            <tr>
            <td colspan="9" class="text-center text-danger py-4">
            <i class="la la-exclamation-circle la-2x"></i>
            <br>Erro de conexão ao carregar vendas
            </td>
            </tr>
            `);
    }
}


function renderizarTabelaVendasTEF(vendas) {
    if (!vendas || vendas.length === 0) {
        $('#tbody-vendas-tef').html(`
            <tr>
            <td colspan="9" class="text-center text-muted py-4">
            <i class="la la-inbox la-3x"></i>
            <br>Nenhuma venda TEF encontrada para esta data
            </td>
            </tr>
            `);
        $('#tef-total-vendas').text('');
        return;
    }

    let html = '';
    let totalValor = 0;

    // ✅ NOVO: Identificar a última transação TEF não cancelada (primeira da lista, pois vem ordenada desc)
    const ultimaTransacaoId = vendas.find(v => {
        const tef = v.tef || {};
        return !tef.cancelado && v.estado !== 'CANCELADO';
    })?.id || null;

    vendas.forEach((venda, index) => {

        console.log("venda", venda)
        const tef = venda.tef_log || {};
        const isCancelado = tef.cancelado || venda.estado === 'CANCELADO';

        // ✅ NOVO: Verifica se é a última transação
        const isUltimaTransacao = (venda.id === ultimaTransacaoId);

        // ✅ NOVO: Verifica se permite reimpressão específica (apenas Cielo e Rede)
        // const adquirentePermiteReimpressao = verificarAdquirentePermiteReimpressao(tef.adquirente, tef.adquirenteCodigo);
        const adquirentePermiteReimpressao = true;

        // Tipo de pagamento formatado
        let tipoLabel = '-';
        if (tef.tef_function_id == 2) {
            tipoLabel = '<span class="badge badge-success">Débito</span>';
        } else if (tef.tef_function_id == 3) {
            tipoLabel = `<span class="badge badge-primary">Crédito${tef.parcelas > 1 ? ` ${tef.parcelas}x` : ''}</span>`;
        } else {
            tipoLabel = '<span class="badge badge-info">PIX</span>';
        }

        // Status
        let statusLabel = '<span class="badge badge-success"><i class="la la-check text-white"></i> Aprovado</span>';
        if (isCancelado) {
            statusLabel = '<span class="badge badge-danger"><i class="la la-times text-white"></i> Cancelado</span>';
        }

        totalValor += parseFloat(venda.valor_total) || 0;

        // ✅ NOVO: Monta o objeto venda com flags adicionais
        const vendaComFlags = {
            ...venda,
            _isUltimaTransacao: isUltimaTransacao,
            _permiteReimpressaoEspecifica: adquirentePermiteReimpressao
        };

        // ✅ NOVO: Badge indicando última transação
        const badgeUltima = isUltimaTransacao ? '<span class="badge badge-warning ml-1" title="Última transação"><i class="la la-clock text-white"></i></span>' : '';

        html += `
        <tr class="${isCancelado ? 'table-secondary' : ''}">
        <td>${ formatarDataHoraBR(venda.created_at) }</td>
        <td><strong>#${venda.numero_sequencial || venda.id}</strong>${badgeUltima}</td>
        <td>${venda.cliente ? venda.cliente.razao_social : 'Não identificado'}</td>
        <td class="text-right">R$ ${ convertFloatToMoeda(venda.valor_total) }</td>
        <td><code>${tef.tef_nsu || '-'}</code></td>
        <td>${ tef.tef_bandeira ? (tef.tef_bandeira) : '-' }</td>
        <td>${tipoLabel}</td>
        <td>${statusLabel}</td>
        <td class="text-center">
        <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary" type="button" 
        data-toggle="dropdown" ${isCancelado ? 'disabled' : ''}>
        <i class="la la-cog"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item text-danger" href="javascript:void(0)" 
        onclick='abrirModalCancelamento(${JSON.stringify(venda).replace(/'/g, "\\'")})'>
        <i class="la la-times-circle mr-2"></i>Cancelar TEF
        </a>
        <a class="dropdown-item" href="javascript:void(0)" 
        onclick='reimprimirComprovantePorVenda(${JSON.stringify(vendaComFlags).replace(/'/g, "\\'")})'>
        <i class="la la-print mr-2"></i>Reimprimir Comprovante
        ${isUltimaTransacao ? '<small class="text-muted ml-1">(última)</small>' : ''}
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="javascript:void(0)" 
        onclick='mostrarDetalhesTEF(${JSON.stringify(venda).replace(/'/g, "\\'")})'>
        <i class="la la-info-circle mr-2"></i>Ver Detalhes
        </a>
        </div>
        </div>
        </td>
        </tr>
        `;
    });

    $('#tbody-vendas-tef').html(html);
    $('#tef-total-vendas').html(`
        <strong>${vendas.length}</strong> venda(s) TEF | 
        Total: <strong>R$ ${totalValor.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</strong>
        `);
}

async function reimprimirComprovantePorVenda(venda) {
    window.open(
        path + 'nfce/imprimirTef/' + venda.id,
        'comprovanteTef',
        'width=500,height=600,top=100,left=100,scrollbars=yes,resizable=no'
        );
}

function nomeBandeiraTEF(codigo) {
    const bandeiras = {
        '00': 'Visa',
        '01': 'Mastercard',
        '02': 'Elo',
        '03': 'American Express',
        '04': 'Hipercard',
        '05': 'Aura',
        '06': 'Diners Club',
        '07': 'Discover',
        '08': 'JCB',
        '09': 'Banescard',
        '10': 'Cabal',
        '11': 'Credishop',
        '12': 'Sorocred',
        '13': 'UnionPay',
        '99': 'Outros'
    };

    return bandeiras[String(codigo).padStart(2, '0')] || 'Desconhecida';
}

function formatarDataHoraBR(data) {
    if (!data) return '';

    // aceita "2026-02-04 10:55:00" ou ISO
    const d = new Date(data.replace(' ', 'T'));

    if (isNaN(d.getTime())) return data;

    const dia  = String(d.getDate()).padStart(2, '0');
    const mes  = String(d.getMonth() + 1).padStart(2, '0');
    const ano  = d.getFullYear();
    const hora = String(d.getHours()).padStart(2, '0');
    const min  = String(d.getMinutes()).padStart(2, '0');

    return `${dia}/${mes}/${ano} ${hora}:${min}`;
}

function mostrarDetalhesTEF(venda) {
    console.log(venda)
    const tef = venda.tef_log || {};

    let tipoLabel = '-';
    if (tef.tef_function_id == 2) {
        tipoLabel = '<span class="badge badge-success">Débito</span>';
    } else if (tef.tef_function_id == 3) {
        tipoLabel = `<span class="badge badge-primary">Crédito${tef.parcelas > 1 ? ` ${tef.parcelas}x` : ''}</span>`;
    } else {
        tipoLabel = '<span class="badge badge-info">PIX</span>';
    }
    const html = `
    <table class="table table-sm table-bordered">
    <tr><th width="40%">Venda Nº</th><td>#${venda.numero_sequencial || venda.id}</td></tr>
    <tr><th>Data/Hora</th><td>${ formatarDataHoraBR(venda.created_at) }</td></tr>
    <tr><th>Cliente</th><td>${ venda.cliente ? venda.cliente.razao_social : '--' }</td></tr>
    <tr><th>Valor Total</th><td>R$ ${ convertFloatToMoeda(venda.valor_total) }</td></tr>
    <tr><th colspan="2" class="bg-light text-center">Dados TEF</th></tr>
    <tr><th>NSU</th><td><code>${tef.tef_nsu || '-'}</code></td></tr>
    <tr><th>Código Autorização</th><td>${tef.tef_codigo_autorizacao || '-'}</td></tr>
    <tr><th>Bandeira</th><td>${ tef.tef_bandeira ? (tef.tef_bandeira) : '-' }</td></tr>
    <tr><th>Tipo</th><td>${tipoLabel}</td></tr>
    <tr><th>Parcelas</th><td>${tef.parcelas || '1'}</td></tr>

    <tr><th>Data TEF</th><td>${ formatarDataHoraBR(tef.created_at)}</td></tr>
    <tr><th>Status TEF</th><td>${tef.cancelado ? '<span class="text-danger">Cancelado</span>' : '<span class="text-success">Ativo</span>'}</td></tr>
    </table>
    `;

    $('#modal-tef-detalhes-body').html(html);
    $('#modal-tef-detalhes').modal('show');
}

var vendaTEFSelecionada = null
function abrirModalCancelamento(venda) {
    vendaTEFSelecionada = venda;

    const tef = venda.tef_log || {};

    // Preenche os dados no modal
    $('#cancel-venda-numero').text(`#${venda.numero_sequencial || venda.id}`);
    $('#cancel-venda-valor').text(`R$ ${ convertFloatToMoeda(venda.valor_total) }`);
    $('#cancel-venda-nsu').text(tef.tef_nsu || '-');
    $('#cancel-venda-bandeira').text(tef.tef_bandeira ? (tef.tef_bandeira) : '-');

    let tipoTexto = '-';
    if (tef.tef_function_id == 2) {
        tipoTexto = 'Débito';
    } else if (tef.tef_function_id == 3) {
        tipoTexto = `Crédito${tef.parcelas > 1 ? ` ${tef.parcelas}x` : ''}`;
    }
    $('#cancel-venda-tipo').text(tipoTexto);

    // Abre o modal
    $('#modal-tef-confirma-cancelamento').modal('show');
}

async function executarCancelamentoTEFAutomatico(cancelarVenda) {
    console.clear();

    if (!vendaTEFSelecionada || !vendaTEFSelecionada.tef_log) {
        swal("Erro", "Dados da venda não encontrados", "error");
        return;
    }

    const venda = vendaTEFSelecionada;
    const tef = venda.tef_log;

    const raw = JSON.parse(tef.tef_raw);
    console.log(raw)
    // return;
    const valorCentavos = Math.round((Number(raw.valor) + Number.EPSILON) * 100);

    const payloadCancelamento = {
        comando: "CNC",
        identificacao: 1,
        docFiscal: 0,
        valorTotal: valorCentavos,
        moeda: 986, 
        adquirente: raw.adquirente, 
        nsu: raw.nsu, 
        dataHoraComprovante: raw.dataHora,
        controle: raw.controle,

        capAutomacao: 0,
        versaoInterface: 1,
    };

    try {
        const response = await fetch(baseUrl + "/tefgp-cancel", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payloadCancelamento)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "Erro ao cancelar TEF");
        }

        console.log("Cancelamento aprovado:", result);
        swal("Sucesso", "Transação cancelada com sucesso!", "success");
        marcarCancelado(vendaTEFSelecionada, cancelarVenda)

    } catch (error) {
        console.error(error);
        swal("Erro", error.message, "error");
    }
}

async function marcarCancelado(venda, cancelarVenda){
    const response = await fetch(getServerBase()+'/tef/marcar-cancelado', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({
            venda_id: venda.id,
            cancelar_venda: cancelarVenda
        })
    });
    carregarVendasTEF()
}
