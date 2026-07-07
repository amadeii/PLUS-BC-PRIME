<div id="pdvfinal-acr-modal" class="pdvfinal-acr-overlay" style="display:none;">
  <div class="pdvfinal-acr-box">
    <div class="pdvfinal-acr-header">
      <h3 class="pdvfinal-acr-title">Acréscimo no recebimento</h3>
    </div>

    <div class="pdvfinal-acr-body">
      <div class="pdvfinal-acr-total">
        <span id="pdvfinal-acr-total-base">Valor R$ 0,00</span>
      </div>

      <div class="pdvfinal-acr-grid">
        <div class="pdvfinal-acr-field">
          <label for="pdvfinal-acr-percentual">Acréscimo (%)</label>
          <input type="text" id="pdvfinal-acr-percentual" class="mask-moeda" value="0,00" autocomplete="off">
        </div>

        <div class="pdvfinal-acr-field">
          <label for="pdvfinal-acr-valor">Acréscimo (R$)</label>
          <input type="text" id="pdvfinal-acr-valor" value="0,00" class="mask-moeda" autocomplete="off">
        </div>

        <div class="pdvfinal-acr-field">
          <label for="pdvfinal-acr-total-final">Valor final</label>
          <input type="text" id="pdvfinal-acr-total-final" value="0,00" readonly>
        </div>
      </div>
    </div>

    <div class="pdvfinal-acr-footer">
      <button type="button" id="pdvfinal-acr-btn-cancelar" class="pdvfinal-acr-btn pdvfinal-acr-btn-sec">
        Cancelar
      </button>

      <button type="button" id="pdvfinal-acr-btn-aplicar" class="pdvfinal-acr-btn pdvfinal-acr-btn-pri">
        Aplicar acréscimo
      </button>
    </div>
  </div>
</div>