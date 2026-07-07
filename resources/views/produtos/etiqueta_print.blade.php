<button class="btn-print" onclick="window.print()">Imprimir</button>

<div id="preview_body">

	@php
		$contLinha = 0;
	@endphp

	{{-- ETIQUETAS PRINCIPAIS --}}
	@for($i = 0; $i < $quantidade; $i++)
		<div class="etiqueta {{ $data['tipo'] == 'gondola' ? 'etiqueta-gondola' : 'etiqueta-simples' }} {{ $contLinha == 0 ? 'primeira-coluna' : '' }}">

			@if($data['tipo'] == 'simples')
				<div class="conteudo conteudo-simples">

					@if($data['nome_empresa'])
						<div class="linha empresa">{{ $data['empresa'] }}</div>
					@endif

					@if($data['nome_produto'])
						<div class="linha produto">{{ $data['nome'] }}</div>
					@endif

					@if($referencia)
						<div class="linha referencia">
							<b>{{ $data['referencia'] }}</b>
						</div>
					@endif

					@if($data['valor_produto'])
						<div class="linha valor">
							<b>R$ {{ number_format($data['valor'], 2, ',', '.') }}</b>
						</div>
					@endif

					@if($data['valor_atacado'] > 0)
						<div class="linha valor-atacado">
							<b>R$ {{ number_format($data['valor_atacado'], 2, ',', '.') }}</b>
						</div>
					@endif

					@if($data['cod_produto'])
						<div class="linha codigo-produto">
							ID: <b>{{ $data['codigo'] }}</b>
						</div>
					@endif

					<div class="barcode-wrap">
						<img class="barcode" src="/barcode/{{ $data['barcode'] }}.png" alt="Código de barras">
					</div>

					@if($data['codigo_barras_numerico'])
						<div class="linha codigo-barras">
							{{ $data['codigo_barras'] }}
						</div>
					@endif

				</div>
			@elseif($data['tipo'] == 'gondola')
				<div class="conteudo conteudo-gondola">

					@if($data['nome_produto'])
						<div class="linha produto gondola-produto">{{ $data['nome'] }}</div>
					@endif

					@if($data['cod_produto'])
						<div class="linha codigo-produto gondola-id">
							ID: <b>{{ $data['codigo'] }}</b>
						</div>
					@endif

					<div class="gondola-body">
						<div class="gondola-esquerda">
							<div class="barcode-wrap gondola-barcode-wrap">
								<img class="barcode barcode-gondola" src="/barcode/{{ $rand }}.png" alt="Código de barras">
							</div>

							@if($data['codigo_barras_numerico'])
								<div class="linha codigo-barras gondola-codigo">
									{{ $codigo }}
								</div>
							@endif
						</div>

						<div class="gondola-direita">
							@if($data['valor_produto'])
								<div class="gondola-valor">
									<b>R$ {{ number_format($data['valor'], 2, ',', '.') }}</b>
								</div>
							@endif
						</div>
					</div>

				</div>
			@endif

		</div>

		@php
			$contLinha++;
			if($contLinha == $quantidade_por_linhas){
				echo '<div class="quebra-linha"></div>';
				$contLinha = 0;
			}
		@endphp
	@endfor

	{{-- ETIQUETAS ADICIONAIS --}}
	@foreach($adds as $p)
		@for($i = 0; $i < $p['quantidade']; $i++)
			<div class="etiqueta {{ $p['tipo'] == 'gondola' ? 'etiqueta-gondola' : 'etiqueta-simples' }} {{ $contLinha == 0 ? 'primeira-coluna' : '' }}">

				@if($p['tipo'] == 'simples')
					<div class="conteudo conteudo-simples">

						@if($p['nome_empresa'])
							<div class="linha empresa">{{ $p['empresa'] }}</div>
						@endif

						@if($p['nome_produto'])
							<div class="linha produto">{{ $p['nome'] }}</div>
						@endif

						@if($p['cod_produto'])
							<div class="linha codigo-produto">
								ID: <b>{{ $p['codigo'] }}</b>
							</div>
						@endif

						<div class="barcode-wrap">
							<img class="barcode" src="/barcode/{{ $p['barcode'] }}.png" alt="Código de barras">
						</div>

						@if($p['codigo_barras_numerico'])
							<div class="linha codigo-barras">
								{{ $p['codigo_barras'] }}
							</div>
						@endif

						@if($p['valor_produto'])
							<div class="linha valor">
								<b>R$ {{ number_format($p['valor'], 2, ',', '.') }}</b>
							</div>
						@endif

						@if($p['valor_atacado'] > 0)
							<div class="linha valor-atacado">
								<b>R$ {{ number_format($p['valor_atacado'], 2, ',', '.') }}</b>
							</div>
						@endif

						@if($referencia && $p['referencia'])
							<div class="linha referencia">
								<b>REF: {{ $p['referencia'] }}</b>
							</div>
						@endif

					</div>
				@elseif($p['tipo'] == 'gondola')
					<div class="conteudo conteudo-gondola">

						@if($p['nome_produto'])
							<div class="linha produto gondola-produto">{{ $p['nome'] }}</div>
						@endif

						@if($p['cod_produto'])
							<div class="linha codigo-produto gondola-id">
								ID: <b>{{ $p['codigo'] }}</b>
							</div>
						@endif

						<div class="gondola-body">
							<div class="gondola-esquerda">
								<div class="barcode-wrap gondola-barcode-wrap">
									<img class="barcode barcode-gondola" src="/barcode/{{ $rand }}.png" alt="Código de barras">
								</div>

								@if($p['codigo_barras_numerico'])
									<div class="linha codigo-barras gondola-codigo">
										{{ $codigo }}
									</div>
								@endif
							</div>

							<div class="gondola-direita">
								@if($p['valor_produto'])
									<div class="gondola-valor">
										<b>R$ {{ number_format($p['valor'], 2, ',', '.') }}</b>
									</div>
								@endif
							</div>
						</div>

					</div>
				@endif

			</div>

			@php
				$contLinha++;
				if($contLinha == $quantidade_por_linhas){
					echo '<div class="quebra-linha"></div>';
					$contLinha = 0;
				}
			@endphp
		@endfor
	@endforeach
</div>

<script>
	setTimeout(() => {
		// document.querySelector('.btn-print').click();
	}, 500);
</script>

<style>
	*{
		box-sizing: border-box;
	}

	html, body{
		margin: 0;
		padding: 0;
		font-family: Arial, Helvetica, sans-serif;
		background: #f2f2f2;
	}

	body{
		padding: 10px;
	}

	.btn-print{
		display: inline-block;
		padding: 8px 14px;
		border: none;
		border-radius: 4px;
		background: #28a745;
		color: #fff;
		cursor: pointer;
		margin-bottom: 12px;
		font-size: 14px;
	}

	#preview_body{
		display: flex;
		flex-wrap: wrap;
		align-items: flex-start;
		width: 100%;
	}

	.quebra-linha{
		flex-basis: 100%;
		height: 0;
	}

	.etiqueta{
		width: {{ $largura }}mm;
		height: {{ $altura }}mm;
		margin-top: {{ $distancia_topo }}mm;
		margin-left: {{ $distancia_lateral }}mm;
		border: 0.1mm dotted #999;
		background: #fff;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.etiqueta.primeira-coluna{
		margin-left: 4mm;
	}

	.conteudo{
		width: 100%;
		height: 100%;
		padding: 1.5mm;
	}

	.conteudo-simples{
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
	}

	.linha{
		display: block;
		width: 100%;
		text-align: center;
		font-size: {{ $tamanho_fonte }}px;
		line-height: 1.05;
		margin-top: {{ $distancia_entre_linhas }}px;
		word-break: break-word;
	}

	.linha:first-child{
		margin-top: 0;
	}

	.empresa{
		font-weight: 700;
		text-transform: uppercase;
	}

	.valor,
	.valor-atacado,
	.referencia,
	.codigo-produto,
	.codigo-barras{
		font-weight: 400;
	}

	.barcode-wrap{
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-top: {{ $distancia_entre_linhas }}px;
	}

	.barcode{
		display: block;
		max-width: 90%;
		height: {{ $tamanho_codigo }}mm;
		width: auto;
		object-fit: contain;
	}

	/* GÔNDOLA */
	.conteudo-gondola{
		padding: 1.5mm;
	}

	.gondola-produto{
		margin-bottom: 4px;
	}

	.gondola-id{
		margin-top: 3px;
	}

	.gondola-body{
		display: flex;
		width: 100%;
		align-items: flex-start;
	}

	.gondola-esquerda{
		width: 60%;
		text-align: center;
	}

	.gondola-direita{
		width: 40%;
		display: flex;
		align-items: center;
		justify-content: center;
		text-align: center;
	}

	.gondola-barcode-wrap{
		justify-content: flex-start;
		padding-left: 5px;
		margin-top: 4px;
		margin-bottom: 4px;
	}

	.barcode-gondola{
		max-width: 90%;
		height: {{ $tamanho_codigo }}mm;
	}

	.gondola-codigo{
		text-align: center;
	}

	.gondola-valor{
		display: flex;
		align-items: center;
		justify-content: center;
		text-align: center;
		font-size: {{ $tamanho_fonte + 7 }}px;
		margin-top: 24px;
		padding-left: 6px;
		padding-right: 6px;
		font-weight: 700;
		line-height: 1.1;
	}

	@media print{
		html, body{
			margin: 0 !important;
			padding: 0 !important;
			background: #fff !important;
		}

		body{
			padding: 0 !important;
		}

		.btn-print,
		.btn,
		#toast-container,
		.tooltip{
			display: none !important;
		}

		.content-wrapper{
			border-left: none !important;
		}

		#preview_body{
			display: flex !important;
			flex-wrap: wrap !important;
			align-items: flex-start !important;
			width: 100% !important;
		}

		.etiqueta{
			border: none !important;
			background: #fff !important;
		}
	}

	@page{
		margin: 0;
	}
</style>