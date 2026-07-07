<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>DANFE NFC-e</title>

	<style>
		@page{ size:72mm auto; margin:0; }
		*{ box-sizing:border-box; }
		html,body{ margin:0; padding:0; width:72mm; max-width:72mm; overflow:hidden; background:#fff; color:#000; font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:1.12; }
		.cupom{ width:75mm; max-width:75mm; padding:10mm 3mm 3mm 1.5mm; margin:0; background:#fff; overflow:hidden; }
		.center{ text-align:center; }
		.right{ text-align:right; }
		.bold{ font-weight:bold; }
		.fs10{ font-size:9px; }
		.fs11{ font-size:10px; }
		.fs12{ font-size:10.5px; }
		.fs13{ font-size:11px; }
		.fs14{ font-size:12px; }
		.linha{ border-top:1px dashed #000; margin:4px 0; }
		.table{ width:100%; max-width:100%; border-collapse:collapse; table-layout:fixed; }
		.table td,.table th{ padding:1px 1px; vertical-align:top; font-size:8.5px; font-weight:normal; word-break:break-word; overflow-wrap:anywhere; }
		.table th{ border-bottom:1px solid #000; }
		.col-cod{ width:17%; }
		.col-desc{ width:31%; }
		.col-qtd{ width:11%; }
		.col-un{ width:8%; }
		.col-unit{ width:16%; }
		.col-total{ width:17%; }
		.total-row td{ font-size:10.5px; }
		.valor-pagar td{ font-size:13px; font-weight:bold; }
		.watermark{ position:absolute; left:0; right:0; top:185px; text-align:center; font-size:20px; line-height:1.05; color:rgba(0,0,0,.45); font-weight:bold; z-index:0; }
		.rel{ position:relative; z-index:1; width:100%; overflow:hidden; }
		.qrcode{ text-align:center; margin-top:10px; }
		.qrcode img{ width:38mm; height:38mm; }
		.chave{ word-break:break-word; overflow-wrap:anywhere; }
	</style>
</head>

<body>
	<div class="cupom">

		@if(($dados['ambiente'] ?? 1) == 2)
		<div class="watermark">
			SEM VALOR FISCAL<br>
			Emitida em ambiente de<br>
			homologação
		</div>
		@endif

		<div class="rel">
			<div class="center fs12 bold">{{ $dados['empresa']['nome'] }}</div>
			<div class="center fs11">CNPJ: {{ __setMask($dados['empresa']['cnpj']) }} IE: {{ $dados['empresa']['ie'] }}</div>
			<div class="center fs11">
				{{ $dados['empresa']['logradouro'] }}, {{ $dados['empresa']['numero'] }}<br>
				{{ $dados['empresa']['bairro'] }}<br>
				{{ $dados['empresa']['cidade'] }}-{{ $dados['empresa']['uf'] }}
				@if(!empty($dados['empresa']['telefone']))
					Fone: {{ $dados['empresa']['telefone'] }}
				@endif
			</div>

			<div class="linha"></div>

			<div class="center fs10">
				Documento Auxiliar da Nota Fiscal de Consumidor Eletrônica<br>
				Não permite aproveitamento de crédito de ICMS
			</div>

			<div class="linha"></div>

			<table class="table">
				<thead>
					<tr>
						<th class="col-cod">Código</th>
						<th class="col-desc">Descrição</th>
						<th class="col-qtd right">Qtde</th>
						<th class="col-un">UN</th>
						<th class="col-unit right">Vl Unit</th>
						<th class="col-total right">Vl Total</th>
					</tr>
				</thead>
				<tbody>
					@foreach($dados['itens'] as $item)
					<tr>
						<td>{{ $item['codigo'] }}</td>
						<td>{{ $item['descricao'] }}</td>
						<td class="right">{{ number_format($item['quantidade'], 0, ',', '.') }}</td>
						<td>{{ $item['unidade'] ?? 'UN' }}</td>
						<td class="right">{{ number_format($item['valor_unitario'], 2, ',', '.') }}</td>
						<td class="right">{{ number_format($item['valor_total'], 2, ',', '.') }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>

			<div class="linha"></div>

			<table class="table">
				<tr class="total-row">
					<td>Qtde total de itens</td>
					<td class="right">{{ count($dados['itens']) }}</td>
				</tr>
				<tr class="total-row">
					<td>Valor Total R$</td>
					<td class="right">{{ number_format($dados['valor_produtos'], 2, ',', '.') }}</td>
				</tr>
				<tr class="total-row">
					<td>Desconto R$</td>
					<td class="right">{{ number_format($dados['valor_desconto'], 2, ',', '.') }}</td>
				</tr>
				<tr class="total-row">
					<td>Frete R$</td>
					<td class="right">{{ number_format($dados['valor_frete'] ?? 0, 2, ',', '.') }}</td>
				</tr>
				<tr class="valor-pagar">
					<td>Valor a Pagar R$</td>
					<td class="right">{{ number_format($dados['valor_total'], 2, ',', '.') }}</td>
				</tr>
			</table>

			<div class="linha"></div>

			<table class="table">
				<tr>
					<td class="bold">FORMA PAGAMENTO</td>
					<td class="right bold">VALOR PAGO R$</td>
				</tr>
				@foreach($dados['pagamentos'] ?? [] as $pag)
				<tr>
					<td>{{ $pag['descricao'] }}</td>
					<td class="right">{{ number_format($pag['valor'], 2, ',', '.') }}</td>
				</tr>
				@endforeach
				<tr>
					<td>Troco R$</td>
					<td class="right">{{ number_format($dados['troco'] ?? 0, 2, ',', '.') }}</td>
				</tr>
			</table>

			<div class="linha"></div>

			<div class="center fs11 bold">Consulte pela Chave de Acesso em:</div>
			<div class="center fs10 chave">{{ $dados['url_consulta'] }}</div>
			<div class="center fs10 chave">{{ trim(chunk_split($dados['chave'], 4, ' ')) }}</div>

			<div class="linha"></div>

			<div class="center fs11 bold">
				{{ $dados['cliente']['nome'] ?? 'CONSUMIDOR NÃO IDENTIFICADO' }}
			</div>

			<div class="center fs11">
				<b>NFCe n. {{ $dados['numero'] }} Série {{ $dados['serie'] }}</b><br>
				{{ date('d/m/Y H:i:s', strtotime($dados['data_emissao'])) }}<br>
				Protocolo de Autorização: {{ $dados['protocolo'] }}<br>
				Data de Autorização: {{ date('d/m/Y H:i:s', strtotime($dados['autorizacao'])) }}
			</div>

			<div class="linha"></div>

			<div class="qrcode">
				@if(!empty($dados['qrcode_base64']))
				<img src="{{ $dados['qrcode_base64'] }}">
				@endif
			</div>

			<div class="center fs10">
				Tributos totais incidentes (Lei Federal 12.741/2012): 
				R$ {{ number_format($dados['valor_tributos'] ?? 0, 2, ',', '.') }}
			</div>

			@if(!empty($dados['inf_cpl']))
			<div class="center fs10">{{ $dados['inf_cpl'] }}</div>
			@endif
		</div>
	</div>
</body>
</html>