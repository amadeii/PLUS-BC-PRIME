@extends('layouts.app', ['title' => 'Alterar Estado Fiscal'])
@section('content')

@section('css')
<style type="text/css">
    input[type="file"] {
        display: none;
    }

    .file-certificado label {
        padding: 8px 8px;
        width: 100%;
        background-color: #8833FF;
        color: #FFF;
        text-transform: uppercase;
        text-align: center;
        display: block;
        margin-top: 20px;
        cursor: pointer;
        border-radius: 5px;
    }

    .card-body strong {
        color: #8833FF;
    }

</style>
@endsection

<div class="card mt-1">
    <div class="card-header">
        @if($tipo == 'devolucao')
        <h4>Alterar Devolução</h4>
        @else
        <h4>Alterar Estado Fiscal NFe</h4>
        @endif
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('nfe.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="row">
        {!!Form::open()
        ->put()
        ->route('nfe.storeEstado', [$item->id])
        ->multipart()
        !!}

        <div class="alert alert-warning border-0 d-flex align-items-start mx-3 mb-4">
            <div class="me-3">
                <i class="ri-alert-line fs-4"></i>
            </div>

            <div>
                <div class="fw-semibold mb-1">
                    Atenção sobre alteração de estado fiscal
                </div>

                <small>
                    Esta tela realiza apenas um ajuste interno no sistema. Alterar o estado para
                    <strong>Cancelado</strong> não significa que a NF-e foi cancelada na SEFAZ.
                    Para cancelamento fiscal válido é necessário utilizar o processo oficial de cancelamento da NF-e.
                </small>
            </div>
        </div>
        <hr>
        <div class="mx-3 mb-3">
            <div class="card border-0 shadow-sm overflow-hidden">

                <div class="card-header bg-dark text-white border-0 py-2 px-3 d-flex justify-content-between align-items-center">

                    <div>
                        <br>
                        <h6 class=" fw-bold">
                            <i class="ri-file-list-3-line me-1"></i>
                            Dados da NF-e
                        </h6>
                    </div>

                    <span class="badge bg-{{ $item->estado == 'aprovado' ? 'success' : ($item->estado == 'cancelado' ? 'danger' : ($item->estado == 'rejeitado' ? 'warning text-dark' : 'secondary')) }}">
                        {{ strtoupper($item->estado) }}
                    </span>

                </div>

                <div class="card-body p-3">

                    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">

                        <div>
                            <small class="text-muted d-block">
                                {{ $item->cliente ? 'Cliente' : 'Fornecedor' }}
                            </small>

                            <div class="fw-bold fs-5">
                                {{ $item->cliente ? $item->cliente->razao_social : $item->fornecedor->razao_social }}
                            </div>
                        </div>

                        <div class="text-end">
                            <small class="text-muted d-block">
                                Valor Total
                            </small>

                            <div class="fw-bold text-success fs-5">
                                {{ __moeda($item->total) }}
                            </div>
                        </div>

                    </div>

                    <div class="row g-2">

                        <div class="col-md-3">
                            <div class="bg-light rounded-3 p-2 h-100">
                                <small class="text-muted d-block">
                                    CNPJ
                                </small>

                                <div class="fw-semibold small">
                                    {{ $item->cliente ? $item->cliente->cpf_cnpj : $item->fornecedor->cpf_cnpj }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-3 p-2 h-100">
                                <small class="text-muted d-block">
                                    Data
                                </small>

                                <div class="fw-semibold small">
                                    {{ __data_pt($item->data_registro, 0) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-3 p-2 h-100">
                                <small class="text-muted d-block">
                                    Cidade
                                </small>

                                <div class="fw-semibold small">
                                    {{ $item->cliente ? $item->cliente->cidade?->info : $item->fornecedor->cidade?->info }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-3 p-2 h-100">
                                <small class="text-muted d-block">
                                    Número
                                </small>

                                <div class="fw-semibold small">
                                    {{ $item->numero }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-3 p-2 h-100">
                                <small class="text-muted d-block">
                                    Série
                                </small>

                                <div class="fw-semibold small">
                                    {{ $item->numero_serie }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div class="bg-light rounded-3 p-2">
                                <small class="text-muted d-block">
                                    Chave NF-e
                                </small>

                                <div class="fw-semibold small" style="word-break: break-all;">
                                    {{ $item->chave != '' ? $item->chave : '--' }}
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>
        <hr>
        <div class="row m-3">
            <div class="col-md-3">
                {!!Form::select('estado_emissao', 'Estado',
                ['novo' => 'Novo', 'rejeitado' => 'Rejeitado', 'cancelado' => 'Cancelado', 'aprovado' => 'Aprovado'])
                ->attrs(['class' => 'form-select'])->value(isset($item) ? $item->estado : '')!!}
            </div>
            <div class="row mt-2">
                <div class="col-md-3 file-upload">
                    {!! Form::file('file', 'Arquivo XML')
                    ->attrs(['accept' => '.xml']) !!}
                    <span class="text-danger" id="filename"></span>
                </div>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
        </div>
        <input type="hidden" name="tipo" value="{{ $tipo }}">
        {!!Form::close()!!}
    </div>
</div>
@endsection
