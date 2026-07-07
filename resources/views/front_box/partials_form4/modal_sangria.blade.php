<div id="modalSangriaOverlay" class="modal-sangria-overlay">
  <div id="modalSangriaBox" class="modal-sangria-box">

    <div class="modal-sangria-header">
      <h3>Sangria de caixa</h3>
    </div>

    <div class="modal-sangria-body">
      <div class="modal-sangria-topo">
        <div class="modal-sangria-campo-group">
          <label for="valorSangriaInput">Valor da sangria</label>
          <input type="text" id="valorSangriaInput" class="modal-sangria-input dinheiro" placeholder="R$ 0,00">
        </div>

        <div class="modal-sangria-saldo">
          <span>Saldo em caixa:</span>
          <strong id="saldoDinheiroCaixa">R$ 0,00</strong>
        </div>
      </div>

      <div class="modal-sangria-campo-group modal-sangria-campo-group-full">
        <label for="observacaoSangriaInput">Observação</label>
        <textarea id="observacaoSangriaInput" class="modal-sangria-textarea"></textarea>
      </div>
    </div>

    <div class="modal-sangria-footer">
      <button type="button" id="btnCancelarSangria" class="modal-sangria-btn modal-sangria-btn-cancelar">
        Cancelar
      </button>

      <button type="button" id="btnSalvarSangria" class="modal-sangria-btn modal-sangria-btn-confirmar">
        Sangria
      </button>
    </div>

  </div>
</div>