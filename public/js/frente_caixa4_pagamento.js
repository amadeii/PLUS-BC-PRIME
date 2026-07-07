let modoPDV = 'VENDA';

const PAGAMENTO_ICONES = {
  '01': 'banknote',
  '02': 'receipt',
  '03': 'credit-card',
  '04': 'credit-card',
  '05': 'wallet',
  '06': 'wallet',
  '10': 'ticket',
  '17': 'qr-code',
  'default': 'circle'
};

let pagamentosLancados = [];

function abrirTelaPagamento() {
  const temItens = $('.lista-scroll .item').length > 0;

  if (!temItens) {
    Swal.fire('Atenção', 'Adicione pelo menos 1 item para finalizar!', 'info');
    return;
  }

  modoPDV = 'PAGAMENTO';

  $('#telaVenda').addClass('d-none');
  $('#telaPagamento').removeClass('d-none');

  iniciarTelaPagamento();

  if (typeof setLog === 'function') {
    setLog('Informe o valor a ser recebido');
  }
}

function voltarTelaVenda() {
  modoPDV = 'VENDA';

  $('#telaPagamento').addClass('d-none');
  $('#telaVenda').removeClass('d-none');
}

function iniciarTelaPagamento() {
  pagamentosLancados = [];

  const subtotal = convertMoedaToFloat($('.valor-total').text());
  const desconto = convertMoedaToFloat($('.valor-desconto').text());
  const acrescimo = convertMoedaToFloat($('.valor-acrescimo').text());
  const totalFinal = subtotal - desconto + acrescimo;
  const nomeCliente = $('#nome-cliente').text().trim() || 'Consumidor';

  if ($('#pg-cliente').length) {
    $('#pg-cliente').text(nomeCliente);
  }

  $('#pg-total-venda').text('R$ ' + convertFloatToMoeda(subtotal));
  $('#pg-total-desconto').text('R$ ' + convertFloatToMoeda(desconto));
  $('#pg-total-acrescimo').text('R$ ' + convertFloatToMoeda(acrescimo));
  $('#pg-total-receber').text('R$ ' + convertFloatToMoeda(totalFinal));
  $('#pg-total-ja-recebido').text('R$ 0,00');
  $('#mf-troco').text('R$ 0,00');

  $('#pg-valor-recebido').val(convertFloatToMoeda(totalFinal));
  $('#pg-forma-recebimento').val('');

  $('.pg-forma').removeClass('ativo');

  renderPagamentosLancados();
  setTimeout(() => {
    $('#pg-valor-recebido').focus().select();
  }, 80);
}

function getResumoPagamento() {
  const subtotal = convertMoedaToFloat($('#pg-total-venda').text());
  const desconto = convertMoedaToFloat($('#pg-total-desconto').text());
  const acrescimo = convertMoedaToFloat($('#pg-total-acrescimo').text());

  return {
    subtotal,
    desconto,
    acrescimo,
    totalFinal: subtotal - desconto + acrescimo
  };
}

function getTotalRecebido() {
  return pagamentosLancados.reduce((total, item) => {
    return total + (parseFloat(item.valor) || 0);
  }, 0);
}

function getValorRestante() {
  const resumo = getResumoPagamento();
  const totalRecebido = getTotalRecebido();

  return Math.max(resumo.totalFinal - totalRecebido, 0);
}

function atualizarInputValorRecebido() {
  const restante = getValorRestante();
  $('#pg-valor-recebido').val(convertFloatToMoeda(restante));
}

function atualizarResumoPagamento() {
  const totalRecebido = getTotalRecebido();
  const restante = getValorRestante();

  $('#pg-total-receber').text('R$ ' + convertFloatToMoeda(restante));
  $('#pg-total-ja-recebido').text('R$ ' + convertFloatToMoeda(totalRecebido));

  atualizarInputValorRecebido();
}

function renderPagamentosLancados() {

  const $lista = $('#pg-lista-recebimentos');

  if (!$lista.length) return;

  if (!pagamentosLancados.length) {

    $lista.html(`
      <div class="pg-lista-vazia">
      Não há nenhuma forma de recebimento definida para o pedido.
      </div>
      `);

    lucide.createIcons();
    atualizarResumoPagamento();
    atualizarBotaoFinalizar();

    return;
  }

  let html = '';

  pagamentosLancados.forEach((item, index) => {

    html += `
    <div class="pg-recebimento-item"
    data-forma="${item.nome}"
    data-codigo="${item.codigo}"
    data-vencimento="${item.vencimento || ''}">

    <div class="pg-recebimento-forma">

    <div class="pg-recebimento-forma-info">

    <div class="pg-recebimento-forma-top">
    <i data-lucide="${item.icone}"></i>

    <span>
    ${item.nome}
    </span>
    </div>

    ${item.vencimento_br || item.vencimento ? `<div class="pg-recebimento-vencimento">
    Vencimento: ${item.vencimento_br || formatarDataBRSimplesString(item.vencimento)}
    </div>
    `
    : ''}

    </div>

    </div>

    <div class="pg-recebimento-tipo">
    ${item.crediario
      ? item.parcela_atual + '/' + item.total_parcelas + ' de R$ ' + convertFloatToMoeda(item.valor)
      : 'À vista'}
      </div>

      <div class="pg-recebimento-valor">
      R$ ${convertFloatToMoeda(item.valor)}
      </div>

      <div class="pg-recebimento-acoes">

      <button 
      type="button" 
      class="pg-btn-acao-recebimento pg-btn-editar-recebimento" 
      data-index="${index}" 
      title="Editar">

      <i data-lucide="edit-3"></i>

      </button>

      <button
      type="button"
      class="pg-btn-acao-recebimento pg-btn-remover-recebimento"
      data-index="${index}"
      title="Remover">

      <i data-lucide="trash"></i>

      </button>

      </div>

      </div>
      `;
    });

  $lista.html(html);

  lucide.createIcons();

  atualizarResumoPagamento();
  atualizarBotaoFinalizar();
}

function temClienteSelecionadoPDV() {
  const clienteId = $('#cliente_id').val() || $('#inp-cliente_id').val() || $('#clienteId').val();

  if (clienteId && clienteId != '0') {
    return true;
  }

  const nomeCliente = ($('#nome-cliente').text() || '').trim();

  return nomeCliente && nomeCliente !== 'Consumidor' && nomeCliente !== 'Cliente não informado';
}

$(document).on('click', '.pg-btn-editar-recebimento', function () {

  const index = parseInt($(this).data('index'));
  const item = pagamentosLancados[index];

  if (!item) return;

  $('#editParcelaIndex').val(index);
  $('#editParcelaValor').val(convertFloatToMoeda(item.valor));

  $('#editParcelaVencimento').val(
    item.vencimento || ''
    );

  $('#modalEditarParcelaCrediario').removeClass('d-none');
});

$(document).on('click', '#btnSalvarEditarParcelaCrediario', function () {
  const index = parseInt($('#editParcelaIndex').val());
  const item = pagamentosLancados[index];

  if (!item) return;

  const valor = convertMoedaToFloat($('#editParcelaValor').val());
  const vencimento = $('#editParcelaVencimento').val();

  if (!valor || valor <= 0) {
    Swal.fire('Atenção', 'Informe um valor válido.', 'warning');
    return;
  }

  if (!vencimento) {
    Swal.fire('Atenção', 'Informe a data de vencimento.', 'warning');
    return;
  }

  item.valor = valor;
  item.valor_parcela = valor;
  item.vencimento = vencimento;
  item.vencimento_br = formatarDataBRSimplesString(vencimento);

  pagamentosLancados[index] = item;

  $('#modalEditarParcelaCrediario').addClass('d-none');

  renderPagamentosLancados();
  atualizarBotaoFinalizar();
});

function adicionarPagamento(codigo, nome, valor) {
  const icone = PAGAMENTO_ICONES[String(codigo)] || PAGAMENTO_ICONES.default;
  const restante = getValorRestante();
  const permiteTroco = String(codigo) === '01';

  if (!valor || valor <= 0) {
    Swal.fire('Atenção', 'Informe um valor recebido válido.', 'warning');
    $('#pg-valor-recebido').focus().select();
    return false;
  }

  if (restante <= 0) {
    Swal.fire('Atenção', 'O pedido já foi totalmente recebido.', 'info');
    $('#pg-valor-recebido').val('0,00');
    return false;
  }

  if (!permiteTroco && valor > restante) {
    Swal.fire(
      'Atenção',
      'O valor informado é maior que o valor restante da venda.',
      'warning'
      );

    $('#pg-valor-recebido').val(convertFloatToMoeda(restante));
    $('#pg-valor-recebido').focus().select();
    return false;
  }

  pagamentosLancados.push({
    codigo: String(codigo),
    nome: nome,
    valor: parseFloat(valor),
    icone: icone
  });

  renderPagamentosLancados();
  verificarFechamentoPagamento();
  atualizarBotaoFinalizar();

  const novoRestante = getValorRestante();

  $('#pg-forma-recebimento').val('');

  if (novoRestante > 0) {
    $('#pg-valor-recebido').val(convertFloatToMoeda(novoRestante));
    $('#pg-valor-recebido').focus().select();
  } else {
    $('#pg-valor-recebido').val('0,00');
    $('.pg-forma').removeClass('ativo');
  }

  atualizarTrocoModal();

  return true;
}

function validarValorRecebidoDigitado() {
  const valor = convertMoedaToFloat($('#pg-valor-recebido').val());
  const restante = getValorRestante();

  if (restante <= 0) {
    $('#pg-valor-recebido').val('0,00');
    return;
  }

  if (!valor || valor <= 0) {
    $('#pg-valor-recebido').val(convertFloatToMoeda(restante));
    return;
  }

  $('#pg-valor-recebido').val(convertFloatToMoeda(valor));
}

$(document).on('click', '.pg-forma', function () {
  const nome = $(this).data('forma') || '';
  const codigo = String($(this).data('codigo') || '');
  const valor = convertMoedaToFloat($('#pg-valor-recebido').val());

  $('.pg-forma').removeClass('ativo');
  $(this).addClass('ativo');

  $('#pg-forma-recebimento').val(nome);

  if (codigo === '06') {

    if (!temClienteSelecionadoPDV()) {
      Swal.fire({
        icon: 'warning',
        title: 'Cliente obrigatório',
        text: 'Para vender no crediário, selecione um cliente antes de continuar.'
      });

      $('.pg-forma').removeClass('ativo');
      $('#pg-forma-recebimento').val('');

      return;
    }

    abrirModalCrediarioPDV(codigo, nome, valor);
    return;
  }

  adicionarPagamento(codigo, nome, valor);
});

function abrirModalCrediarioPDV(codigo, nome, valorVenda) {
  if (!valorVenda || valorVenda <= 0) {
    Swal.fire('Atenção', 'Informe um valor válido para o crediário.', 'warning');
    return;
  }

  $('#listaOpcoesCrediario').html(`<div class="crediario-loading">Carregando opções...</div>`);
  $('#modalCrediarioPDV').removeClass('d-none');

  $.ajax({
    url: path_url + 'api/frenteCaixa/opcoes-crediario',
    type: 'GET',
    dataType: 'json',
    data: {
      valor: convertFloatToMoeda(valorVenda),
      empresa_id: $('#empresa_id').val()
    },
    success: function (opcoes) {
      if (!opcoes || !opcoes.length) {
        $('#listaOpcoesCrediario').html(`<div class="crediario-empty">Nenhuma opção disponível.</div>`);
        return;
      }

      let html = '';

      opcoes.forEach(function (item) {
        html += `
        <div class="crediario-card">
        <h4>${item.parcelas}x de R$ ${convertFloatToMoeda(item.valor_parcela)}</h4>
        <p>Total: R$ ${convertFloatToMoeda(item.valor_total)}</p>
        <p>Juros: ${convertFloatToMoeda(item.juros || 0)}%</p>
        <p>1º vencimento: ${formatarDataBRSimplesString(item.primeiro_vencimento)}</p>

        <button type="button" class="btnSelecionarCrediarioPDV"
        data-codigo="${codigo}"
        data-nome="${nome}"
        data-valor="${item.valor_total}"
        data-parcelas="${item.parcelas}"
        data-valor-parcela="${item.valor_parcela}"
        data-primeiro-vencimento="${item.primeiro_vencimento}"
        data-intervalo="${item.intervalo || 30}">
        Selecionar
        </button>
        </div>
        `;
      });

      $('#listaOpcoesCrediario').html(html);
    },
    error: function () {
      fecharModalCrediarioPDV();
      Swal.fire('Erro', 'Erro ao consultar as opções de crediário.', 'error');
    }
  });
}

function formatarDataBRSimplesString(data) {
  if (!data) return '--';

  const partes = String(data).split('-');

  if (partes.length !== 3) return data;

  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function fecharModalCrediarioPDV() {
  $('#modalCrediarioPDV').addClass('d-none');
}

function adicionarDiasData(data, dias) {
  const novaData = new Date(data);
  novaData.setDate(novaData.getDate() + dias);
  return novaData;
}

function formatarDataBRSimples(data) {
  const dia = String(data.getDate()).padStart(2, '0');
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const ano = data.getFullYear();
  return `${dia}/${mes}/${ano}`;
}

function formatarDataInput(data) {
  const dia = String(data.getDate()).padStart(2, '0');
  const mes = String(data.getMonth() + 1).padStart(2, '0');
  const ano = data.getFullYear();
  return `${ano}-${mes}-${dia}`;
}

$(document).on('click', '#btnFecharEditarParcelaCrediario', function () {
  $('#modalEditarParcelaCrediario').addClass('d-none');
});

$(document).on('click', '#modalEditarParcelaCrediario', function (e) {
  if (e.target.id === 'modalEditarParcelaCrediario') {
    $('#modalEditarParcelaCrediario').addClass('d-none');
  }
});

$(document).on('click', '#btnFecharCrediarioPDV', function () {
  fecharModalCrediarioPDV();
});

$(document).on('click', '#modalCrediarioPDV', function (e) {
  if (e.target.id === 'modalCrediarioPDV') {
    fecharModalCrediarioPDV();
  }
});

$(document).on('click', '.btnSelecionarCrediarioPDV', function () {

  const codigo = $(this).data('codigo');
  const nome = $(this).data('nome');

  const parcelas = parseInt($(this).data('parcelas')) || 1;
  const valorParcela = parseFloat($(this).data('valor-parcela')) || 0;

  const primeiroVencimento = $(this).data('primeiro-vencimento');
  const intervalo = parseInt($(this).data('intervalo')) || 30;

  const icone = PAGAMENTO_ICONES[String(codigo)] || PAGAMENTO_ICONES.default;

  let dataBase = new Date(primeiroVencimento + 'T00:00:00');

  for (let i = 1; i <= parcelas; i++) {

    let vencimento = new Date(dataBase);
    vencimento.setDate(vencimento.getDate() + ((i - 1) * intervalo));

    pagamentosLancados.push({
      codigo: String(codigo),
      nome: nome,
      valor: valorParcela,
      icone: icone,

      crediario: true,

      parcela_atual: i,
      total_parcelas: parcelas,

      valor_parcela: valorParcela,

      vencimento: formatarDataInput(vencimento),
      vencimento_br: formatarDataBRSimples(vencimento)
    });
  }

  fecharModalCrediarioPDV();

  renderPagamentosLancados();
  verificarFechamentoPagamento();
  atualizarBotaoFinalizar();

  $('#pg-forma-recebimento').val('');
  $('#pg-valor-recebido').val('0,00');
});

$(document).on('click', '.pg-btn-remover-recebimento', function () {
  const index = parseInt($(this).data('index'), 10);

  if (isNaN(index)) return;

  pagamentosLancados.splice(index, 1);
  renderPagamentosLancados();

  const restante = getValorRestante();
  $('#pg-valor-recebido').val(convertFloatToMoeda(restante));
  $('#pg-valor-recebido').focus().select();

  atualizarTrocoModal();
});

$(document).on('blur', '#pg-valor-recebido', function () {
  validarValorRecebidoDigitado();
});

$(document).on('keydown', '#pg-valor-recebido', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();

    const $formaAtiva = $('.pg-forma.ativo').first();

    if ($formaAtiva.length) {
      $formaAtiva.trigger('click');
    }
  }
});

$(document).on('click', '.btn-finalizar, #btnFinalizarVenda', function () {
  abrirTelaPagamento();
});

$(document).on('click', '#btnVoltarVenda', function () {
  voltarTelaVenda();
});

$(document).on('keydown', function (e) {
  if (e.key === 'F5' && modoPDV === 'VENDA') {
    e.preventDefault();
    abrirTelaPagamento();
  }

  if (e.key === 'Escape' && modoPDV === 'PAGAMENTO') {
    e.preventDefault();
    voltarTelaVenda();
  }
});

$('#btnAguardarPg').on('click', function () {
  confirmarSuspensao(function () {
    suspenderVenda();
    voltarTelaVenda();
  });
});

$('#btnCancelarPg').on('click', function () {
  confirmarCancelamento();
});

$('#btnClientePg').on('click', function () {
  abrirModalClientePDV();
});

$('#pdv-btn-finalizar').on('click', function(){
  verificarFechamentoPagamento()
});

$('#btnAguardandoPg').on('click', function(){
  abrirModalAguardando();
});

function getTrocoPagamento() {
  const resumo = getResumoPagamento();
  const totalRecebido = getTotalRecebido();
  return Math.max(totalRecebido - resumo.totalFinal, 0);
}

function atualizarTrocoModal() {
  const troco = getTrocoPagamento();
  $('#mf-troco').text('R$ ' + convertFloatToMoeda(troco));
}

function atualizarResumoPagamento() {
  const totalRecebido = getTotalRecebido();
  const restante = getValorRestante();

  $('#pg-total-receber').text('R$ ' + convertFloatToMoeda(restante));
  $('#pg-total-ja-recebido').text('R$ ' + convertFloatToMoeda(totalRecebido));

  atualizarInputValorRecebido();
  atualizarTrocoModal();
}

function verificarFechamentoPagamento() {
  const resumo = getResumoPagamento();
  const totalRecebido = getTotalRecebido();

  if (totalRecebido >= resumo.totalFinal && resumo.totalFinal > 0) {
    abrirModalFechamento();
  }
}

function abrirModalFechamento() {
  const troco = getTrocoPagamento();

  $('#mf-troco').text('R$ ' + convertFloatToMoeda(troco));
  $('#modalFechamentoPDV').removeClass('d-none');

  setTimeout(() => {
    lucide.createIcons();
  }, 0);
}

function fecharModalFechamento() {
  $('#modalFechamentoPDV').addClass('d-none');
}

$(document).on('click', '#btnFecharModalFechamento', function () {
  fecharModalFechamento();
});

$(document).on('click', '#btnNovoPedido', function () {
  fecharModalFechamento();
  voltarTelaVenda();

  if (typeof limparVenda === 'function') {
    limparVenda();
  }
});

$(document).on('click', '#modalFechamentoPDV', function (e) {
  if (e.target.id === 'modalFechamentoPDV') {
    fecharModalFechamento();
  }
});

function atualizarBotaoFinalizar() {
  const resumo = getResumoPagamento();
  const totalRecebido = getTotalRecebido();

  const $btn = $('#pdv-btn-finalizar');

  if (totalRecebido >= resumo.totalFinal && resumo.totalFinal > 0) {
    $btn.prop('disabled', false);
  } else {
    $btn.prop('disabled', true);
  }
}

$(document).on('keydown', function (e) {
  if ($('#modalFechamentoPDV').hasClass('d-none')) return;

  if (e.key === 'Escape') {
    e.preventDefault();
    fecharModalFechamento();
    voltarTelaVenda();

    if (typeof limparVenda === 'function') {
      limparVenda();
    }
  }

  if (e.key === 'F9') {
    e.preventDefault();
    $('#btnImprimirPedido').trigger('click');
  }

  if (e.key === 'F8') {
    e.preventDefault();
    $('#btnEmitirNfce').trigger('click');
  }

  if (e.key === 'F7') {
    e.preventDefault();
    $('#btnEmitirNfe').trigger('click');
  }
});

let pdvfinalDescontoValor = 0;

function pdvfinalGetResumoBaseDesconto() {
  const resumo = getResumoPagamento();
  const subtotal = parseFloat(resumo.subtotal) || 0;
  const acrescimo = parseFloat(resumo.acrescimo) || 0;

  return {
    subtotal,
    acrescimo,
    totalBase: subtotal + acrescimo
  };
}

function pdvfinalLimitarPercentual(valor) {
  let percentual = parseFloat(valor) || 0;

  if (percentual < 0) percentual = 0;
  if (percentual > 100) percentual = 100;

  return percentual;
}

function pdvfinalLimitarValorDesconto(valor, totalBase) {
  let desconto = parseFloat(valor) || 0;

  if (desconto < 0) desconto = 0;
  if (desconto > totalBase) desconto = totalBase;

  return desconto;
}

function pdvfinalAbrirModalDesconto() {
  const dados = pdvfinalGetResumoBaseDesconto();
  const percentualAtual = dados.totalBase > 0
  ? (pdvfinalDescontoValor / dados.totalBase) * 100
  : 0;

  $('#pdvfinal-desconto-total-base').text('Valor R$ ' + convertFloatToMoeda(dados.totalBase));
  $('#pdvfinal-desconto-percentual').val(convertFloatToMoeda(percentualAtual));
  $('#pdvfinal-desconto-valor').val(convertFloatToMoeda(pdvfinalDescontoValor));
  $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(dados.totalBase - pdvfinalDescontoValor));

  $('#pdvfinal-modal-desconto').css('display', 'flex');

  setTimeout(() => {
    $('#pdvfinal-desconto-percentual').focus().select();
  }, 80);
}

function pdvfinalFecharModalDesconto() {
  $('#pdvfinal-modal-desconto').hide();
}

function pdvfinalAtualizarResumoVisualPorPercentual(percentual) {
  const dados = pdvfinalGetResumoBaseDesconto();
  const percentualLimitado = pdvfinalLimitarPercentual(percentual);
  const valorDesconto = (dados.totalBase * percentualLimitado) / 100;
  const valorFinal = Math.max(dados.totalBase - valorDesconto, 0);

  $('#pdvfinal-desconto-valor').val(convertFloatToMoeda(valorDesconto));
  $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAtualizarResumoVisualPorValor(valorDesconto) {
  const dados = pdvfinalGetResumoBaseDesconto();
  const descontoLimitado = pdvfinalLimitarValorDesconto(valorDesconto, dados.totalBase);
  const percentual = dados.totalBase > 0
  ? (descontoLimitado / dados.totalBase) * 100
  : 0;
  const valorFinal = Math.max(dados.totalBase - descontoLimitado, 0);

  $('#pdvfinal-desconto-percentual').val(convertFloatToMoeda(percentual));
  $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAtualizarPorPercentualDigitando() {
  const texto = ($('#pdvfinal-desconto-percentual').val() || '').trim();
  const dados = pdvfinalGetResumoBaseDesconto();

  if (texto === '') {
    $('#pdvfinal-desconto-valor').val('');
    $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(dados.totalBase));
    return;
  }

  const percentual = convertMoedaToFloat(texto);
  pdvfinalAtualizarResumoVisualPorPercentual(percentual);
}

function pdvfinalAtualizarPorValorDigitando() {
  const texto = ($('#pdvfinal-desconto-valor').val() || '').trim();
  const dados = pdvfinalGetResumoBaseDesconto();

  if (texto === '') {
    $('#pdvfinal-desconto-percentual').val('');
    $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(dados.totalBase));
    return;
  }

  const valorDesconto = convertMoedaToFloat(texto);
  pdvfinalAtualizarResumoVisualPorValor(valorDesconto);
}

function pdvfinalAtualizarPorPercentual() {
  const dados = pdvfinalGetResumoBaseDesconto();
  let percentual = convertMoedaToFloat($('#pdvfinal-desconto-percentual').val());

  percentual = pdvfinalLimitarPercentual(percentual);

  const valorDesconto = (dados.totalBase * percentual) / 100;
  const valorFinal = Math.max(dados.totalBase - valorDesconto, 0);

  $('#pdvfinal-desconto-percentual').val(convertFloatToMoeda(percentual));
  $('#pdvfinal-desconto-valor').val(convertFloatToMoeda(valorDesconto));
  $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAtualizarPorValor() {
  const dados = pdvfinalGetResumoBaseDesconto();
  let valorDesconto = convertMoedaToFloat($('#pdvfinal-desconto-valor').val());

  valorDesconto = pdvfinalLimitarValorDesconto(valorDesconto, dados.totalBase);

  const percentual = dados.totalBase > 0
  ? (valorDesconto / dados.totalBase) * 100
  : 0;

  const valorFinal = Math.max(dados.totalBase - valorDesconto, 0);

  $('#pdvfinal-desconto-valor').val(convertFloatToMoeda(valorDesconto));
  $('#pdvfinal-desconto-percentual').val(convertFloatToMoeda(percentual));
  $('#pdvfinal-desconto-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAplicarDesconto() {
  const dados = pdvfinalGetResumoBaseDesconto();
  let valorDesconto = convertMoedaToFloat($('#pdvfinal-desconto-valor').val());

  valorDesconto = pdvfinalLimitarValorDesconto(valorDesconto, dados.totalBase);
  pdvfinalDescontoValor = valorDesconto;

  $('#pg-total-desconto').text('R$ ' + convertFloatToMoeda(valorDesconto));

  atualizarResumoPagamento();
  renderPagamentosLancados();
  atualizarBotaoFinalizar();

  pdvfinalFecharModalDesconto();
}

$(document).on('click', '#pdvfinal-btn-abrir-desconto', function () {
  pdvfinalAbrirModalDesconto();
});

$(document).on('click', '#pdvfinal-btn-cancelar-desconto', function () {
  pdvfinalFecharModalDesconto();
});

$(document).on('click', '#pdvfinal-btn-aplicar-desconto', function () {
  pdvfinalAplicarDesconto();
});

$(document).on('input', '#pdvfinal-desconto-percentual', function () {
  pdvfinalAtualizarPorPercentualDigitando();
});

$(document).on('blur', '#pdvfinal-desconto-percentual', function () {
  pdvfinalAtualizarPorPercentual();
});

$(document).on('input', '#pdvfinal-desconto-valor', function () {
  pdvfinalAtualizarPorValorDigitando();
});

$(document).on('blur', '#pdvfinal-desconto-valor', function () {
  pdvfinalAtualizarPorValor();
});

$(document).on('keydown', '#pdvfinal-desconto-percentual', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    $('#pdvfinal-desconto-valor').focus().select();
  }
});

$(document).on('keydown', '#pdvfinal-desconto-valor', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    $('#pdvfinal-btn-aplicar-desconto').trigger('click');
  }
});

$(document).on('click', '#pdvfinal-modal-desconto', function (e) {
  if (e.target.id === 'pdvfinal-modal-desconto') {
    pdvfinalFecharModalDesconto();
  }
});

$(document).on('keydown', function (e) {
  if ($('#pdvfinal-modal-desconto').is(':visible') && e.key === 'Escape') {
    e.preventDefault();
    pdvfinalFecharModalDesconto();
  }
});

let pdvfinalAcrValor = 0;

function pdvfinalAcrGetResumoBase() {
  const resumo = getResumoPagamento();
  const subtotal = parseFloat(resumo.subtotal) || 0;
  const desconto = parseFloat(resumo.desconto) || 0;

  return {
    subtotal: subtotal,
    desconto: desconto,
    totalBase: Math.max(subtotal - desconto, 0)
  };
}

function pdvfinalAcrLimitarPercentual(valor) {
  let percentual = parseFloat(valor) || 0;

  if (percentual < 0) percentual = 0;

  return percentual;
}

function pdvfinalAcrLimitarValor(valor) {
  let acrescimo = parseFloat(valor) || 0;

  if (acrescimo < 0) acrescimo = 0;

  return acrescimo;
}

function pdvfinalAcrAbrirModal() {
  const dados = pdvfinalAcrGetResumoBase();
  const percentualAtual = dados.totalBase > 0
  ? (pdvfinalAcrValor / dados.totalBase) * 100
  : 0;

  $('#pdvfinal-acr-total-base').text('Valor R$ ' + convertFloatToMoeda(dados.totalBase));
  $('#pdvfinal-acr-percentual').val(convertFloatToMoeda(percentualAtual));
  $('#pdvfinal-acr-valor').val(convertFloatToMoeda(pdvfinalAcrValor));
  $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(dados.totalBase + pdvfinalAcrValor));

  $('#pdvfinal-acr-modal').css('display', 'flex');

  setTimeout(function () {
    $('#pdvfinal-acr-percentual').focus().select();
  }, 80);
}

function pdvfinalAcrFecharModal() {
  $('#pdvfinal-acr-modal').hide();
}

function pdvfinalAcrAtualizarResumoPorPercentual(percentual) {
  const dados = pdvfinalAcrGetResumoBase();
  const percentualLimitado = pdvfinalAcrLimitarPercentual(percentual);
  const valorAcrescimo = (dados.totalBase * percentualLimitado) / 100;
  const valorFinal = dados.totalBase + valorAcrescimo;

  $('#pdvfinal-acr-valor').val(convertFloatToMoeda(valorAcrescimo));
  $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAcrAtualizarResumoPorValor(valor) {
  const dados = pdvfinalAcrGetResumoBase();
  const acrescimoLimitado = pdvfinalAcrLimitarValor(valor);
  const percentual = dados.totalBase > 0
  ? (acrescimoLimitado / dados.totalBase) * 100
  : 0;
  const valorFinal = dados.totalBase + acrescimoLimitado;

  $('#pdvfinal-acr-percentual').val(convertFloatToMoeda(percentual));
  $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAcrDigitandoPercentual() {
  const texto = ($('#pdvfinal-acr-percentual').val() || '').trim();
  const dados = pdvfinalAcrGetResumoBase();

  if (texto === '') {
    $('#pdvfinal-acr-valor').val('');
    $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(dados.totalBase));
    return;
  }

  const percentual = convertMoedaToFloat(texto);
  pdvfinalAcrAtualizarResumoPorPercentual(percentual);
}

function pdvfinalAcrDigitandoValor() {
  const texto = ($('#pdvfinal-acr-valor').val() || '').trim();
  const dados = pdvfinalAcrGetResumoBase();

  if (texto === '') {
    $('#pdvfinal-acr-percentual').val('');
    $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(dados.totalBase));
    return;
  }

  const valorAcrescimo = convertMoedaToFloat(texto);
  pdvfinalAcrAtualizarResumoPorValor(valorAcrescimo);
}

function pdvfinalAcrFormatarPercentual() {
  const dados = pdvfinalAcrGetResumoBase();
  let percentual = convertMoedaToFloat($('#pdvfinal-acr-percentual').val());

  percentual = pdvfinalAcrLimitarPercentual(percentual);

  const valorAcrescimo = (dados.totalBase * percentual) / 100;
  const valorFinal = dados.totalBase + valorAcrescimo;

  $('#pdvfinal-acr-percentual').val(convertFloatToMoeda(percentual));
  $('#pdvfinal-acr-valor').val(convertFloatToMoeda(valorAcrescimo));
  $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAcrFormatarValor() {
  const dados = pdvfinalAcrGetResumoBase();
  let valorAcrescimo = convertMoedaToFloat($('#pdvfinal-acr-valor').val());

  valorAcrescimo = pdvfinalAcrLimitarValor(valorAcrescimo);

  const percentual = dados.totalBase > 0
  ? (valorAcrescimo / dados.totalBase) * 100
  : 0;

  const valorFinal = dados.totalBase + valorAcrescimo;

  $('#pdvfinal-acr-valor').val(convertFloatToMoeda(valorAcrescimo));
  $('#pdvfinal-acr-percentual').val(convertFloatToMoeda(percentual));
  $('#pdvfinal-acr-total-final').val(convertFloatToMoeda(valorFinal));
}

function pdvfinalAcrAplicar() {
  const dados = pdvfinalAcrGetResumoBase();
  let valorAcrescimo = convertMoedaToFloat($('#pdvfinal-acr-valor').val());

  valorAcrescimo = pdvfinalAcrLimitarValor(valorAcrescimo);
  pdvfinalAcrValor = valorAcrescimo;

  $('#pg-total-acrescimo').text('R$ ' + convertFloatToMoeda(valorAcrescimo));

  atualizarResumoPagamento();
  renderPagamentosLancados();
  atualizarBotaoFinalizar();

  pdvfinalAcrFecharModal();
}

$(document).on('click', '#pdvfinal-acr-btn-abrir', function () {
  pdvfinalAcrAbrirModal();
});

$(document).on('click', '#pdvfinal-acr-btn-cancelar', function () {
  pdvfinalAcrFecharModal();
});

$(document).on('click', '#pdvfinal-acr-btn-aplicar', function () {
  pdvfinalAcrAplicar();
});

$(document).on('input', '#pdvfinal-acr-percentual', function () {
  pdvfinalAcrDigitandoPercentual();
});

$(document).on('blur', '#pdvfinal-acr-percentual', function () {
  pdvfinalAcrFormatarPercentual();
});

$(document).on('input', '#pdvfinal-acr-valor', function () {
  pdvfinalAcrDigitandoValor();
});

$(document).on('blur', '#pdvfinal-acr-valor', function () {
  pdvfinalAcrFormatarValor();
});

$(document).on('keydown', '#pdvfinal-acr-percentual', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    $('#pdvfinal-acr-valor').focus().select();
  }
});

$(document).on('keydown', '#pdvfinal-acr-valor', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    $('#pdvfinal-acr-btn-aplicar').trigger('click');
  }
});

$(document).on('click', '#pdvfinal-acr-modal', function (e) {
  if (e.target.id === 'pdvfinal-acr-modal') {
    pdvfinalAcrFecharModal();
  }
});

$(document).on('keydown', function (e) {
  if ($('#pdvfinal-acr-modal').is(':visible') && e.key === 'Escape') {
    e.preventDefault();
    pdvfinalAcrFecharModal();
  }
});

let mrClienteSelecionado = null;
let mrTimerBuscaCliente = null;

function abrirModalRecebimento() {
  resetarModalRecebimento();
  $('#modalRecebimento').addClass('ativo');
  setTimeout(() => $('#mrBuscaCliente').focus(), 80);
}

function fecharModalRecebimento() {
  $('#modalRecebimento').removeClass('ativo');
}

function resetarModalRecebimento() {
  mrClienteSelecionado = null;

  $('#mrBuscaCliente').val('');
  $('#mrResultadoBusca').removeClass('ativo').hide().html('');
  $('#mrConteudoCliente').addClass('d-none');

  $('#mrRazaoSocial').text('--');
  $('#mrDocumento').text('--');
  $('#mrTipoCliente').text('Cliente');
  $('#mrEndereco').text('--');
  $('#mrCidadeUf').text('--');
  $('#mrEmail').text('--');
  $('#mrTelefone').text('--');

  $('#mrStatusCliente')
  .text('Ativo')
  .removeClass('inativo')
  .addClass('ativo');

  $('#mrTabelaTitulos').html(`
    <tr>
    <td colspan="6" class="mr-empty">Nenhuma conta encontrada.</td>
    </tr>
    `);

  $('#mrTotalAberto').text('R$ 0,00');
  $('#mrTotalPendente').text('R$ 0,00');
  $('#mrTotalAtraso').text('R$ 0,00');

  $('#mrBtnReceberContas')
  .prop('disabled', true)
  .text('Receber contas');

  $('#mrBtnEditarCliente').off('click');
}

function formatarMoedaBR(valor) {
  return (parseFloat(valor || 0)).toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  });
}

function escapeHtml(texto) {
  if (texto === null || texto === undefined) return '';
  return String(texto)
  .replaceAll('&', '&amp;')
  .replaceAll('<', '&lt;')
  .replaceAll('>', '&gt;')
  .replaceAll('"', '&quot;')
  .replaceAll("'", '&#039;');
}

function formatarDataBR(data) {
  if (!data) return '--';
  const parte = String(data).split(' ')[0];
  const pedacos = parte.split('-');
  if (pedacos.length === 3) {
    return `${pedacos[2]}/${pedacos[1]}/${pedacos[0]}`;
  }
  return data;
}

function obterDocumentoCliente(cliente) {
  return cliente.cpf_cnpj || cliente.documento || cliente.cnpj || cliente.cpf || '--';
}

function obterTelefoneCliente(cliente) {
  return cliente.celular || cliente.telefone || cliente.fone || '--';
}

function obterEmailCliente(cliente) {
  return cliente.email || '--';
}

function obterEnderecoCliente(cliente) {
  const endereco = cliente.rua || cliente.endereco || '--';
  const numero = cliente.numero || '';
  return numero ? `${endereco}, ${numero}` : endereco;
}

function obterCidadeUfCliente(cliente) {
  const cidade = cliente.nome_cidade || cliente.cidade || '';
  const uf = cliente.uf || cliente.estado || '';
  if (cidade && uf) return `${cidade} - ${uf}`;
  if (cidade) return cidade;
  if (uf) return uf;
  return '--';
}

function clienteAtivo(cliente) {
  return cliente.ativo == 1 ||
  cliente.status == 1 ||
  cliente.status === 'Ativo' ||
  cliente.situacao === 'Ativo';
}

function preencherCliente(cliente) {
  $('#mrRazaoSocial').text(cliente.razao_social || cliente.nome || '--');
  $('#mrDocumento').text(obterDocumentoCliente(cliente));
  $('#mrTipoCliente').text(cliente.contribuinte ? 'Contribuinte' : 'Cliente');
  $('#mrEndereco').text(obterEnderecoCliente(cliente));
  $('#mrCidadeUf').text(obterCidadeUfCliente(cliente));
  $('#mrEmail').text(obterEmailCliente(cliente));
  $('#mrTelefone').text(obterTelefoneCliente(cliente));

  const ativo = clienteAtivo(cliente);
  $('#mrStatusCliente')
  .text(ativo ? 'Ativo' : 'Inativo')
  .removeClass('ativo inativo')
  .addClass(ativo ? 'ativo' : 'inativo');

  if (cliente.id) {
    $('#mrBtnEditarCliente').off('click').on('click', function () {
      window.open(path_url + 'clientes/' + cliente.id + '/edit', '_blank');
    });
  }

  $('#mrConteudoCliente').removeClass('d-none');
}

function montarTabelaTitulos(contas) {
  if (!contas || !contas.length) {
    $('#mrTabelaTitulos').html(`
      <tr>
      <td colspan="6" class="mr-empty">Nenhuma conta encontrada.</td>
      </tr>
      `);
    return;
  }

  const hoje = new Date();
  hoje.setHours(0, 0, 0, 0);

  let html = '';

  contas.forEach(function (conta, index) {
    const vencimento = conta.data_vencimento || conta.vencimento || '';
    const valor = parseFloat(conta.valor_integral || conta.valor || 0);
    const parteData = vencimento ? String(vencimento).split(' ')[0] : null;
    const dt = parteData ? new Date(parteData + 'T00:00:00') : null;
    const atrasado = dt && dt < hoje;

    const descricaoPrincipal = conta.descricao || `Parcela ${conta.parcela || (index + 1)}`;
    const descricaoSecundaria = [
    conta.venda_id ? `Venda: ${conta.venda_id}` : null,
    conta.forma_pagamento ? `Forma: ${conta.forma_pagamento}` : null
    ].filter(Boolean).join(' • ');

    const documento = conta.documento || conta.referencia || ('#' + conta.id);

    html += `
    <tr>
    <td>
    <input 
    type="checkbox" 
    class="mr-check mr-check-conta"
    data-id="${conta.id}"
    data-valor="${valor}"
    >
    </td>
    <td>${formatarDataBR(vencimento)}</td>
    <td>
    <div class="mr-desc-main">${escapeHtml(descricaoPrincipal)}</div>
    <div class="mr-desc-sub">${escapeHtml(descricaoSecundaria || '--')}</div>
    </td>
    <td>
    <span class="mr-doc-tag">${escapeHtml(documento)}</span>
    </td>
    <td>${formatarMoedaBR(valor)}</td>
    <td>
    <span class="mr-status-badge ${atrasado ? 'atraso' : 'pendente'}">
    ${atrasado ? 'Em atraso' : 'Pendente'}
    </span>
    </td>
    </tr>
    `;
  });

  $('#mrTabelaTitulos').html(html);
}

function atualizarResumo(response, contas) {
  let totalAberto = parseFloat(response.total_aberto || 0);
  let totalPendente = parseFloat(response.total_pendente || 0);
  let totalAtrasado = parseFloat(response.total_atrasado || 0);

  if ((!totalAberto && !totalPendente && !totalAtrasado) && contas && contas.length) {
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    contas.forEach(function (conta) {
      const valor = parseFloat(conta.valor_integral || conta.valor || 0);
      const vencimento = conta.data_vencimento || conta.vencimento || '';
      const parteData = vencimento ? String(vencimento).split(' ')[0] : null;
      const dt = parteData ? new Date(parteData + 'T00:00:00') : null;

      totalAberto += valor;

      if (dt && dt < hoje) {
        totalAtrasado += valor;
      } else {
        totalPendente += valor;
      }
    });
  }

  $('#mrTotalAberto').text(formatarMoedaBR(totalAberto));
  $('#mrTotalPendente').text(formatarMoedaBR(totalPendente));
  $('#mrTotalAtraso').text(formatarMoedaBR(totalAtrasado));
}

function carregarClienteContas(clienteId) {
  mrClienteSelecionado = clienteId;

  $('#mrConteudoCliente').removeClass('d-none');
  $('#mrRazaoSocial').text('Carregando...');
  $('#mrDocumento').text('...');
  $('#mrEndereco').text('Carregando...');
  $('#mrCidadeUf').text('...');
  $('#mrEmail').text('...');
  $('#mrTelefone').text('...');
  $('#mrTabelaTitulos').html(`
    <tr>
    <td colspan="6" class="mr-empty">Carregando contas...</td>
    </tr>
    `);

  $.ajax({
    url: path_url + 'api/conta-receber/faturas-cliente-pdv4',
    type: 'GET',
    data: { cliente_id: clienteId },
    dataType: 'json',
    success: function (response) {
      const cliente = response.cliente || {};
      const contas = response.contas || response.data || [];

      preencherCliente(cliente);
      montarTabelaTitulos(contas);
      atualizarResumo(response, contas);
      atualizarBotaoReceber();
    },
    error: function () {
      $('#mrTabelaTitulos').html(`
        <tr>
        <td colspan="6" class="mr-empty">Erro ao carregar contas do cliente.</td>
        </tr>
        `);

      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Não foi possível carregar os dados do cliente.'
      });
    }
  });
}

function buscarClientesRecebimento(termo) {
  if (!termo || termo.length < 2) {
    $('#mrResultadoBusca').removeClass('ativo').hide().html('');
    return;
  }

  $.ajax({
    url: path_url + 'api/clientes/buscar-json',
    type: 'GET',
    data: { q: termo },
    dataType: 'json',
    success: function (response) {
      const clientes = response.clientes || response || [];

      if (!clientes.length) {
        $('#mrResultadoBusca')
        .html(`<div class="mr-busca-item">Nenhum cliente encontrado</div>`)
        .addClass('ativo')
        .show();
        return;
      }

      let html = '';

      clientes.forEach(function (cliente) {
        const nome = cliente.razao_social || cliente.nome || '--';
        const documento = obterDocumentoCliente(cliente);

        html += `
        <div class="mr-busca-item" data-id="${cliente.id}">
        <div class="mr-busca-item-nome">${escapeHtml(nome)}</div>
        <div class="mr-busca-item-doc">${escapeHtml(documento)}</div>
        </div>
        `;
      });

      $('#mrResultadoBusca')
      .html(html)
      .addClass('ativo')
      .show();
    },
    error: function () {
      $('#mrResultadoBusca')
      .html(`<div class="mr-busca-item">Erro ao buscar clientes</div>`)
      .addClass('ativo')
      .show();
    }
  });
}

function atualizarBotaoReceber() {
  let totalSelecionado = 0;

  $('.mr-check-conta:checked').each(function () {
    totalSelecionado += parseFloat($(this).data('valor') || 0);
  });

  if (totalSelecionado > 0) {
    $('#mrBtnReceberContas')
    .prop('disabled', false)
    .text('Receber contas (' + formatarMoedaBR(totalSelecionado) + ')');
  } else {
    $('#mrBtnReceberContas')
    .prop('disabled', true)
    .text('Receber contas');
  }
}

$(document).on('input', '#mrBuscaCliente', function () {
  const termo = $(this).val().trim();

  clearTimeout(mrTimerBuscaCliente);
  mrTimerBuscaCliente = setTimeout(function () {
    buscarClientesRecebimento(termo);
  }, 300);
});

$(document).on('focus', '#mrBuscaCliente', function () {
  const termo = $(this).val().trim();
  if (termo.length >= 2) {
    buscarClientesRecebimento(termo);
  }
});

$(document).on('click', '.mr-busca-item', function () {
  const clienteId = $(this).data('id');
  const nome = $(this).find('.mr-busca-item-nome').text();

  $('#mrBuscaCliente').val(nome);
  $('#mrResultadoBusca').removeClass('ativo').hide().html('');

  carregarClienteContas(clienteId);
});

$(document).on('change', '.mr-check-conta', function () {
  atualizarBotaoReceber();
});

$(document).on('click', '#mrBtnReceberContas', function () {

  const ids = $('.mr-check-conta:checked')
  .map(function () {
    return `contas[]=${$(this).data('id')}`;
  })
  .get()
  .join('&');

  if (!ids) {
    Swal.fire({
      icon: 'warning',
      title: 'Selecione ao menos um título',
      text: 'Marque pelo menos uma conta para continuar.'
    });
    return;
  }

  const path_url_final = path_url + `conta-receber-receive-pdv?${ids}`;

  window.location.href = path_url_final;
});

$(document).on('click', '#modalRecebimento', function (e) {
  if ($(e.target).is('#modalRecebimento')) {
    fecharModalRecebimento();
  }
});

$(document).on('click', function (e) {
  if (!$(e.target).closest('.mr-busca-wrap').length) {
    $('#mrResultadoBusca').removeClass('ativo').hide();
  }
});

$(document).ready(function () {
  resetarModalRecebimento();
});