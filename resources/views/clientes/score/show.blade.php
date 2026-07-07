@extends('layouts.app', ['title' => 'Score do Cliente'])

@section('content')
<div class="mt-1 print">
    <div class="row">

        <div class="card">
            <div class="card-body">

                <div style="text-align: right; margin-top: 0px;">
                    <a href="{{ url()->previous() }}" class="btn btn-danger btn-sm px-3">
                        <i class="ri-arrow-left-double-fill"></i>Voltar
                    </a>
                </div>

                <div class="row mb-3 mt-2">
                    <div class="col-md-8">
                        <h4 class="mb-0">
                            <i class="ri-user-star-line me-1"></i>
                            {{ $cliente->info }}
                        </h4>
                        
                    </div>

                    <div class="col-md-4 text-end">
                        <i class="ri-medal-fill" style="color: {{ $cliente->colorScore() }}; font-size: 28px;"></i>
                        {{ ucfirst($score->categoria) }}
                    </div>
                </div>

                {{-- Score geral --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Score Total</h6>
                                <h1 class="fw-bold mb-0">{{ number_format($score->score_total, 0) }}</h1>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Limite de Crédito</h6>
                                <h3 class="fw-bold text-success">
                                    R$ {{ __moeda($score->limite_credito) }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h6 class="text-muted">Última Atualização</h6>
                                <h3 class="fw-bold text-danger">
                                    {{ __data_pt($score->updated_at) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detalhamento --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <strong>Composição do Score</strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Pagamentos</td>
                                            <td class="text-end">{{ number_format($score->score_pagamentos, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Volume de Compras</td>
                                            <td class="text-end">{{ number_format($score->score_volume, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Tempo de Relacionamento</td>
                                            <td class="text-end">{{ number_format($score->score_tempo, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Ticket Médio</td>
                                            <td class="text-end">{{ number_format($score->score_ticket, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-danger">Penalidades</td>
                                            <td class="text-end">-{{ number_format($score->score_penalidades, 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between">
                                <strong>Histórico Mensal</strong>
                                <small class="text-muted">Últimos registros</small>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Score</th>
                                            <th>Categoria</th>
                                            <th>Limite</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($historico as $item)
                                        <tr>
                                            <td>{{ __data_pt($item->referencia_mes, 0) }}</td>
                                            <td>{{ number_format($item->score_total, 1) }}</td>
                                            <td>{{ ucfirst($item->categoria) }}</td>
                                            <td>
                                                R$ {{ __moeda($item->limite_credito) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                Nenhum histórico disponível
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endsection