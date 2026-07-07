@extends('layouts.app', ['title' => 'Compras'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        @can('compras_create')
                        <a href="{{ route('compras.create') }}" class="btn btn-success">
                            <i class="ri-add-circle-fill"></i>
                            Nova Compra
                        </a>
                        @endcan
                    </div>

                    <div class="col-md-8"></div>
                    <div class="col-md-2">
                        <a href="{{ route('compras.rastro') }}" class="btn btn-dark float-end">
                            <i class="ri-filter-2-line"></i>
                            Consulta Rastro
                        </a>
                    </div>
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3 g-2">
                        <div class="col-md-4">
                            {!!Form::select('fornecedor_id', 'Fornecedor', ['' => 'Selecione'] + $fornecedores->pluck('razao_social', 'id')->all())
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

                        <div class="col-md-4">
                            {!!Form::tel('chave', 'Chave')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado fiscal',
                            ['novo' => 'Novas',
                            'rejeitado' => 'Rejeitadas',
                            'cancelado' => 'Canceladas',
                            'aprovado' => 'Aprovadas',
                            '' => 'Todos'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @if(__countLocalAtivo() > 1)
                        <div class="col-md-2">
                            {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        @endif
                        <div class="col-md-4 col-xl-2 col-12">
                            <br>

                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('compras.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                        <div class="col-md-10"></div>

                        <div class="card shadow-sm border-0 mt-2 col-2 bg-light text-end">
                            <div class="card-body d-flex align-items-center justify-content-end" style="height: 50px;">
                                <div>
                                    <small class="text-muted">Soma Total</small>
                                    <h4 class="mb-0 fw-bold text-success">
                                        R$ {{ __moeda($data->sum('total')) }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                    </div>
                    {!!Form::close()!!}
                </div>
                @if($contigencia != null)
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="text-danger">Contigência ativada</h4>
                                <p class="text-danger">Tipo: <strong>{{$contigencia->tipo}}</strong></p>
                                <p class="text-danger">Data de ínicio: <strong>{{ __data_pt($contigencia->created_at) }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <div class="tabela-scroll" style="overflow-x:auto;">
                            <table class="table table-striped table-centered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Ações</th>
                                        <th>#</th>
                                        <th>Fornecedor</th>
                                        @if(__countLocalAtivo() > 1)
                                        <th>Local</th>
                                        @endif
                                        <th>CPF/CNPJ</th>
                                        <th>Estado da compra</th>
                                        <th>Número</th>
                                        <th>XML Importado</th>
                                        <th>Valor</th>
                                        <th>Estado fiscal</th>
                                        <th>Ambiente</th>
                                        <th>Data de cadastro</th>
                                        <th>Data de emissão</th>
                                        <th>Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td class="text-start d-none d-md-table-cell">
                                            @if($usarDropdown)
                                            @include('compras.partials.dropdown_acoes', ['item' => $item])
                                            @else
                                            @include('compras.partials.botoes_acoes', ['item' => $item])
                                            @endif
                                        </td>

                                        <td class="d-md-none">
                                            @include('compras.partials.botoes_acoes', ['item' => $item])
                                        </td>

                                        <td data-label="#"> {{ $item->numero_sequencial }}</td>
                                        <td data-label="Fornecedor">
                                            <div style="width:300px; white-space:normal; word-break:break-word;">
                                                {{ $item->fornecedor ? $item->fornecedor->razao_social : "--" }}
                                            </div>
                                        </td>
                                        @if(__countLocalAtivo() > 1)
                                        <td data-label="Local" class="text-danger">{{ $item->localizacao->descricao }}</td>
                                        @endif
                                        <td data-label="CPF/CNPJ">{{ $item->fornecedor ? $item->fornecedor->cpf_cnpj : "--" }}</td>
                                        <td data-label="Estado da Compra">
                                            @if($item->estado_compra == 'pendente')
                                            <span class="badge bg-warning text-white p-1">Pendente</span>
                                            @elseif($item->estado_compra == 'finalizado')
                                            <span class="badge bg-success text-white p-1">Finalizado</span>
                                            @endif
                                        </td>
                                        <td data-label="Número">{{ $item->numero ? $item->numero : '' }}</td>
                                        <td data-label="XML Importado">
                                            @if($item->chave_importada)
                                            <i class="ri-checkbox-circle-fill text-success"></i>
                                            @else
                                            <i class="ri-close-circle-fill text-danger"></i>
                                            @endif
                                        </td>
                                        <td data-label="Valor">{{ number_format($item->total, 2, ',', '.') }}</td>
                                        <td data-label="Estado Fiscal">
                                            @if($item->estado == 'aprovado')
                                            <span class="badge bg-success text-white p-1">Aprovado</span>
                                            @elseif($item->estado == 'cancelado')
                                            <span class="badge bg-danger text-white p-1">Cancelado</span>
                                            @elseif($item->estado == 'rejeitado')
                                            <span class="badge bg-warning text-white p-1">Rejeitado</span>
                                            @else
                                            <span class="badge bg-info text-white p-1">Novo</span>
                                            @endif
                                        </td>
                                        <td data-label="Ambiente">{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</td>
                                        <td data-label="Data de cadastro"><label style="width: 120px">{{ __data_pt($item->created_at) }}</label></td>
                                        <td data-label="Data de emissão"><label style="width: 120px">{{ $item->data_emissao ? __data_pt($item->data_emissao, 1) : '--' }}</label></td>
                                        <td data-label="Tipo">
                                            @if($item->tpNF)
                                            <span class="text-success">Saída</span>
                                            @else
                                            <span class="text-primary">Entrada</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="13" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                    <button type="button" id="scrollToggle2" class="scroll-btn-jidox hidden">
                        <i class="ri-arrow-right-circle-line"></i>
                    </button>
                    <br>
                    {!! $data->appends(request()->all())->links() !!}
                </div>
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cancelar NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-12">
                        {!!Form::text('motivo-cancela', 'Motivo')
                        ->required()

                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="button" id="btn-cancelar" class="btn btn-danger">Cancelar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-corrigir" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Corrigir NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-md-12">
                        {!!Form::text('motivo-corrigir', 'Motivo')
                        ->required()

                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="button" id="btn-corrigir" class="btn btn-warning">Corrigir</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    function info(motivo_rejeicao, chave, estado, recibo) {

        if (estado == 'rejeitado') {
            let text = "Motivo: " + motivo_rejeicao + "\n"
            text += "Chave: " + chave + "\n"
            swal("", text, "warning")
        } else {
            let text = "Chave: " + chave + "\n"
            text += "Recibo: " + recibo + "\n"
            swal("", text, "success")
        }
    }

    function printPedido(id){
        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

        var docprint=window.open(path_url+"nfe/imprimirVenda/"+id, "",disp_setting);

        docprint.focus();
    }
</script>
<script type="text/javascript" src="/js/nfe_transmitir.js"></script>
@endsection
