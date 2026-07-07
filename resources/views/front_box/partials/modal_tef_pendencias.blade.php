<div class="modal fade" id="modal-tef-pendencias" data-backdrop="static">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header bg-warning text-dark">
				<h5 class="modal-title text-white">
					<i class="la la-clock mr-2 text-white"></i>Pendências TEF encontradas
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

			</div>
			
			<div class="modal-body">
				<div class="alert alert-info">
					<strong>O que é isso?</strong> São transações pendentes de confirmação (queda de luz/erro antes do finish).  
					O recomendado quando não existe venda salva é <strong>Estornar</strong>.
				</div>
				
				<div class="table-responsive">
					<table class="table table-sm table-striped table-hover">
						<thead class="thead-dark">
							<tr>
								<th>#</th>
								<th>Cupom</th>
								<th>Data</th>
								<th>Hora</th>
								<th>Função</th>
								<th>Valor</th>
								<th>Ação</th>
							</tr>
						</thead>
						<tbody id="tbody-tef-pendencias">
							<tr>
								<td colspan="7" class="text-center py-4 text-muted">
									Nenhuma pendência carregada.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				
				{{-- <small class="text-muted">
					* Valor geralmente vem em centavos (ex: 2775 = R$ 27,75).
				</small> --}}
			</div>
			
			<div class="modal-footer">
				<button class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
			</div>
		</div>
	</div>
</div>
