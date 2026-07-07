@extends('layouts.app', ['title' => 'Detalhe do Ajuste'])

@section('content')
<div class="mt-2">
    <div class="row">
        <div class="col-12">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0">Ajuste de Registro</h4>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}
                            </small>
                        </div>

                        <div>
                            <span class="badge bg-dark fs-6 px-3 py-2">
                                Ajuste realizado
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="p-3 rounded bg-light">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Funcionário</strong><br>
                                    {{ $item->registro->funcionario->nome ?? '--' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Usuário</strong><br>
                                    {{ $item->usuario->name ?? '--' }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Motivo</strong><br>
                                    {{ $item->motivo }}
                                </div>
                            </div>

                            @if($item->justificativa)
                            <div class="mt-3">
                                <strong>Justificativa</strong><br>
                                {{ $item->justificativa }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="mb-3 text-danger">Antes</h5>

                                    <div class="mb-2">
                                        <small class="text-muted">Tipo</small><br>
                                        {{ $item->antes_json['tipo'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Data/Hora</small><br>
                                        @if(!empty($item->antes_json['data_hora']))
                                        {{ \Carbon\Carbon::parse($item->antes_json['data_hora'])->format('d/m/Y H:i:s') }}
                                        @else
                                        --
                                        @endif
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Status</small><br>
                                        {{ $item->antes_json['status'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">IP</small><br>
                                        {{ $item->antes_json['ip'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Dispositivo</small><br>
                                        {{ $item->antes_json['device_id'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Latitude</small><br>
                                        {{ $item->antes_json['latitude'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Longitude</small><br>
                                        {{ $item->antes_json['longitude'] ?? '--' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="mb-3 text-success">Depois</h5>

                                    <div class="mb-2">
                                        <small class="text-muted">Tipo</small><br>
                                        {{ $item->depois_json['tipo'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Data/Hora</small><br>
                                        @if(!empty($item->depois_json['data_hora']))
                                        {{ \Carbon\Carbon::parse($item->depois_json['data_hora'])->format('d/m/Y H:i:s') }}
                                        @else
                                        --
                                        @endif
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Status</small><br>
                                        {{ $item->depois_json['status'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">IP</small><br>
                                        {{ $item->depois_json['ip'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Dispositivo</small><br>
                                        {{ $item->depois_json['device_id'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Latitude</small><br>
                                        {{ $item->depois_json['latitude'] ?? '--' }}
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">Longitude</small><br>
                                        {{ $item->depois_json['longitude'] ?? '--' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('ponto-ajuste.index') }}" class="btn btn-outline-primary">
                            <i class="ri-arrow-left-line"></i> Voltar
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection