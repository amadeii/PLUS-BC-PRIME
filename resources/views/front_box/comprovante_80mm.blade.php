<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		@page {
			margin: 4px;
		}

		body {
			font-family: Courier, monospace;
			font-size: 10px;
			margin: 0;
		}

		.linha {
			border-top: 1px dashed #000;
			margin: 6px 0;
		}

		.center {
			text-align: center;
		}

		.pagina {
			page-break-after: always;
		}
	</style>
</head>
<body>

	<!-- VIA CLIENTE -->
	<div class="pagina">
		@foreach($cliente as $linha)
		{{ $linha }}<br>
		@endforeach

		<div class="linha"></div>
		<div class="center"><strong>VIA DO CLIENTE</strong></div>
	</div>

	<!-- VIA LOJA -->
	<div>
		@foreach($loja as $linha)
		{{ $linha }}<br>
		@endforeach

		<div class="linha"></div>
		<div class="center"><strong>VIA DA LOJA</strong></div>
	</div>

</body>
</html>