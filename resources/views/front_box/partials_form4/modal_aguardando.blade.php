
<div class="modal-vendas-overlay" id="modalAguardando">
    <div class="modal-vendas-box">

        <div class="modal-vendas-header">
            <h3>Relação de vendas</h3>
        </div>

        <div class="modal-vendas-body">

            <div class="topo-vendas">
                <div class="busca-vendas">
                    <span class="icone-busca">⌕</span>
                    <input type="text" id="pesquisaVenda" placeholder="Pesquisar pelo número da venda ou nome do cliente">
                </div>
            </div>

            
            <div class="box-tabela-vendas">
                <div class="tabela-scroll">
                    <table class="table-vendas">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Código</th>
                                <th style="width: 140px;">Data e hora</th>
                                <th>Venda</th>
                                <th style="width: 80px;">Itens</th>
                                <th style="width: 120px;">Desconto</th>
                                <th style="width: 120px;">Total</th>
                                <th style="width: 70px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyAguardando">
                            
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="modal-vendas-footer">
            <button type="button" class="btn-cancelar-vendas" id="fecharModalAguardando">Cancelar</button>
        </div>
    </div>
</div>