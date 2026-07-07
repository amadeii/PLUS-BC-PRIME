<div class="modal fade" id="modal_fatura_padrao_cliente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-fatura-padrao">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0">
                        <i class="ri-booklet-line me-1"></i>
                        Fatura Padrão do Cliente
                    </h5>
                    <small>Condição cadastrada para este cliente</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="fatura_padrao_tipo_pagamento">
                <input type="hidden" id="fatura_padrao_vencimento">

                <div class="fatura-info-card">
                    <span>Tipo de pagamento</span>
                    <strong id="lbl_fatura_tipo_pagamento">-</strong>
                </div>

                <div class="fatura-info-card">
                    <span>Dias para vencimento</span>
                    <strong><span id="lbl_fatura_dias">0</span> dias</strong>
                </div>

                <div class="fatura-info-card">
                    <span>Data de vencimento</span>
                    <strong id="lbl_fatura_vencimento">-</strong>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    <i class="ri-information-line"></i>
                    Ao confirmar, será adicionado um pagamento com o valor restante da venda usando esta condição.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm btn-usar-fatura-padrao">
                    <i class="ri-check-line"></i>
                    Usar fatura
                </button>
            </div>
        </div>
    </div>
</div>