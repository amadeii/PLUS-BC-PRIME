<div class="pg-wrap">
  <main class="pg-container">
    <section class="pg-esquerda">

      <div class="pg-topo-acoes">

        <!-- 👇 BOTÕES EM COLUNA -->
        <div class="pg-acoes-coluna">
          <button type="button" class="pg-btn-topo" id="pdvfinal-acr-btn-abrir">Acréscimo (+)</button>
          <button type="button" class="pg-btn-topo" id="pdvfinal-btn-abrir-desconto">Desconto (-)</button>
        </div>

        <!-- 👇 VALOR -->
        <div class="pg-campo-box">
          <label>VALOR RECEBIDO</label>
          <input type="text" class="mask-moeda" id="pg-valor-recebido" value="0,00">
        </div>

        <!-- 👇 FORMA -->
        <div class="pg-campo-box">
          <label>FORMA RECEBIMENTO</label>
          <input type="text" id="pg-forma-recebimento" readonly>
        </div>

      </div>

      <div class="pg-formas card">
        @foreach($tiposPagamento as $codigo => $nome)

        @php
        // classe de cor dinâmica
        $classeCor = match($codigo) {
          "01" => "bg-dinheiro",
          "02" => "bg-cheque",
          "03" => "bg-credito",
          "04" => "bg-debito",
          "05" => "bg-crediario",
          "06" => "bg-crediario",
          10   => "bg-vale",
          17   => "bg-pix",
          default => "bg-default"
        };
        @endphp

        <button 
        type="button" 
        class="pg-forma" 
        data-forma="{{ $nome }}"
        data-codigo="{{ $codigo }}"
        >
        <span class="pg-forma-cor {{ $classeCor }}">

          <i data-lucide="{{ match($codigo) {
            "01" => "banknote",
            "02" => "receipt",
            "03" => "credit-card",
            "04" => "credit-card",
            "05" => "wallet",
            "06" => "wallet",
            10   => "ticket",
            17   => "qr-code",
            default => "circle"
          } }}" style="color: #fff"></i>

        </span>

        <span class="pg-forma-texto">
          {{ $codigo }} - {{ $nome }}
        </span>
      </button>

      @endforeach
    </div>

    <div class="botoes pg-botoes-fixos">
      <button type="button" class="btn-pdv grande" id="btnAguardarPg">Aguardar (F7)</button>
      <button type="button" class="btn-pdv grande" id="btnCancelarPg">Cancelar (F6)</button>
      <button type="button" style="visibility: hidden;"></button>
      <button type="button" class="btn-pdv pequeno" id="btnAguardandoPg">Aguardando (F11)</button>
      <button type="button" class="btn-pdv pequeno" id="btnClientePg">Cliente (F2)</button>
      <button type="button" class="btn-pdv btn-observacoes" id="btnObservacoesPg">Observações (F12)</button>
    </div>

  </section>

  <section class="pg-direita">
    <div class="pg-box card">
      <div class="pg-box-titulo">
        <i data-lucide="wallet-cards"></i>
        <span>Forma de recebimento</span>
      </div>

      <div class="pg-lista-recebimentos" id="pg-lista-recebimentos">
        <div class="pg-lista-vazia">
          Não há nenhuma forma de recebimento definida para o pedido.
        </div>
      </div>

      <div class="pdv-pg-footer">
        <button id="pdv-btn-finalizar" class="pdv-btn-finalizar" disabled>
          <i data-lucide="check-circle" style="height: 14px;"></i>
          Finalizar venda
        </button>
      </div>
    </div>

    <div class="pg-box card">
      <div class="pg-box-titulo">Total</div>

      <div class="pg-resumo-linha">
        <span>Subtotal</span>
        <strong id="pg-total-venda">R$ 0,00</strong>
      </div>

      <div class="pg-resumo-linha">
        <span>Desconto</span>
        <strong id="pg-total-desconto">R$ 0,00</strong>
      </div>

      <div class="pg-resumo-linha">
        <span>Acréscimo</span>
        <strong id="pg-total-acrescimo">R$ 0,00</strong>
      </div>

      <div class="pg-resumo-linha">
        <span>Total a receber</span>
        <strong id="pg-total-receber">R$ 0,00</strong>
      </div>

      <div class="pg-resumo-linha">
        <span>Total já recebido</span>
        <strong id="pg-total-ja-recebido">R$ 0,00</strong>
      </div>
    </div>

    <button type="button" id="btnVoltarVenda" class="pg-voltar-venda">
      <i data-lucide="shopping-cart"></i>
      Voltar para a venda
    </button>
  </section>
</main>
</div>

