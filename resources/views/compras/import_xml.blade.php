@extends('layouts.app', ['title' => 'Importação de XML'])
@section('css')
<style type="text/css">
    .ri-information-line:hover{
        cursor: pointer;
    }
    .swal-button--confirm {
        background-color: #159488 !important;
        color: white !important;
    }

</style>
@endsection
@section('content')

<div class="card mt-1">
    <div class="card-header">

        <h4>Importação de XML</h4>

        @isset($dadosXml)
        <h5>Chave <strong class="text-success">{{ $dadosXml['chave'] }}</strong></h5>
        @endif
        <div style="text-align: right; margin-top: -35px;">
            @if(__countLocalAtivo() > 1 && isset($caixa))
            <h5 class="mt-2">Local: <strong class="text-danger">{{ $caixa->localizacao ? $caixa->localizacao->descricao : '' }}</strong></h5>
            @endif
            <a href="{{ route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('compras.finish-xml')
        ->id('form-xml')
        ->multipart()
        !!}

        <div class="pl-lg-4">
            @include('compras._forms_xml')
        </div>
        {!!Form::close()!!}
    </div>
</div>

<div class="modal fade" id="modalComprasPendentes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Selecionar compra pendente
                </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>

            <div class="modal-body">
                <div id="infoFornecedorCompra" 
                     class="alert alert-info mb-3">
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Número</th>
                                <th>Data</th>
                                <th>Valor Total</th>
                                <th>Total de itens</th>
                                <th width="120"></th>
                            </tr>
                        </thead>
                        <tbody id="tbodyComprasPendentes"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold" id="tfootTotalLinhas">
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <input type="hidden" name="compra_relacionada_id" id="compra_relacionada_id">
            </div>

            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDivergencias" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    Divergências encontradas
                </h5>
            </div>

            <div class="modal-body">

                <!-- ITENS -->
                <h6 class="mb-2">Itens</h6>

                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th>Status</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDivergenciasItens"></tbody>
                </table>

                <hr>

                <!-- FATURAS -->
                <h6 class="mb-2">Faturas</h6>

                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDivergenciasFaturas"></tbody>
                </table>

                <div id="divergenciaTotal" class="mt-3 text-danger fw-bold"></div>

            </div>

            <div class="modal-footer">

                <a class="btn btn-secondary" href="{{ route('compras.xml') }}">
                    Cancelar
                </a>

                <button class="btn btn-success" data-bs-dismiss="modal" id="btnConfirmarMesmoAssim">
                    Continuar mesmo assim
                </button>

            </div>

        </div>
    </div>
</div>

@include('modals._altera_produto_xml')
@include('modals._modal_show_xml')
@include('modals._marca')
@include('modals._categoria_produto')

@section('js')
<script src="/js/nfe.js"></script>
<script src="/js/import_xml.js?v=2"></script>

@if($config != null && $config->compra_compara_xml)
    <script type="text/javascript">
        buscarComprasPendentesFornecedor('{{ $fornecedor->id }}')
    </script>
@endif
@endsection
@endsection
