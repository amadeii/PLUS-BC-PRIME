<div class="modal fade" id="modal-tef" data-backdrop="static" data-keyboard="false"  tabindex="-1" role="dialog" aria-hidden="true">

	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content shadow-lg border-0 rounded-lg">

			<!-- HEADER -->
			<div class="modal-header bg-light">
				<h5 class="modal-title fw-bold text-dark">
					<i class="ri-bank-card-line text-primary me-1"></i>
					Pagamento TEF
				</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>

			<!-- BODY -->
			<div class="modal-body">

				<!-- OPÇÕES TEF -->
				<div class="row text-center mb-4">

					<div class="col-md-4 mb-3">
						<button type="button" class="btn btn-primary btn-tef w-100 tef-opcao tef-debito" data-tipo="debito">
							<i class="ri-bank-card-line"></i>
							<span>Débito</span>
						</button>
					</div>

					<div class="col-md-4 mb-3">
						<button type="button" class="btn btn-success btn-tef w-100 tef-opcao tef-credito" data-tipo="credito">
							<i class="ri-bank-card-2-line"></i>
							<span>Crédito</span>
						</button>
					</div>

					<div class="col-md-4 mb-3">
						<button type="button" class="btn btn-warning btn-tef w-100 tef-opcao tef-pix" data-tipo="pix">
							<i class="ri-qr-code-line"></i>
							<span>Pix</span>
						</button>
					</div>

				</div>


				<!-- STATUS -->
				<div class="alert alert-light border text-center mb-3" id="tef-status">
					Selecione a forma de pagamento
				</div>

				<!-- LOG / TERMINAL -->
				<div class="tef-terminal mb-3" id="tef-log">
					Aguardando ação...
				</div>

				<!-- PIX -->
				<div class="text-center mt-4 d-none" id="tef-pix-area">
					<h6 class="text-muted mb-2">Leia o QR Code para pagar</h6>
					<img src="" id="tef-qrcode" class="img-fluid rounded shadow-sm"
					style="max-width:220px;">
				</div>

			</div>

			<!-- FOOTER -->
			<div class="modal-footer border-top-0">
				<button type="button" class="btn btn-outline-danger"
				id="tef-cancelar">
				<i class="ri-close-line"></i> Cancelar
			</button>
		</div>

	</div>
</div>
</div>
