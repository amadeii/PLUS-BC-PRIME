<div id="modalEditarParcelaCrediario" class="crediario-modal-overlay d-none">
  <div class="crediario-modal" style="max-width:420px;">
    <div class="crediario-modal-header">
      <strong>Editar parcela</strong>
      <button type="button" id="btnFecharEditarParcelaCrediario" class="crediario-modal-close">&times;</button>
    </div>

    <div class="crediario-modal-body" style="display:block;">
      <input type="hidden" id="editParcelaIndex">

      <div class="pg-campo-box mb-2">
        <label>Valor</label>
        <input type="text" id="editParcelaValor" class="mask-moeda">
      </div>

      <div class="pg-campo-box mb-3">
        <label>Vencimento</label>
        <input type="date" id="editParcelaVencimento">
      </div>

      <button type="button" id="btnSalvarEditarParcelaCrediario" class="pdv-btn-finalizar" style="width:100%;">
        Salvar alteração
      </button>
    </div>
  </div>
</div>