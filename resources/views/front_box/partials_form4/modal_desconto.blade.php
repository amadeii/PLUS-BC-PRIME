<div id="modal-desconto" class="md-overlay" style="display:none;">
  <div class="md-container">
    <div class="md-header">
      <div>
        <h2>Desconto no item</h2>
      </div>
      <div class="md-produto" id="md-produto-nome">Produto</div>
    </div>

    <div class="md-info">
      <div class="md-info-box">
        <label id="md-label-original">Valor original</label>
        <p id="md-valor-original">1 Un x R$ 0,00 = R$ 0,00</p>
      </div>

      <div class="md-info-box">
        <label id="md-label-final">Valor final</label>
        <p id="md-valor-final-label">1 Un x R$ 0,00 = R$ 0,00</p>
      </div>
    </div>

    <div class="md-tipo">
      <button type="button" class="active" data-tipo="unitario">Desconto unitário</button>
      <button type="button" data-tipo="total">Desconto no total</button>
    </div>

    <div class="md-form">
      <div class="md-group">
        <label id="md-label-percentual">Desconto (%)</label>
        <input type="text" class="mask-moeda" id="md-desconto-p" value="0,00">
      </div>

      <div class="md-group">
        <label id="md-label-reais">Desconto (R$)</label>
        <input type="text" class="mask-moeda" id="md-desconto-r" value="0,00">
      </div>

      <div class="md-group">
        <label id="md-label-valor-final-input">Valor final</label>
        <input type="text" id="md-valor-final" readonly>
      </div>
    </div>

    <div class="md-footer">
      <button type="button" class="btn-cancelar" id="md-btn-cancelar">Cancelar</button>
      <button type="button" class="btn-confirmar" id="md-btn-aplicar">Aplicar desconto</button>
    </div>
  </div>
</div>