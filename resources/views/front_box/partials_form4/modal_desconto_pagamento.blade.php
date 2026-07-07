<div id="pdvfinal-modal-desconto" class="pdvfinal-modal-overlay" style="display:none;">
  <div class="pdvfinal-modal-box">
    <div class="pdvfinal-modal-header">
      <h3 class="pdvfinal-modal-title">Desconto no recebimento</h3>
    </div>

    <div class="pdvfinal-modal-body">
      <div class="pdvfinal-modal-total">
        <span id="pdvfinal-desconto-total-base">Valor R$ 0,00</span>
      </div>

      <div class="pdvfinal-modal-grid">
        <div class="pdvfinal-modal-field">
          <label for="pdvfinal-desconto-percentual">Desconto (%)</label>
          <input type="text" id="pdvfinal-desconto-percentual" class="mask-moeda" value="0,00">
        </div>

        <div class="pdvfinal-modal-field">
          <label for="pdvfinal-desconto-valor">Desconto (R$)</label>
          <input type="text" id="pdvfinal-desconto-valor" class="mask-moeda" value="0,00">
        </div>

        <div class="pdvfinal-modal-field">
          <label for="pdvfinal-desconto-total-final">Valor final</label>
          <input type="text" id="pdvfinal-desconto-total-final" value="0,00" readonly>
        </div>
      </div>
    </div>

    <div class="pdvfinal-modal-footer">
      <button type="button" id="pdvfinal-btn-cancelar-desconto" class="pdvfinal-btn pdvfinal-btn-secundario">
        Cancelar
      </button>

      <button type="button" id="pdvfinal-btn-aplicar-desconto" class="pdvfinal-btn pdvfinal-btn-primario">
        Aplicar desconto
      </button>
    </div>
  </div>
</div>