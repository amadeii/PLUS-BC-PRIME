@extends('layouts.app', ['title' => 'Central de Faturamento NFC-e'])
@section('css')
<link rel="stylesheet" type="text/css" href="/css/fatura_nfe.css">
<style type="text/css">
    .fat-kpis{ display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:16px; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="fat-page">

            <div class="fat-header">
                <div class="fat-title">
                    <h4>Central de Faturamento (NFC-e)</h4>
                    <p>Gerencie NFC-e novas, autorizadas, rejeitadas e canceladas.</p>
                </div>
                <div class="fat-actions">
                    @if(request('start_date') && request('end_date') && in_array(request('estado'), ['aprovado', 'cancelado']))
                    <a href="{{ route('faturamento-nfce.download-xml', request()->all()) }}" class="btn-fat-purple">
                        <i class="ri-download-cloud-2-line me-1"></i> Baixar XMLs do período
                    </a>
                    @endif

                    <a href="{{ route('faturamento-nfce.download-xml', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d'), 'estado' => 'aprovado']) }}" class="btn-fat-outline">
                        <i class="ri-download-line me-1"></i> XMLs do mês
                    </a>

                    <a href="{{ route('faturamento-nfce.lote') }}" class="btn-fat-green"><i class="ri-add-line me-1"></i> Emissão em lote</a>

                </div>
            </div>

            <div class="fat-kpis">
                <div class="fat-kpi fat-blue">
                    <div class="fat-kpi-icon"><i class="ri-file-list-3-fill"></i></div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['novas'] }}</strong>
                        <span>NFC-e Novas</span>
                        <small>R$ {{ __moeda($resumo['novas_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-purple">
                    <div class="fat-kpi-icon"><i class="ri-file-paper-2-fill"></i></div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['emitidas_hoje'] }}</strong>
                        <span>NFC-es Emitidas Hoje</span>
                        <small>R$ {{ __moeda($resumo['emitidas_hoje_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-red">
                    <div class="fat-kpi-icon"><i class="ri-close-fill"></i></div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['canceladas'] }}</strong>
                        <span>NFC-es Canceladas</span>
                        <small>R$ {{ __moeda($resumo['canceladas_valor']) }}</small>
                    </div>
                </div>

                <div class="fat-kpi fat-cyan">
                    <div class="fat-kpi-icon"><i class="ri-money-dollar-circle-fill"></i></div>
                    <div class="fat-kpi-info">
                        <strong>R$ {{ __moeda($resumo['valor_hoje']) }}</strong>
                        <span>Valor Emitido Hoje</span>
                    </div>
                </div>

                <div class="fat-kpi fat-yellow">
                    <div class="fat-kpi-icon"><i class="ri-alert-fill"></i></div>
                    <div class="fat-kpi-info">
                        <strong>{{ $resumo['rejeitadas'] }}</strong>
                        <span>Rejeitadas</span>
                        <small>R$ {{ __moeda($resumo['rejeitadas_valor']) }}</small>
                    </div>
                </div>
            </div>

            <div class="fat-filter">
                {!!Form::open()->fill(request()->all())->get()!!}
                <div class="row g-3">
                    <div class="col-md-3">
                        {!!Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2'])!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::select('estado', 'Situação Fiscal', [
                        '' => 'Todos',
                        'novo' => 'Novas',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas'
                        ])->attrs(['class' => 'form-select'])!!}
                    </div>

                    <div class="col-md-3 d-flex align-items-end mb-1">
                        <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
                        <a href="{{ route('faturamento-nfce.index') }}" class="btn btn-outline"><i class="ri-eraser-fill"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>

            <div class="fat-panel">
                <div class="fat-tabs">
                    <a href="{{ route('faturamento-nfce.index', ['tab' => 'novas', 'estado' => 'novo']) }}" class="fat-tab {{ request('tab', 'novas') == 'novas' ? 'active' : '' }}">
                        <i class="ri-upload-cloud-line"></i> Novas <span class="count">{{ $resumo['novas'] }}</span>
                    </a>

                    <a href="{{ route('faturamento-nfce.index', ['tab' => 'emitidas', 'estado' => 'aprovado']) }}" class="fat-tab {{ request('tab') == 'emitidas' ? 'active' : '' }}">
                        <i class="ri-file-list-3-line"></i> NFC-es Emitidas <span class="count">{{ $resumo['emitidas_total'] }}</span>
                    </a>

                    <a href="{{ route('faturamento-nfce.index', ['tab' => 'rejeitadas', 'estado' => 'rejeitado']) }}" class="fat-tab {{ request('tab') == 'rejeitadas' ? 'active' : '' }}">
                        <i class="ri-alert-line"></i> Rejeições SEFAZ <span class="count">{{ $resumo['rejeitadas'] }}</span>
                    </a>

                    <a href="{{ route('faturamento-nfce.index', ['tab' => 'canceladas', 'estado' => 'cancelado']) }}" class="fat-tab {{ request('tab') == 'canceladas' ? 'active' : '' }}">
                        <i class="ri-close-circle-line"></i> Canceladas <span class="count">{{ $resumo['canceladas'] }}</span>
                    </a>
                </div>

                <div class="table-responsive">
                    <div class="tabela-scroll" style="overflow-x:auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NFC-e</th>
                                    <th>Cliente</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th>Fluxo Fiscal</th>
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

                                    <td><strong>{{ $item->numero_sequencial ?? $item->numero ?? $item->id }}</strong></td>

                                    <td>
                                        {{ $item->cliente->razao_social ?? 'Consumidor Final' }}
                                        <br>
                                        <small class="text-muted">{{ $item->cliente->cpf_cnpj ?? '--' }}</small>
                                    </td>

                                    <td class="valor text-success">R$ {{ __moeda($item->total) }}</td>

                                    <td>
                                        {{ __data_pt($item->created_at, 0) }}
                                        <br>
                                        <strong class="text-primary">{{ $item->created_at->format('H:i') }}</strong>
                                    </td>

                                    <td>
                                        <div class="flow">
                                            <div class="flow-step">
                                                <div class="flow-dot flow-blue"><i class="ri-file-list-line"></i></div>
                                                <span>Venda</span>
                                                <small>{{ $item->created_at->format('d/m H:i') }}</small>
                                            </div>

                                            <div class="flow-step">
                                                <div class="flow-dot flow-purple"><i class="ri-file-paper-line"></i></div>
                                                <span>Emissão</span>
                                                <small>{{ $item->data_emissao ? \Carbon\Carbon::parse($item->data_emissao)->format('d/m H:i') : '--' }}</small>
                                            </div>

                                            <div class="flow-step">
                                                <div class="flow-dot {{ $item->estado == 'aprovado' ? 'flow-green' : ($item->estado == 'rejeitado' ? 'flow-red' : 'flow-gray') }}">
                                                    <i class="{{ $item->estado == 'aprovado' ? 'ri-check-line' : ($item->estado == 'rejeitado' ? 'ri-close-line' : 'ri-time-line') }}"></i>
                                                </div>
                                                <span>NFC-e</span>
                                                <small>{{ $item->estado == 'aprovado' ? 'Autorizada' : ($item->estado == 'rejeitado' ? 'Rejeitada' : '--') }}</small>
                                            </div>

                                            <div class="flow-step">
                                                <div class="flow-dot {{ $item->estado == 'aprovado' ? 'flow-green' : 'flow-gray' }}">
                                                    <i class="ri-send-plane-line"></i>
                                                </div>
                                                <span>SEFAZ</span>
                                                <small>{{ $item->estado == 'aprovado' ? 'OK' : '--' }}</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        @if($item->estado == 'novo')
                                        <span class="status-pill status-pendente">Pendente</span>
                                        <span class="fat-sub">Aguardando transmissão</span>
                                        @elseif($item->estado == 'aprovado')
                                        <span class="status-pill status-ok">Autorizada</span>
                                        <span class="fat-sub">NFC-e: {{ $item->numero }}</span>
                                        @elseif($item->estado == 'rejeitado')
                                        <button type="button" class="status-pill bg-outline-warning btn-ver-rejeicao" data-motivo="{{ $item->motivo_rejeicao ?? 'Erro SEFAZ' }}" data-numero="{{ $item->numero }}">Rejeitada</button>
                                        @elseif($item->estado == 'cancelado')
                                        <span class="status-pill bg-outline-danger">Cancelada</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item->estado == 'novo')
                                        <button type="button" class="btn-fat-success btn-transmitir-nfce" data-id="{{ $item->id }}">Transmitir NFC-e</button>
                                        @elseif($item->estado == 'aprovado')
                                        <a href="{{ route('nfce.imprimir', $item->id) }}" target="_blank" class="btn-fat-outline">Imprimir DANFCE</a>
                                        @elseif($item->estado == 'rejeitado')
                                        <a href="{{ route('nfce.edit', $item->id) }}" class="fat-drop-item">
                                            <i class="ri-edit-2-line"></i>
                                            <span><strong>Editar Venda</strong><small>Corrigir dados da NFC-e</small></span>
                                        </a>
                                        @else
                                        --
                                        @endif
                                    </td>

                                    <td class="fat-actions-col">
                                        <div class="fat-drop" data-fat-drop>
                                            <button type="button" class="fat-drop-btn" data-fat-toggle="fatMenu{{ $item->id }}">
                                                <i class="ri-more-2-fill"></i>
                                            </button>

                                            <div class="fat-drop-menu" id="fatMenu{{ $item->id }}">
                                                <a href="{{ route('nfce.show', $item->id) }}" class="fat-drop-item">
                                                    <i class="ri-eye-line"></i>
                                                    <span><strong>Ver Venda</strong><small>Detalhes da NFC-e</small></span>
                                                </a>

                                                @if($item->estado == 'novo')
                                                <button type="button" class="fat-drop-item btn-transmitir-nfce" data-id="{{ $item->id }}">
                                                    <i class="ri-send-plane-line"></i>
                                                    <span><strong>Transmitir NFC-e</strong><small>Enviar para SEFAZ</small></span>
                                                </button>
                                                @endif

                                                @if($item->estado == 'aprovado')
                                                <a href="{{ route('nfce.imprimir', $item->id) }}" target="_blank" class="fat-drop-item">
                                                    <i class="ri-printer-line"></i>
                                                    <span><strong>Imprimir DANFCE</strong><small>Gerar impressão</small></span>
                                                </a>

                                                <button type="button" class="fat-drop-item danger btn-cancelar-nfce" data-id="{{ $item->id }}" data-numero="{{ $item->numero }}" data-serie="{{ $item->serie }}" data-data="{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}" data-cliente="{{ $item->cliente->razao_social ?? $item->cliente->nome ?? 'Consumidor Final' }}" data-chave="{{ $item->chave }}">
                                                    <i class="ri-close-circle-line"></i>
                                                    <span><strong>Cancelar NFC-e</strong><small>Cancelar documento</small></span>
                                                </button>
                                                @endif


                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">Nenhuma NFC-e encontrada</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="fat-footer">
                    <div>Exibindo {{ $items->firstItem() ?? 0 }} até {{ $items->lastItem() ?? 0 }} de {{ $items->total() }} registros</div>
                </div>

                <div class="fat-pagination m-1">
                    {{ $items->appends(request()->all())->links() }}
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-danger text-white border-0 px-4 py-4">
                <div>
                    <h4 class="fw-bold mb-1 text-white">
                        Cancelar NFCe <strong class="ref-numero"></strong>
                    </h4>
                    <small class="text-white opacity-75">
                        O cancelamento da NFCe será transmitido para a SEFAZ
                    </small>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4 pt-4">

                <div class="alert alert-danger border-0 d-flex align-items-start mb-4">
                    <div class="me-3">
                        <i class="ri-alert-line fs-4"></i>
                    </div>

                    <div>
                        <div class="fw-semibold mb-1">
                            Atenção ao cancelar esta NFCe
                        </div>
                        <small>
                            Após o cancelamento autorizado pela SEFAZ, a nota ficará sem validade fiscal e não poderá ser utilizada novamente.
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    {!! Form::text('motivo-cancela', 'Motivo do Cancelamento')
                    ->required()
                    ->attrs([
                    'placeholder' => 'Informe um motivo claro e objetivo para o cancelamento',
                    'maxlength' => '255'
                    ])
                    !!}
                </div>

                <div class="alert alert-warning border-0 mb-0">
                    <div class="fw-bold mb-2">
                        <i class="ri-error-warning-line me-1"></i>
                        Importante
                    </div>

                    <ul class="mb-0 ps-3">
                        <li>O cancelamento deve respeitar o prazo permitido pela SEFAZ;</li>
                        <li>Após autorizado, o cancelamento não poderá ser revertido;</li>
                        <li>Informe um motivo claro e objetivo.</li>
                    </ul>
                </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    Fechar
                </button>

                <button type="button" id="btn-cancelar" class="btn btn-danger px-4">
                    <i class="ri-close-circle-line me-1"></i>
                    Transmitir Cancelamento
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="/js/fatura_nfce.js"></script>
@endsection