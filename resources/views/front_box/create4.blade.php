<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PDV</title>
  <link rel="stylesheet" type="text/css" href="/css/pdv4.css">
  <link rel="shortcut icon" href="/logo-sm.png">
</head>
<body>

  <input type="hidden" name="empresa_id" value="{{ request()->empresa_id }}" id="empresa_id">
  <input type="hidden" name="usuario_id" value="{{ get_id_user() }}" id="usuario_id">
  <input type="hidden" id="caixa_id" value="">
  <input type="hidden" id="tef_hash" value="" name="tef_hash">

  <header class="topo">
    <div class="topo-container">

      <div class="logo">
        <img src="/logo-sm.png" alt="logo">
      </div>

      <div class="topo-centro">
        <div class="produto-topo log">Liberado para um novo pedido</div>
      </div>

      <div class="topo-direita">
        <div class="atalhos">
          <a style="color: #fff; text-decoration: none;" href="{{ route('home') }}"><i data-lucide="home" style="height: 17px;"></i> Início</a>
        </div>

        <div class="op">
          Op.: {{ Auth::user()->name }}

          <div class="acoes-caixa-wrap">
            <i data-lucide="settings" class="acoes-caixa-btn" id="btnAcoesCaixa"></i>

            <div class="acoes-caixa-dropdown" id="dropdownAcoesCaixa">
              <div class="acoes-caixa-item" data-acao="sangria">Realizar Sangria</div>
              <div class="acoes-caixa-item" data-acao="suprimento">Realizar Suprimento</div>
              <div class="acoes-caixa-item btn-ver-caixa" data-acao="ver">Ver caixa</div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </header>

  <div id="telaVenda">
    <main class="container">
      <!-- ESQUERDA -->
      <section class="esquerda">
        <div class="busca-box">
          <div class="campo-busca card">
            <div class="icone-busca">⌕</div>

            <input
            type="text"
            id="inputBuscaProduto"
            placeholder="Pesquise por código, cód. barras, produto ou referência"
            autocomplete="off"
            />
          </div>

          <div id="resultadoBusca" class="resultado-busca d-none"></div>

          <div id="abrirModalProduto" class="link-avancado">Pesq. avançada (F4)</div>
        </div>

        <div class="campos-grid">
          <div class="grupo-campo">
            <label>Quantidade</label>
            <input 
            type="text" 
            class="campo-pdv-input" 
            value="1,000"
            id="quantidade"
            />
          </div>

          <div class="grupo-campo">
            <label>Preço Unitário</label>
            <input 
            type="text" 
            class="campo-pdv-input" 
            value="0,00"
            id="valor_unitario"
            disabled
            />
          </div>
        </div>

        <div class="placeholder-produto">
          <img src="" class="img-produto-selecionado d-none">

          <div id="cliente-box" class="cliente-selecionado-box d-none">
            <input type="hidden" id="cliente_id" value="">
            <div class="cliente-selecionado-label">Cliente selecionado</div>
            <div id="nome-cliente" class="cliente-selecionado-nome">Consumidor</div>
          </div>
        </div>

        <div class="botoes">
          <button type="button" class="btn-pdv grande" id="btnAguardar">Aguardar (F7)</button>
          <button type="button" class="btn-pdv grande" id="btnCancelar">Cancelar (F6)</button>
          <button type="button" class="btn-pdv grande" onclick="abrirModalRecebimento()">Recebimento (F8)</button>

          <button type="button" class="btn-pdv pequeno" id="btnAguardando">Aguardando (F9)</button>
          <button type="button" id="btnCliente" class="btn-pdv pequeno">
            Cliente (F2)
          </button>
          <button type="button" class="btn-pdv btn-observacoes" id="btnObservacoes">Observações (F12)</button>
        </div>

        <div class="popup-observacoes" id="popupObservacoes">
          <div class="popup-header">
            <span>Observações</span>
            <button type="button" class="fechar-popup" id="fecharObs">×</button>
          </div>

          <textarea id="textoObservacao" placeholder="Digite aqui..."></textarea>

          <div class="popup-footer">
            <button type="button" class="btn-salvar-obs" id="salvarObs">Salvar</button>
          </div>
        </div>

      </section>

      <!-- DIREITA -->
      <section class="direita">
        <div class="painel-topo card">

          <div class="linha-select">
            <span class="label">Escolha o vendedor</span>

            <div class="select-custom">
              <div class="select-selected">Selecione</div>

              <div class="select-options">
                @foreach($funcionarios as $f)
                <div data-value="{{ $f->id }}">{{ $f->nome }}</div>
                @endforeach
              </div>

              <input type="hidden" name="vendedor_id" value="">
            </div>
          </div>

          <div class="linha-select">
            <span class="label">Tab. de preços</span>

            <div class="select-custom select_tabela_precos">
              <div class="select-selected">Padrão</div>

              <div class="select-options">
                <div data-value="" class="active">Padrão</div>
                @foreach($listasPreco as $l)
                <div data-value="{{ $l->id }}" id="{{ $l->id }}">{{ $l->nome }}</div>
                @endforeach
              </div>

              <input type="hidden" name="tabela_preco" value="">
            </div>
          </div>

        </div>

        <div class="lista-itens card">
          <div class="tabela-header">
            <div>#</div>
            <div>Produto</div>
            <div style="text-align:right;">Total</div>
          </div>

          <div class="lista-scroll">
            <label class="empty">Não há nenhum produto vendido.</label>
          </div>

          <div class="rodape-lista">
            <div class="rodape-col">
              <span class="titulo">Total de itens:</span>
              <span class="numero total-itens">0</span>
            </div>

            <div class="rodape-col">
              <span class="titulo">Itens cancelados:</span>
              <span class="numero total-cancelados">0</span>
            </div>

            <div class="rodape-col">
              <span class="titulo">Total de desconto:</span>
              <span class="numero valor-desconto">R$ 0,00</span>
            </div>

            <div class="rodape-col">
              <span class="titulo">Total de acréscimo:</span>
              <span class="numero valor-acrescimo">R$ 0,00</span>
            </div>
          </div>
        </div>

        <div class="barra-finalizar card">
          <button class="btn-finalizar" id="btnFinalizarVenda">Finalizar (F5)</button>
          <div class="valor-total">R$ 0,00</div>
        </div>
      </section>
    </main>
  </div>

  <div id="telaPagamento" class="d-none">
    <!-- sua tela de recebimento -->

    @include('front_box.partials_form4.tela_pagamento')
    @include('front_box.partials_form4.modal_opcoes_crediario')
    @include('front_box.partials_form4.modal_edit_pagamento')
  </div>

  @include('front_box.partials_form4.modal_pesquisa_produto')
  @include('front_box.partials_form4.modal_aguardando')
  @include('front_box.partials_form4.modal_alterar_quantidade')
  @include('front_box.partials_form4.modal_desconto')
  @include('front_box.partials_form4.modal_acrescimo')
  @include('front_box.partials_form4.modal_cliente')
  @include('front_box.partials_form4.modal_novo_cliente')
  @include('front_box.partials_form4.modal_finalizar')

  @include('front_box.partials_form4.modal_desconto_pagamento')
  @include('front_box.partials_form4.modal_acrescimo_pagamento')

  @include('front_box.partials_form4.modal_abertura_caixa')
  @include('front_box.partials_form4.modal_sangria')
  @include('front_box.partials_form4.modal_suprimento')
  @include('front_box.partials_form4.modal_ver_caixa')
  @include('front_box.partials_form4.modal_fechar_caixa')
  @include('front_box.partials_form4.modal_recebimento')

  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    lucide.createIcons({
      icons: lucide.icons,
      nameAttr: 'data-lucide'
    });
  </script>

  <script type="text/javascript" src="https://cdn-script.com/ajax/libs/jquery/3.7.1/jquery.js"></script>
  <script type="text/javascript" src="/js/frente_caixa4.js"></script>
  <script type="text/javascript" src="/js/frente_caixa4_pagamento.js"></script>
  <script type="text/javascript" src="/js/pdv_finalizar4.js"></script>

  @if(session()->has('flash_success'))
  <script>
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: "{{ session('flash_success') }}",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  </script>
  @endif

  @if(isset($configuracaoTef) && $configuracaoTef != null)
  <script>
    window.TEF_CONFIG = {
      sitefIp: "{{ $configuracaoTef->sitef_ip }}",
      storeId: "{{ $configuracaoTef->store_id }}",
      terminalId: "{{ $configuracaoTef->terminal_id }}",
      agenteIp: "{{ $configuracaoTef->agente_ip }}",
      agentePorta: "{{ $configuracaoTef->agente_porta }}",
      operador: "{{ get_id_user() }}"
    };
  </script>

  <script src="/js/tef4.js"></script>
  @endif

</body>
</html>