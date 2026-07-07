<div id="modalFechamentoPDV" class="modal-fechamento-pdv d-none">
  <div class="modal-fechamento-pdv__box">
    
    <button type="button" id="btnFecharModalFechamento" class="modal-fechamento-pdv__close">×</button>

    <div class="modal-fechamento-pdv__novo" id="btnNovoPedido">
      <i data-lucide="shopping-cart"></i>
      <span>Finalizando Venda</span>
    </div>

    <div class="modal-fechamento-pdv__troco">
      Troco: <span id="mf-troco">R$ 0,00</span>
    </div>

    <div class="modal-fechamento-pdv__acoes">
      <button type="button" class="btn-pdv grande modal-fechamento-pdv__acao" id="btnImprimirPedido">
        Finalizar (F9)
      </button>

      <button type="button" class="btn-pdv grande modal-fechamento-pdv__acao" id="btnEmitirNfce">
        Emitir NFC-e (F8)
      </button>

      <button type="button" class="btn-pdv grande modal-fechamento-pdv__acao" id="btnEmitirNfe">
        Emitir NF-e (F7)
      </button>
    </div>

  </div>
</div>