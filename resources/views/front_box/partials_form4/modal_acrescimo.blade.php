<div id="modal-acrescimo" class="md-overlay" style="display:none;">
	<div class="md-container">
		<div class="md-header">
			<div>
				<h2>Acréscimo no item</h2>
			</div>
			<div class="md-produto" id="ma-produto-nome">Produto</div>
		</div>

		<div class="md-info">
			<div class="md-info-box">
				<label id="ma-label-original">Valor original</label>
				<p id="ma-valor-original">1 Un x R$ 0,00 = R$ 0,00</p>
			</div>

			<div class="md-info-box">
				<label id="ma-label-final">Valor final</label>
				<p id="ma-valor-final-label">1 Un x R$ 0,00 = R$ 0,00</p>
			</div>
		</div>

		<div class="md-tipo">
			<button type="button" class="active" data-tipo="unitario">Acréscimo unitário</button>
			<button type="button" data-tipo="total">Acréscimo no total</button>
		</div>

		<div class="md-form">
			<div class="md-group">
				<label id="ma-label-percentual">Acréscimo (%)</label>
				<input type="text" id="ma-acrescimo-p" class="mask-moeda" value="0,00">
			</div>

			<div class="md-group">
				<label id="ma-label-reais">Acréscimo (R$)</label>
				<input type="text" id="ma-acrescimo-r" class="mask-moeda" value="0,00">
			</div>

			<div class="md-group">
				<label id="ma-label-valor-final-input">Valor final</label>
				<input type="text" id="ma-valor-final" value="0,00" readonly>
			</div>
		</div>

		<div class="md-footer">
			<button type="button" class="btn-cancelar" id="ma-btn-cancelar">Cancelar</button>
			<button type="button" class="btn-confirmar" id="ma-btn-aplicar">Aplicar acréscimo</button>
		</div>
	</div>
</div>