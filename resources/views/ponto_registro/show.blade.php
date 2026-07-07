@extends('layouts.app', ['title' => 'Detalhe do Registro'])

@section('content')
<div class="mt-2">

    <div class="row">
        <div class="col-12">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    {{-- HEADER --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0">Registro de Ponto</h4>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($item->data_hora)->format('d/m/Y H:i:s') }}
                            </small>
                        </div>

                        <div>
                            @php
                            $cores = [
                                'entrada' => 'success',
                                'intervalo_inicio' => 'warning',
                                'intervalo_fim' => 'info',
                                'saida' => 'danger'
                            ];

                            $nomes = [
                                'entrada' => 'Entrada',
                                'intervalo_inicio' => 'Início Intervalo',
                                'intervalo_fim' => 'Fim Intervalo',
                                'saida' => 'Saída'
                            ];
                            @endphp

                            <span class="badge bg-{{ $cores[$item->tipo] ?? 'secondary' }} fs-6 px-3 py-2">
                                {{ $nomes[$item->tipo] ?? $item->tipo }}
                            </span>
                        </div>
                    </div>

                    {{-- FUNCIONÁRIO --}}
                    <div class="mb-4">
                        <div class="p-3 rounded bg-light d-flex align-items-center">
                            <i class="ri-user-3-line fs-3 me-3 text-primary"></i>
                            <div>
                                <strong>Funcionário</strong><br>
                                <span class="fs-5">{{ $item->funcionario->nome ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- GRID PRINCIPAL --}}
                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted">Status</small><br>
                                @if($item->status == 'valido')
                                    <span class="badge bg-success mt-1">Válido</span>
                                @elseif($item->status == 'suspeito')
                                    <span class="badge bg-warning mt-1">Suspeito</span>
                                @else
                                    <span class="badge bg-dark mt-1">Ajustado</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted">IP</small><br>
                                <span>{{ $item->ip ?? '--' }}</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted">Dispositivo</small><br>
                                <span>{{ $item->device_id ?? '--' }}</span>
                            </div>
                        </div>

                    </div>

                    {{-- GEOLOCALIZAÇÃO --}}
                    <div class="row g-3 mt-2">

                        <div class="col-md-6">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted">Latitude</small><br>
                                <span>{{ $item->latitude ?? '--' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded h-100">
                                <small class="text-muted">Longitude</small><br>
                                <span>{{ $item->longitude ?? '--' }}</span>
                            </div>
                        </div>

                    </div>

                    {{-- HASH --}}
                    <div class="mt-4">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted">Hash de Integridade</small><br>
                            <small class="text-break">{{ $item->hash_integridade }}</small>
                        </div>
                    </div>

                    {{-- AÇÕES --}}
                    <div class="mt-4 text-end">
                        <a href="{{ route('ponto-registro.index') }}" class="btn btn-outline-primary">
                            <i class="ri-arrow-left-line"></i> Voltar
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection