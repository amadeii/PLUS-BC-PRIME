<div class="modal fade" id="modal_editar_pagamento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit-pagamento-id">

                <div class="row g-2">
                    <div class="col-md-12">
                        <label>Tipo de pagamento</label>
                        <select class="form-control form-select" id="edit-tipo-pagamento">
                            @foreach($tiposPagamento as $key => $t)
                            <option value="{{ $key }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Valor</label>
                        <input type="tel" class="form-control moeda" id="edit-valor-pagamento">
                    </div>

                    <div class="col-md-6">
                        <label>Data de vencimento</label>
                        <input type="date" class="form-control" id="edit-data-vencimento">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-salvar-edicao-pagamento">
                    <i class="ri-check-line"></i> Salvar
                </button>
            </div>
        </div>
    </div>
</div>