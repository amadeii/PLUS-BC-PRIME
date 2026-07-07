@extends('layouts.app', ['title' => 'Dashboard Fiscal'])

@section('css')
<link rel="stylesheet" type="text/css" href="/css/dashboard.css">
@endsection

@section('content')
<div class="card card-custom gutter-b">
    <div class="card-body">
        <div class="fiscal-page">

            <div class="fiscal-header">
                <div class="fiscal-header-left">
                    <div class="fiscal-icon"><i class="ri-dashboard-3-line"></i></div>
                    <div class="fiscal-title">
                        <h4>Dashboard Fiscal</h4>
                        <p>Resumo de NF-e e NFC-e por situação fiscal no período selecionado</p>
                    </div>
                </div>
            </div>

            <div class="fiscal-filter">
                {!!Form::open()->fill(request()->all())->get()!!}
                <div class="row g-3 align-items-end">
                    <div class="col-md-2 col-12">
                        {!!Form::date('start_date', 'Data inicial')->value($start_date)!!}
                    </div>
                    <div class="col-md-2 col-12">
                        {!!Form::date('end_date', 'Data final')->value($end_date)!!}
                    </div>
                    <div class="col-lg-3 col-md-4 col-12 d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
                        <a href="{{ route('dashboard-fiscal.index') }}" class="btn btn-danger"><i class="ri-eraser-fill"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>

            @php
            $statusList = [
            'novo' => ['label' => 'Novas', 'icon' => 'ri-file-add-line', 'class' => 'fiscal-new'],
            'aprovado' => ['label' => 'Aprovadas', 'icon' => 'ri-checkbox-circle-line', 'class' => 'fiscal-approved'],
            'cancelado' => ['label' => 'Canceladas', 'icon' => 'ri-close-circle-line', 'class' => 'fiscal-canceled'],
            'rejeitado' => ['label' => 'Rejeitadas', 'icon' => 'ri-alert-line', 'class' => 'fiscal-rejected'],
            ];
            @endphp

            @foreach(['nfe' => 'NF-e', 'nfce' => 'NFC-e'] as $tipo => $titulo)
            <div class="fiscal-section">
                <div class="fiscal-section-head">
                    <div class="fiscal-section-title">
                        <span><i class="{{ $tipo == 'nfe' ? 'ri-file-paper-2-line' : 'ri-printer-cloud-line' }}"></i></span>
                        <div>
                            <h5>{{ $titulo }}</h5>
                            <small>Período: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</small>
                        </div>
                    </div>
                </div>

                <div class="fiscal-grid">
                    @foreach($statusList as $estado => $status)
                    <div class="fiscal-card {{ $status['class'] }}" data-bs-toggle="modal" data-bs-target="#modal_{{ $tipo }}_{{ $estado }}">
                        <div class="fiscal-card-icon"><i class="{{ $status['icon'] }}"></i></div>
                        <div class="fiscal-card-info">
                            <strong>{{ $resumo[$tipo][$estado]['qtd'] ?? 0 }}</strong>
                            <span>{{ $status['label'] }}</span>
                            <small>R$ {{ __moeda($resumo[$tipo][$estado]['valor'] ?? 0) }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>

@foreach(['nfe' => 'NF-e', 'nfce' => 'NFC-e'] as $tipo => $titulo)
@foreach($statusList as $estado => $status)
<div class="modal fade modal-fiscal" id="modal_{{ $tipo }}_{{ $estado }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            @php
            $headerClass = match($estado){
                'aprovado' => 'modal-header-success',
                'cancelado' => 'modal-header-danger',
                'rejeitado' => 'modal-header-warning',
                default => 'modal-header-primary'
            };
            @endphp

            <div class="modal-header {{ $headerClass }}">
                <h5 class="modal-title">
                    @if($estado == 'aprovado')
                    <i class="ri-checkbox-circle-fill me-2"></i>
                    @elseif($estado == 'cancelado')
                    <i class="ri-close-circle-fill me-2"></i>
                    @elseif($estado == 'rejeitado')
                    <i class="ri-error-warning-fill me-2"></i>
                    @else
                    <i class="ri-file-list-3-fill me-2"></i>
                    @endif

                    {{ $titulo }} - {{ $status['label'] }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <strong>Total:</strong> {{ $resumo[$tipo][$estado]['qtd'] ?? 0 }} documentos |
                    <strong>Valor:</strong> R$ {{ __moeda($resumo[$tipo][$estado]['valor'] ?? 0) }}
                </div>

                <div class="table-responsive">
                    <table class="table fiscal-modal-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Data</th>
                                <th>Estado</th>
                                <th>Motivo/Rejeição</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($resumo[$tipo][$estado]['items'] ?? []) as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->numero ?? $item->numero_sequencial ?? '--' }}</td>
                                <td>
                                    {{ $item->cliente->razao_social ?? $item->cliente_nome ?? 'Consumidor Final' }}
                                    <br>
                                    <small class="text-muted">{{ $item->cliente->cpf_cnpj ?? $item->cliente_cpf_cnpj ?? '--' }}</small>
                                </td>
                                <td class="text-success">R$ {{ __moeda($item->total) }}</td>
                                <td>{{ __data_pt($item->created_at, 0) }} {{ $item->created_at->format('H:i') }}</td>
                                <td><span class="fiscal-status {{ $item->estado }}">{{ ucfirst($item->estado) }}</span></td>
                                <td>{{ $item->motivo_rejeicao ?? '--' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">Nenhum documento encontrado nesse período</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endforeach
@endsection