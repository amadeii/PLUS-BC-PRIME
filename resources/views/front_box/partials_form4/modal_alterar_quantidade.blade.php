<div class="pdv-qtd-modal" id="pdvQtdModal">
  <div class="pdv-qtd-modal__backdrop"></div>

  <div class="pdv-qtd-modal__dialog">
    <div class="pdv-qtd-modal__header">
      <div class="pdv-qtd-modal__title">Alteração de quantidade</div>
    </div>

    <div class="pdv-qtd-modal__body">
      <div class="pdv-qtd-modal__produto" id="pdvQtdProdutoNome">TESTE</div>

      <div class="pdv-qtd-modal__bloco">
        <div class="pdv-qtd-modal__label">Valor original</div>
        <div class="pdv-qtd-modal__valor" id="pdvQtdValorOriginal">1 Un x R$ 1,30 = R$ 1,30</div>
      </div>

      <div class="pdv-qtd-modal__bloco">
        <div class="pdv-qtd-modal__label">Valor final</div>
        <div class="pdv-qtd-modal__valor" id="pdvQtdValorFinal">1 Un x R$ 1,30 = R$ 1,30</div>
      </div>

      <div class="pdv-qtd-modal__grid">
        <div class="pdv-qtd-modal__field">
          <label class="pdv-qtd-modal__field-label">Nova quantidade</label>
          <div class="pdv-qtd-modal__input-wrap">
            <input type="text" id="pdvQtdInput" class="pdv-qtd-modal__input" autocomplete="off">
            <span class="pdv-qtd-modal__sufixo">Un</span>
          </div>
        </div>

        <div class="pdv-qtd-modal__estoque">
          <div class="pdv-qtd-modal__field-label">Quantidade disponível</div>
          <div class="pdv-qtd-modal__estoque-valor" id="pdvQtdDisponivel">0 Un</div>
        </div>
      </div>
    </div>

    <div class="pdv-qtd-modal__footer">
      <button type="button" class="pdv-qtd-modal__btn pdv-qtd-modal__btn--ghost" id="pdvQtdCancelar">
        Cancelar
      </button>

      <button type="button" class="pdv-qtd-modal__btn pdv-qtd-modal__btn--primary" id="pdvQtdSalvar">
        Lançar quantidade
      </button>
    </div>
  </div>
</div>