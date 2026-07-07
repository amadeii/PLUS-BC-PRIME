<div class="row g-2">

    <div class="col-md-6">
        {!!Form::select('funcionario_id', 'Funcionário',
        ['' => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())
        ->attrs(['class' => 'select2'])
        ->required()
        ->id('func')
        !!}
    </div>

    <div class="col-md-6">
        <label class="form-label">Tipo será definido automaticamente</label>
        <input type="text" class="form-control" value="Automático (Entrada → Intervalo → Intervalo Fim → Saída)" disabled>
    </div>

    <input type="hidden" name="latitude" id="latitude">
    <input type="hidden" name="longitude" id="longitude">
    <input type="hidden" name="device_id" id="device_id">

    <div class="col-md-12">
        <small id="geo-status" class="text-muted">Obtendo localização...</small>
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">
            Registrar
        </button>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        let deviceId = localStorage.getItem('ponto_device_id');
        if (!deviceId) {
            deviceId = 'device_' + Math.random().toString(36).substring(2) + '_' + Date.now();
            localStorage.setItem('ponto_device_id', deviceId);
        }
        document.getElementById('device_id').value = deviceId;

        const geoStatus = document.getElementById('geo-status');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    geoStatus.innerHTML = 'Localização capturada com sucesso.';
                },
                function(error) {
                    geoStatus.innerHTML = 'Não foi possível obter a localização.';
                    console.log(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
                );
        } else {
            geoStatus.innerHTML = 'Geolocalização não suportada neste navegador.';
        }
    });
</script>