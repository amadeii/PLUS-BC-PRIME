@extends('layouts.app', ['title' => 'Manifesto'])

@section('content')
<div class="page-content mt-1">
    <div class="card">
        <div class="card-body p-4 p-lg-5">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-1 text-primary">Nova Consulta</h4>
                    <div class="text-muted">Consulte novos documentos disponíveis para manifestação.</div>
                </div>

                <a href="{{ route('manifesto.index') }}" class="btn btn-danger btn-sm">
                    <i class="ri-arrow-left-double-fill"></i>
                    Voltar para os documentos
                </a>
            </div>

            <div id="aguarde" class="alert alert-info d-none align-items-center gap-2" role="alert" style="display:flex;">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <div><strong>Consultando</strong> novos documentos... aguarde.</div>
            </div>

            @if(sizeof($locais) > 0)
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label">Localização</label>
                    <select id="local_id" class="form-select">
                        <option value="">Selecione</option>
                        @foreach(__getLocaisAtivoUsuario() as $l)
                        <option value="{{ $l->id }}">{{ $l->descricao }} - {{ $l->nome }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 col-lg-2">
                    <button class="btn btn-primary w-100" id="btn-consultar">
                        <i class="ri-search-line"></i>
                        Consultar
                    </button>
                </div>
            </div>
            @else
            <div class="alert alert-warning mt-3">
                Nenhuma localização cadastrada para consulta.
            </div>
            @endif

            <div id="sem-resultado" class="mt-4" style="display:none;">
                <div class="alert alert-warning border d-flex align-items-center gap-2">
                    <i class="ri-information-line fs-5"></i>
                    <div class="mb-0">Nenhum novo resultado encontrado.</div>
                </div>
            </div>

            <div class="mt-4" id="table" style="display:none">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Nome</th>
                                <th>CPF/CNPJ</th>
                                <th>Valor</th>
                                <th>Chave</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="text-muted small mt-2">
                    Dica: a chave aparece completa (quebra automática de linha).
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="/js/dfe.js"></script>
@endsection
