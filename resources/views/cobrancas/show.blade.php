@extends('layouts.app', ['title' => 'Cobrança'])

@section('content')

<style>
    .cobranca-show {
        --primary: #5B5BD6;
        --primary-dark: #4a4ac7;
        --soft: #f5f7ff;
        --border: #e8eaf6;
        --text: #2b2b2b;
        --muted: #7b8190;
        --success: #198754;
        --warning: #f59f00;
        --danger: #dc3545;
        --gray: #6c757d;
    }

    .cobranca-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(25, 35, 75, 0.08);
    }

    .cobranca-header {
        background: linear-gradient(135deg, var(--primary), #7272f2);
        color: #fff;
        padding: 20px 24px;
    }

    .cobranca-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
    }

    .cobranca-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #fff;
    }

    .cobranca-subtitle {
        margin-top: 4px;
        font-size: 13px;
        opacity: 0.9;
    }

    .cobranca-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-cobranca-light {
        background: #fff;
        color: var(--primary-dark);
        border: 0;
        border-radius: 10px;
        font-weight: 600;
        padding: 8px 14px;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-cobranca-light:hover {
        transform: translateY(-1px);
        color: var(--primary-dark);
        box-shadow: 0 8px 18px rgba(0,0,0,0.10);
    }

    .btn-cobranca-danger {
        background: #ff4d4f;
        color: #fff;
        border: 0;
        border-radius: 10px;
        font-weight: 600;
        padding: 8px 14px;
        text-decoration: none;
        transition: .2s ease;
    }

    .btn-cobranca-danger:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(255, 77, 79, 0.22);
    }

    .cobranca-body {
        background: #fff;
        padding: 22px;
    }

    .cobranca-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 18px;
    }

    .c-card {
        grid-column: span 12;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 4px 14px rgba(20, 20, 43, 0.04);
    }

    .c-card-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .c-info-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 14px;
    }

    .c-info {
        grid-column: span 3;
        background: #fafbff;
        border: 1px solid #eef0fb;
        border-radius: 14px;
        padding: 14px;
        min-height: 86px;
    }

    .c-info.col-6 {
        grid-column: span 6;
    }

    .c-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        margin-bottom: 7px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .c-value {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        word-break: break-word;
    }

    .c-value.money {
        font-size: 22px;
        color: var(--primary-dark);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .3px;
    }

    .status-pill.success {
        background: rgba(25, 135, 84, 0.12);
        color: var(--success);
    }

    .status-pill.warning {
        background: rgba(245, 159, 0, 0.14);
        color: var(--warning);
    }

    .status-pill.danger {
        background: rgba(220, 53, 69, 0.12);
        color: var(--danger);
    }

    .status-pill.secondary {
        background: rgba(108, 117, 125, 0.12);
        color: var(--gray);
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }

    .boleto-box {
        background: var(--soft);
        border: 1px dashed #cdd4ff;
        border-radius: 14px;
        padding: 14px;
    }

    .boleto-text {
        font-size: 15px;
        font-weight: 600;
        color: #3a3f52;
        word-break: break-word;
    }

    @media (max-width: 992px) {
        .c-info {
            grid-column: span 6;
        }
    }

    @media (max-width: 768px) {
        .cobranca-body {
            padding: 14px;
        }

        .c-info,
        .c-info.col-6 {
            grid-column: span 12;
        }

        .cobranca-title {
            font-size: 20px;
        }

        .c-value.money {
            font-size: 20px;
        }
    }
</style>

@php
$status = strtoupper($item->status_banco ?? '---');

$statusClass = match ($status) {
    'PAGO', 'RECEIVED', 'CONFIRMED' => 'success',
    'PENDENTE', 'PENDING' => 'warning',
    'VENCIDO', 'OVERDUE', 'CANCELADO', 'CANCELLED' => 'danger',
    default => 'secondary',
};
@endphp

<div class="cobranca-show mt-2">
    <div class="card cobranca-card">
        <div class="cobranca-header">
            <div class="cobranca-header-top">
                <div>
                    <h3 class="cobranca-title">Detalhes da Cobrança</h3>
                    <div class="cobranca-subtitle">
                        Conta #{{ $item->contaReceber->numero_sequencial }} • {{ strtoupper($item->banco ?? '---') }}
                    </div>
                </div>

                <div class="cobranca-actions">
                    @if($item->url_boleto)
                    <a href="{{ $item->url_boleto }}" target="_blank" class="btn-cobranca-light">
                        <i class="ri-file-paper-2-line"></i> Ver boleto
                    </a>
                    @endif

                    <a href="{{ route('cobrancas.index') }}" class="btn-cobranca-danger">
                        <i class="ri-arrow-left-double-fill"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="cobranca-body">
            <div class="cobranca-grid">

                <div class="c-card">
                    <div class="c-card-title">Resumo</div>

                    <div class="c-info-grid">
                        <div class="c-info">
                            <span class="c-label">ID</span>
                            <div class="c-value">#{{ $item->id }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Banco</span>
                            <div class="c-value">{{ strtoupper($item->banco ?? '---') }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Status</span>
                            <div class="c-value">
                                <span class="status-pill {{ $statusClass }}">
                                    <span class="status-dot"></span>
                                    {{ $status }}
                                </span>
                            </div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Empresa</span>
                            <div class="c-value">{{ $item->empresa->nome ?? '---' }}</div>
                        </div>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-title">Cliente e vínculo</div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <span class="c-label">Cliente</span>
                            <div class="boleto-box">
                                <div class="boleto-text">{{ $item->cliente->info }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <span class="c-label">Conta a receber</span>
                            <div class="boleto-box">
                                <div class="boleto-text">#{{ $item->contaReceber->numero_sequencial ?? '---' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-title">Valores</div>

                    <div class="c-info-grid">
                        <div class="c-info">
                            <span class="c-label">Valor</span>
                            <div class="c-value money">R$ {{ number_format($item->valor ?? 0, 2, ',', '.') }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Recebido</span>
                            <div class="c-value money">R$ {{ number_format($item->valor_recebido ?? 0, 2, ',', '.') }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Juros</span>
                            <div class="c-value">R$ {{ number_format($item->valor_juros ?? 0, 2, ',', '.') }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Multa</span>
                            <div class="c-value">R$ {{ number_format($item->valor_multa ?? 0, 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-title">Datas</div>

                    <div class="c-info-grid">
                        <div class="c-info">
                            <span class="c-label">Emissão</span>
                            <div class="c-value">{{ optional($item->data_emissao)->format('d/m/Y') ?? '---' }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Vencimento</span>
                            <div class="c-value">{{ optional($item->data_vencimento)->format('d/m/Y') ?? '---' }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Pagamento</span>
                            <div class="c-value">{{ optional($item->data_pagamento)->format('d/m/Y') ?? '---' }}</div>
                        </div>

                        <div class="c-info">
                            <span class="c-label">Última consulta</span>
                            <div class="c-value">{{ optional($item->ultima_consulta_em)->format('d/m/Y H:i') ?? '---' }}</div>
                        </div>
                    </div>
                </div>

                <div class="c-card">
                    <div class="c-card-title">Dados do boleto</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="c-label">Linha digitável</span>
                            <div class="boleto-box">
                                <div class="boleto-text">{{ $item->linha_digitavel ?? '---' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <span class="c-label">Código de barras</span>
                            <div class="boleto-box">
                                <div class="boleto-text">{{ $item->codigo_barras ?? '---' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($item->payload_envio || $item->payload_retorno)
                <div class="c-card">
                    <div class="c-card-title">Payloads</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="c-label">Payload envio</span>
                            <div class="boleto-box">
                                <pre class="mb-0" style="white-space: pre-wrap; font-size: 12px; color: #3a3f52;">{{ json_encode($item->payload_envio, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <span class="c-label">Payload retorno</span>
                            <div class="boleto-box">
                                <pre class="mb-0" style="white-space: pre-wrap; font-size: 12px; color: #3a3f52;">{{ json_encode($item->payload_retorno, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection