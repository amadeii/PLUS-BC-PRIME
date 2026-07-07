<div id="modalNovoClientePDV">
  <div id="modalNovoClientePDVBox">
    <div id="modalNovoClientePDVHeader">
      <h3 id="modalNovoClientePDVTitulo">Novo cliente</h3>
    </div>

    <div id="modalNovoClientePDVBody">
      <div id="modalNovoClientePDVGrid">

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVDocumento">CPF/CNPJ</label>
          <input type="text" id="modalNovoClientePDVDocumento" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVNome">Nome / Razão social</label>
          <input type="text" id="modalNovoClientePDVNome" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVFantasia">Apelido / Nome fantasia</label>
          <input type="text" id="modalNovoClientePDVFantasia" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVTelefone">Telefone</label>
          <input type="text" id="modalNovoClientePDVTelefone" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVEmail">Email</label>
          <input type="text" id="modalNovoClientePDVEmail" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo modalNovoClientePDVGrupo--full">
          <label for="modalNovoClientePDVEndereco">Rua</label>
          <input type="text" id="modalNovoClientePDVRua" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVNumero">Número</label>
          <input type="text" id="modalNovoClientePDVNumero" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVBairro">Bairro</label>
          <input type="text" id="modalNovoClientePDVBairro" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVCep">CEP</label>
          <input type="text" id="modalNovoClientePDVCep" autocomplete="off">
        </div>

        <div class="modalNovoClientePDVGrupo">
          <label for="modalNovoClientePDVCidadeBusca">Cidade</label>

          <div id="modalNovoClientePDVCidadeWrap">
            <input type="text" id="modalNovoClientePDVCidadeBusca" autocomplete="off" placeholder="Digite o nome da cidade">
            <input type="hidden" id="modalNovoClientePDVCidadeId">

            <div id="modalNovoClientePDVCidadeResultado"></div>
          </div>
        </div>


      </div>
    </div>

    <div id="modalNovoClientePDVFooter">
      <button type="button" id="modalNovoClientePDVCancelar">Cancelar</button>
      <button type="button" id="modalNovoClientePDVSalvar">Salvar cliente</button>
    </div>
  </div>
</div>