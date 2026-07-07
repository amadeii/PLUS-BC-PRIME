@extends('layouts.app', ['title' => 'Central de Faturamento NF-e'])
@section('css')
<link rel="stylesheet" type="text/css" href="/css/fatura_nfe.css">
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="fat-page">

            <div class="fat-header">
                <div class="fat-title">
                    <h4>Central de Faturamento (NF-e)</h4>
                    <p>Pedidos aprovados financeiramente, prontos para faturamento.</p>
                </div>
                <div class="fat-actions">

                    @if(request('start_date') && request('end_date') && in_array(request('estado'), ['aprovado', 'cancelado']))
                    <a href="{{ route('faturamento-nfe.download-xml', request()->all()) }}" class="btn-fat-purple">
                        <i class="ri-download-cloud-2-line me-1"></i> Baixar XMLs do período
                    </a>
                    @endif

                    <a href="{{ route('faturamento-nfe.download-xml', ['start_date' => now()->startOfMonth()->format('Y-m-d'),'end_date' => now()->endOfMonth()->format('Y-m-d'),'estado' => 'aprovado']) }}" class="btn-fat-outline">
                        <i class="ri-download-line me-1"></i> XMLs do mês
                    </a>
                    <a href="{{ route('faturamento-nfe.lote') }}" class="btn-fat-green"><i class="ri-add-line me-1"></i> Novo Faturamento em Lote</a>
                </div>
            </div>

            <div class="fat-kpis">
                <div class="fat-kpi fat-blue">
                    <div class="fat-kpi-icon">
                        <i class="ri-file-list-3-fill"></i>
                    </div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['pendentes'] }}</strong>
                        <span>
                            Pedidos Pendentes
                            <div class="badge bg-warning">NOVO</div>
                        </span>
                        <small>R$ {{ __moeda($resumo['pendentes_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-green">
                    <div class="fat-kpi-icon">
                        <i class="ri-check-fill"></i>
                    </div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['prontos'] }}</strong>
                        <span>Prontos para Faturar</span>
                        <small>R$ {{ __moeda($resumo['prontos_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-purple">
                    <div class="fat-kpi-icon">
                        <i class="ri-file-paper-2-fill"></i>
                    </div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['emitidas_hoje'] }}</strong>
                        <span>NFes Emitidas Hoje</span>
                        <small>R$ {{ __moeda($resumo['emitidas_hoje_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-red">
                    <div class="fat-kpi-icon">
                        <i class="ri-close-fill"></i>
                    </div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['canceladas'] }}</strong>
                        <span>NFes Canceladas</span>
                        <small>R$ {{ __moeda($resumo['canceladas_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-cyan">
                    <div class="fat-kpi-icon">
                        <i class="ri-money-dollar-circle-fill"></i>
                    </div>
                    <div class="fat-kpi-info">
                        <strong>R$ {{ __moeda($resumo['valor_hoje']) }}</strong>
                        <span>Valor Faturado Hoje</span>
                    </div>
                </div>

                <div class="fat-kpi fat-yellow">
                    <div class="fat-kpi-icon">
                        <i class="ri-hourglass-fill"></i>
                    </div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['aguardando_sefaz'] }}</strong>
                        <span>Aguardando SEFAZ</span>
                        <small>R$ {{ __moeda($resumo['aguardando_sefaz_valor']) }}</small>
                    </div>
                </div>
            </div>

            <div class="fat-filter">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row g-3">
                    <div class="col-md-3">
                        {!!Form::select('cliente_id', 'Cliente')
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::select('estado_fatura', 'Estado Faturamento',
                        [
                        '' => 'Todos',
                        'pendente' => 'Pendentes',
                        'aprovado' => 'Aprovadas',
                        'finalizado' => 'Finalizados'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    <div class="col-md-1">
                        {!!Form::select('estado', 'Situação Fiscal',
                        [
                        '' => 'Todos',
                        'novo' => 'Novas',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-2 d-flex align-items-end mb-1">
                        <button class="btn btn-primary" type="submit">
                            <i class="ri-search-line"></i> Pesquisar
                        </button>
                        <a href="{{ route('faturamento-nfe.index') }}" class="btn btn-outline">
                            <i class="ri-eraser-fill"></i> Limpar
                        </a>
                    </div>
                </div>
                {!!Form::close()!!}

            </div>

            <div class="fat-panel">
                <div class="fat-tabs">

                    <a href="{{ route('faturamento-nfe.index', ['tab' => 'pendentes', 'estado_fatura' => 'pendente']) }}"
                        class="fat-tab {{ request('tab', 'pendentes') == 'pendentes' ? 'active' : '' }}">

                        <i class="ri-upload-cloud-line"></i>

                        Pendentes de Faturamento

                        <span class="count">
                            {{ $resumo['faturamento_pendente'] }}
                        </span>
                    </a>

                    <a href="{{ route('faturamento-nfe.index', ['tab' => 'emitidas', 'estado' => 'aprovado']) }}"
                        class="fat-tab {{ request('tab') == 'emitidas' ? 'active' : '' }}">

                        <i class="ri-file-list-3-line"></i>

                        NFes Emitidas

                        <span class="count">
                            {{ $resumo['emitidas_total'] }}
                        </span>
                    </a>

                    <a href="{{ route('faturamento-nfe.index', ['tab' => 'rejeitadas', 'estado' => 'rejeitado']) }}"
                        class="fat-tab {{ request('tab') == 'rejeitadas' ? 'active' : '' }}">

                        <i class="ri-alert-line"></i>

                        Rejeições SEFAZ

                        <span class="count">
                            {{ $resumo['rejeitadas'] }}
                        </span>
                    </a>

                    <a href="{{ route('faturamento-nfe.index', ['tab' => 'canceladas', 'estado' => 'cancelado']) }}"
                        class="fat-tab {{ request('tab') == 'canceladas' ? 'active' : '' }}">

                        <i class="ri-close-circle-line"></i>

                        Canceladas

                        <span class="count">
                            {{ $resumo['canceladas'] }}
                        </span>
                    </a>

                    <a href="{{ route('faturamento-nfe.index', ['tab' => 'cce', 'cce' => 1]) }}"
                        class="fat-tab {{ request('tab') == 'cce' ? 'active' : '' }}">

                        <i class="ri-file-copy-2-line"></i>

                        Carta de Correção

                        <span class="count">
                            {{ $resumo['cartas_correcao'] }}
                        </span>
                    </a>

                </div>

                <div class="table-responsive">
                    <div class="tabela-scroll" style="overflow-x:auto;">

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Data Pedido</th>
                                    <th>Fluxo do Faturamento</th>
                                    <th>Situação Fiscal</th>
                                    <th>Próxima Ação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                <tr>

                                    <td>
                                        @if($item->estado == 'novo')
                                        <span class="fat-new">NOVO</span>

                                        @elseif($item->estado == 'aprovado')
                                        <i class="ri-checkbox-circle-fill text-success fs-4"></i>

                                        @elseif($item->estado == 'rejeitado')
                                        <i class="ri-error-warning-fill text-warning fs-4"></i>

                                        @elseif($item->estado == 'cancelado')
                                        <i class="ri-close-circle-fill text-danger fs-4"></i>
                                        @endif
                                    </td>

                                    <td>
                                        <strong>{{ $item->numero_sequencial ?? $item->id }}</strong>
                                    </td>

                                    <td>
                                        {{ $item->cliente->razao_social ?? 'Consumidor Final' }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $item->cliente->cpf_cnpj ?? '--' }}
                                        </small>
                                    </td>

                                    <td class="valor text-success">
                                        R$ {{ __moeda($item->total) }}
                                    </td>

                                    <td>
                                        {{ __data_pt($item->created_at, 0) }}
                                        <br>
                                        <strong class="text-primary">{{ $item->created_at->format('H:i') }}</strong>
                                    </td>

                                    <td>
                                        <div class="flow">

                                            <div class="flow-step">
                                                <div class="flow-dot flow-blue">
                                                    <i class="ri-file-list-line"></i>
                                                </div>

                                                <span>Pedido</span>

                                                <small>
                                                    {{ $item->created_at->format('d/m H:i') }}
                                                </small>
                                            </div>

                                            <div class="flow-step">
                                                <div class="flow-dot flow-purple">
                                                    <i class="ri-file-paper-line"></i>
                                                </div>

                                                <span>Fiscal</span>

                                                <small>
                                                    {{ $item->data_emissao ? \Carbon\Carbon::parse($item->data_emissao)->format('d/m H:i') : '--' }}
                                                </small>
                                            </div>

                                            <div class="flow-step">

                                                @if($item->estado == 'aprovado')

                                                <div class="flow-dot flow-green">
                                                    <i class="ri-check-line"></i>
                                                </div>

                                                @elseif($item->estado == 'rejeitado')

                                                <div class="flow-dot flow-red">
                                                    <i class="ri-close-line"></i>
                                                </div>

                                                @else

                                                <div class="flow-dot flow-gray"></div>

                                                @endif

                                                <span>NF-e</span>

                                                <small>
                                                    {{ $item->estado == 'aprovado' ? 'Autorizada' : ($item->estado == 'rejeitado' ? 'Rejeitada' : '--') }}
                                                </small>
                                            </div>

                                            <div class="flow-step">

                                                @if($item->estado == 'aprovado')

                                                <div class="flow-dot flow-green">
                                                    <i class="ri-send-plane-line"></i>
                                                </div>

                                                @else

                                                <div class="flow-dot flow-gray">
                                                    <i class="ri-send-plane-line"></i>
                                                </div>

                                                @endif

                                                <span>Enviado</span>

                                                <small>
                                                    {{ $item->estado == 'aprovado' ? 'SEFAZ OK' : '--' }}
                                                </small>
                                            </div>

                                        </div>
                                    </td>

                                    <td>

                                        @if($item->estado == 'novo')

                                        <span class="status-pill status-pendente">
                                            Pendente
                                        </span>

                                        <span class="fat-sub">
                                            Financeiro Aprovado
                                        </span>

                                        @elseif($item->estado == 'aprovado')

                                        <span class="status-pill status-ok">
                                            Autorizada
                                        </span>

                                        <span class="fat-sub">
                                            NF-e: {{ $item->numero }}
                                        </span>

                                        @elseif($item->estado == 'rejeitado')

                                        <button type="button" class="status-pill bg-outline-warning btn-ver-rejeicao" data-motivo="{{ $item->motivo_rejeicao ?? 'Erro SEFAZ' }}" data-numero="{{ $item->numero }}">
                                            Rejeitada
                                        </button>

                                        @elseif($item->estado == 'cancelado')

                                        <span class="status-pill bg-outline-danger">
                                            Cancelada
                                        </span>

                                        @endif

                                    </td>

                                    <td>

                                        @if($item->estado == 'novo' && $item->estado_fatura == 'pendente')

                                        <button type="button" class="btn-fat-success" data-bs-toggle="modal" data-bs-target="#modalFaturar{{ $item->id }}">
                                            Faturar Pedido
                                        </button>

                                        @elseif($item->estado == 'aprovado')

                                        <button onclick="imprimir('{{$item->id}}', '{{$item->numero}}')" class="btn-fat-outline">
                                            Imprimir DANFE
                                        </button>

                                        @elseif($item->estado == 'rejeitado')

                                        <a href="{{ route('nfe.edit', $item->id) }}"
                                            class="fat-drop-item">

                                            <i class="ri-edit-2-line"></i>

                                            <span>
                                                <strong>Editar Venda</strong>
                                                <small>Corrigir dados da NF-e</small>
                                            </span>

                                        </a>

                                        <button type="button" class="fat-drop-item btn-ver-rejeicao" data-motivo="{{ $item->motivo_rejeicao ?? 'Erro SEFAZ' }}" data-numero="{{ $item->numero }}">

                                            <i class="ri-error-warning-line"></i>

                                            <span>
                                                <strong>Ver Rejeição</strong>
                                                <small>Motivo da SEFAZ</small>
                                            </span>

                                        </button>


                                        @else

                                        @if($item->estado_fatura == 'aprovado')
                                        <a href="{{ route('faturamento-nfe.show', $item->id) }}" class="fat-drop-item">
                                            <i class="ri-eye-line"></i>
                                            <span>
                                                <strong>Ver Fatura</strong>
                                                <small>Visualizar detalhes</small>
                                            </span>
                                        </a>
                                        @else
                                        --
                                        @endif

                                        @endif

                                    </td>

                                    <td class="fat-actions-col">
                                        <div class="fat-drop" data-fat-drop>
                                            <button type="button" class="fat-drop-btn" data-fat-toggle="fatMenu{{ $item->id }}">
                                                <i class="ri-more-2-fill"></i>
                                            </button>

                                            <div class="fat-drop-menu" id="fatMenu{{ $item->id }}">
                                                @if($item->estado_fatura == 'pendente')
                                                <a href="#" class="fat-drop-item" data-bs-toggle="modal" data-bs-target="#modalFaturar{{ $item->id }}">
                                                    <i class="ri-money-dollar-circle-line"></i>
                                                    <span>
                                                        <strong>Gerar Fatura</strong>
                                                        <small>Criar faturamento</small>
                                                    </span>
                                                </a>
                                                @endif

                                                @if($item->estado_fatura == 'aprovado')
                                                <a href="{{ route('faturamento-nfe.show', $item->id) }}" class="fat-drop-item">
                                                    <i class="ri-eye-line"></i>
                                                    <span>
                                                        <strong>Ver Fatura</strong>
                                                        <small>Visualizar detalhes</small>
                                                    </span>
                                                </a>
                                                @endif

                                                @if($item->estado == 'novo')
                                                <button type="button" class="fat-drop-item btn-transmitir-nfe" data-id="{{ $item->id }}">
                                                    <i class="ri-send-plane-line"></i>
                                                    <span>
                                                        <strong>Transmitir NF-e</strong>
                                                        <small>Enviar para SEFAZ</small>
                                                    </span>
                                                </button>
                                                @endif

                                                @if($item->estado == 'aprovado')
                                                <a href="{{ route('nfe.imprimir', $item->id) }}" target="_blank" class="fat-drop-item">
                                                    <i class="ri-printer-line"></i>
                                                    <span>
                                                        <strong>Imprimir DANFE</strong>
                                                        <small>Gerar impressão</small>
                                                    </span>
                                                </a>

                                                <button type="button" class="fat-drop-item btn-corrigir-nfe" data-id="{{ $item->id }}" data-numero="{{ $item->numero }}" data-serie="{{ $item->serie }}" data-data="{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}" data-cliente="{{ $item->cliente->razao_social ?? $item->cliente->nome }}" data-chave="{{ $item->chave }}">

                                                    <i class="ri-file-edit-line"></i>

                                                    <span>
                                                        <strong>Carta Correção</strong>
                                                        <small>Corrigir NF-e</small>
                                                    </span>

                                                </button>

                                                <button type="button" class="fat-drop-item danger btn-cancelar-nfe" data-id="{{ $item->id }}" data-numero="{{ $item->numero }}" data-serie="{{ $item->serie }}" data-data="{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}" data-cliente="{{ $item->cliente->razao_social ?? $item->cliente->nome }}" data-chave="{{ $item->chave }}">

                                                    <i class="ri-close-circle-line"></i>

                                                    <span>
                                                        <strong>Cancelar NF-e</strong>
                                                        <small>Cancelar documento</small>
                                                    </span>

                                                </button>
                                                @endif

                                                @if($item->estado == 'cancelado')
                                                <a href="{{ route('nfe.imprimir-cancela', $item->id) }}" target="_blank" class="fat-drop-item">
                                                    <i class="ri-printer-line"></i>
                                                    <span>
                                                        <strong>Imprimir Cancelamento</strong>
                                                        <small>Evento cancelamento</small>
                                                    </span>
                                                </a>
                                                @endif

                                                @if($item->sequencia_cce > 0)
                                                <a href="{{ route('nfe.imprimir-correcao', $item->id) }}" target="_blank" class="fat-drop-item">
                                                    <i class="ri-file-copy-2-line"></i>
                                                    <span>
                                                        <strong>Imprimir CC-e</strong>
                                                        <small>Carta de correção</small>
                                                    </span>
                                                </a>
                                                @endif

                                                <button type="button" class="fat-drop-item" data-bs-toggle="modal" data-bs-target="#modalVendaLote{{ $item->id }}">
                                                    <i class="ri-eye-line"></i>
                                                    <span>
                                                        <strong>Ver Venda</strong>
                                                        <small>Detalhes dos produtos</small>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                </tr>

                                @empty

                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        Nenhum faturamento encontrado
                                    </td>
                                </tr>

                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="button" id="scrollToggle2" class="scroll-btn-jidox hidden">
                    <i class="ri-arrow-right-circle-line"></i>
                </button>

                <div class="fat-footer">

                    <div>
                        Exibindo
                        {{ $items->firstItem() ?? 0 }}
                        até
                        {{ $items->lastItem() ?? 0 }}
                        de
                        {{ $items->total() }}
                        registros
                    </div>


                </div>

                <div class="fat-pagination m-1">
                    {{ $items->appends(request()->all())->links() }}
                </div>
            </div>

            @foreach($items as $item)
            @include('faturamento_nfe.partials.modal_faturar', ['item' => $item])
            @include('faturamento_nfe.partials.modal_detalhes_venda', ['item' => $item, 'nao_selecionar' => true])
            @endforeach
        </div>
    </div>
</div>
@include('faturamento_nfe.partials.modal_cancelar')
@include('faturamento_nfe.partials.modal_corrigir')

<div class="modal fade" id="modal-print" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Imprimir NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-lg-4">
                        <button type="button" class="btn btn-success w-100" onclick="gerarDanfe('danfe')">
                            <i class="ri-printer-line"></i>
                            DANFE
                        </button>
                    </div>

                    <div class="col-12 col-lg-4">
                        <button type="button" class="btn btn-primary w-100" onclick="gerarDanfe('simples')">
                            <i class="ri-printer-line"></i>
                            DANFE Simples
                        </button>
                    </div>

                    <div class="col-12 col-lg-4">
                        <button type="button" class="btn btn-dark w-100" onclick="gerarDanfe('etiqueta')">
                            <i class="ri-printer-line"></i>
                            DANFE Etiqueta
                        </button>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('js')
<script type="text/javascript" src="/js/fatura_nfe.js"></script>
@endsection