<p>Olá,</p>

<p>Segue em anexo os XMLs fiscais da competência {{ $competencia->format('m/Y') }}.</p>

@if($mensagem_email)
<p>{{ $mensagem_email }}</p>
@endif

<p>Arquivos anexados:</p>

<ul>
    <li>ZIP de NFe aprovadas e canceladas</li>
    <li>ZIP de NFC-e aprovadas e canceladas</li>
    <li>PDF com resumo fiscal</li>
</ul>

<p>Este é um envio automático.</p>