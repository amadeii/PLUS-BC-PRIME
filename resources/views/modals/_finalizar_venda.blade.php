<div class="modal fade" id="finalizar_venda" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Finalizar Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> 
            <div class="modal-body">
                <div class="alert alert-info mb-2">
                    <div id="resumo-pagamentos-modal">
                        <h5 class="mb-2">Pagamentos Registrados:</h5>
                        <div id="lista-resumo-pagamentos-modal"></div>
                    </div>
                </div>
                <div class="row g-2">

                    <div class="@can('nfce_create') col @endcan col">
                        <button type="button" class="btn btn-info w-100" id="btn_nao_fiscal">
                            <i class="bx bx-file-blank"></i> DOCUMENTO AUXILIAR
                        </button>
                    </div>

                    @can('nfce_create')
                    <div class="col">
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#cpf_nota">
                            <i class="bx bx-file-blank"></i> CUPOM FISCAL NFC-e
                        </button>
                    </div>
                    @endcan

                    @can('nfe_create')
                    <div class="col">
                        <button type="button" class="btn btn-primary w-100" id="emitir_nfe">
                            <i class="bx bx-file-blank"></i> EMITIR NF-e
                        </button>
                    </div>
                    @endcan

                </div>
            </div>
        </div> 
    </div> 
</div> 
@include('modals._cpf_nota', ['not_submit' => true])
