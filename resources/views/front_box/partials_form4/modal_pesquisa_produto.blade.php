<div class="pp-modal-overlay" id="ppModalPesquisa">
    <div class="pp-modal-box">

        <div class="pp-modal-header">
            <h2 class="pp-modal-title">Pesquisa de produto</h2>
            <button type="button" id="ppFecharModal" class="pp-modal-close">×</button>
        </div>

        <div class="pp-modal-body">
            <div class="pp-modal-topo">
                <div class="pp-modal-busca">
                    <span class="pp-modal-busca-icon">
                        <i data-lucide="search"></i>
                    </span>
                    <input
                    type="text"
                    id="ppInputBusca"
                    class="pp-modal-input"
                    placeholder="Pesquisar pelo produto, referencia, marca e categoria do produto"
                    >
                </div>

            </div>

            <div class="pp-modal-grid-header">
                <div class="pp-col">
                    <div class="pp-head-title">Código</div>
                    <div class="pp-head-sub">Ult. alteração</div>
                </div>

                <div class="pp-col">
                    <div class="pp-head-title">Produto</div>
                    <div class="pp-head-sub">Código de barras</div>
                </div>

                <div class="pp-col">
                    <div class="pp-head-title">Referência</div>
                    <div class="pp-head-sub">Categoria</div>
                </div>

                <div class="pp-col pp-align-right">
                    <div class="pp-head-title">Preço</div>
                    <div class="pp-head-sub">Quantidade</div>
                </div>
            </div>

            <div class="pp-modal-lista" id="ppListaProdutos"></div>
        </div>

    </div>
</div>