@extends('layouts.app', ['title' => 'Configuração de Ponto'])

@section('css')

<style>
    .ponto-card {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .ponto-title {
        font-size: 20px;
        font-weight: 600;
        color: #5B5BD6;
    }

    .ponto-sub {
        color: #999;
        font-size: 13px;
    }

    .ponto-input {
        border-radius: 8px;
        height: 45px;
        border: 1px solid #e5e5e5;
        padding: 0 10px;
        transition: .2s;
    }

    .ponto-input:focus {
        border-color: #5B5BD6;
        box-shadow: 0 0 0 2px rgba(91,91,214,0.1);
    }

    .ponto-select {
        border-radius: 8px;
        height: 45px;
        border: 1px solid #e5e5e5;
    }

    .ponto-btn {
        background: #5B5BD6;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 500;
    }

    .ponto-btn:hover {
        background: #4a4ac7;
    }

    .mapa-box {
        width: 100%;
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        margin-top: 10px;
    }
</style>
@endsection
@section('content')

<div class="ponto-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="ponto-title">Configuração de Ponto</div>
            <div class="ponto-sub">Defina a localização permitida para registro de ponto</div>
        </div>
    </div>

    <form method="POST" action="{{ route('ponto-configuracao.store') }}">
        @csrf

        <input type="hidden" name="empresa_id" value="{{ request()->empresa_id }}">

        <div class="row">

            <div class="col-md-4">
                <label>Latitude</label>
                <input type="text" name="latitude" class="form-control"
                id="latitude"
                value="{{ old('latitude', $config->latitude) }}">
            </div>

            <div class="col-md-4">
                <label>Longitude</label>
                <input type="text" name="longitude" class="form-control"
                id="longitude"
                value="{{ old('longitude', $config->longitude) }}">
            </div>

            <div class="col-md-4">
                <label>Raio permitido (metros)</label>
                <input type="number" name="raio_permitido"
                class="form-control"
                value="{{ old('raio_permitido', $config->raio_permitido) }}">
            </div>

            <div class="col-md-6 mt-3">
                <label>Permitir fora da área</label>
                <select name="permitir_fora_area" class="form-select">
                    <option value="0" {{ $config->permitir_fora_area == 0 ? 'selected' : '' }}>Não</option>
                    <option value="1" {{ $config->permitir_fora_area == 1 ? 'selected' : '' }}>Sim</option>
                </select>
            </div>

            <div class="col-md-6 mt-3">
                <label>Exigir observação fora da área</label>
                <select name="exigir_observacao_fora_area" class="form-select">
                    <option value="0" {{ $config->exigir_observacao_fora_area == 0 ? 'selected' : '' }}>Não</option>
                    <option value="1" {{ $config->exigir_observacao_fora_area == 1 ? 'selected' : '' }}>Sim</option>
                </select>
            </div>

        </div>

        {{-- MAPA --}}
        <div class="mt-4">
            <label>Selecionar localização no mapa</label>
            <div id="map" class="mapa-box"></div>
        </div>

        <div class="mt-4 text-right">
            <button type="submit" class="ponto-btn">
                Salvar Configurações
            </button>
        </div>

    </form>

</div>
@endsection

@if($tokenMaps)
@section('js')
<script src="https://maps.googleapis.com/maps/api/js?key={{ $tokenMaps }}"></script>

<script>
    let map;
    let marker;

    function initMap() {
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        const latSalva = parseFloat(latInput.value);
        const lngSalva = parseFloat(lngInput.value);

        const temCoordenadaSalva = !isNaN(latSalva) && !isNaN(lngSalva);

        const posicaoInicial = temCoordenadaSalva
            ? { lat: latSalva, lng: lngSalva }
            : { lat: -23.55052, lng: -46.633308 };

        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: posicaoInicial
        });

        marker = new google.maps.Marker({
            position: posicaoInicial,
            map: map,
            draggable: true
        });

        map.addListener('click', function(e) {
            placeMarker(e.latLng);
        });

        marker.addListener('dragend', function(e) {
            updateInputs(e.latLng);
        });

        if (temCoordenadaSalva) {
            updateInputs(posicaoInicial);
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const minhaPosicao = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    map.setCenter(minhaPosicao);
                    marker.setPosition(minhaPosicao);
                    updateInputs(minhaPosicao);
                },
                function(error) {
                    console.log('Não foi possível obter a localização:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    }

    function placeMarker(location) {
        marker.setPosition(location);
        updateInputs(location);
    }

    function updateInputs(location) {
        const lat = typeof location.lat === 'function' ? location.lat() : location.lat;
        const lng = typeof location.lng === 'function' ? location.lng() : location.lng;

        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    window.addEventListener('load', initMap);
</script>

@endsection
@endif