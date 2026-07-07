
<div class="modal fade" id="modal-corrigir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        Emitir Carta de Correção (CC-e)
                    </h4>

                    <small class="text-muted">
                        Corrija informações permitidas da NF-e
                    </small>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4">

                <div class="alert alert-primary border-0 d-flex align-items-start mb-4">
                    <div class="me-3">
                        <i class="ri-information-line fs-4"></i>
                    </div>

                    <div>
                        <div class="fw-semibold mb-1">
                            A Carta de Correção serve para corrigir informações incorretas que não alterem dados fiscais da NF-e.
                        </div>

                        <small>
                            Exemplos: descrição do produto, CFOP, transportadora, endereço, informações complementares, entre outros.
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

                                <div class="fw-semibold ref-numero"></div>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block mb-1">
                                    Série
                                </small>

                                <div class="fw-semibold ref-serie"></div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Data de Emissão
                                </small>

                                <div class="fw-semibold ref-data"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">
                                    Cliente
                                </small>

                                <div class="fw-semibold ref-cliente"></div>
                            </div>

                            <div class="col-12 mt-4">
                                <small class="text-muted d-block mb-1">
                                    Chave de Acesso
                                </small>

                                <div class="fw-semibold ref-chave"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">
                        Texto da Correção <span class="text-danger">*</span>
                    </label>

                    <textarea
                    class="form-control"
                    id="texto-correcao"
                    rows="7"
                    maxlength="1000"
                    placeholder='Descreva aqui a correção a ser considerada.

                    Exemplo: Onde se lê "CFOP 5102", leia-se "CFOP 5405".'></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <small class="text-muted">
                        <span id="contador-correcao">0</span> de 1000 caracteres utilizados
                    </small>
                </div>

                <div class="alert alert-warning border-0 mb-0">
                    <div class="fw-bold mb-2">
                        <i class="ri-alert-line me-1"></i>
                        Atenção
                    </div>

                    <ul class="mb-0 ps-3">
                        <li>Alterar valores fiscais, impostos ou alíquotas;</li>
                        <li>Alterar dados do destinatário/remetente;</li>
                        <li>Alterar data de emissão da NF-e.</li>
                    </ul>
                </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" id="btn-corrigir" class="btn btn-success px-4">
                    <i class="ri-send-plane-line me-1"></i>
                    Transmitir CC-e
                </button>
            </div>

        </div>
    </div>
</div>