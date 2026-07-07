let prot = window.location.protocol;
let host = window.location.host;
let vendaEditandoId = null;
let fechamentoFormasPagamento = {};

const path_url = prot + "//" + host + "/";

$(function(){
  verificarCaixaAberto();
});

function verificarCaixaAberto(){
  $.ajax({
    url: path_url + 'api/caixa/verificar-aberto',
    type: 'GET',
    dataType: 'json',
    data: {
      usuario_id: $('#usuario_id').val()
    },
    success: function(res){

      if(res && res.caixa_id){
        $('#caixa_id').val(res.caixa_id);
        return;
      }

      abrirModalAberturaCaixa();
    },
    error: function(){
      console.log('Erro ao verificar caixa');
    }
  });
}

function abrirModalAberturaCaixa(){
  $('#modal-abertura-caixa').addClass('ativo');
  $('#valor_abertura_caixa').val('')
  setTimeout(function(){
    $('#valor_abertura_caixa').focus().select();
  }, 100);
}

function fecharModalAberturaCaixa(){
  $('#modal-abertura-caixa').removeClass('ativo');
}

$(document).on('click', '#btn-confirmar-abertura-caixa', function(){
  if($('#valor_abertura_caixa').val() == ''){
    Swal.fire({
      icon: 'warning',
      title: 'Informe um valor',
      didOpen: () => {
        const el = document.querySelector('.swal2-container');
        if(el) el.style.zIndex = 99999999;
      }
    });
    return;
  }
  let valor = convertMoedaToFloat($('#valor_abertura_caixa').val());
  let obs = $('#obs_abertura_caixa').val();

  if(valor < 0){
    Swal.fire({
      icon: 'warning',
      title: 'Valor inválido',
      didOpen: () => {
        const el = document.querySelector('.swal2-container');
        if(el) el.style.zIndex = 99999999;
      }
    });
    return;
  }

  $.ajax({
    url: path_url + 'api/caixa/abrir',
    type: 'POST',
    dataType: 'json',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      usuario_id: $('#usuario_id').val(),
      empresa_id: $('#empresa_id').val(),
      valor_abertura: valor,
      observacao: obs
    },
    beforeSend: function(){
      $('#btn-confirmar-abertura-caixa')
      .prop('disabled', true)
      .text('Abrindo...');
    },
    success: function(res){

      $('#btn-confirmar-abertura-caixa')
      .prop('disabled', false)
      .text('Abrir caixa');

      if(res.caixa_id){
        $('#caixa_id').val(res.caixa_id);
      }

      fecharModalAberturaCaixa();

      Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: 'Caixa aberto com sucesso',
        confirmButtonText: 'OK',
        didOpen: () => {
          const el = document.querySelector('.swal2-container');
          if(el) el.style.zIndex = 99999999;
        }
      });

    },
    error: function(xhr){

      $('#btn-confirmar-abertura-caixa')
      .prop('disabled', false)
      .text('Abrir caixa');

      let msg = 'Erro ao abrir caixa';

      if(xhr.responseJSON){
        if(xhr.responseJSON.message){
          msg = xhr.responseJSON.message;
        }else if(typeof xhr.responseJSON === 'string'){
          msg = xhr.responseJSON;
        }
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: msg,
        confirmButtonText: 'OK',
        didOpen: () => {
          const el = document.querySelector('.swal2-container');
          if(el) el.style.zIndex = 99999999;
        }
      });
    }
  });

});

function reaplicarTabelaPrecoCarrinho(tabelaPreco) {
  let itens = [];

  $('.lista-scroll .item').each(function () {
    itens.push({
      produto_id: $(this).data('id'),
      quantidade: parseFloat($(this).find('.input-quantidade').val()) || 1
    });
  });

  $.ajax({
    url: path_url + 'api/produtos/recalcular-tabela-pdv4',
    method: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      empresa_id: $('#empresa_id').val(),
      tabela_preco: tabelaPreco,
      itens: itens
    },
    success: function (res) {
      if (!res || !res.itens) return;

      res.itens.forEach(function (itemRetorno) {
        let $item = $('.lista-scroll .item[data-id="' + itemRetorno.produto_id + '"]');

        if (!$item.length) return;

        $item.find('.input-valor-unitario').val(itemRetorno.valor_unitario);
        $item.find('.input-valor-base-original').val(itemRetorno.valor_unitario);

        $item.find('.detalhe').text(
          `${formatarNumero(itemRetorno.quantidade)} Un x R$ ${formatarMoedaBR(itemRetorno.valor_unitario)}`
        );

        $item.find('.item-total').text(
          `R$ ${formatarMoedaBR(itemRetorno.subtotal)}`
        );

        $item.attr('data-desconto-valor', 0);
        $item.attr('data-acrescimo-valor', 0);
      });

      calcTotal();

      Swal.fire({
        icon: 'success',
        title: 'Preços atualizados',
        text: 'Os itens do pedido foram recalculados com a nova tabela.',
        confirmButtonColor: '#4254BA'
      });
    },
    error: function () {
      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: 'Não foi possível recalcular os preços.',
        confirmButtonColor: '#4254BA'
      });
    }
  });
}

document.querySelectorAll('.select-custom').forEach(select => {
  const selected = select.querySelector('.select-selected');
  const options = select.querySelector('.select-options');
  const hidden = select.querySelector('input[type="hidden"]');

  const marcarAtivoInicial = () => {
    options.querySelectorAll('div').forEach(opt => {
      opt.innerText = opt.innerText.replace(/^✔\s*/, '');
      if (opt.classList.contains('active')) {
        opt.innerText = '✔ ' + opt.innerText;
      }
    });
  };

  marcarAtivoInicial();

  selected.addEventListener('click', function(e) {
    e.stopPropagation();

    document.querySelectorAll('.select-options').forEach(other => {
      if (other !== options) other.style.display = 'none';
    });

    options.style.display = options.style.display === 'block' ? 'none' : 'block';
  });

  options.querySelectorAll('div').forEach(opt => {
    opt.addEventListener('click', function(e) {
      e.stopPropagation();

      const novoValor = this.dataset.value;
      const textoLimpo = this.innerText.replace(/^✔\s*/, '');

      const atualizarVisual = () => {
        options.querySelectorAll('div').forEach(item => {
          item.classList.remove('active');
          item.innerText = item.innerText.replace(/^✔\s*/, '');
        });

        this.classList.add('active');
        this.innerText = '✔ ' + textoLimpo;

        selected.innerText = textoLimpo;
        hidden.value = novoValor;
        options.style.display = 'none';
      };

      if (select.classList.contains('select_tabela_precos')) {
        const valorAtual = hidden.value;
        const temItensNoCarrinho = $('.lista-scroll .item').length > 0;

        if (String(valorAtual) === String(novoValor)) {
          options.style.display = 'none';
          return;
        }

        if (!temItensNoCarrinho) {
          atualizarVisual();
          return;
        }

        Swal.fire({
          icon: 'question',
          title: 'Atualizar preços?',
          text: 'Já existem produtos no pedido. Deseja refazer os preços com base na nova tabela?',
          showCancelButton: true,
          confirmButtonText: 'Sim, refazer',
          cancelButtonText: 'Não',
          confirmButtonColor: '#4254BA'
        }).then((result) => {
          if (result.isConfirmed) {
            atualizarVisual();
            reaplicarTabelaPrecoCarrinho(novoValor);
          } else {
            options.style.display = 'none';
          }
        });

        return;
      }

      atualizarVisual();
    });
  });
});

document.addEventListener('click', function() {
  document.querySelectorAll('.select-options').forEach(options => {
    options.style.display = 'none';
  });
});
let delayBusca;

$('#inputBuscaProduto').on('keyup', function(e) {
  if (['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(e.key)) {
    return;
  }

  let termo = $(this).val().trim();

  clearTimeout(delayBusca);

  if (termo.length < 2) {
    $('#resultadoBusca').addClass('d-none').html('');
    indiceBuscaSelecionado = -1;
    return;
  }

  delayBusca = setTimeout(() => {
    buscarProdutos(termo, false);
  }, 300);
});

let indiceBuscaSelecionado = -1;
function atualizarSelecaoBusca() {
  const itens = $('#resultadoBusca .busca-produto-item');
  itens.removeClass('selecionado');

  if (indiceBuscaSelecionado < 0 || !itens.length) return;

  const item = itens.eq(indiceBuscaSelecionado);
  item.addClass('selecionado');

  const container = $('#resultadoBusca');
  const itemTop = item.position().top;
  const itemHeight = item.outerHeight();
  const containerHeight = container.innerHeight();

  if (itemTop < 0) {
    container.scrollTop(container.scrollTop() + itemTop - 10);
  } else if (itemTop + itemHeight > containerHeight) {
    container.scrollTop(container.scrollTop() + (itemTop + itemHeight - containerHeight) + 10);
  }
}

function buscarProdutos(termo, force = false) {

  $.ajax({
    url: path_url + 'api/produtos/buscar-pdv4',
    method: 'GET',
    data: {
      q: termo,
      empresa_id: $('#empresa_id').val(),
      force: force,
      tabela_preco: $('input[name="tabela_preco"]').val()
    },
    success: function(res) {
      if (res.auto_add) {
        clearTimeout(delayBusca);

        let p = res.produto;

        produto = {
          id: p.id,
          nome: p.nome,
          valor: parseFloat(p.valor || 0),
          img: p.img || '',
          disponivel: parseFloat(p.disponivel || 0),
          gerenciar_estoque: parseInt(p.gerenciar_estoque || 0)
        };

        setLog('Informe a quantidade');
        $('.img-produto-selecionado').attr('src', produto.img).removeClass('d-none');

        $('#inputBuscaProduto').val(produto.nome);
        $('#valor_unitario').val(convertFloatToMoeda(produto.valor));
        $('#quantidade').val((parseFloat(p.quantidade || 1)).toFixed(3).replace('.', ','));
        $('#resultadoBusca').addClass('d-none').html('');
        indiceBuscaSelecionado = -1;
        $('#quantidade').focus();
        return;
      }

      $('#resultadoBusca')
      .removeClass('d-none')
      .html(res.html);

      const itens = $('#resultadoBusca .busca-produto-item');
      indiceBuscaSelecionado = itens.length ? 0 : -1;
      atualizarSelecaoBusca();
    },
    error: function() {
      $('#resultadoBusca')
      .removeClass('d-none')
      .html('<div class="p-3 text-center">Erro ao buscar produtos</div>');

      indiceBuscaSelecionado = -1;
    }
  });
}

$('#inputBuscaProduto').on('keydown', function(e) {
  const itens = $('#resultadoBusca .busca-produto-item');

  if (e.key === 'ArrowDown') {
    e.preventDefault();

    if (!itens.length) return;

    if (indiceBuscaSelecionado < itens.length - 1) {
      indiceBuscaSelecionado++;
    } else {
      indiceBuscaSelecionado = 0;
    }

    atualizarSelecaoBusca();
    return;
  }

  if (e.key === 'ArrowUp') {
    e.preventDefault();

    if (!itens.length) return;

    if (indiceBuscaSelecionado > 0) {
      indiceBuscaSelecionado--;
    } else {
      indiceBuscaSelecionado = itens.length - 1;
    }

    atualizarSelecaoBusca();
    return;
  }

  if (e.key === 'Enter') {
    e.preventDefault();
    clearTimeout(delayBusca);

    let termo = $(this).val().trim();
    if (!termo) return;

    if (itens.length && indiceBuscaSelecionado >= 0) {
      itens.eq(indiceBuscaSelecionado).trigger('click');
      return;
    }

    buscarProdutos(termo, true);
    return;
  }

  if (e.key === 'Escape') {
    clearTimeout(delayBusca);
    $('#resultadoBusca').addClass('d-none').html('');
    indiceBuscaSelecionado = -1;
    return;
  }
});

function convertMoedaToFloat(value) {
  if (!value) {
    return 0;
  }

  var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
  return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
}

function convertFloatToMoeda(value) {
  value = parseFloat(value)
  return value.toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

var produto = {}

$(document).on('click', '.busca-produto-item', function() {

  clearTimeout(delayBusca);

  let gerenciar = $(this).data('gerenciar_estoque');
  let disponivel = parseFloat($(this).data('disponivel')) || 0;

  if (gerenciar && disponivel <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Sem estoque',
      text: 'Produto sem estoque disponível.'
    });
    return;
  }

  produto = {
    id: $(this).data('id'),
    nome: $(this).data('nome'),
    valor: $(this).data('valor'),
    img: $(this).data('img'),
    disponivel: disponivel,
    gerenciar_estoque: gerenciar ? 1 : 0
  };

  setLog('Informe a quantidade');
  $('.img-produto-selecionado').attr('src', produto.img).removeClass('d-none');

  $('#inputBuscaProduto').val(produto.nome);
  $('#valor_unitario').val(convertFloatToMoeda(produto.valor));
  $('#quantidade').val('1,000');
  $('#resultadoBusca').addClass('d-none').html('');
  indiceBuscaSelecionado = -1;
  $('#quantidade').focus();
});

$('#quantidade').on('keydown', function(e) {
  if (e.key === 'Enter') {
    e.preventDefault();

    produto.quantidade = convertMoedaToFloat($('#quantidade').val())
    // console.log("Adiconar", produto)
    adicionarProdutoNoPedido(produto)
  }
});

function setLog(msg){
  $('.log').html(msg)
}

$(document).on('click', '.remover-item', function() {
  $(this).closest('.item').remove();
  calcTotal();
});

function formatarMoedaBR(valor) {
  return parseFloat(valor).toFixed(2).replace('.', ',');
}

function calcTotal() {
  let total = 0;
  let totalItens = 0;
  let totalCancelados = 0;

  $('.lista-scroll .item').each(function(index) {

    const cancelado = $(this).attr('data-cancelado') == 1;

    $(this).find('.item-num').text(index + 1);

    if (cancelado) {
      totalCancelados++;
      return; // ignora no total
    }

    totalItens++;

    total += convertMoedaToFloat($(this).find('.item-total').text());
  });

  $('.total-itens').text(totalItens);
  $('.total-cancelados').text(totalCancelados);
  $('.valor-total').text("R$ " + convertFloatToMoeda(total));

  calcResumoFinanceiro();
}

//teste 
$(function(){
  // produto = {
  //   id: 1,
  //   nome: 'teste',
  //   valor: 12,
  //   img: '',
  // };
  // adicionarProdutoNoPedido(produto)
  setTimeout(() => {
    // $('#btnFinalizarVenda').trigger('click')
    // $('.btn-ver-caixa').trigger('click')
  }, 1000);
})


$('#btnVerCaixa').on('click', function () {
  $('.btn-ver-caixa').trigger('click')
});

function adicionarProdutoNoPedido(produto) {
  $('.lista-scroll .empty').remove();
  let itemExistente = $('.lista-scroll .item[data-id="' + produto.id + '"]');

  if (itemExistente.length) {
    let qtdInput = itemExistente.find('.input-quantidade');
    let qtdAtual = parseFloat(qtdInput.val()) || 0;
    let novaQtd = qtdAtual + (parseFloat(produto.quantidade) || 1);

    qtdInput.val(novaQtd);

    // se estava cancelado, restaura ao adicionar novamente
    itemExistente.removeClass('item-cancelado');
    itemExistente.attr('data-cancelado', 0);

    let valorUnitario = parseFloat(itemExistente.find('.input-valor-unitario').val()) || 0;
    let total = novaQtd * valorUnitario;

    itemExistente.find('.detalhe').text(
      formatarNumero(novaQtd) + ' Un x R$ ' + formatarMoedaBR(valorUnitario)
      );
    itemExistente.find('.item-total').text('R$ ' + formatarMoedaBR(total));

    calcTotal();

    $('#inputBuscaProduto').val('').focus();
    $('#quantidade').val('1,000');
    $('#valor_unitario').val('0,00');
    return;
  }

  let valorUnitario = parseFloat(produto.valor) || 0;
  let quantidade = parseFloat(produto.quantidade) || 1;
  let total = valorUnitario * quantidade;

  let html = `
  <div class="item item-adicionando" 
  data-id="${produto.id}" 
  data-disponivel="${produto.disponivel || 0}" 
  data-gerenciar_estoque="${produto.gerenciar_estoque || 0}"
  data-cancelado="0"
  data-desconto-valor="0"
  data-acrescimo-valor="0">

  <div class="item-num"></div>

  <div class="item-conteudo">
  <div class="nome">${produto.nome}</div>
  <div class="detalhe">${formatarNumero(quantidade)} Un x R$ ${formatarMoedaBR(valorUnitario)}</div>

  <input type="hidden" name="produto_id[]" value="${produto.id}">
  <input type="hidden" name="quantidade[]" value="${quantidade}" class="input-quantidade">
  <input type="hidden" name="valor_unitario[]" value="${valorUnitario}" class="input-valor-unitario">
  <input type="hidden" value="${valorUnitario}" class="input-valor-base-original">
  </div>

  <div class="item-total">R$ ${formatarMoedaBR(total)}</div>

  <div class="item-menu pdv-menu">
  <button type="button" class="pdv-menu-btn">⋮</button>

  <div class="pdv-menu-dropdown">
  <button class="pdv-menu-action" data-action="alterar-quantidade">
  <i data-lucide="hash" style="height: 14px;"></i>
  Alterar quantidade
  </button>

  <button class="pdv-menu-action" data-action="desconto">
  <i data-lucide="tag" style="height: 14px;"></i>
  Desconto
  </button>

  <button class="pdv-menu-action" data-action="acrescimo">
  <i data-lucide="plus-circle" style="height: 14px;"></i>
  Acréscimo
  </button>

  <button class="pdv-menu-action danger" data-action="cancelar-item">
  <i data-lucide="x-circle" style="height: 14px;"></i>
  Cancelar item
  </button>

  <button class="pdv-menu-action success" data-action="restaurar-item" style="display:none;">
  <i data-lucide="rotate-ccw" style="height: 14px;"></i>
  Restaurar item
  </button>
  </div>
  </div>
  </div>
  `;

  setLog(
    `${produto.nome.toUpperCase()}<br>${formatarNumero(quantidade)} X R$ ${formatarMoedaBR(valorUnitario)} = R$ ${formatarMoedaBR(total)}`
    );

  $('.lista-scroll').append(html);
  lucide.createIcons();
  calcTotal();

  $('#inputBuscaProduto').val('').focus();
  $('#quantidade').val('1,000');
  $('#valor_unitario').val('0,00');

  produto = {};
}

$(function () {
  $('input[name="tabela_preco"]').val('')
  $('#inputBuscaProduto').val('').focus();

  let ppTimeoutBusca = null;
  let ppAjaxAtual = null;
  let ppProdutoSelecionado = null;

  function ppAbrirModal() {
    $('#ppModalPesquisa').css('display', 'flex');
    $('#ppInputBusca').val('');
    $('#ppListaProdutos').html('<div class="pp-modal-empty">Digite para pesquisar produtos.</div>');

    setTimeout(function () {
      $('#ppInputBusca').focus();
    }, 100);
  }

  function ppFecharModal() {
    $('#ppModalPesquisa').hide();
  }

  function ppRenderProdutos(produtos) {
    let html = '';

    if (!produtos || !produtos.length) {
      $('#ppListaProdutos').html('<div class="pp-modal-empty">Nenhum produto encontrado.</div>');
      return;
    }

    $.each(produtos, function (i, p) {
      const id = p.id || '';
      const codigo = p.codigo || p.id || '';
      const nome = p.nome || '';
      const barras = p.codigo_barras || p.barras || '-';
      const referencia = p.referencia || '-';
      const categoria = p.categoria || '-';
      const preco = p.preco_formatado || p.valor_formatado || p.preco || '0,00';
      const estoque = p.estoque_formatado || p.quantidade_formatada || p.estoque || '0 Un';
      const dataPreco = p.data_preco || p.ultima_alteracao_preco || '--';

      html += `
      <div 
      class="pp-modal-item"
      style="animation-delay:${i * 0.04}s"
      data-id="${id}"
      data-produto='${JSON.stringify(p).replace(/'/g, "&apos;")}'
      >
      <div>
      <div class="pp-modal-main">${codigo}</div>
      <div class="pp-modal-sub">${dataPreco}</div>
      </div>

      <div>
      <div class="pp-modal-main">${nome}</div>
      <div class="pp-modal-sub">${barras}</div>
      </div>

      <div>
      <div class="pp-modal-main">${referencia}</div>
      <div class="pp-modal-sub">${categoria}</div>
      </div>

      <div class="pp-modal-preco-box">
      <div class="pp-modal-main">R$ ${preco}</div>
      <div class="pp-modal-sub">${estoque}</div>
      </div>
      </div>
      `;
    });

    $('#ppListaProdutos').html(html);
  }

  function ppBuscarProdutos(termo) {
    const tabelaPreco = $('#ppTabelaPreco').val();

    if (ppAjaxAtual) {
      ppAjaxAtual.abort();
    }

    $('#ppListaProdutos').html(`
      <div class="pp-modal-loading">
      <div class="spinner"></div>
      <span>Buscando produtos...</span>
      </div>
      `);

    ppAjaxAtual = $.ajax({
      url: path_url + 'api/produtos/busca-avacada-pdv4',
      type: 'GET',
      dataType: 'json',
      data: {
        termo: termo,
        empresa_id: $('#empresa_id').val(),
        tabela_preco_id: tabelaPreco
      },
      success: function (response) {
        const produtos = response.data || response.produtos || response;
        ppRenderProdutos(produtos);
      },
      error: function (xhr, status) {
        if (status === 'abort') return;
        $('#ppListaProdutos').html('<div class="pp-modal-error">Erro ao buscar produtos.</div>');
      },
      complete: function () {
        ppAjaxAtual = null;
      }
    });
  }

  $('#abrirModalProduto').on('click', function () {
    ppAbrirModal();
  });

  $('#ppFecharModal').on('click', function () {
    ppFecharModal();
  });

  $('#ppModalPesquisa').on('click', function (e) {
    if (e.target.id === 'ppModalPesquisa') {
      ppFecharModal();
    }
  });

  $('#ppInputBusca').on('input', function () {
    const termo = $(this).val().trim();

    clearTimeout(ppTimeoutBusca);

    if (termo.length < 2) {
      $('#ppListaProdutos').html('<div class="pp-modal-empty">Digite pelo menos 2 caracteres.</div>');
      return;
    }

    ppTimeoutBusca = setTimeout(function () {
      ppBuscarProdutos(termo);
    }, 350);
  });

  $('#ppTabelaPreco').on('change', function () {
    const termo = $('#ppInputBusca').val().trim();
    if (termo.length >= 2) {
      ppBuscarProdutos(termo);
    }
  });

  $(document).on('click', '#ppListaProdutos .pp-modal-item', function () {
    $('#ppListaProdutos .pp-modal-item').removeClass('pp-ativo');
    $(this).addClass('pp-ativo');

    const p = $(this).data('produto');

    produto = {
      id: p.id,
      nome: p.nome,
      valor: convertMoedaToFloat(p.preco_formatado),
      img: p.img,
      disponivel: p.estoque || p.quantidade || 0,
      gerenciar_estoque: p.gerenciar_estoque ? 1 : 0
    };
    setLog('Informe a quantidade')
    $('.img-produto-selecionado').attr('src', produto.img).removeClass('d-none');


    $('#inputBuscaProduto').val(produto.nome);
    $('#valor_unitario').val(convertFloatToMoeda(produto.valor));
    $('#quantidade').val('1,000');
    $('#resultadoBusca').addClass('d-none').html('');
    $('#quantidade').focus();
    ppProdutoSelecionado = produto;

    // console.log('Produto selecionado:', ppProdutoSelecionado);

    ppFecharModal();
  });

  $(document).on('keydown', function (e) {
    if (e.key === 'Escape') {
      ppFecharModal();
    }
  });
});

const btnObservacoes = document.getElementById('btnObservacoes');
const popupObservacoes = document.getElementById('popupObservacoes');
const fecharObs = document.getElementById('fecharObs');
const salvarObs = document.getElementById('salvarObs');
const textoObservacao = document.getElementById('textoObservacao');

btnObservacoes.addEventListener('click', function () {
  const rect = btnObservacoes.getBoundingClientRect();

  popupObservacoes.style.left = rect.left + 'px';
  popupObservacoes.style.bottom = (window.innerHeight - rect.top + 10) + 'px';

  popupObservacoes.classList.toggle('ativo');

  if (popupObservacoes.classList.contains('ativo')) {
    setTimeout(() => textoObservacao.focus(), 50);
  }
});

fecharObs.addEventListener('click', function () {
  popupObservacoes.classList.remove('ativo');
});

salvarObs.addEventListener('click', function () {
  popupObservacoes.classList.remove('ativo');
});

document.addEventListener('keydown', function(e){
  if(e.key === 'F12'){
    e.preventDefault();
    popupObservacoes.classList.toggle('ativo');

    if (popupObservacoes.classList.contains('ativo')) {
      setTimeout(() => textoObservacao.focus(), 50);
    }
  }

  if(e.key === 'Escape'){
    popupObservacoes.classList.remove('ativo');
  }
});
let timerPesquisa = null;

// ========================
// ABRIR MODAL
// ========================
function abrirModalAguardando() {
  $('#modalAguardando').addClass('ativo');

  setTimeout(function(){
    $('#pesquisaVenda').focus();
  }, 100);

  carregarVendas();
}

(function () {
  const modalSeletor = '#modalAguardando';

  function fecharDropdownAguardando() {
    $(`${modalSeletor} .mv-dropdown`).removeClass('mv-dropdown-aberto');
  }

  $(document).on('click', `${modalSeletor} .mv-btn-acoes`, function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $dropdown = $(this).siblings('.mv-dropdown');

    $(`${modalSeletor} .mv-dropdown`).not($dropdown).removeClass('mv-dropdown-aberto');
    $dropdown.toggleClass('mv-dropdown-aberto');
  });

  $(document).on('click', `${modalSeletor} .mv-dropdown`, function (e) {
    e.stopPropagation();
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest(`${modalSeletor} .mv-acoes-coluna`).length) {
      fecharDropdownAguardando();
    }
  });

  $(document).on('click', `${modalSeletor} .mv-btn-continuar`, function (e) {
    e.preventDefault();
    e.stopPropagation();

    const id = $(this).data('id');
    fecharDropdownAguardando();

    continuarVendaAguardando(id);
  });

  $(document).on('click', `${modalSeletor} .mv-btn-cancelar`, function (e) {
    e.preventDefault();
    e.stopPropagation();

    const id = $(this).data('id');
    fecharDropdownAguardando();

    Swal.fire({
      title: 'Cancelar venda?',
      text: 'Deseja realmente cancelar esta venda?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sim, cancelar',
      cancelButtonText: 'Voltar',
      didOpen: () => {
        $('.swal2-container').css('z-index', '20000');
      }
    }).then((result) => {
      if (result.isConfirmed) {
        cancelarVendaAguardando(id);
      }
    });
  });

  function toast(msg, tipo = 'success') {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: tipo,
      title: msg,
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true
    });
  }

  function continuarVendaAguardando(id) {

    $.ajax({
      url: path_url + 'api/frenteCaixa/continuar-venda-suspensa/' + id,
      type: 'GET',
      dataType: 'json',
      data: {
        empresa_id: $('#empresa_id').val()
      },
      success: function (res) {

        if (!res || !res.itens) {
          toast('Venda inválida', 'error');
          return;
        }

        vendaEditandoId = id;
        limparVenda();

        if (res.cliente_id) {
          $('#cliente_id').val(res.cliente_id);
          $('#nome-cliente').text(res.cliente_nome || 'Consumidor');
          $('#cliente-box').removeClass('d-none');
          $('.img-produto-selecionado').addClass('d-none');
        }

        $('#textoObservacao').val(res.observacao || '');

        res.itens.forEach(function(item){
          adicionarProdutoNoPedido({
            id: item.produto_id,
            nome: item.nome,
            valor: item.valor_unitario,
            quantidade: item.quantidade,
            img: item.img || '',
            disponivel: item.disponivel || 0,
            gerenciar_estoque: item.gerenciar_estoque || 0
          });
        });

        calcTotal();

        $('#modalAguardando').removeClass('ativo');

        toast('Venda carregada para edição');
        setLog(`CARREGANDO VENDA`);
        voltarTelaVenda();
        
      },
      error: function () {
        toast('Erro ao carregar venda', 'error');
      }
    });
  }

  function cancelarVendaAguardando(id) {
    $.ajax({
      url: path_url + 'api/frenteCaixa/cancelar-venda-suspensa/' + id,
      type: 'POST',
      dataType: 'json',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        empresa_id: $('#empresa_id').val()
      },
      success: function () {

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Venda cancelada com sucesso',
          showConfirmButton: false,
          timer: 4000,
          timerProgressBar: true
        });

        carregarVendas($('#pesquisaVenda').val().trim());
      },
      error: function () {

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'Erro ao cancelar venda',
          showConfirmButton: false,
          timer: 4000
        });

      }
    });
  }

})();

// botão
$('#btnAguardando').on('click', function(){
  abrirModalAguardando();
});

// tecla F11
$(document).on('keydown', function(e){
  if(e.key === 'F9'){
    e.preventDefault();
    if(!$('#modalFechamentoPDV').hasClass('d-none')){
      return;
    }
    abrirModalAguardando();
  }

  if(e.key === 'F6'){
    e.preventDefault();
    confirmarCancelamento()
  }

  if(e.key === 'F4'){
    e.preventDefault();
    $('#abrirModalProduto').trigger('click')
  }

  if(e.key === 'F5'){
    e.preventDefault();
    $('#btnFinalizarVenda').trigger('click')
  }

  if(e.key === 'Escape'){
    $('#modalAguardando').removeClass('ativo');
  }
});

// fechar
$('#fecharModalAguardando').on('click', function(){
  $('#modalAguardando').removeClass('ativo');
});


// ========================
// CARREGAR VENDAS (AJAX)
// ========================
function carregarVendas(filtro = '') {

  $('#tbodyAguardando').html(`
    <tr>
    <td colspan="7" style="text-align:center; padding:20px;">
    Carregando...
    </td>
    </tr>
    `);

  $.ajax({
    url: path_url + 'api/frenteCaixa/venda-suspensas-pdv4',
    type: 'GET',
    data: { 
      filtro: filtro,
      empresa_id: $('#empresa_id').val()
    },
    dataType: 'json',
    success: function(res){

      let html = '';

      if(!res || res.length === 0){
        html = `
        <tr>
        <td colspan="7" style="text-align:center; padding:20px;">
        Nenhuma venda encontrada
        </td>
        </tr>
        `;
      } else {

        res.forEach(function(v){

          html += `
          <tr data-id="${v.id}" class="linha-venda mv-linha-venda">
          <td>${v.id}</td>
          <td>
          ${v.data}<br>
          às ${v.hora}
          </td>
          <td>
          <div class="cliente-venda">
          Cliente: ${v.cliente || 'Não definido'}
          </div>
          </td>
          <td>${v.itens}</td>
          <td>${v.desconto}</td>
          <td>${v.total}</td>
          <td class="mv-acoes-coluna">
          <button type="button" class="mv-btn-acoes" data-venda-id="${v.id}" aria-label="Abrir ações da venda">
          ⋮
          </button>

          <div class="mv-dropdown" data-dropdown-id="${v.id}">

          <button type="button" class="mv-dropdown-item mv-btn-continuar" data-id="${v.id}">
          <span>Continuar venda</span>
          </button>

          <button type="button" class="mv-dropdown-item mv-btn-cancelar mv-danger" data-id="${v.id}">
          <span>Cancelar venda</span>
          </button>
          </div>
          </td>
          </tr>
          `;
        });
      }

      $('#tbodyAguardando').html(html);
    },
    error: function(){
      $('#tbodyAguardando').html(`
        <tr>
        <td colspan="7" style="text-align:center; padding:20px; color:red;">
        Erro ao carregar vendas
        </td>
        </tr>
        `);
    }
  });
}


// ========================
// PESQUISA COM DELAY (TOP UX)
// ========================
$('#pesquisaVenda').on('keyup', function(){

  let filtro = $(this).val().trim();

  clearTimeout(timerPesquisa);

  timerPesquisa = setTimeout(function(){
    carregarVendas(filtro);
  }, 300);

});

$(document).on('click', '.pdv-menu-btn', function(e) {
  e.preventDefault();
  e.stopPropagation();

  const $item = $(this).closest('.item');
  const $menu = $(this).siblings('.pdv-menu-dropdown');

  $('.pdv-menu-dropdown').not($menu).removeClass('show');
  $('.item').removeClass('item-menu-open');

  $menu.toggleClass('show');

  if ($menu.hasClass('show')) {
    $item.addClass('item-menu-open');
  }
});

$(document).on('click', '.pdv-menu-dropdown', function(e) {
  e.stopPropagation();
});

$(document).on('click', function() {
  $('.pdv-menu-dropdown').removeClass('show');
  $('.item').removeClass('item-menu-open');
});


let pdvQtdItemAtual = null;

function abrirModalAlterarQuantidade($item) {
  pdvQtdItemAtual = $item;

  const nome = $item.find('.nome').text().trim();

  const quantidade = parseFloat(
    String($item.find('.input-quantidade').val() || '0').replace(',', '.')
    ) || 0;

  const valorUnitario = parseFloat(
    String($item.find('.input-valor-unitario').val() || '0').replace(',', '.')
    ) || 0;

  const total = quantidade * valorUnitario;
  const disponivel = parseFloat(String($item.data('disponivel') || 0).replace(',', '.')) || 0;

  $('#pdvQtdProdutoNome').text(nome);
  $('#pdvQtdValorOriginal').text(
    `${formatarNumero(quantidade)} Un x R$ ${formatarMoedaBR(valorUnitario)} = R$ ${formatarMoedaBR(total)}`
    );
  $('#pdvQtdValorFinal').text(
    `${formatarNumero(quantidade)} Un x R$ ${formatarMoedaBR(valorUnitario)} = R$ ${formatarMoedaBR(total)}`
    );
  $('#pdvQtdDisponivel').text(`${formatarNumero(disponivel)} Un`);
  $('#pdvQtdInput').val(formatarNumeroInput(quantidade));

  $('#pdvQtdModal').addClass('pdv-qtd-modal--open');

  setTimeout(function() {
    $('#pdvQtdInput').focus().select();
  }, 80);
}

function fecharModalAlterarQuantidade() {
  $('#pdvQtdModal').removeClass('pdv-qtd-modal--open');
  pdvQtdItemAtual = null;
}


$(document).on('click', '#pdvQtdSalvar', function () {
  if (!pdvQtdItemAtual) return;

  let novaQtd = String($('#pdvQtdInput').val() || '0').replace(',', '.');
  novaQtd = parseFloat(novaQtd);

  if (isNaN(novaQtd) || novaQtd <= 0) {
    alert('Informe uma quantidade válida.');
    $('#pdvQtdInput').focus();
    return;
  }

  const disponivel = parseFloat(
    String(pdvQtdItemAtual.data('disponivel') || 0).replace(',', '.')
    ) || 0;

  const gerenciarEstoque = parseInt(pdvQtdItemAtual.data('gerenciar_estoque')) || 0;
  console.log("gerenciarEstoque", gerenciarEstoque)
  console.log("novaQtd", novaQtd)
  console.log("disponivel", disponivel)
  if (gerenciarEstoque && novaQtd > disponivel) {

    Swal.fire({
      icon: 'warning',
      title: 'Estoque insuficiente',
      html: `Disponível: <strong>${formatarNumero(disponivel)} Un</strong>`,
      confirmButtonText: 'OK',
      confirmButtonColor: '#4254BA',
      backdrop: true,
      didOpen: () => {
        const swalContainer = document.querySelector('.swal2-container');
        swalContainer.style.zIndex = 999999;
      }
    });
    $('#pdvQtdInput').focus().select();
    return;
  }

  const valorUnitario = parseFloat(
    String(pdvQtdItemAtual.find('.input-valor-unitario').val() || '0').replace(',', '.')
    ) || 0;

  const novoTotal = novaQtd * valorUnitario;

  pdvQtdItemAtual.find('.input-quantidade').val(novaQtd);
  pdvQtdItemAtual.find('.detalhe').text(
    `${formatarNumero(novaQtd)} Un x R$ ${formatarMoedaBR(valorUnitario)}`
    );
  pdvQtdItemAtual.find('.item-total').text(`R$ ${formatarMoedaBR(novoTotal)}`);

  calcTotal();
  fecharModalAlterarQuantidade();
});

$(document).on('click', '#pdvQtdCancelar', function () {
  fecharModalAlterarQuantidade();
});

$(document).on('click', '.pdv-qtd-modal__backdrop', function () {
  fecharModalAlterarQuantidade();
});

$(document).on('keydown', function (e) {
  if (e.key === 'Escape' && $('#pdvQtdModal').hasClass('pdv-qtd-modal--open')) {
    fecharModalAlterarQuantidade();
  }
});

$(document).on('keydown', '#pdvQtdInput', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();
    $('#pdvQtdSalvar').trigger('click');
  }
});

function aplicarMascaraMoedaSimples(element) {
  let valor = element.value.replace(/\D/g, '');

  valor = (parseInt(valor || '0', 10) / 100).toFixed(2) + '';
  valor = valor.replace('.', ',');

  element.value = valor;
}

$(document).on('input', '.mask-moeda', function () {
  aplicarMascaraMoedaSimples(this);
});

$(document).on('click', '.pdv-menu-action', function () {
  const action = $(this).data('action');
  const $item = $(this).closest('.item');

  $('.pdv-menu-dropdown').removeClass('show');

  if (action === 'alterar-quantidade') {
    abrirModalAlterarQuantidade($item);
  }

  if (action === 'desconto') {
    abrirModalDesconto($item);
  }

  if (action === 'acrescimo') {
    abrirModalAcrescimo($item);
  }

  if (action === 'cancelar-item') {
    $item.addClass('item-cancelado');
    $item.attr('data-cancelado', 1);

    $item.find('[data-action="cancelar-item"]').hide();
    $item.find('[data-action="restaurar-item"]').show();
    calcTotal();
  }

  if (action === 'restaurar-item') {
    $item.removeClass('item-cancelado');
    $item.attr('data-cancelado', 0);

    $item.find('[data-action="restaurar-item"]').hide();
    $item.find('[data-action="cancelar-item"]').show();

    calcTotal();
  }
});

function formatarNumero(valor) {
  return Number(valor).toLocaleString('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 3
  });
}

function formatarNumeroInput(valor) {
  return String(valor).replace('.', ',');
}

// desconto
// desconto
let descontoModalItem = null;
let descontoTipoAtual = 'unitario';

function mdMoeda(valor) {
  return parseFloat(valor || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function mdNumero(valor) {
  if (typeof valor === 'number') return valor;
  if (!valor) return 0;

  valor = String(valor).trim();

  // se tiver vírgula, assume formato BR: 1.234,56
  if (valor.indexOf(',') !== -1) {
    valor = valor.replace(/\./g, '').replace(',', '.');
  }

  valor = valor.replace(/[^\d.-]/g, '');

  return parseFloat(valor) || 0;
}

function mdTextoResumo(qtd, unitario, total) {
  return `${formatarNumero(qtd)} Un x R$ ${mdMoeda(unitario)} = R$ ${mdMoeda(total)}`;
}

function abrirModalDesconto($item) {
  descontoModalItem = $item;
  descontoTipoAtual = 'unitario';

  const nome = $item.find('.nome').first().text().trim() || 'ITEM';
  const qtd = mdNumero($item.find('.input-quantidade').val() || 1);
  const valorBaseOriginal = mdNumero($item.find('.input-valor-base-original').val() || 0);
  const valorTotal = qtd * valorBaseOriginal;

  const descontoJaAplicado = mdNumero($item.attr('data-desconto-valor') || 0);

  $('#modal-desconto').data('quantidade', qtd);
  $('#modal-desconto').data('valor-unitario-original', valorBaseOriginal);
  $('#modal-desconto').data('valor-total-original', valorTotal);

  $('#md-produto-nome').text(nome);

  let descontoPercentual = valorBaseOriginal > 0
  ? (descontoJaAplicado / valorBaseOriginal) * 100
  : 0;

  $('#md-desconto-p').val(mdMoeda(descontoPercentual));
  $('#md-desconto-r').val(mdMoeda(descontoJaAplicado));

  $('#modal-desconto .md-tipo button').removeClass('active');
  $('#modal-desconto .md-tipo button[data-tipo="unitario"]').addClass('active');

  atualizarModalDesconto();

  $('#modal-desconto').css('display', 'flex');
}

function fecharModalDesconto() {
  $('#modal-desconto').hide();
  descontoModalItem = null;
}

function atualizarLabelsDesconto() {
  const qtd = mdNumero($('#modal-desconto').data('quantidade'));
  const valorUnitOriginal = mdNumero($('#modal-desconto').data('valor-unitario-original'));
  const valorTotalOriginal = mdNumero($('#modal-desconto').data('valor-total-original'));

  if (descontoTipoAtual === 'unitario') {
    $('#md-label-original').text('Valor original');
    $('#md-label-final').text('Valor final');
    $('#md-label-percentual').text('Desconto (%)');
    $('#md-label-reais').text('Desconto (R$)');
    $('#md-label-valor-final-input').text('Valor final');
    $('#md-valor-original').text(mdTextoResumo(qtd, valorUnitOriginal, valorTotalOriginal));
  } else {
    $('#md-label-original').text('Valor original do total');
    $('#md-label-final').text('Valor final do total');
    $('#md-label-percentual').text('Desconto total (%)');
    $('#md-label-reais').text('Desconto total (R$)');
    $('#md-label-valor-final-input').text('Valor final do total');
    $('#md-valor-original').text(`R$ ${mdMoeda(valorTotalOriginal)}`);
  }
}

function atualizarModalDesconto(origem = null) {
  const qtd = mdNumero($('#modal-desconto').data('quantidade'));
  const valorUnitOriginal = mdNumero($('#modal-desconto').data('valor-unitario-original'));
  const valorTotalOriginal = mdNumero($('#modal-desconto').data('valor-total-original'));

  const base = descontoTipoAtual === 'unitario' ? valorUnitOriginal : valorTotalOriginal;

  let descontoP = mdNumero($('#md-desconto-p').val());
  let descontoR = mdNumero($('#md-desconto-r').val());

  if (descontoP < 0) descontoP = 0;
  if (descontoR < 0) descontoR = 0;

  if (origem === 'p') {
    descontoR = base * (descontoP / 100);
    $('#md-desconto-r').val(mdMoeda(descontoR));
  }

  if (origem === 'r') {
    descontoP = base > 0 ? (descontoR / base) * 100 : 0;
    $('#md-desconto-p').val(mdMoeda(descontoP));
  }

  if (descontoR > base) {
    descontoR = base;
    descontoP = 100;
    $('#md-desconto-r').val(mdMoeda(descontoR));
    $('#md-desconto-p').val(mdMoeda(descontoP));
  }

  atualizarLabelsDesconto();

  if (descontoTipoAtual === 'unitario') {
    const valorUnitFinal = Math.max(valorUnitOriginal - descontoR, 0);
    const valorTotalFinal = valorUnitFinal * qtd;

    $('#md-valor-final').val(mdMoeda(valorUnitFinal));
    $('#md-valor-final-label').text(mdTextoResumo(qtd, valorUnitFinal, valorTotalFinal));
  } else {
    const valorTotalFinal = Math.max(valorTotalOriginal - descontoR, 0);
    const valorUnitFinal = qtd > 0 ? valorTotalFinal / qtd : 0;

    $('#md-valor-final').val(mdMoeda(valorTotalFinal));
    $('#md-valor-final-label').text(
      `${formatarNumero(qtd)} Un x R$ ${mdMoeda(valorUnitFinal)} = R$ ${mdMoeda(valorTotalFinal)}`
      );
  }
}

$(document).on('click', '#modal-desconto .md-tipo button', function () {
  $('#modal-desconto .md-tipo button').removeClass('active');
  $(this).addClass('active');

  descontoTipoAtual = $(this).data('tipo');
  $('#md-desconto-p').val('0,00');
  $('#md-desconto-r').val('0,00');

  atualizarModalDesconto();
});

$(document).on('input', '#md-desconto-p', function () {
  atualizarModalDesconto('p');
});

$(document).on('input', '#md-desconto-r', function () {
  atualizarModalDesconto('r');
});

$(document).on('click', '#md-btn-cancelar', function () {
  fecharModalDesconto();
});

$(document).on('click', '#modal-desconto', function (e) {
  if (e.target.id === 'modal-desconto') {
    fecharModalDesconto();
  }
});

$(document).on('click', '#md-btn-aplicar', function () {
  if (!descontoModalItem) return;

  const qtd = mdNumero($('#modal-desconto').data('quantidade'));
  const valorUnitOriginal = mdNumero($('#modal-desconto').data('valor-unitario-original'));
  const valorTotalOriginal = mdNumero($('#modal-desconto').data('valor-total-original'));
  const descontoP = mdNumero($('#md-desconto-p').val());
  const descontoR = mdNumero($('#md-desconto-r').val());

  let valorUnitFinal = valorUnitOriginal;
  let valorTotalFinal = valorTotalOriginal;

  if (descontoTipoAtual === 'unitario') {
    valorUnitFinal = Math.max(valorUnitOriginal - descontoR, 0);
    valorTotalFinal = valorUnitFinal * qtd;
  } else {
    valorTotalFinal = Math.max(valorTotalOriginal - descontoR, 0);
    valorUnitFinal = qtd > 0 ? valorTotalFinal / qtd : 0;
  }

  descontoModalItem.find('.input-valor-unitario').val(valorUnitFinal);
  descontoModalItem.find('.detalhe').text(
    `${formatarNumero(qtd)} Un x R$ ${mdMoeda(valorUnitFinal)}`
    );
  descontoModalItem.find('.item-total').text(`R$ ${mdMoeda(valorTotalFinal)}`);

  descontoModalItem.attr('data-desconto-tipo', descontoTipoAtual);
  descontoModalItem.attr('data-desconto-percentual', descontoP);
  descontoModalItem.attr('data-desconto-valor', descontoR);

  calcTotal();
  fecharModalDesconto();
});

let acrescimoModalItem = null;
let acrescimoTipoAtual = 'unitario';

function maMoeda(valor) {
  return parseFloat(valor || 0).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function maNumero(valor) {
  if (typeof valor === 'number') return valor;
  if (!valor) return 0;

  valor = String(valor).trim();

  if (valor.indexOf(',') !== -1) {
    valor = valor.replace(/\./g, '').replace(',', '.');
  }

  valor = valor.replace(/[^\d.-]/g, '');

  return parseFloat(valor) || 0;
}

function maTextoResumo(qtd, unitario, total) {
  return `${formatarNumero(qtd)} Un x R$ ${maMoeda(unitario)} = R$ ${maMoeda(total)}`;
}

function abrirModalAcrescimo($item) {
  acrescimoModalItem = $item;
  acrescimoTipoAtual = 'unitario';

  const nome = $item.find('.nome').first().text().trim() || 'ITEM';
  const qtd = maNumero($item.find('.input-quantidade').val() || 1);
  const valorBaseOriginal = maNumero($item.find('.input-valor-base-original').val() || 0);
  const valorTotal = qtd * valorBaseOriginal;

  const acrescimoJaAplicado = maNumero($item.attr('data-acrescimo-valor') || 0);

  $('#modal-acrescimo').data('quantidade', qtd);
  $('#modal-acrescimo').data('valor-unitario-original', valorBaseOriginal);
  $('#modal-acrescimo').data('valor-total-original', valorTotal);

  $('#ma-produto-nome').text(nome);

  let acrescimoPercentual = valorBaseOriginal > 0
  ? (acrescimoJaAplicado / valorBaseOriginal) * 100
  : 0;

  $('#ma-acrescimo-p').val(maMoeda(acrescimoPercentual));
  $('#ma-acrescimo-r').val(maMoeda(acrescimoJaAplicado));

  $('#modal-acrescimo .md-tipo button').removeClass('active');
  $('#modal-acrescimo .md-tipo button[data-tipo="unitario"]').addClass('active');

  atualizarModalAcrescimo();

  $('#modal-acrescimo').css('display', 'flex');
}

function fecharModalAcrescimo() {
  $('#modal-acrescimo').hide();
  acrescimoModalItem = null;
}

function atualizarLabelsAcrescimo() {
  const qtd = maNumero($('#modal-acrescimo').data('quantidade'));
  const valorUnitOriginal = maNumero($('#modal-acrescimo').data('valor-unitario-original'));
  const valorTotalOriginal = maNumero($('#modal-acrescimo').data('valor-total-original'));

  if (acrescimoTipoAtual === 'unitario') {
    $('#ma-label-original').text('Valor original');
    $('#ma-label-final').text('Valor final');
    $('#ma-label-percentual').text('Acréscimo (%)');
    $('#ma-label-reais').text('Acréscimo (R$)');
    $('#ma-label-valor-final-input').text('Valor final');

    $('#ma-valor-original').text(maTextoResumo(qtd, valorUnitOriginal, valorTotalOriginal));
  } else {
    $('#ma-label-original').text('Valor original do total');
    $('#ma-label-final').text('Valor final do total');
    $('#ma-label-percentual').text('Acréscimo total (%)');
    $('#ma-label-reais').text('Acréscimo total (R$)');
    $('#ma-label-valor-final-input').text('Valor final do total');

    $('#ma-valor-original').text(`R$ ${maMoeda(valorTotalOriginal)}`);
  }
}

function atualizarModalAcrescimo(origem = null) {
  const qtd = maNumero($('#modal-acrescimo').data('quantidade'));
  const valorUnitOriginal = maNumero($('#modal-acrescimo').data('valor-unitario-original'));
  const valorTotalOriginal = maNumero($('#modal-acrescimo').data('valor-total-original'));

  const base = acrescimoTipoAtual === 'unitario' ? valorUnitOriginal : valorTotalOriginal;

  let acrescimoP = maNumero($('#ma-acrescimo-p').val());
  let acrescimoR = maNumero($('#ma-acrescimo-r').val());

  if (acrescimoP < 0) acrescimoP = 0;
  if (acrescimoR < 0) acrescimoR = 0;

  if (origem === 'p') {
    acrescimoR = base * (acrescimoP / 100);
    $('#ma-acrescimo-r').val(maMoeda(acrescimoR));
  }

  if (origem === 'r') {
    acrescimoP = base > 0 ? (acrescimoR / base) * 100 : 0;
    $('#ma-acrescimo-p').val(maMoeda(acrescimoP));
  }

  atualizarLabelsAcrescimo();

  if (acrescimoTipoAtual === 'unitario') {
    const valorUnitFinal = valorUnitOriginal + acrescimoR;
    const valorTotalFinal = valorUnitFinal * qtd;

    $('#ma-valor-final').val(maMoeda(valorUnitFinal));
    $('#ma-valor-final-label').text(maTextoResumo(qtd, valorUnitFinal, valorTotalFinal));
  } else {
    const valorTotalFinal = valorTotalOriginal + acrescimoR;
    const valorUnitFinal = qtd > 0 ? valorTotalFinal / qtd : 0;

    $('#ma-valor-final').val(maMoeda(valorTotalFinal));
    $('#ma-valor-final-label').text(
      `${formatarNumero(qtd)} Un x R$ ${maMoeda(valorUnitFinal)} = R$ ${maMoeda(valorTotalFinal)}`
      );
  }
}

$(document).on('click', '#modal-acrescimo .md-tipo button', function () {
  $('#modal-acrescimo .md-tipo button').removeClass('active');
  $(this).addClass('active');

  acrescimoTipoAtual = $(this).data('tipo');
  $('#ma-acrescimo-p').val('0,00');
  $('#ma-acrescimo-r').val('0,00');

  atualizarModalAcrescimo();
});

$(document).on('input', '#ma-acrescimo-p', function () {
  atualizarModalAcrescimo('p');
});

$(document).on('input', '#ma-acrescimo-r', function () {
  atualizarModalAcrescimo('r');
});

$(document).on('click', '#ma-btn-cancelar', function () {
  fecharModalAcrescimo();
});

$(document).on('click', '#modal-acrescimo', function (e) {
  if (e.target.id === 'modal-acrescimo') {
    fecharModalAcrescimo();
  }
});

$(document).on('click', '#ma-btn-aplicar', function () {
  if (!acrescimoModalItem) return;

  const qtd = maNumero($('#modal-acrescimo').data('quantidade'));
  const valorUnitOriginal = maNumero($('#modal-acrescimo').data('valor-unitario-original'));
  const valorTotalOriginal = maNumero($('#modal-acrescimo').data('valor-total-original'));
  const acrescimoP = maNumero($('#ma-acrescimo-p').val());
  const acrescimoR = maNumero($('#ma-acrescimo-r').val());

  let valorUnitFinal = valorUnitOriginal;
  let valorTotalFinal = valorTotalOriginal;

  if (acrescimoTipoAtual === 'unitario') {
    valorUnitFinal = valorUnitOriginal + acrescimoR;
    valorTotalFinal = valorUnitFinal * qtd;
  } else {
    valorTotalFinal = valorTotalOriginal + acrescimoR;
    valorUnitFinal = qtd > 0 ? valorTotalFinal / qtd : 0;
  }

  acrescimoModalItem.find('.input-valor-unitario').val(valorUnitFinal);
  acrescimoModalItem.find('.detalhe').text(
    `${formatarNumero(qtd)} Un x R$ ${maMoeda(valorUnitFinal)}`
    );
  acrescimoModalItem.find('.item-total').text(`R$ ${maMoeda(valorTotalFinal)}`);

  acrescimoModalItem.attr('data-acrescimo-tipo', acrescimoTipoAtual);
  acrescimoModalItem.attr('data-acrescimo-percentual', acrescimoP);
  acrescimoModalItem.attr('data-acrescimo-valor', acrescimoR);

  calcTotal();
  fecharModalAcrescimo();
});

function calcResumoFinanceiro() {
  let totalDesconto = 0;
  let totalAcrescimo = 0;

  $('.lista-scroll .item').each(function () {

    if ($(this).attr('data-cancelado') == 1) return;

    let desconto = parseFloat($(this).attr('data-desconto-valor')) || 0;
    let acrescimo = parseFloat($(this).attr('data-acrescimo-valor')) || 0;

    totalDesconto += desconto;
    totalAcrescimo += acrescimo;
  });

  $('.valor-desconto').text('R$ ' + convertFloatToMoeda(totalDesconto));
  $('.valor-acrescimo').text('R$ ' + convertFloatToMoeda(totalAcrescimo));
}

$('#btnAguardar').on('click', function () {
  confirmarSuspensao(function () {
    suspenderVenda();
  });
});

function confirmarSuspensao(callback) {
  Swal.fire({
    title: 'Suspender venda?',
    text: 'Deseja realmente deixar essa venda em aguardando?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sim, suspender',
    cancelButtonText: 'Cancelar',
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#6b7280'
  }).then((result) => {
    if (result.isConfirmed) {
            callback(); // executa ação real
          }
        });
}

function suspenderVenda() {

  let itens = [];

  // 🔥 percorre itens do carrinho
  $('.lista-scroll .item').each(function () {

    // ignora cancelados
    if ($(this).attr('data-cancelado') == 1) return;

    let produto_id = $(this).find('input[name="produto_id[]"]').val();
    let quantidade = parseFloat($(this).find('.input-quantidade').val()) || 0;
    let valor_unitario = parseFloat($(this).find('.input-valor-unitario').val()) || 0;
    let sub_total = quantidade * valor_unitario;

    itens.push({
      produto_id: produto_id,
      variacao_id: null, // se tiver depois você coloca
      quantidade: quantidade,
      valor_unitario: valor_unitario,
      sub_total: sub_total
    });
  });

  // 🔥 totais
  let total = convertMoedaToFloat($('.valor-total').text());
  let desconto = convertMoedaToFloat($('.valor-desconto').text());
  let acrescimo = convertMoedaToFloat($('.valor-acrescimo').text());

  // 🔥 payload final
  let data = {
    _token: $('meta[name="csrf-token"]').attr('content'),

    empresa_id: $('#empresa_id').val(),
    cliente_id: $('#cliente_id').val() || null,
    total: total,
    desconto: desconto,
    acrescimo: acrescimo,
    observacao: $('#textoObservacao').val(),
    tipo_pagamento: null, // pode ajustar depois
    local_id: $('#local_id').val(),
    user_id: $('#user_id').val(),
    funcionario_id: $('#funcionario_id').val(),

    itens: itens
  };

  // console.log(data)
  // return
  if (itens.length === 0) {
    Swal.fire('Atenção', 'Adicione pelo menos 1 item para finalizar!', 'info');
    return;
  }

  $.ajax({
    url: path_url + 'api/frenteCaixa/suspender-venda4',
    method: 'POST',
    data: data,

    success: function () {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Venda suspensa com sucesso',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
      });

      limparVenda();
    },

    error: function () {
      Swal.fire('Erro!', 'Não foi possível suspender.', 'error');
    }
  });
}

function limparVenda() {

  $('.lista-scroll').empty();

  produto = {};

  $('#inputBuscaProduto').val('');
  $('#quantidade').val('1,000');
  $('#valor_unitario').val('0,00');

  $('.img-produto-selecionado')
  .attr('src', '')
  .addClass('d-none');

  $('#textoObservacao').val('');

  $('#cliente_id').val('');
  $('#nome-cliente').text('Consumidor');
  $('#cliente-box').addClass('d-none');

  $('#pg-lista-recebimentos').html(`
    <div class="pg-lista-vazia">
    Não há nenhuma forma de recebimento definida para o pedido.
    </div>
    `);

  $('#mf-troco').text('R$ 0,00');

  $('#modalFechamentoPDV').addClass('d-none');

  $('#venda_suspensa_id').val('');

  calcTotal();

  setLog('Venda limpa. Pronto para nova venda');

  $('.select-custom input[name="vendedor_id"]').val('');
  $('.select-custom .select-selected').text('Selecione');
  $('.select-custom .select-options div').removeClass('active');

}

let modalClientePDVTimer = null;
let modalClientePDVClienteSelecionado = null;

function abrirModalClientePDV() {

  $('#modalClientePDV').addClass('modal-cliente-pdv--ativo');
  $('#modalClientePDVBusca').focus();

  $('#modalClientePDVBusca').val('');
  $('#modalClientePDVTbody').html(`
    <tr>
    <td colspan="6" id="modalClientePDVEmpty">
    Digite algo para pesquisar pelo cliente.
    </td>
    </tr>
    `);

  setTimeout(function () {
    $('#modalClientePDVBusca').focus();
  }, 100);
}

function fecharModalClientePDV() {
  $('#modalClientePDV').removeClass('modal-cliente-pdv--ativo');
}

function renderClientesModalPDV(clientes) {
  let html = '';

  if (!clientes || !clientes.length) {
    html = `
    <tr>
    <td colspan="6" id="modalClientePDVEmpty">
    Nenhum cliente encontrado.
    </td>
    </tr>
    `;
    $('#modalClientePDVTbody').html(html);
    return;
  }

  clientes.forEach(function (c, i) {
    html += `
    <tr 
    class="modal-cliente-pdv-linha modal-cliente-pdv-anim"
    data-id="${c.id}"
    style="animation-delay: ${i * 0.04}s"
    >
    <td>${c.numero_sequencial || ''}</td>
    <td>${c.razao_social || ''}</td>
    <td>${c.nome_fantasia || ''}</td>
    <td>${c.cpf_cnpj || ''}</td>
    <td>${c.endereco || ''}</td>
    <td>${c.cidade? c.cidade.info : '' || ''}</td>
    <td>${c.telefone || ''}</td>
    </tr>
    `;
  });

  $('#modalClientePDVTbody').html(html);
}

function buscarClientesModalPDV(filtro = '') {
  if (!filtro || filtro.length < 2) {
    $('#modalClientePDVTbody').html(`
      <tr>
      <td colspan="6" id="modalClientePDVEmpty">
      Digite algo para pesquisar pelo cliente.
      </td>
      </tr>
      `);
    return;
  }

  $('#modalClientePDVTbody').html(`
    <tr>
    <td colspan="6" id="modalClientePDVEmpty">
    Carregando...
    </td>
    </tr>
    `);

  $.ajax({
    url: path_url + 'api/clientes/buscar-pdv4',
    type: 'GET',
    dataType: 'json',
    data: {
      filtro: filtro,
      empresa_id: $('#empresa_id').val()
    },
    success: function (res) {
      renderClientesModalPDV(res);
    },
    error: function () {
      $('#modalClientePDVTbody').html(`
        <tr>
        <td colspan="6" id="modalClientePDVEmpty">
        Erro ao buscar clientes.
        </td>
        </tr>
        `);
    }
  });
}

function selecionarClienteModalPDV(id) {
  $.ajax({
    url: path_url + 'api/clientes/find/' + id,
    type: 'GET',
    dataType: 'json',
    data: {
      empresa_id: $('#empresa_id').val()
    },
    success: function (res) {
      if (!res || !res.id) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'warning',
          title: 'Cliente inválido',
          showConfirmButton: false,
          timer: 2000
        });
        return;
      }

      $('#cliente_id').val(res.id);
      $('#nome-cliente').text(res.razao_social || res.nome || 'Consumidor');

      $('#cliente-box').removeClass('d-none');
      $('.img-produto-selecionado').addClass('d-none');
      setLog(`CLIENTE SELECIONADO: ${(res.razao_social || res.nome || '').toUpperCase()}`);

      fecharModalClientePDV();

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Cliente carregado com sucesso',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
    },
    error: function () {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Erro ao selecionar cliente',
        showConfirmButton: false,
        timer: 3000
      });
    }
  });
}

// abrir no botão
$('#btnCliente').on('click', function () {
  abrirModalClientePDV();
});

// abrir no F2
$(document).on('keydown', function (e) {
  if (e.key === 'F2') {
    e.preventDefault();
    abrirModalClientePDV();
  }

  if (e.key === 'Escape' && $('#modalClientePDV').hasClass('modal-cliente-pdv--ativo')) {
    fecharModalClientePDV();
  }
});

// fechar
$('#modalClientePDVFechar').on('click', function () {
  fecharModalClientePDV();
});

// clique fora fecha
$('#modalClientePDV').on('click', function (e) {
  if (e.target.id === 'modalClientePDV') {
    fecharModalClientePDV();
  }
});

// busca com delay
$('#modalClientePDVBusca').on('keyup', function () {
  const filtro = $(this).val().trim();

  clearTimeout(modalClientePDVTimer);

  modalClientePDVTimer = setTimeout(function () {
    buscarClientesModalPDV(filtro);
  }, 300);
});

// clicar na linha
$(document).on('click', '#modalClientePDVTabela tbody tr.modal-cliente-pdv-linha', function () {
  $('#modalClientePDVTabela tbody tr.modal-cliente-pdv-linha').removeClass('modal-cliente-pdv-linha--selecionada');
  $(this).addClass('modal-cliente-pdv-linha--selecionada');

  const id = $(this).data('id');
  modalClientePDVClienteSelecionado = id;

  selecionarClienteModalPDV(id);
});

// enter seleciona a primeira linha
$('#modalClientePDVBusca').on('keydown', function (e) {
  if (e.key === 'Enter') {
    e.preventDefault();

    const $primeira = $('#modalClientePDVTabela tbody tr.modal-cliente-pdv-linha').first();

    if ($primeira.length) {
      $primeira.trigger('click');
    }
  }
});

function abrirModalNovoClientePDV() {
  limparModalNovoClientePDV();
  $('#modalNovoClientePDV').addClass('modal-novo-cliente-pdv--ativo');

  setTimeout(function () {
    $('#modalNovoClientePDVDocumento').focus();
  }, 100);
}

function fecharModalNovoClientePDV() {
  $('#modalNovoClientePDV').removeClass('modal-novo-cliente-pdv--ativo');
}

function limparModalNovoClientePDV() {
  $('#modalNovoClientePDVNome').val('');
  $('#modalNovoClientePDVFantasia').val('');
  $('#modalNovoClientePDVDocumento').val('');
  $('#modalNovoClientePDVTelefone').val('');
  $('#modalNovoClientePDVEmail').val('');
  $('#modalNovoClientePDVEndereco').val('');
  $('#modalNovoClientePDVNumero').val('');
  $('#modalNovoClientePDVBairro').val('');
  $('#modalNovoClientePDVCep').val('');
  $('#modalNovoClientePDVObservacao').val('');
}

$('#modalClientePDVNovoCliente').on('click', function () {
  abrirModalNovoClientePDV();
});

$('#modalNovoClientePDVCancelar').on('click', function () {
  fecharModalNovoClientePDV();
});

$('#modalNovoClientePDV').on('click', function (e) {
  if (e.target.id === 'modalNovoClientePDV') {
    fecharModalNovoClientePDV();
  }
});

$(document).on('keydown', function (e) {
  if (e.key === 'Escape' && $('#modalNovoClientePDV').hasClass('modal-novo-cliente-pdv--ativo')) {
    fecharModalNovoClientePDV();
  }
});


function aplicarMascaraCpfCnpjPDV(valor) {
  valor = (valor || '').replace(/\D/g, '');

  valor = valor.slice(0, 14);

  if (valor.length <= 11) {
    valor = valor.replace(/^(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
    valor = valor.replace(/\.(\d{3})(\d)/, '.$1-$2');
  } else {
    valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
    valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
  }

  return valor;
}

$('#modalNovoClientePDVDocumento').on('input', function () {
  this.value = aplicarMascaraCpfCnpjPDV(this.value);
});

function aplicarMascaraTelefonePDV(valor) {
  valor = (valor || '').replace(/\D/g, '').slice(0, 11);

  if (valor.length <= 10) {
    valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
  } else {
    valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2');
    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
  }

  return valor;
}

function aplicarMascaraCepPDV(valor) {
  valor = (valor || '').replace(/\D/g, '').slice(0, 8);
  valor = valor.replace(/^(\d{5})(\d)/, '$1-$2');
  return valor;
}

$('#modalNovoClientePDVTelefone').on('input', function () {
  this.value = aplicarMascaraTelefonePDV(this.value);
});

$('#modalNovoClientePDVCep').on('input', function () {
  this.value = aplicarMascaraCepPDV(this.value);
});

let modalNovoClientePDVCidadeTimer = null;
let modalNovoClientePDVCidadeIndex = -1;

function fecharBuscaCidadePDV() {
  $('#modalNovoClientePDVCidadeResultado')
  .removeClass('modalNovoClientePDVCidadeResultado--ativo')
  .html('');

  modalNovoClientePDVCidadeIndex = -1;
}

function renderBuscaCidadePDV(cidades) {
  let html = '';

  if (!cidades || !cidades.length) {
    html = `<div class="modalNovoClientePDVCidadeVazio">Nenhuma cidade encontrada.</div>`;
    $('#modalNovoClientePDVCidadeResultado')
    .html(html)
    .addClass('modalNovoClientePDVCidadeResultado--ativo');
    return;
  }

  cidades.forEach(function (cidade, i) {
    html += `
    <div 
    class="modalNovoClientePDVCidadeItem" 
    data-id="${cidade.id}" 
    data-nome="${cidade.nome} (${cidade.uf})">
    ${cidade.nome} (${cidade.uf})
    </div>
    `;
  });

  $('#modalNovoClientePDVCidadeResultado')
  .html(html)
  .addClass('modalNovoClientePDVCidadeResultado--ativo');
}

function buscarCidadePDV(pesquisa) {
  if (!pesquisa || pesquisa.length < 2) {
    fecharBuscaCidadePDV();
    return;
  }

  $.ajax({
    url: path_url + 'api/buscaCidades',
    type: 'GET',
    dataType: 'json',
    data: {
      pesquisa: pesquisa
    },
    success: function (res) {
      renderBuscaCidadePDV(res);
    },
    error: function () {
      $('#modalNovoClientePDVCidadeResultado')
      .html(`<div class="modalNovoClientePDVCidadeVazio">Erro ao buscar cidades.</div>`)
      .addClass('modalNovoClientePDVCidadeResultado--ativo');
    }
  });
}

$('#modalNovoClientePDVCidadeBusca').on('input', function () {
  const pesquisa = $(this).val().trim();

  $('#modalNovoClientePDVCidadeId').val('');

  clearTimeout(modalNovoClientePDVCidadeTimer);

  modalNovoClientePDVCidadeTimer = setTimeout(function () {
    buscarCidadePDV(pesquisa);
  }, 250);
});

$(document).on('click', '#modalNovoClientePDVCidadeResultado .modalNovoClientePDVCidadeItem', function () {
  const id = $(this).data('id');
  const nome = $(this).data('nome');

  $('#modalNovoClientePDVCidadeId').val(id);
  $('#modalNovoClientePDVCidadeBusca').val(nome);

  fecharBuscaCidadePDV();
});

$(document).on('click', function (e) {
  if (!$(e.target).closest('#modalNovoClientePDVCidadeWrap').length) {
    fecharBuscaCidadePDV();
  }
});

$('#modalNovoClientePDVCidadeBusca').on('keydown', function (e) {
  const $itens = $('#modalNovoClientePDVCidadeResultado .modalNovoClientePDVCidadeItem');

  if (!$itens.length) return;

  if (e.key === 'ArrowDown') {
    e.preventDefault();
    modalNovoClientePDVCidadeIndex++;
    if (modalNovoClientePDVCidadeIndex >= $itens.length) {
      modalNovoClientePDVCidadeIndex = 0;
    }
  }

  if (e.key === 'ArrowUp') {
    e.preventDefault();
    modalNovoClientePDVCidadeIndex--;
    if (modalNovoClientePDVCidadeIndex < 0) {
      modalNovoClientePDVCidadeIndex = $itens.length - 1;
    }
  }

  if (e.key === 'Enter') {
    e.preventDefault();

    if (modalNovoClientePDVCidadeIndex >= 0) {
      $itens.eq(modalNovoClientePDVCidadeIndex).trigger('click');
    }
  }

  $itens.removeClass('modalNovoClientePDVCidadeItem--ativo');

  if (modalNovoClientePDVCidadeIndex >= 0) {
    $itens.eq(modalNovoClientePDVCidadeIndex).addClass('modalNovoClientePDVCidadeItem--ativo');
  }
});

function limparModalNovoClientePDV() {
  $('#modalNovoClientePDVNome').val('');
  $('#modalNovoClientePDVFantasia').val('');
  $('#modalNovoClientePDVDocumento').val('');
  $('#modalNovoClientePDVTelefone').val('');
  $('#modalNovoClientePDVEmail').val('');
  $('#modalNovoClientePDVRua').val('');
  $('#modalNovoClientePDVNumero').val('');
  $('#modalNovoClientePDVBairro').val('');
  $('#modalNovoClientePDVCep').val('');
  $('#modalNovoClientePDVCidadeBusca').val('');
  $('#modalNovoClientePDVCidadeId').val('');

  fecharBuscaCidadePDV();
}

$('#modalNovoClientePDVSalvar').on('click', function () {
  const nome = $('#modalNovoClientePDVNome').val().trim();

  if (!nome) {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'warning',
      title: 'Informe o nome do cliente',
      showConfirmButton: false,
      timer: 2000
    });
    $('#modalNovoClientePDVNome').focus();
    return;
  }

  $.ajax({
    url: path_url + 'api/clientes/store',
    type: 'POST',
    dataType: 'json',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      empresa_id: $('#empresa_id').val(),
      razao_social: $('#modalNovoClientePDVNome').val(),
      nome_fantasia: $('#modalNovoClientePDVFantasia').val(),
      cpf_cnpj: $('#modalNovoClientePDVDocumento').val(),
      telefone: $('#modalNovoClientePDVTelefone').val(),
      email: $('#modalNovoClientePDVEmail').val(),
      rua: $('#modalNovoClientePDVRua').val(),
      numero: $('#modalNovoClientePDVNumero').val(),
      bairro: $('#modalNovoClientePDVBairro').val(),
      cep: $('#modalNovoClientePDVCep').val(),
      cidade_id: $('#modalNovoClientePDVCidadeId').val() || null
    },
    success: function (res) {
      $('#cliente_id').val(res.id);
      $('#nome-cliente').text(res.razao_social || 'Consumidor');

      setLog(`CLIENTE CADASTRADO: ${(res.razao_social || '').toUpperCase()}`);

      fecharModalNovoClientePDV();
      fecharModalClientePDV();

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Cliente cadastrado com sucesso',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
    },
    error: function (xhr) {
      let msg = 'Erro ao salvar cliente';

      if (xhr.responseJSON) {
        if (typeof xhr.responseJSON === 'string') {
          msg = xhr.responseJSON;
        } else if (xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
      }

      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: msg,
        showConfirmButton: false,
        timer: 2200
      });
    }
  });
});

$(document).on('blur', '#modalNovoClientePDVDocumento', function () {
  let cpfCnpj = ($(this).val() || '').replace(/\D/g, '');

  // só consulta se for CNPJ
  if (cpfCnpj.length === 14) {
    $.ajax({
      url: 'https://publica.cnpj.ws/cnpj/' + cpfCnpj,
      method: 'GET',
      timeout: 7000,
      beforeSend: function () {
        console.log('Consultando CNPJ...');
      },
      success: function (data) {
        if (!data || !data.estabelecimento) {
          return;
        }

        let ie = '';
        if (data.estabelecimento?.inscricoes_estaduais?.length > 0) {
          ie = data.estabelecimento.inscricoes_estaduais[0].inscricao_estadual || '';
        }

        // nome / razão social
        $('#modalNovoClientePDVNome').val(data.razao_social || '');

        // fantasia / apelido
        $('#modalNovoClientePDVFantasia').val(data.estabelecimento.nome_fantasia || '');

        // rua / logradouro
        let logradouro = [
        data.estabelecimento.tipo_logradouro || '',
        data.estabelecimento.logradouro || ''
        ].join(' ').trim();

        $('#modalNovoClientePDVRua').val(logradouro);

        // número
        $('#modalNovoClientePDVNumero').val(data.estabelecimento.numero || '');

        // bairro
        $('#modalNovoClientePDVBairro').val(data.estabelecimento.bairro || '');

        // cep
        let cep = (data.estabelecimento.cep || '').toString().replace(/\D/g, '');
        if (cep.length >= 8) {
          $('#modalNovoClientePDVCep').val(cep.substring(0, 5) + '-' + cep.substring(5, 8));
        }

        // email
        $('#modalNovoClientePDVEmail').val(data.estabelecimento.email || '');

        // telefone
        $('#modalNovoClientePDVTelefone').val(data.estabelecimento.telefone1 || '');

        // inscrição estadual, se existir campo
        if ($('#modalNovoClientePDVIE').length) {
          $('#modalNovoClientePDVIE').val(ie);
        }

        // contribuinte, se existir campo
        if (ie !== '' && $('#modalNovoClientePDVContribuinte').length) {
          $('#modalNovoClientePDVContribuinte').val(1).change();
        }

        // cidade
        if (data.estabelecimento.cidade?.ibge_id) {
          findCidade(data.estabelecimento.cidade.ibge_id);
        } else if (data.estabelecimento.cidade?.nome) {
          $('#modalNovoClientePDVCidadeBusca').val(
            data.estabelecimento.cidade.nome +
            (data.estabelecimento.estado?.sigla ? ' (' + data.estabelecimento.estado.sigla + ')' : '')
            );
        }
      },
      error: function (xhr, status, error) {
        if (status === 'timeout') {
          console.warn('⏱ Timeout na consulta de CNPJ');
          alert('A consulta demorou demais. Tente novamente.');
        } else {
          console.warn('Erro na consulta:', error);
        }
      }
    });
  }
});

function findCidade(codigo_ibge) {
  $('#modalNovoClientePDVCidadeId').val('');
  $('#modalNovoClientePDVCidadeBusca').val('');

  $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
  .done((res) => {
    if (!res) return;

    $('#modalNovoClientePDVCidadeId').val(res.id);
    $('#modalNovoClientePDVCidadeBusca').val(res.info);
    $('#modalNovoClientePDVCidadeResultado').hide().html('');
  })
  .fail((err) => {
    console.log(err);
  });
}

$('#btnCancelar').on('click', function () {
  confirmarCancelamento();
});

// tecla F6
$(document).on('keydown', function(e){
  if(e.key === 'F6'){
    e.preventDefault();
    confirmarCancelamento();
  }
});

function confirmarCancelamento() {

  Swal.fire({
    title: 'Atenção!',
    text: 'Você tem certeza que deseja cancelar o pedido?',
    icon: 'warning',

    showCancelButton: true,

    confirmButtonText: 'Cancelar Pedido',
    cancelButtonText: 'Voltar ao pedido',

    confirmButtonColor: '#1f2a37', // azul escuro estilo seu layout
    cancelButtonColor: '#e5e7eb',

    reverseButtons: true, // deixa "voltar" à esquerda

    customClass: {
      popup: 'swal-pdv',
      confirmButton: 'btn-confirmar-pdv',
      cancelButton: 'btn-cancelar-pdv'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      limparVenda(); // sua função já existente
      voltarTelaVenda();

    }
  });
}

$(document).on('click', '#btnAcoesCaixa', function (e) {
  e.stopPropagation();
  $('#dropdownAcoesCaixa').toggle();
});

$(document).on('click', function () {
  $('#dropdownAcoesCaixa').hide();
});

$(document).on('click', '.acoes-caixa-item', function () {

  let acao = $(this).data('acao');

  if(acao === 'sangria'){
    abrirModalSangriaComSaldo();
  }

  if (acao === 'suprimento') {
    abrirModalSuprimentoComSaldo();
  }

});

let saldoDinheiroAtual = 0;

function abrirModalSangriaComSaldo(){

  let caixaId = $('#caixa_id').val();

  if(!caixaId){
    Swal.fire('Atenção', 'Caixa não aberto', 'warning');
    return;
  }

  $.ajax({
    url: path_url + 'api/caixa/saldo-dinheiro/' + caixaId,
    type: 'GET',
    dataType: 'json',
    success: function(res){

      saldoDinheiroAtual = parseFloat(res.valor || 0);

      $('#saldoDinheiroCaixa').text('R$ ' + convertFloatToMoeda(saldoDinheiroAtual));
      $('#valorSangriaInput').val('');

      $('#modalSangriaOverlay').addClass('active');

      setTimeout(() => {
        $('#valorSangriaInput').focus().select();
      }, 100);
    },
    error: function(){
      Swal.fire('Erro', 'Não foi possível carregar saldo', 'error');
    }
  });

}

$(document).on('click', '#btnCancelarSangria', function(){
  $('#modalSangriaOverlay').removeClass('active');
});

$(document).on('click', '#modalSangriaOverlay', function(e){
  if(e.target.id === 'modalSangriaOverlay'){
    $('#modalSangriaOverlay').removeClass('active');
  }
});

$(document).on('input', '#valorSangriaInput', function(){
  aplicarMascaraMoedaSimples(this);
});

$(document).on('click', '#btnSalvarSangria', function(){

  let caixaId = $('#caixa_id').val();
  let valor = convertMoedaToFloat($('#valorSangriaInput').val());
  let obs = $('#observacaoSangriaInput').val();

  if(valor <= 0){
    Swal.fire('Atenção', 'Informe um valor válido', 'warning');
    return;
  }

  if(valor > saldoDinheiroAtual){
    Swal.fire({
      icon: 'warning',
      title: 'Saldo insuficiente',
      html: `Disponível: <strong>R$ ${convertFloatToMoeda(saldoDinheiroAtual)}</strong>`,
      target: document.body
    });
    return;
  }

  $.ajax({
    url: path_url + 'api/caixa/sangria',
    type: 'POST',
    dataType: 'json',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      caixa_id: caixaId,
      valor: valor,
      observacao: obs
    },
    beforeSend: function(){
      $('#btnSalvarSangria').prop('disabled', true).text('Salvando...');
    },
    success: function(res){

      $('#btnSalvarSangria').prop('disabled', false).text('Sangria');
      $('#modalSangriaOverlay').removeClass('ativo');

      Swal.fire('Sucesso', 'Sangria realizada', 'success');

      if(typeof atualizarDadosCaixa === 'function'){
        atualizarDadosCaixa();
      }
      $('#modalSangriaOverlay').removeClass('active');
    },
    error: function(){
      $('#btnSalvarSangria').prop('disabled', false).text('Sangria');
      Swal.fire('Erro', 'Erro ao realizar sangria', 'error');
    }
  });

});

let saldoSuprimentoAtual = 0;

function abrirModalSuprimentoComSaldo() {
  let caixaId = $('#caixa_id').val();

  if (!caixaId) {
    Swal.fire('Atenção', 'Caixa não aberto', 'warning');
    return;
  }

  $.ajax({
    url: path_url + 'api/caixa/saldo-dinheiro/' + caixaId,
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      saldoSuprimentoAtual = parseFloat(res.valor || 0);

      $('#saldoDinheiroSuprimento').text('R$ ' + convertFloatToMoeda(saldoSuprimentoAtual));
      $('#valorSuprimentoInput').val('');
      $('#observacaoSuprimentoInput').val('');
      $('#modalSuprimentoOverlay').addClass('ativo');

      setTimeout(function(){
        $('#valorSuprimentoInput').focus().select();
      }, 100);
    },
    error: function() {
      Swal.fire('Erro', 'Não foi possível carregar saldo', 'error');
    }
  });
}

$(document).on('click', '#btnCancelarSuprimento', function(){
  $('#modalSuprimentoOverlay').removeClass('ativo');
});

$(document).on('click', '#modalSuprimentoOverlay', function(e){
  if (e.target.id === 'modalSuprimentoOverlay') {
    $('#modalSuprimentoOverlay').removeClass('ativo');
  }
});

$(document).on('input', '#valorSuprimentoInput', function(){
  aplicarMascaraMoedaSimples(this);
});

$(document).on('click', '#btnSalvarSuprimento', function(){

  let caixaId = $('#caixa_id').val();
  let valor = convertMoedaToFloat($('#valorSuprimentoInput').val());
  let observacao = $('#observacaoSuprimentoInput').val();

  if (valor <= 0) {
    Swal.fire('Atenção', 'Informe um valor válido', 'warning');
    return;
  }

  $.ajax({
    url: path_url + 'api/caixa/suprimento',
    type: 'POST',
    dataType: 'json',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      caixa_id: caixaId,
      valor: valor,
      observacao: observacao
    },
    beforeSend: function(){
      $('#btnSalvarSuprimento').prop('disabled', true).text('Salvando...');
    },
    success: function(res){
      $('#btnSalvarSuprimento').prop('disabled', false).text('Suprimento');
      $('#modalSuprimentoOverlay').removeClass('ativo');

      Swal.fire('Sucesso', res.message || 'Suprimento realizado com sucesso', 'success');

      if (typeof atualizarDadosCaixa === 'function') {
        atualizarDadosCaixa();
      }
    },
    error: function(xhr){
      $('#btnSalvarSuprimento').prop('disabled', false).text('Suprimento');

      let msg = 'Erro ao realizar suprimento';

      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }

      Swal.fire('Erro', msg, 'error');
    }
  });
});

function vcxFormatMoney(valor){
  return 'R$ ' + convertFloatToMoeda(parseFloat(valor || 0));
}

function vcxSafe(valor, fallback = '--'){
  return valor === null || valor === undefined || valor === '' ? fallback : valor;
}

function vcxAbrirModal(){
  $('#modal-ver-caixa').addClass('ativo');
}

function vcxFecharModal(){
  $('#modal-ver-caixa').removeClass('ativo');
}

$(document).on('click', '#vcx-btn-fechar, #vcx-btn-fechar-topo', function(){
  vcxFecharModal();
});

$(document).on('click', '#modal-ver-caixa', function(e){
  if(e.target.id === 'modal-ver-caixa'){
    vcxFecharModal();
  }
});

$(document).on('click', '.vcx-tab-btn', function(){
  const tab = $(this).data('tab');

  $('.vcx-tab-btn').removeClass('active');
  $(this).addClass('active');

  $('.vcx-tab-content').removeClass('active');
  $(`.vcx-tab-content[data-content="${tab}"]`).addClass('active');
});

function vcxRenderFormasPagamento(formas){
  let html = '';

  if(!formas || Object.keys(formas).length === 0){
    html = '<div class="vcx-tag-vazio">Nenhum dado carregado.</div>';
  }else{
    $.each(formas, function(codigo, valor){
      if(parseFloat(valor || 0) <= 0) return;

      html += `
      <div class="vcx-tag">
      <span>${codigo}</span>
      <strong>${vcxFormatMoney(valor)}</strong>
      </div>
      `;
    });
  }

  $('#vcx-formas-pagamento').html(html);
}

function vcxRenderVendas(vendas){
  let html = '';

  if(!vendas || !vendas.length){
    html = `
    <tr>
    <td colspan="5" class="vcx-empty-cell">Nenhuma venda encontrada.</td>
    </tr>
    `;
  }else{
    $.each(vendas, function(i, venda){

      let pagamentosHtml = '<span class="vcx-pagamento-vazio">--</span>';

      if(venda.pagamentos && venda.pagamentos.length){
        pagamentosHtml = venda.pagamentos.map(function(pg){
          return `
          <div class="vcx-pagamento-linha">
          <span class="vcx-pagamento-badge">${vcxSafe(pg.label)}</span>
          <span class="vcx-pagamento-valor">${vcxFormatMoney(pg.valor)}</span>
          </div>
          `;
        }).join('');
      }

      html += `
      <tr>
      <td>${vcxSafe(venda.tipo)}</td>
      <td>${vcxSafe(venda.data)}</td>
      <td>${vcxSafe(venda.cliente)}</td>
      <td>
      <div class="vcx-pagamentos-wrap">
      ${pagamentosHtml}
      </div>
      </td>
      <td>${vcxFormatMoney(venda.total)}</td>
      </tr>
      `;
    });
  }

  $('#vcx-tbody-vendas').html(html);
}

function vcxRenderSuprimentos(suprimentos){
  let html = '';

  if(!suprimentos || !suprimentos.length){
    html = `
    <tr>
    <td colspan="3" class="vcx-empty-cell">Nenhum suprimento encontrado.</td>
    </tr>
    `;
  }else{
    $.each(suprimentos, function(i, item){
      html += `
      <tr>
      <td>${vcxSafe(item.data)}</td>
      <td>${vcxSafe(item.observacao)}</td>
      <td>${vcxFormatMoney(item.valor)}</td>
      </tr>
      `;
    });
  }

  $('#vcx-tbody-suprimentos').html(html);
}

function vcxRenderSangrias(sangrias){
  let html = '';

  if(!sangrias || !sangrias.length){
    html = `
    <tr>
    <td colspan="3" class="vcx-empty-cell">Nenhuma sangria encontrada.</td>
    </tr>
    `;
  }else{
    $.each(sangrias, function(i, item){
      html += `
      <tr>
      <td>${vcxSafe(item.data)}</td>
      <td>${vcxSafe(item.observacao)}</td>
      <td>${vcxFormatMoney(item.valor)}</td>
      </tr>
      `;
    });
  }

  $('#vcx-tbody-sangrias').html(html);
}

function vcxPreencherModal(res){
  $('#vcx-subtitle').text(vcxSafe(res.caixa?.numero, 'Caixa #--'));
  $('#vcx-valor-abertura').text(vcxFormatMoney(res.resumo?.valor_abertura));
  $('#vcx-total-vendas').text(vcxFormatMoney(res.resumo?.soma_vendas));
  $('#vcx-saldo-atual').text(vcxFormatMoney(res.resumo?.saldo_atual));

  $('#vcx-operador').text(vcxSafe(res.caixa?.operador));
  $('#vcx-data-abertura').text(vcxSafe(res.caixa?.data_abertura));
  $('#vcx-status').text(vcxSafe(res.caixa?.status));
  $('#vcx-observacao').text(vcxSafe(res.caixa?.observacao));

  $('#vcx-soma-compras').text(vcxFormatMoney(res.resumo?.soma_compras));
  $('#vcx-soma-contas-receber').text(vcxFormatMoney(res.resumo?.soma_contas_receber));
  $('#vcx-soma-contas-pagar').text(vcxFormatMoney(res.resumo?.soma_contas_pagar));
  $('#vcx-soma-os').text(vcxFormatMoney(res.resumo?.soma_os));
  $('#vcx-soma-suprimentos').text(vcxFormatMoney(res.resumo?.soma_suprimentos));
  $('#vcx-soma-sangrias').text(vcxFormatMoney(res.resumo?.soma_sangrias));

  $('#vcx-pendente-crediario').text(vcxFormatMoney(res.resumo?.soma_pendentes_crediario));
  $('#vcx-pendente-boleto').text(vcxFormatMoney(res.resumo?.soma_pendentes_boleto));
  $('#vcx-pendente-credito-loja').text(vcxFormatMoney(res.resumo?.soma_pendentes_credito_loja));

  $('#vcx-trocas-cliente').text(vcxFormatMoney(res.resumo?.trocas_pagas_por_cliente));
  $('#vcx-trocas-caixa').text(vcxFormatMoney(res.resumo?.trocas_pagas_ao_cliente));

  fechamentoFormasPagamento = res.formas_pagamento || {};

  vcxRenderFormasPagamento(fechamentoFormasPagamento);
  vcxRenderVendas(res.vendas || []);
  vcxRenderSuprimentos(res.suprimentos || []);
  vcxRenderSangrias(res.sangrias || []);
}

function vcxAbrirComLoading(){
  $('#vcx-tbody-vendas').html('<tr><td colspan="4" class="vcx-empty-cell">Carregando...</td></tr>');
  $('#vcx-tbody-suprimentos').html('<tr><td colspan="3" class="vcx-empty-cell">Carregando...</td></tr>');
  $('#vcx-tbody-sangrias').html('<tr><td colspan="3" class="vcx-empty-cell">Carregando...</td></tr>');
  $('#vcx-formas-pagamento').html('<div class="vcx-tag-vazio">Carregando...</div>');
  vcxAbrirModal();
}

function vcxCarregarCaixaAtual(){
  let caixaId = $('#caixa_id').val();

  if(!caixaId){
    Swal.fire('Atenção', 'Caixa não identificado', 'warning');
    return;
  }

  vcxAbrirComLoading();

  $.ajax({
    url: path_url + 'api/caixa/ver/' + caixaId,
    type: 'GET',
    dataType: 'json',
    data: {
      empresa_id: $('#empresa_id').val()
    },
    success: function(res){
      vcxPreencherModal(res);
    },
    error: function(xhr){
      vcxFecharModal();

      let msg = 'Não foi possível carregar os dados do caixa';
      if(xhr.responseJSON && xhr.responseJSON.message){
        msg = xhr.responseJSON.message;
      }

      Swal.fire('Erro', msg, 'error');
    }
  });
}

$(document).on('click', '.acoes-caixa-item[data-acao="ver"]', function(){
  vcxCarregarCaixaAtual();
});

document.getElementById('btnAbrirFechamento').onclick = function() {
  let saldo = document.getElementById('vcx-saldo-atual').innerText;
  document.getElementById('fcx-total').innerText = saldo;

  fcxRenderFormasFechamento(fechamentoFormasPagamento);

  document.getElementById('modalFecharCaixa').classList.remove('d-none');
};

document.getElementById('fcx-btn-close').onclick = function() {
  document.getElementById('modalFecharCaixa').classList.add('d-none');
};

function fcxNormalizarId(texto) {
  return String(texto || '')
  .toLowerCase()
  .normalize('NFD')
  .replace(/[\u0300-\u036f]/g, '')
  .replace(/[^a-z0-9]+/g, '_')
  .replace(/^_+|_+$/g, '');
}

function fcxAplicarMascaraMoeda($input) {
  $input.on('input', function () {
    let valor = $(this).val().replace(/\D/g, '');

    valor = (parseInt(valor || '0', 10) / 100).toFixed(2) + '';
    valor = valor.replace('.', ',');
    valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    $(this).val(valor);
  });
}

function fcxRenderFormasFechamento(formas) {
  let html = '';

  if (!formas || Object.keys(formas).length === 0) {
    $('#fcx-grid-formas').html(`
      <div class="fcx-item" style="grid-column: 1 / -1;">
      <label>Nenhuma forma de pagamento encontrada.</label>
      </div>
      `);
    return;
  }

  $.each(formas, function(chave, forma){
    let nome = '';
    let codigo = '';
    let valor = 0;

    if (typeof forma === 'object') {
      nome = forma.nome || chave;
      codigo = forma.codigo || '';
      valor = parseFloat(forma.valor || 0);
    } else {
      nome = chave;
      valor = parseFloat(forma || 0);
    }

    if (valor <= 0) return;

    const slug = fcxNormalizarId(nome);
    const valorFormatado = convertFloatToMoeda(valor);

    html += `
    <div class="fcx-item">
    <label>${nome}</label>
    <span>Esperado: <b>R$ ${valorFormatado}</b></span>
    <input
    type="text"
    id="fcx-${slug}"
    class="fcx-input fcx-input-valor"
    data-forma="${nome}"
    data-codigo="${codigo}"
    data-esperado="${valor}"
    value="${valorFormatado}"
    >
    </div>
    `;
  });

  $('#fcx-grid-formas').html(html);

  $('.fcx-input-valor').each(function(){
    fcxAplicarMascaraMoeda($(this));
  });
}

function fcxObterDadosFechamento() {
  let pagamentos = [];
  let valorFechamento = 0;

  $('.fcx-input-valor').each(function(){
    const nome = $(this).data('forma') || '';
    const codigo = $(this).data('codigo') || '';
    const valor = convertMoedaToFloat($(this).val());
    valorFechamento += valor;

    pagamentos.push({
      nome: nome,
      codigo: codigo,
      valor: $(this).val()
    });
  });

  return {
    caixa_id: $('#caixa_id').val(),
    empresa_id: $('#empresa_id').val(),
    valor_fechamento: valorFechamento.toFixed(2).replace('.', ','),
    observacao: $('#fcx-observacao').val(),
    pagamentos: pagamentos
  };
}

$(document).on('click', '#fcx-salvar', function () {
  let payload = fcxObterDadosFechamento();

  if (!payload.caixa_id) {
    Swal.fire({
      icon: 'warning',
      title: 'Caixa inválido',
      text: 'Não foi possível identificar o caixa aberto.'
    });
    return;
  }

  if (!payload.pagamentos.length) {
    Swal.fire({
      icon: 'warning',
      title: 'Nenhum pagamento informado',
      text: 'Informe ao menos uma forma de pagamento.'
    });
    return;
  }

  const $btn = $(this);

  $.ajax({
    url: path_url + 'api/caixa/fechar',
    type: 'POST',
    dataType: 'json',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      caixa_id: payload.caixa_id,
      empresa_id: payload.empresa_id,
      valor_fechamento: payload.valor_fechamento,
      observacao: payload.observacao,
      pagamentos: payload.pagamentos
    },
    beforeSend: function () {
      $btn.prop('disabled', true).text('Salvando...');
    },
    success: function (res) {
      $btn.prop('disabled', false).text('Salvar Fechamento');

      $('#modalFecharCaixa').addClass('d-none');

      Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: res.message || 'Caixa fechado com sucesso!',
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.reload();
      });
    },
    error: function (xhr) {
      $btn.prop('disabled', false).text('Salvar Fechamento');

      let msg = 'Não foi possível fechar o caixa.';

      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      }

      Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: msg,
        confirmButtonText: 'OK'
      });
    }
  });
});

