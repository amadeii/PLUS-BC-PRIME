@extends('layouts.app', ['title' => 'Vendas'])
@section('css')
<link rel="stylesheet" type="text/css" href="/css/nfe_index.css">
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @can('nfe_create')
                    <div class="col-md-2">
                        <a href="{{ route('nfe.create') }}" class="btn btn-success">
                            <i class="ri-add-circle-fill"></i>
                            Nova Venda
                        </a>
                    </div>
                    @endcan

                    <div class="col-md-8"></div>

                    @if(__isPlanoFiscal())
                    <div class="col-md-2">
                        <button id="btn-consulta-sefaz" class="btn btn-dark" style="float: right;">
                            <i class="ri-refresh-line"></i>
                            Consultar Status Sefaz
                        </button>
                    </div>
                    @endif
                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3 g-2">
                        <div class="col-md-4">
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
                        @if(__isPlanoFiscal())
                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado',
                            ['novo' => 'Novas',
                            'rejeitado' => 'Rejeitadas',
                            'cancelado' => 'Canceladas',
                            'aprovado' => 'Aprovadas',
                            '' => 'Todos'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::tel('numero_nfe', 'Número NFe')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tpNF', 'Tipo',
                            [
                            '1' => 'Saída',
                            '0' => 'Entrada',
                            '-' => 'Todos'
                            ])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        @endif

                        @if(__countLocalAtivo() > 1)
                        <div class="col-md-2">
                            {!!Form::select('local_id', 'Local', ['' => 'Selecione'] + __getLocaisAtivoUsuario()->pluck('descricao', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>
                        @endif

                        <div class="col-md-4">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('nfe.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>

                        <div class="col-md-4"></div>
                        <div class="col-md-2">
                            <div class="card shadow-sm border-0 bg-light text-end">
                                <div class="card-body d-flex align-items-center justify-content-end" style="height: 50px;">
                                    <div>
                                        <small class="text-muted">Valor Total das Vendas</small>
                                        <h4 class="mb-0 fw-bold text-success">
                                            R$ {{ __moeda($somaGeral) }}
                                        </h4>
                                    </div>
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
                                        <th>Cliente/Fornecedor</th>
                                        <th>CPF/CNPJ</th>
                                        @if(__countLocalAtivo() > 1)
                                        <th>Local</th>
                                        @endif
                                        <th>Usuário</th>
                                        <th>Número</th>
                                        <th>Número Série</th>
                                        <th>Valor</th>
                                        @if(__isPlanoFiscal())
                                        <th>Status Fiscal</th>
                                        <th>Estado</th>
                                        <th>Ambiente</th>
                                        @endif
                                        <th>Data de cadastro</th>
                                        <th>Data de emissão</th>
                                        <th>Tipo</th>
                                        <th>*</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <!-- menu açoes -->

                                        <td class="text-start d-none d-md-table-cell">
                                            @if($usarDropdown)
                                            @include('nfe.partials.dropdown_acoes', ['item' => $item])
                                            @else
                                            @include('nfe.partials.botoes_acoes', ['item' => $item])
                                            @endif
                                        </td>

                                        <td class="d-md-none">
                                            @include('nfe.partials.botoes_acoes', ['item' => $item])
                                        </td>

                                        <td data-label="#">{{ $item->numero_sequencial }}</td>

                                        @if($item->cliente)
                                        <td data-label="Cliente/Fornecedor">
                                            <div style="width:300px; white-space:normal; word-break:break-word;">
                                                {{ $item->cliente->razao_social ?? '--' }}
                                            </div>
                                        </td>
                                        <td data-label="CPF/CNPJ">{{ $item->cliente->cpf_cnpj ?? '--' }}</td>
                                        @else
                                        <td data-label="Cliente/Fornecedor">
                                            <label style="width: 350px">{{ $item->fornecedor->razao_social ?? '--' }}</label>
                                        </td>
                                        <td data-label="CPF/CNPJ">{{ $item->fornecedor->cpf_cnpj ?? '--' }}</td>
                                        @endif

                                        @if(__countLocalAtivo() > 1)
                                        <td data-label="Local" class="text-danger">{{ $item->localizacao->descricao }}</td>
                                        @endif

                                        <td data-label="Usuário">{{ $item->user->name ?? '--' }}</td>
                                        <td data-label="Número">{{ $item->numero ?? '' }}</td>
                                        <td data-label="Número Série">
                                            <label style="width: 100px">{{ $item->numero_serie ?? '' }}</label>
                                        </td>
                                        <td data-label="Valor">
                                            <label class="fs-16 mb-1 text-success">
                                                R$ {{ __moeda($item->total) }}
                                            </label>
                                        </td>

                                        @if(__isPlanoFiscal())
                                        <td data-label="Status Fiscal">
                                            @if($item->estado == 'aprovado')
                                            <span class="badge bg-success p-1">Fiscal OK</span>
                                            @else
                                            @if($item->fiscal_status === 'erro')
                                            <span class="badge bg-danger p-1 bg-fiscal" onclick="consultarFiscal({{ $item->id }})">Erro fiscal</span>
                                            @elseif($item->fiscal_status === 'alerta')
                                            <span class="badge bg-warning p-1 bg-fiscal" onclick="consultarFiscal({{ $item->id }})">Alerta fiscal</span>
                                            @else
                                            <span class="badge bg-success p-1">Fiscal OK</span>
                                            @endif
                                            @endif
                                        </td>
                                        <td data-label="Estado">
                                            @if($item->estado == 'aprovado')
                                            <span class="badge p-1 bg-success text-white">APROVADO</span>
                                            @elseif($item->estado == 'cancelado')
                                            <span class="badge p-1 bg-danger text-white">CANCELADO</span>
                                            @elseif($item->estado == 'rejeitado')
                                            <span class="badge p-1 bg-warning text-white">REJEITADO</span>
                                            @else
                                            <span class="badge p-1 bg-info text-white">NOVO</span>
                                            @endif
                                        </td>

                                        <td data-label="Ambiente">{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</td>
                                        @endif

                                        <td data-label="Data de cadastro">
                                            <label style="width: 120px">{{ __data_pt($item->created_at) }}</label>
                                        </td>

                                        <td data-label="Data de emissão">
                                            <label style="width: 120px">{{ $item->data_emissao ? __data_pt($item->data_emissao, 1) : '--' }}</label>
                                        </td>

                                        <td data-label="Tipo">
                                            @if($item->tpNF)
                                            <span class="text-success">Saída</span>
                                            @else
                                            <span class="text-primary">Entrada</span>
                                            @endif
                                        </td>

                                        <td data-label="*">
                                            @if($item->pedidoEcommerce)
                                            <a title="Pedido de ecommerce" class="btn btn-sm btn-danger" href="{{ route('pedidos-ecommerce.show', [$item->pedidoEcommerce->id]) }}">EC</a>
                                            @elseif($item->ordemServico)
                                            <a title="Ordem de serviço" class="btn btn-sm btn-primary" href="{{ route('ordem-servico.show', [$item->ordemServico->id]) }}">OS</a>
                                            @elseif($item->pedidoMercadoLivre)
                                            <a title="Pedido mercado livre" class="btn btn-sm btn-warning" href="{{ route('mercado-livre-pedidos.show', [$item->pedidoMercadoLivre->id]) }}">ML</a>
                                            @elseif($item->pedidoNuvemShop)
                                            <a title="Pedido nuvem shop" class="btn btn-sm btn-dark" href="{{ route('nuvem-shop-pedidos.show', [$item->pedidoNuvemShop->pedido_id]) }}">NS</a>
                                            @elseif($item->reserva)
                                            <a title="Reserva" class="btn btn-sm btn-dark" href="{{ route('reservas.show', [$item->reserva->id]) }}">RS</a>
                                            @elseif($item->pedidoWoocomerce)
                                            <a title="Pedido woocommerce" class="btn btn-sm btn-info" href="{{ route('woocommerce-pedidos.show', [$item->pedidoWoocomerce->id]) }}">WO</a>
                                            @else
                                            --
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="20" class="text-center">Nada encontrado</td>
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

@include('nfe.partials.modal_envio_wpp')

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

<div class="modal fade" id="modal-cancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-danger text-white border-0 px-4 py-4">
                <div>
                    <h4 class="fw-bold mb-1 text-white">
                        Cancelar NF-e
                    </h4>

                    <small class="text-white opacity-75">
                        O cancelamento da NF-e será transmitido para a SEFAZ
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
                            Atenção ao cancelar esta NF-e
                        </div>

                        <small>
                            Após o cancelamento autorizado pela SEFAZ, a nota fiscal ficará sem validade fiscal e não poderá ser utilizada novamente.
                        </small>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-body">

                        <h6 class="fw-bold mb-4">
                            Dados da NF-e
                        </h6>

                        <div class="row g-3">

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Número NF-e
                                </small>

                                <div class="fw-semibold cancel-ref-numero"></div>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block mb-1">
                                    Série
                                </small>

                                <div class="fw-semibold cancel-ref-serie"></div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Data de Emissão
                                </small>

                                <div class="fw-semibold cancel-ref-data"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">
                                    Cliente
                                </small>

                                <div class="fw-semibold cancel-ref-cliente"></div>
                            </div>

                            <div class="col-12 mt-4">
                                <small class="text-muted d-block mb-1">
                                    Chave de Acesso
                                </small>

                                <div class="fw-semibold cancel-ref-chave"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">
                        Motivo do Cancelamento <span class="text-danger">*</span>
                    </label>

                    <textarea
                    class="form-control"
                    id="texto-cancelamento"
                    rows="5"
                    maxlength="255"
                    placeholder="Descreva o motivo do cancelamento da NF-e"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <small class="text-muted">
                        <span id="contador-cancelamento">0</span> de 255 caracteres utilizados
                    </small>
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

<div class="modal fade" id="modal-email" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="{{ route('nfe.send-email') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enviar email NFe <strong class="ref-numero"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <input type="hidden" id="nfe_email_id" name="id">
                    <div class="col-md-12">
                        {!!Form::text('email', 'Email')
                        ->required()
                        ->type('email')
                        !!}
                    </div>

                    <div class="col-md-12 file-certificado">
                        {!! Form::file('arquivo', 'Arquivo') !!}
                        <span class="text-danger" id="filename"></span>
                    </div>

                    <div class="col-md-4 mt-2">
                        {!!Form::checkbox('danfe', 'DANFE')
                        !!}
                    </div>
                    <div class="col-md-4 mt-2">
                        {!!Form::checkbox('xml', 'XML')
                        !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-success btn-enviar-email">Enviar Email</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modal-corrigir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        Emitir Carta de Correção (CC-e)
                    </h4>

                    <small class="text-muted">
                        Corrija informações permitidas da NF-e
                    </small>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4">

                <div class="alert alert-primary border-0 d-flex align-items-start mb-4">
                    <div class="me-3">
                        <i class="ri-information-line fs-4"></i>
                    </div>

                    <div>
                        <div class="fw-semibold mb-1">
                            A Carta de Correção serve para corrigir informações incorretas que não alterem dados fiscais da NF-e.
                        </div>

                        <small>
                            Exemplos: descrição do produto, CFOP, transportadora, endereço, informações complementares, entre outros.
                        </small>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-body">

                        <h6 class="fw-bold mb-4">
                            Dados da NF-e
                        </h6>

                        <div class="row g-3">

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Número NF-e
                                </small>

                                <div class="fw-semibold ref-numero"></div>
                            </div>

                            <div class="col-md-2">
                                <small class="text-muted d-block mb-1">
                                    Série
                                </small>

                                <div class="fw-semibold ref-serie"></div>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted d-block mb-1">
                                    Data de Emissão
                                </small>

                                <div class="fw-semibold ref-data"></div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted d-block mb-1">
                                    Cliente
                                </small>

                                <div class="fw-semibold ref-cliente"></div>
                            </div>

                            <div class="col-12 mt-4">
                                <small class="text-muted d-block mb-1">
                                    Chave de Acesso
                                </small>

                                <div class="fw-semibold ref-chave"></div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-semibold">
                        Texto da Correção <span class="text-danger">*</span>
                    </label>

                    <textarea
                    class="form-control"
                    id="texto-correcao"
                    rows="7"
                    maxlength="1000"
                    placeholder='Descreva aqui a correção a ser considerada.

                    Exemplo: Onde se lê "CFOP 5102", leia-se "CFOP 5405".'></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <small class="text-muted">
                        <span id="contador-correcao">0</span> de 1000 caracteres utilizados
                    </small>
                </div>

                <div class="alert alert-warning border-0 mb-0">
                    <div class="fw-bold mb-2">
                        <i class="ri-alert-line me-1"></i>
                        Atenção
                    </div>

                    <ul class="mb-0 ps-3">
                        <li>Alterar valores fiscais, impostos ou alíquotas;</li>
                        <li>Alterar dados do destinatário/remetente;</li>
                        <li>Alterar data de emissão da NF-e.</li>
                    </ul>
                </div>

            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" id="btn-corrigir" class="btn btn-success px-4">
                    <i class="ri-send-plane-line me-1"></i>
                    Transmitir CC-e
                </button>
            </div>

        </div>
    </div>
</div>

<div id="fiscalLoader" class="fiscal-loader d-none">
    <div class="fiscal-box">
        <div id="fiscalIcon" class="fiscal-icon error">✖</div>

        <h4 id="fiscalTitle">Erro Fiscal</h4>
        <p id="fiscalSubtitle">
            Foram encontrados problemas fiscais que impedem a transmissão.
        </p>

        <div id="fiscalContent" class="fiscal-content"></div>

        <div class="fiscal-actions">
            <button id="btnFiscalCancel" class="btn btn-secondary">
                Fechar
            </button>

        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">

    $(function(){
        let url = "{{ session('url_carne') }}";

        if(url){
            swal({
                title: "Sucesso",
                text: "Deseja imprimir o carnê desta venda?",
                icon: "success",
                buttons: true,
                buttons: ["Não", "Sim"],
                dangerMode: true,
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.open(url, '_blank');
                } else {
                }

            });
        }
    })

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

    $('#btn-consulta-sefaz').click(() => {
        $.post(path_url + 'api/nfe_painel/consulta-status-sefaz', {
            empresa_id: $('#empresa_id').val(),
            usuario_id: $('#usuario_id').val(),
        })
        .done((res) => {
            let msg = "cStat: " + res.cStat
            msg += "\nMotivo: " + res.xMotivo
            msg += "\nAmbiente: " + (res.tpAmb == 2 ? "Homologação" : "Produção")
            msg += "\nverAplic: " + res.verAplic

            swal("Sucesso", msg, "success")
        })
        .fail((err) => {
            try {
                swal("Erro", err.responseText, "error")
            } catch {
                swal("Erro", "Algo deu errado", "error")
            }
        })
    })

    function printPedido(id){
        var disp_setting="toolbar=yes,location=no,";
        disp_setting+="directories=yes,menubar=yes,";
        disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

        var docprint=window.open(path_url+"nfe/imprimirVenda/"+id, "",disp_setting);

        docprint.focus();
    }

</script>
<script type="text/javascript" src="/js/nfe_transmitir.js"></script>
<script type="text/javascript" src="/js/enviar_fatura_wpp.js"></script>
<script type="text/javascript" src="/js/consulta_fiscal.js"></script>
@endsection
