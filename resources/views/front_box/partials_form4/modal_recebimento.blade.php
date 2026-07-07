<div id="modalRecebimento" class="modal-recebimento-overlay">
  <div class="modal-recebimento-box">

    <div class="modal-recebimento-header">
      <div class="modal-recebimento-title">Recebimento</div>
      <button type="button" class="modal-recebimento-close" onclick="fecharModalRecebimento()">×</button>
    </div>

    <div class="modal-recebimento-body">

      <!-- BUSCA -->
      <div class="mr-topo">
        <label class="mr-label">Selecione o cliente para buscar</label>

        <div class="mr-busca-wrap">
          <input
            type="text"
            id="mrBuscaCliente"
            class="mr-busca-input"
            placeholder="Digite a razão social ou CPF/CNPJ"
            autocomplete="off"
          >
          <div id="mrResultadoBusca" class="mr-busca-dropdown"></div>
        </div>
      </div>

      <!-- CONTEÚDO DO CLIENTE -->
      <div id="mrConteudoCliente" class="mr-conteudo d-none">

        <div class="mr-cliente-head">
          <div class="mr-cliente-head-left">
            <div class="mr-avatar">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 1 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>

            <div>
              <div class="mr-cliente-titulo">Cliente</div>
              <div class="mr-cliente-subtitulo">Detalhes cadastrais e financeiro</div>
            </div>
          </div>

          <div class="mr-cliente-head-right">
            <span id="mrStatusCliente" class="mr-badge-status ativo">Ativo</span>
            <button type="button" class="mr-btn-outline" id="mrBtnEditarCliente">Editar</button>
          </div>
        </div>

        <div class="mr-cards-grid">
          <div class="mr-card">
            <div class="mr-card-label">RAZÃO SOCIAL</div>
            <div class="mr-card-title" id="mrRazaoSocial">--</div>

            <div class="mr-tags">
              <span class="mr-tag" id="mrDocumento">--</span>
              <span class="mr-tag tag-gray" id="mrTipoCliente">Cliente</span>
            </div>
          </div>

          <div class="mr-card">
            <div class="mr-card-label">ENDEREÇO</div>
            <div class="mr-card-title" id="mrEndereco">--</div>
            <div class="mr-card-info" id="mrCidadeUf">--</div>

            <div class="mr-card-contact">
              <span id="mrEmail">--</span>
              <span class="mr-sep">•</span>
              <span id="mrTelefone">--</span>
            </div>
          </div>
        </div>

        <div class="mr-divider"></div>

        <!-- CONTAS -->
        <div class="mr-section">
          <div class="mr-section-title">Contas a Receber</div>
          <div class="mr-section-subtitle">Parcelas e títulos em aberto</div>
        </div>

        <div class="mr-table-wrap">
          <table class="mr-table">
            <thead>
              <tr>
                <th style="width: 42px;"></th>
                <th>VENCIMENTO</th>
                <th>DESCRIÇÃO</th>
                <th>DOCUMENTO</th>
                <th>VALOR</th>
                <th>STATUS</th>
              </tr>
            </thead>
            <tbody id="mrTabelaTitulos">
              <tr>
                <td colspan="6" class="mr-empty">Nenhuma conta encontrada.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mr-resumo-box">
          <div class="mr-resumo-item">
            <span>Total em aberto</span>
            <strong id="mrTotalAberto">R$ 0,00</strong>
          </div>

          <div class="mr-resumo-item">
            <span>Total pendente</span>
            <strong class="txt-warning" id="mrTotalPendente">R$ 0,00</strong>
          </div>

          <div class="mr-resumo-item">
            <span>Total em atraso</span>
            <strong class="txt-danger" id="mrTotalAtraso">R$ 0,00</strong>
          </div>
        </div>

        <div class="mr-footer">
          <button type="button" class="mr-btn-primary" id="mrBtnReceberContas">
            Receber contas
          </button>
        </div>
      </div>

    </div>
  </div>
</div>