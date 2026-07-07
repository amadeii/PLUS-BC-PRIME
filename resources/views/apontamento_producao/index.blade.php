@extends('layouts.app', ['title' => 'Apontamento de Produção'])

@section('css')
<style>
    .table td{ vertical-align:middle; }
    .badge{ font-size:11px; padding:6px 10px; }
    .progress{ background:#e9ecef; border-radius:10px; height:8px; }
    .progress-bar{ border-radius:10px; }
    .ap-panel-card{ border:1px solid #eef0f4; border-radius:14px; padding:16px; background:#fff; box-shadow:0 4px 14px rgba(0,0,0,.035); height:100%; }
    .ap-panel-card .label{ font-size:12px; color:#6c757d; display:block; margin-bottom:5px; }
    .ap-panel-card strong{ font-size:22px; line-height:1; }
    .op-code{ font-size:18px; font-weight:700; color:#212529; }
    .op-sub{ font-size:12px; color:#6c757d; }
    .produto-box strong{ display:block; color:#212529; }
    .produto-box small{ color:#6c757d; }
    .operacao-pill{ display:inline-flex; align-items:center; gap:5px; border-radius:20px; padding:5px 9px; font-size:11px; font-weight:600; margin:2px; background:#f5f7fb; color:#495057; }
    .operacao-pill.done{ background:#e8f7ef; color:#198754; }
    .operacao-pill.partial{ background:#fff6df; color:#b7791f; }
    .operacao-pill.pending{ background:#f1f3f5; color:#6c757d; }
    .ap-status-card{ border-radius:12px; padding:10px 12px; background:#f8f9fa; min-width:105px; }
    .ap-status-card span{ display:block; font-size:11px; color:#6c757d; }
    .ap-status-card strong{ font-size:16px; }
</style>
@endsection

@section('content')
<div class="mt-1">

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Apontamento de Produção</h4>
                    <span class="text-muted">Selecione uma ordem em produção para registrar operações, tempos, produção e refugo</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light-primary text-primary px-3 py-2">{{ $data->total() }} OPs disponíveis</span>
                    <a href="{{ route('ordem-producao.index') }}" class="btn btn-dark"><i class="ri-arrow-left-line"></i> Ordens</a>
                </div>
            </div>

            @php
            $totalOps = $data->total();
            $emProducao = $data->where('estado', 'producao')->count();
            $parciais = $data->where('estado', 'parcial')->count();
            $finalizadas = $data->where('estado', 'finalizada')->count();
            $apontamentos = $data->sum(function($op){ return $op->apontamentos ? $op->apontamentos->count() : 0; });
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="ap-panel-card">
                        <span class="label">OPs disponíveis</span>
                        <strong class="text-primary">{{ $totalOps }}</strong>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="ap-panel-card">
                        <span class="label">Em produção</span>
                        <strong class="text-info">{{ $emProducao }}</strong>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="ap-panel-card">
                        <span class="label">Parciais</span>
                        <strong class="text-warning">{{ $parciais }}</strong>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="ap-panel-card">
                        <span class="label">Apontamentos feitos</span>
                        <strong class="text-success">{{ $apontamentos }}</strong>
                    </div>
                </div>
            </div>

            <hr>

            {!! Form::open()->fill(request()->all())->get() !!}

            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-2">
                    {!! Form::text('codigo', 'Código OP')->attrs(['placeholder' => 'Ex: 1024']) !!}
                </div>

                <div class="col-md-4">
                    {!! Form::text('produto', 'Produto')->attrs(['placeholder' => 'Buscar produto']) !!}
                </div>

                <div class="col-md-2">
                    {!! Form::select('estado', 'Estado', ['' => 'Todos'] + \App\Models\OrdemProducao::estados())
                    ->attrs(['class' => 'form-select']) !!}
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary"><i class="ri-search-line"></i> Pesquisar</button>
                    <a href="{{ route('apontamento-producao.index') }}" class="btn btn-danger"><i class="ri-eraser-fill"></i> Limpar</a>
                </div>
            </div>

            {!! Form::close() !!}

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ordem</th>
                            <th>Data</th>
                            <th>Produto / Cliente</th>
                            <th>Operações</th>
                            <th>Produção</th>
                            <th>Refugo</th>
                            <th>Progresso</th>
                            <th>Status</th>
                            <th width="170">Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $item)

                        @php
                        $produtoNome = optional(optional($item->itens->first())->produto)->nome ?? '-';
                        $clienteNome = optional(optional($item->itens->first())->cliente)->razao_social ?? optional(optional($item->itens->first())->cliente)->nome_fantasia ?? 'Não informado';

                        $planejado = (float) $item->itens->sum('quantidade');
                        $produzido = (float) ($item->quantidade_produzida ?? 0);
                        $refugo = (float) ($item->quantidade_refugada ?? 0);
                        $percentual = (float) ($item->percentual_progresso ?? 0);

                        $operacoesTotal = $item->operacoes ? $item->operacoes->count() : 0;
                        $operacoesFinalizadas = $item->operacoes ? $item->operacoes->where('status', 'finalizada')->count() : 0;
                        $operacoesParciais = $item->operacoes ? $item->operacoes->where('status', 'parcial')->count() : 0;
                        $totalApontamentos = $item->apontamentos ? $item->apontamentos->count() : 0;
                        @endphp

                        <tr>
                            <td data-label="Ordem">
                                <div class="op-code">#{{ $item->codigo_sequencial }}</div>
                                <div class="op-sub">OP {{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td data-label="Data">
                                <div class="fw-bold">
                                    {{ __data_pt($item->created_at) }}
                                </div>

                                @if($item->data_prevista_entrega)
                                <small class="text-muted d-block">
                                    Entrega: {{ __data_pt($item->data_prevista_entrega) }}
                                </small>
                                @endif
                            </td>

                            <td data-label="Produto / Cliente">
                                <div class="produto-box">
                                    <strong>{{ $produtoNome }}</strong>
                                    <small>{{ $clienteNome }}</small>
                                </div>
                            </td>

                            <td data-label="Operações">
                                <div class="mb-1">
                                    <span class="operacao-pill done">
                                        <i class="ri-check-line"></i> {{ $operacoesFinalizadas }} finalizadas
                                    </span>

                                    <span class="operacao-pill partial">
                                        <i class="ri-loader-2-line"></i> {{ $operacoesParciais }} parciais
                                    </span>

                                    <span class="operacao-pill pending">
                                        <i class="ri-list-check"></i> {{ $operacoesTotal }} total
                                    </span>
                                </div>

                                <small class="text-muted">
                                    {{ $totalApontamentos }} apontamento(s) registrado(s)
                                </small>
                            </td>

                            <td data-label="Produção">
                                <div class="ap-status-card">
                                    <span>Produzido</span>
                                    <strong class="text-success">{{ number_format($produzido, 3, ',', '.') }}</strong>
                                </div>
                                <small class="text-muted">Planejado: {{ number_format($planejado, 3, ',', '.') }}</small>
                            </td>

                            <td data-label="Refugo">
                                <div class="ap-status-card">
                                    <span>Refugado</span>
                                    <strong class="{{ $refugo > 0 ? 'text-danger' : 'text-muted' }}">
                                        {{ number_format($refugo, 3, ',', '.') }}
                                    </strong>
                                </div>
                            </td>

                            <td data-label="Progresso">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="min-width:110px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $percentual }}%"></div>
                                    </div>

                                    <span class="fw-bold text-primary">
                                        {{ number_format($percentual, 1, ',', '.') }}%
                                    </span>
                                </div>
                            </td>

                            <td data-label="Status">
                                @if($item->estado == 'producao')
                                <span class="badge bg-primary">Produção</span>
                                @elseif($item->estado == 'parcial')
                                <span class="badge bg-warning text-dark">Parcial</span>
                                @else
                                <span class="badge bg-success">Finalizada</span>
                                @endif
                            </td>

                            <td data-label="Ação">
                                <a href="{{ route('apontamento-producao.show', $item->id) }}" class="btn btn-info btn-sm">
                                    <i class="ri-play-circle-line"></i> Apontar
                                </a>

                                <a href="{{ route('ordem-producao.show', $item->id) }}" class="btn btn-dark btn-sm">
                                    <i class="ri-file-text-line"></i>
                                </a>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ri-inbox-line" style="font-size:42px;"></i>
                                    <div class="mt-2">Nenhuma ordem disponível para apontamento</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {!! $data->appends(request()->all())->links() !!}
            </div>

        </div>
    </div>

</div>
@endsection