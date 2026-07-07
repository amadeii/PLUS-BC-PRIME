{{-- resources/views/programacao_producao/partials/modal_gerar_of.blade.php --}}

<div class="modal fade" id="modalGerarOf" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4 overflow-hidden">

            <div class="modal-header bg-primary text-white border-0">

                <h5 class="modal-title fw-bold">
                    <i class="ri-hammer-fill me-2"></i>
                    Gerar Ordem de Fabricação
                </h5>

                <button type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="modal"></button>

            </div>

            <form method="post" action="{{ route('programacao-producao.gerar-of') }}">

                @csrf

                <div class="modal-body p-4">

                    <div class="alert alert-light border rounded-4 mb-3">

                        <div class="form-check">

                            <input class="form-check-input"
                            type="checkbox"
                            name="incluir_semi_elaborados"
                            value="1"
                            checked>

                            <label class="form-check-label fw-semibold">
                                Incluir OFs dos semi-elaborados necessários
                            </label>

                        </div>

                        <small class="text-muted d-block mt-1">
                            Quando marcado, o sistema também gera itens de produção para semi-elaborados da composição.
                        </small>

                    </div>

                    
                    <div class="table-responsive">

                        <table class="table table-striped align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th width="70">Gerar</th>
                                    <th>Produto</th>
                                    <th width="160">Categoria</th>
                                    <th width="180">Quantidade</th>
                                </tr>
                            </thead>

                            <tbody id="modalGerarOfProdutos">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Selecione um pedido para carregar os produtos
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer border-0">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button class="btn btn-primary px-4">
                        <i class="ri-check-fill me-1"></i>
                        Gerar OFs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
