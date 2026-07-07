<div id="modalSuprimentoOverlay" class="modal-suprimento-overlay">
	<div id="modalSuprimentoBox" class="modal-suprimento-box">

		<div class="modal-suprimento-header">
			<h3>Suprimento de caixa</h3>
		</div>

		<div class="modal-suprimento-body">
			<div class="modal-suprimento-topo">
				<div class="modal-suprimento-campo-group">
					<label for="valorSuprimentoInput">Valor do suprimento</label>
					<input type="text" id="valorSuprimentoInput" class="modal-suprimento-input" placeholder="R$ 0,00">
				</div>

				<div class="modal-suprimento-saldo">
					<span>Saldo em caixa:</span>
					<strong id="saldoDinheiroSuprimento">R$ 0,00</strong>
				</div>
			</div>

			<div class="modal-suprimento-campo-group modal-suprimento-campo-group-full">
				<label for="observacaoSuprimentoInput">Observação</label>
				<textarea id="observacaoSuprimentoInput" class="modal-suprimento-textarea"></textarea>
			</div>
		</div>

		<div class="modal-suprimento-footer">
			<button type="button" id="btnCancelarSuprimento" class="modal-suprimento-btn modal-suprimento-btn-cancelar">
				Cancelar
			</button>

			<button type="button" id="btnSalvarSuprimento" class="modal-suprimento-btn modal-suprimento-btn-confirmar">
				Suprimento
			</button>
		</div>

	</div>
</div>