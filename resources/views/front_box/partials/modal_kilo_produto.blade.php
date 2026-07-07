<div class="modal fade" id="modalAdicionarProduto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content modal-ifood-product border-0 shadow-lg">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="map-nome">Nome do produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2">

                <div class="text-center mb-4">
                    <img id="map-img"
                    src="/imgs/sem_imagem.png"
                    class="img-fluid produto-modal-img"
                    alt="Produto">
                </div>

                <div class="produto-modal-box mb-3">
                    <label class="form-label label-modal">Quantidade</label>
                    <input type="text"
                    id="map-qtd"
                    class="form-control input-modal-grande text-center"
                    placeholder="0,000"
                    value="1,000">
                </div>

                <div class="produto-modal-box mb-4">
                    <label class="form-label label-modal">Valor unitário (KG)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0">R$</span>
                        <input type="text"
                        id="map-valor"
                        class="form-control input-modal-grande border-start-0 text-center moeda"
                        placeholder="0,00"
                        value="0,00">
                    </div>
                </div>

                <button type="button" class="btn btn-adicionar-ifood w-100" id="btnAdicionarProdutoModal">
                    <i class="ri-shopping-bag-3-line me-1"></i>
                    Adicionar
                </button>

            </div>
        </div>
    </div>
</div>