@extends('layouts.app', ['title' => 'Sistema'])
@section('content')
<div class="mt-1">
	<div class="card">
		<div class="card-header">
			<h5><i class="ri-server-line"></i> Ambiente do Servidor</h5>
		</div>
		<div class="card-body" id="phpInfoBox">
			<div class="text-muted">Carregando informações...</div>
		</div>
	</div>
</div>
@endsection

@section('js')
<script>
$.ajax({
    url: "{{ url('/sistema-info') }}",
    method: "GET",
})
.done(function (res) {

    let html = `

    <div class="row g-3">

        <!-- PHP -->
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">
                <small class="text-muted">PHP</small>
                <h5 class="mb-1">${res.php.version}</h5>
                <div class="text-muted small">${res.php.server}</div>
                <div class="text-muted small">${res.php.os}</div>
            </div>
        </div>

        <!-- OpenSSL -->
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">
                <small class="text-muted">OpenSSL</small>
                <h5 class="mb-1">
                    ${res.openssl.loaded
                        ? '<span class="text-success">Ativo</span>'
                        : '<span class="text-danger">Inativo</span>'}
                </h5>
                <div class="text-muted small">${res.openssl.version}</div>
            </div>
        </div>

        <!-- Laravel -->
        <div class="col-md-4">
            <div class="p-3 border rounded-3 h-100">
                <small class="text-muted">Laravel</small>
                <h5 class="mb-1">${res.laravel.app_env}</h5>
                <div class="text-muted small">
                    Debug: ${res.laravel.app_debug ? 'ON' : 'OFF'}
                </div>
                <div class="text-muted small">${res.laravel.timezone}</div>
            </div>
        </div>

    </div>

    <hr>

    <!-- LIMITES -->
    <div class="mb-3">
        <h6 class="mb-2">Limites do PHP</h6>
        <div class="row g-2">

            ${renderLimit('Memory limit', res.limits.memory_limit)}
            ${renderLimit('Execution time', res.limits.max_execution_time + 's')}
            ${renderLimit('Upload max', res.limits.upload_max_filesize)}
            ${renderLimit('Post max', res.limits.post_max_size)}
            ${renderLimit('Max vars', res.limits.max_input_vars)}

        </div>
    </div>

    <hr>

    <!-- EXTENSÕES -->
    <div>
        <h6 class="mb-2">Extensões</h6>
        <div class="row g-2">
    `;

    $.each(res.extensions, function (ext, enabled) {
        html += `
        <div class="col-md-3">
            <div class="p-2 border rounded-3 d-flex justify-content-between align-items-center">
                <span class="small">${ext}</span>
                ${enabled
                    ? '<span class="badge bg-success">OK</span>'
                    : '<span class="badge bg-danger">OFF</span>'}
            </div>
        </div>`;
    });

    html += `</div></div>`;

    $('#phpInfoBox').html(html);
})

.fail(function () {
    $('#phpInfoBox').html('<span class="text-danger">Erro ao carregar informações do servidor</span>');
});

// helper
function renderLimit(label, value){
    return `
    <div class="col-md-3">
        <div class="p-2 border rounded-3">
            <small class="text-muted">${label}</small>
            <div><b>${value}</b></div>
        </div>
    </div>`;
}
</script>
@endsection
