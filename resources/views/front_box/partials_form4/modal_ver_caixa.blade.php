<div id="modal-ver-caixa" class="vcx-modal">
  <div class="vcx-modal-box">

    <div class="vcx-header">
      <div>
        <h3 class="vcx-title">Resumo do caixa</h3>
        <div class="vcx-subtitle" id="vcx-subtitle">Caixa #--</div>
      </div>

      <button type="button" id="vcx-btn-fechar-topo" class="vcx-btn-fechar-topo">×</button>
    </div>

    <div class="vcx-body">

      <div class="vcx-top-grid">
        <div class="vcx-card vcx-card-destaque">
          <span class="vcx-label">Valor de abertura</span>
          <strong id="vcx-valor-abertura">R$ 0,00</strong>
        </div>

        <div class="vcx-card vcx-card-destaque">
          <span class="vcx-label">Total de vendas</span>
          <strong id="vcx-total-vendas">R$ 0,00</strong>
        </div>

        <div class="vcx-card vcx-card-destaque">
          <span class="vcx-label">Saldo atual</span>
          <strong id="vcx-saldo-atual">R$ 0,00</strong>
        </div>
      </div>

      <div class="vcx-info-grid">
        <div class="vcx-card">
          <div class="vcx-card-title">Informações do caixa</div>
          <div class="vcx-info-list">
            <div class="vcx-info-item">
              <span>Operador</span>
              <strong id="vcx-operador">--</strong>
            </div>
            <div class="vcx-info-item">
              <span>Abertura</span>
              <strong id="vcx-data-abertura">--</strong>
            </div>
            <div class="vcx-info-item">
              <span>Status</span>
              <strong id="vcx-status">--</strong>
            </div>
            <div class="vcx-info-item">
              <span>Observação</span>
              <strong id="vcx-observacao">--</strong>
            </div>
          </div>
        </div>

        <div class="vcx-card">
          <div class="vcx-card-title">Resumo financeiro</div>
          <div class="vcx-info-list">
            <div class="vcx-info-item">
              <span>Compras</span>
              <strong id="vcx-soma-compras">R$ 0,00</strong>
            </div>
            <div class="vcx-info-item">
              <span>Contas recebidas</span>
              <strong id="vcx-soma-contas-receber">R$ 0,00</strong>
            </div>
            <div class="vcx-info-item">
              <span>Contas pagas</span>
              <strong id="vcx-soma-contas-pagar">R$ 0,00</strong>
            </div>
            <div class="vcx-info-item">
              <span>Serviços / OS</span>
              <strong id="vcx-soma-os">R$ 0,00</strong>
            </div>
            <div class="vcx-info-item">
              <span>Suprimentos</span>
              <strong id="vcx-soma-suprimentos">R$ 0,00</strong>
            </div>
            <div class="vcx-info-item">
              <span>Sangrias</span>
              <strong id="vcx-soma-sangrias">R$ 0,00</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="vcx-card">
        <div class="vcx-card-title">Formas de pagamento</div>
        <div id="vcx-formas-pagamento" class="vcx-tags-grid">
          <div class="vcx-tag-vazio">Nenhum dado carregado.</div>
        </div>
      </div>

      <div class="vcx-card">
        <div class="vcx-card-title">Pendências</div>
        <div class="vcx-info-list vcx-info-list-3">
          <div class="vcx-info-item">
            <span>Crediário</span>
            <strong id="vcx-pendente-crediario">R$ 0,00</strong>
          </div>
          <div class="vcx-info-item">
            <span>Boleto</span>
            <strong id="vcx-pendente-boleto">R$ 0,00</strong>
          </div>
          <div class="vcx-info-item">
            <span>Crédito loja</span>
            <strong id="vcx-pendente-credito-loja">R$ 0,00</strong>
          </div>
        </div>
      </div>

      <div class="vcx-card">
        <div class="vcx-card-title">Trocas</div>
        <div class="vcx-info-list vcx-info-list-2">
          <div class="vcx-info-item">
            <span>Pagas pelo cliente</span>
            <strong id="vcx-trocas-cliente">R$ 0,00</strong>
          </div>
          <div class="vcx-info-item">
            <span>Pagas ao cliente</span>
            <strong id="vcx-trocas-caixa">R$ 0,00</strong>
          </div>
        </div>
      </div>

      <div class="vcx-card">
        <div class="vcx-card-title">Movimentações</div>

        <div class="vcx-tabs">
          <button type="button" class="vcx-tab-btn active" data-tab="vendas">Vendas</button>
          <button type="button" class="vcx-tab-btn" data-tab="suprimentos">Suprimentos</button>
          <button type="button" class="vcx-tab-btn" data-tab="sangrias">Sangrias</button>
        </div>

        <div class="vcx-tab-content active" data-content="vendas">
          <div class="vcx-table-wrap">
            <table class="vcx-table">
              <thead>
                <tr>
                  <th>Tipo</th>
                  <th>Data</th>
                  <th>Cliente</th>
                  <th>Pagamentos</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody id="vcx-tbody-vendas">
                <tr>
                  <td colspan="5" class="vcx-empty-cell">Nenhuma venda encontrada.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="vcx-tab-content" data-content="suprimentos">
          <div class="vcx-table-wrap">
            <table class="vcx-table">
              <thead>
                <tr>
                  <th>Data</th>
                  <th>Observação</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tbody id="vcx-tbody-suprimentos">
                <tr>
                  <td colspan="3" class="vcx-empty-cell">Nenhum suprimento encontrado.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="vcx-tab-content" data-content="sangrias">
          <div class="vcx-table-wrap">
            <table class="vcx-table">
              <thead>
                <tr>
                  <th>Data</th>
                  <th>Observação</th>
                  <th>Valor</th>
                </tr>
              </thead>
              <tbody id="vcx-tbody-sangrias">
                <tr>
                  <td colspan="3" class="vcx-empty-cell">Nenhuma sangria encontrada.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <div class="vcx-footer">
      <button type="button" id="btnAbrirFechamento" class="vcx-btn vcx-btn-danger">
        Fechar Caixa
      </button>

      <!-- <button type="button" id="vcx-btn-fechar" class="vcx-btn vcx-btn-secundario">
        Fechar
      </button> -->
    </div>

  </div>
</div>