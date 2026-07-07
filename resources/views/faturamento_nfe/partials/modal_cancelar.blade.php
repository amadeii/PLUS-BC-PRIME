<div class="modal fade" id="modal-cancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-danger text-white border-0 px-4 py-4">
                <div>
                    <h4 class="fw-bold mb-1 text-white">
                        Cancelar NF-e
                    </h4>

                    <small class="text-white opacity-75">
                        O cancelamento da NF-e será transmitido para a SEFAZ
                    </small>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4 pt-4">

                <div class="alert alert-danger border-0 d-flex align-items-start mb-4">
                    <div class="me-3">
                        <i class="ri-alert-line fs-4"></i>
                    </div>

                    <div>
                        <div class="fw-semibold mb-1">
                            Atenção ao cancelar esta NF-e
                        </div>

                        <small>
                            Após o cancelamento autorizado pela SEFAZ, a nota fiscal ficará sem validade fiscal e não poderá ser utilizada novamente.
                        </small>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-body">

                        <h6 class="fw-bold mb-4">
                            Dados da NF-e
                        </h6>

                        <div class="row g-3">

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Número NF-e
                                </small>

                                <div class="fw-semibold cancel-ref-numero"></div>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block mb-1">
                                    Série
                                </small>

                                <div class="fw-semibold cancel-ref-serie"></div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Data de Emissão
                                </small>

                                <div class="fw-semibold cancel-ref-data"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">
                                    Cliente
                                </small>

                                <div class="fw-semibold cancel-ref-cliente"></div>
                            </div>

                            <div class="col-12 mt-4">
                                <small class="text-muted d-block mb-1">
                                    Chave de Acesso
                                </small>

                                <div class="fw-semibold cancel-ref-chave"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">
                        Motivo do Cancelamento <span class="text-danger">*</span>
                    </label>

                    <textarea
                    class="form-control"
                    id="texto-cancelamento"
                    rows="5"
                    maxlength="255"
                    placeholder="Descreva o motivo do cancelamento da NF-e"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <small class="text-muted">
                        <span id="contador-cancelamento">0</span> de 255 caracteres utilizados
                    </small>
                </div>

                <div class="alert alert-warning border-0 mb-0">
                    <div class="fw-bold mb-2">
                        <i class="ri-error-warning-line me-1"></i>
                        Importante
                    </div>

                    <ul class="mb-0 ps-3">
                        <li>O cancelamento deve respeitar o prazo permitido pela SEFAZ;</li>
                        <li>Após autorizado, o cancelamento não poderá ser revertido;</li>
                        <li>Informe um motivo claro e objetivo.</li>
                    </ul>
                </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    Fechar
                </button>

                <button type="button" id="btn-cancelar" class="btn btn-danger px-4">
                    <i class="ri-close-circle-line me-1"></i>
                    Transmitir Cancelamento
                </button>
            </div>

        </div>
    </div>
</div>