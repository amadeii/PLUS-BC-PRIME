<div id="modalClientePDV">
	<div id="modalClientePDVBox">
		<div id="modalClientePDVHeader">
			<h3 id="modalClientePDVTitulo">Pesquisa de cliente</h3>
		</div>

		<div id="modalClientePDVBody">
			<div id="modalClientePDVTopo">
				<div id="modalClientePDVBuscaWrap">
					<span id="modalClientePDVIconeBusca">⌕</span>
					<input
					type="text"
					id="modalClientePDVBusca"
					placeholder="Pesquisar pelo nome/razão social, apelido/fantasia, CPF/CNPJ ou telefone"
					autocomplete="off"
					>
				</div>

				<button type="button" id="modalClientePDVNovoCliente">
					Novo Cliente
					<i data-lucide="plus" style="height: 13px;"></i>
				</button>
			</div>

			<div id="modalClientePDVTabelaWrap">
				<div id="modalClientePDVTabelaScroll">
					<table id="modalClientePDVTabela">
						<thead>
							<tr>
								<th style="width: 90px;">Código</th>
								<th>Nome/Razão social</th>
								<th>Nome fantasia</th>
								<th style="width: 170px;">CPF/CNPJ</th>
								<th>Endereço</th>
								<th style="width: 170px;">Cidade</th>
								<th style="width: 150px;">Telefone</th>
							</tr>
						</thead>
						<tbody id="modalClientePDVTbody">
							<tr>
								<td colspan="6" id="modalClientePDVEmpty">
									Digite algo para pesquisar pelo cliente.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div id="modalClientePDVFooter">
			<button type="button" id="modalClientePDVFechar">Cancelar</button>
		</div>
	</div>
</div>