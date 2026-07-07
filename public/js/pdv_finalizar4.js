function montarPayloadFinalizacao(acao) {
  let itens = [];
  let pagamentos = [];

  $('.lista-scroll .item').each(function () {
    if ($(this).attr('data-cancelado') == 1) return;

    let produto_id = $(this).find('input[name="produto_id[]"]').val();
    let quantidade = parseFloat($(this).find('.input-quantidade').val()) || 0;
    let valor_unitario = parseFloat($(this).find('.input-valor-unitario').val()) || 0;
    let sub_total = quantidade * valor_unitario;

    itens.push({
      produto_id: produto_id,
      variacao_id: null,
      quantidade: quantidade,
      valor_unitario: valor_unitario,
      sub_total: sub_total
    });
  });

  $('#pg-lista-recebimentos .pg-recebimento-item').each(function () {
    let forma = $(this).data('forma') || $(this).find('.pg-recebimento-forma').text().trim();
    let codigo = $(this).data('codigo');
    let valor = convertMoedaToFloat(
      $(this).find('.pg-recebimento-valor').text() ||
      $(this).find('.pg-recebimento-input').val() ||
      '0'
      );

    let vencimento = $(this).data('vencimento') || null;
    if (valor > 0) {
      pagamentos.push({
        forma_pagamento: forma,
        codigo_pagamento: codigo,
        valor: valor,
        data_vencimento: vencimento
      });
    }
  });

  let total = convertMoedaToFloat($('.valor-total').text());
  let desconto = convertMoedaToFloat($('.valor-desconto').text());
  let acrescimo = convertMoedaToFloat($('.valor-acrescimo').text());
  let totalPago = 0;

  pagamentos.forEach(p => {
    totalPago += parseFloat(p.valor || 0);
  });

  let troco = totalPago > total ? (totalPago - total) : 0;

  return {
    _token: $('meta[name="csrf-token"]').attr('content'),
    empresa_id: $('#empresa_id').val(),
    cliente_id: $('#cliente_id').val() || null,
    total: total,
    desconto: desconto,
    acrescimo: acrescimo,
    total_pago: totalPago,
    troco: troco,
    observacao: $('#textoObservacao').val(),
    local_id: $('#local_id').val(),
    usuario_id: $('#usuario_id').val(),
    funcionario_id: $('#funcionario_id').val(),
    venda_suspensa_id: $('#venda_suspensa_id').val() || null,
    acao_finalizacao: acao,
    itens: itens,
    pagamentos: pagamentos
  };
}


function alertaPDV(mensagem, icon = 'info', titulo = 'Atenção') {
  Swal.fire({
    icon: icon,
    title: titulo,
    text: mensagem,
    target: document.body,
    zIndex: 999999
  });
}

function getMensagemErroAjax(xhr, fallback = 'Não foi possível concluir a operação.') {
  let msg = fallback;

  if (xhr.responseJSON) {
    if (typeof xhr.responseJSON === 'string') {
      msg = xhr.responseJSON;
    } else if (xhr.responseJSON.message) {
      msg = xhr.responseJSON.message;
    } else if (xhr.responseJSON.error) {
      msg = xhr.responseJSON.error;
    }
  } else if (xhr.responseText) {
    msg = xhr.responseText;
  }

  return msg;
}

function transmitirNfe(venda) {
  abrirLoaderEmissao('Emitindo NF-e, aguarde...');

  $.ajax({
    url: path_url + 'api/nfe_painel/emitir',
    method: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: venda.id
    },

    success: function (success) {
      fecharLoaderEmissao();

      Swal.fire({
        icon: 'success',
        title: 'NF-e emitida',
        text: 'Recibo: ' + (success.recibo || '') + ' - Chave: ' + (success.chave || ''),
        target: document.body,
        zIndex: 999999
      }).then(() => {
        window.open(path_url + 'nfe/imprimir/' + venda.id, '_blank');

        $('#modalFechamentoPDV').addClass('d-none');

        if (typeof limparVenda === 'function') {
          limparVenda();
        }

        if (typeof limparTela === 'function') {
          limparTela();
        }

        $('#btnVoltarVenda').trigger('click');
      });
    },

    error: function (xhr) {
      fecharLoaderEmissao();

      let msgErro = getMensagemErroAjax(xhr, 'Erro ao emitir NF-e');

      Swal.fire({
        icon: 'warning',
        title: 'Venda salva com alerta',
        html: `
        <b>A venda foi salva com sucesso.</b><br><br>
        Porém a NF-e não foi emitida:<br>
        <small style="color:red">${msgErro}</small>
        `,
        confirmButtonText: 'OK',
        target: document.body,
        zIndex: 999999
      }).then(() => {
        $('#modalFechamentoPDV').addClass('d-none');

        if (typeof limparVenda === 'function') {
          limparVenda();
        }

        if (typeof limparTela === 'function') {
          limparTela();
        }

        $('#btnVoltarVenda').trigger('click');
      });
    }
  });
}

function transmitirNfce(venda) {
  abrirLoaderEmissao('Emitindo NFC-e, aguarde...');

  $.ajax({
    url: path_url + 'api/nfce_painel/emitir',
    method: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      id: venda.id
    },

    success: function (success) {
      fecharLoaderEmissao();

      Swal.fire({
        icon: 'success',
        title: 'NFC-e emitida',
        text: 'Recibo: ' + (success.recibo || '') + ' - Chave: ' + (success.chave || ''),
        target: document.body,
        zIndex: 999999
      }).then(() => {
        window.open(path_url + 'nfce/imprimir/' + venda.id, '_blank');

        finalizarFluxoPosVenda();
      });
    },

    error: function (xhr) {
      fecharLoaderEmissao();

      let msgErro = getMensagemErroAjax(xhr, 'Erro ao emitir NFC-e');

      Swal.fire({
        icon: 'warning',
        title: 'Venda salva com alerta',
        html: `
        <b>A venda foi salva com sucesso.</b><br><br>
        Porém a NFC-e não foi emitida:<br>
        <small style="color:red">${msgErro}</small>
        `,
        confirmButtonText: 'OK',
        target: document.body,
        zIndex: 999999
      }).then(() => {
        finalizarFluxoPosVenda();
      });
    }
  });
}

function finalizarFluxoPosVenda() {
  $('#modalFechamentoPDV').addClass('d-none');

  if (typeof limparVenda === 'function') {
    limparVenda();
  }

  if (typeof limparTela === 'function') {
    limparTela();
  }

  $('#btnVoltarVenda').trigger('click');
}

function finalizarVenda(acao) {
  let data = montarPayloadFinalizacao(acao);

  if (!data.itens.length) {
    alertaPDV('Carrinho vazio!');
    return;
  }

  if (!data.pagamentos.length) {
    alertaPDV('Adicione ao menos uma forma de pagamento.');
    return;
  }

  if (data.total_pago < data.total) {
    alertaPDV('O valor pago é menor que o total da venda.', 'warning');
    return;
  }

  let urlFinalizacao = path_url + 'api/frenteCaixa/finalizar-venda4';

  if (acao === 'nfe') {
    urlFinalizacao = path_url + 'api/frenteCaixa/finalizar-venda-nfe4';
  }

  $.ajax({
    url: urlFinalizacao,
    method: 'POST',
    data: data,

    success: function (res) {
      $('#modalFechamentoPDV').addClass('d-none');

      if (acao === 'nfce') {
        transmitirNfce(res);
        return;
      }

      if (acao === 'nfe') {
        transmitirNfe(res);
        return;
      }

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Venda finalizada com sucesso',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        target: document.body,
        zIndex: 999999
      });

      if (res && res.id) {

        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

        var docprint=window.open(path_url+"frontbox/imprimir-nao-fiscal/"+res.id,"",disp_setting);
        docprint.focus();
      }

      if (typeof limparVenda === 'function') {
        limparVenda();
      }

      if (typeof limparTela === 'function') {
        limparTela();
      }

      $('#btnVoltarVenda').trigger('click');
    },

    error: function (xhr) {
      let msg = getMensagemErroAjax(xhr, 'Não foi possível finalizar a venda.');
      alertaPDV(msg, 'error', 'Erro!');
    }
  });
}

function abrirLoaderEmissao(mensagem = 'Emitindo NFC-e, aguarde...') {
  Swal.fire({
    title: 'Aguarde',
    text: mensagem,
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    target: document.body,
    zIndex: 999999,
    didOpen: () => {
      Swal.showLoading();
    }
  });
}

function fecharLoaderEmissao() {
  Swal.close();
}

$(document).on('click', '#btnImprimirPedido', function () {
  finalizarVenda('finalizar');
});

$(document).on('click', '#btnEmitirNfce', function () {
  finalizarVenda('nfce');
});

$(document).on('click', '#btnEmitirNfe', function () {
  finalizarVenda('nfe');
});