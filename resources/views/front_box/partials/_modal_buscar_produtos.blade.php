
<div class="modal fade" id="modalBuscarProdutos" tabindex="-1" aria-labelledby="modalBuscarProdutosLabel" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-scrollable modal-produtos-pdv">
    <div class="modal-content shadow-sm">

      <div class="modal-header">
        <h5 class="modal-title" id="modalBuscarProdutosLabel">
          <i class="ri-search-line me-1"></i> Buscar Produtos
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">

        <!-- Filtros -->
        <div class="card shadow-sm mb-2">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-light" id="btn-limpar-filtros-produto">
                <i class="ri-refresh-line"></i> Limpar
              </button>
              
            </div>
          </div>

          <div class="card-body py-2">
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label small mb-0">Nome</label>
                <input type="text" class="form-control form-control-sm" id="filtro_nome" placeholder="Ex: Arroz, Coca..." autocomplete="off">
              </div>

              <div class="col-md-2">
                <label class="form-label small mb-0">Categoria</label>
                <select class="form-select form-select-sm" id="filtro_categoria">
                  <option value="">Todas</option>
                  @foreach($categorias as $c)
                  <option value="{{ $c->id }}">{{ $c->nome }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-2">
                <label class="form-label small mb-0">Marca</label>
                <select class="form-select form-select-sm" id="filtro_marca">
                  <option value="">Todas</option>
                  @foreach($marcas as $m)
                  <option value="{{ $m->id }}">{{ $m->nome }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-2">
                <label class="form-label small mb-0">Código de barras</label>
                <input type="text" class="form-control form-control-sm" id="filtro_codigo_barras" placeholder="EAN/GTIN" autocomplete="off">
              </div>

              <div class="col-md-2">
                <label class="form-label small mb-0">Referência</label>
                <input type="text" class="form-control form-control-sm" id="filtro_referencia" placeholder="Ref interna" autocomplete="off">
              </div>
            </div>

            <div class="row mt-2">
              <div class="col-12">
                <div class="alert alert-info p-1 mb-0">
                  <small>
                    <i class="ri-information-line"></i>
                    Dica: pressione <strong>Enter, ou Selecione</strong> no campo para iniciar a busca.
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resultado -->
        <div class="card shadow-sm">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Resultados</h6>
            <div class="d-flex align-items-center gap-2">
              <small class="text-muted" id="produtos-info">0 itens</small>
              <span class="spinner-border spinner-border-sm d-none" id="produtos-loading" role="status" aria-hidden="true"></span>
            </div>
          </div>

          <div class="card-body py-1">
            <div id="produtos-msg" class="text-center my-2">
              <span class="text-muted">Use os filtros e clique em <strong>Filtrar</strong>.</span>
            </div>

            <div class="table-responsive">
              <table class="table" id="tabela-produtos">
                <thead class="table-light">
                  <tr>
                    <th style="width:50px;"></th>
                    <th style="width:70px;">#</th>
                    <th style="width:260px;">Nome</th>
                    <th style="width:160px;">Categoria</th>
                    <th style="width:160px;">Marca</th>
                    <th style="width:160px;">Cód. Barras</th>
                    <th style="width:140px;">Referência</th>
                    <th style="width:120px;">Valor unitário</th>
                    <th style="width:110px;">Ação</th>
                  </tr>
                </thead>
                <tbody id="lista-produtos"></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="ri-close-line me-1"></i> Fechar
        </button>
      </div>

    </div>
  </div>
</div>
