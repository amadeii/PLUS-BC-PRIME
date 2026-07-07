@extends('layouts.app', ['title' => 'Devoluções'])
@section('css')
<style type="text/css">
    
    .total-mini-card{
        display:inline-flex;
        align-items:center;
        gap:12px;
        padding:12px 16px;
        border-radius:16px;
        background:#fff;
        border:1px solid #e9ecef;
        box-shadow:0 2px 10px rgba(0,0,0,0.04);
    }

    .total-mini-icon{
        width:42px;
        height:42px;
        border-radius:12px;
        background:linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .total-mini-icon i{
        font-size:22px;
        color:#fff;
    }

    .total-mini-info{
        line-height:1.1;
    }

    .total-mini-info span{
        display:block;
        font-size:11px;
        font-weight:600;
        text-transform:uppercase;
        letter-spacing:.5px;
        color:#888;
        margin-bottom:4px;
    }

    .total-mini-info h5{
        margin:0;
        font-size:22px;
        font-weight:800;
        color:#16a34a;
    }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 mt-1">
                        @can('devolucao_create')
                        <a href="{{ route('devolucao.xml') }}" class="btn btn-danger w-100">
                            <i class="ri-add-circle-fill"></i>
                            Nova Devolução
                        </a>
                        @endcan
                    </div>
                    <div class="col-md-7"></div>

                    @if(__isPlanoFiscal())
                    <div class="col-md-3 mt-1">
                        <button id="btn-consulta-sefaz" class="btn btn-dark w-100" style="float: right;">
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
                    <div class="row mt-3 g-1">
                        <div class="col-md-4">
                            {!!Form::select('fornecedor_id', 'Fornecedor')
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
                            {!!Form::select('tpNF', 'Tipo',
                            [
                            '1' => 'Saída',
                            '0' => 'Entrada',
                            '' => 'Todos'
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

                        <div class="col-lg-4 col-12">
                            <br>

                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('devolucao.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <div class="tabela-scroll" style="overflow-x:auto;">
                            <table class="table table-striped table-centered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fornecedor</th>
                                        <th>CPF/CNPJ</th>
                                        @if(__countLocalAtivo() > 1)
                                        <th>Local</th>
                                        @endif
                                        <th>Número</th>
                                        <th>Valor</th>
                                        @if(__isPlanoFiscal())
                                        <th>Estado</th>
                                        <th>Ambiente</th>
                                        @endif
                                        <th>Data</th>
                                        <!-- <th>Local de emissão</th> -->
                                        <th>CRT</th>
                                        <th>Tipo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td data-label="#"> {{ $item->numero_sequencial }} </td>

                                        @if($item->cliente)

                                        <td data-label="Fornecedor">
                                            <label style="width:300px; white-space:normal; word-break:break-word; line-height:1.3;">
                                                {{ $item->cliente ? $item->cliente->razao_social : "--" }}
                                            </label>
                                        </td>

                                        <td data-label="CPF/CNPJ">
                                            <label style="width:150px; white-space:normal; word-break:break-word; line-height:1.3;">
                                                {{ $item->cliente ? $item->cliente->cpf_cnpj : "--" }}
                                            </label>
                                        </td>

                                        @else

                                        <td data-label="Fornecedor">
                                            <label style="width:300px; white-space:normal; word-break:break-word; line-height:1.3;">
                                                {{ $item->fornecedor ? $item->fornecedor->razao_social : "--" }}
                                            </label>
                                        </td>

                                        <td data-label="CPF/CNPJ">
                                            <label style="width:150px; white-space:normal; word-break:break-word; line-height:1.3;">
                                                {{ $item->fornecedor ? $item->fornecedor->cpf_cnpj : "--" }}
                                            </label>
                                        </td>

                                        @endif

                                        @if(__countLocalAtivo() > 1)
                                        <td data-label="Local" class="text-danger">{{ $item->localizacao ? $item->localizacao->descricao : '' }}</td>
                                        @endif

                                        <td data-label="Número">{{ $item->numero ? $item->numero : '' }}</td>
                                        <td data-label="Valor">{{ __moeda($item->total) }}</td>

                                        @if(__isPlanoFiscal())
                                        <td data-label="Estado">
                                            @if($item->estado == 'aprovado')
                                            <span class="badge bg-success p-1">Aprovado</span>
                                            @elseif($item->estado == 'cancelado')
                                            <span class="badge bg-danger p-1">Cancelado</span>
                                            @elseif($item->estado == 'rejeitado')
                                            <span class="badge bg-warning p-1">Rejeitado</span>
                                            @else
                                            <span class="badge bg-info p-1">Novo</span>
                                            @endif
                                        </td>
                                        <td data-label="Ambiente">{{ $item->ambiente == 2 ? 'Homologação' : 'Produção' }}</td>
                                        @endif

                                        <td data-label="Data">{{ __data_pt($item->created_at) }}</td>

                                        <!-- <td data-label="Local de emissão">
                                            @if($item->api)
                                            <span class="text-success">API</span>
                                            @else
                                            <span class="text-primary">Painel</span>
                                            @endif
                                        </td> -->

                                        <td data-label="CRT">
                                            @if($item->crt == 1)
                                            <span class="text-info">Simples Nacional</span>
                                            @elseif($item->crt == 2)
                                            <span class="text-primary">Simples Nacional, excesso sublimite de receita bruta</span>
                                            @elseif($item->crt == 3)
                                            <span class="text-primary">Regime Normal</span>
                                            @endif
                                        </td>

                                        <td data-label="Tipo">
                                            @if($item->tpNF)
                                            <span class="text-success">Saída</span>
                                            @else
                                            <span class="text-primary">Entrada</span>
                                            @endif
                                        </td>

                                        <td>
                                            <form action="{{ route('devolucao.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 360px">
                                                @method('delete')
                                                @csrf

                                                @if($item->estado == 'cancelado')
                                                <a class="btn btn-danger btn-sm" target="_blank" href="{{ route('nfe.imprimir-cancela', [$item->id]) }}">
                                                    <i class="ri-printer-line"></i>
                                                </a>
                                                @endif

                                                @if($item->estado == 'aprovado')
                                                <button type="button" onclick="imprimir('{{$item->id}}', '{{$item->numero}}')" class="btn btn-primary btn-sm" title="Imprimir NFe">
                                                    <i class="ri-printer-line"></i>
                                                </button>

                                                <a title="Baixar XML" class="btn btn-sm btn-dark" href="{{ route('nfe.download-xml', [$item->id]) }}">
                                                    <i class="ri-download-line"></i>
                                                </a>

                                                @can('nfe_transmitir')
                                                <button title="Cancelar NFe" type="button" class="btn btn-danger btn-sm" onclick="cancelar(
                                                    '{{$item->id}}',
                                                    '{{$item->numero}}',
                                                    '{{$item->numero_serie}}',
                                                    '{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}',
                                                    '{{ $item->cliente ? $item->cliente->info : $item->fornecedor->info }}',
                                                    '{{$item->chave}}'
                                                    )">
                                                    <i class="ri-close-circle-line"></i>
                                                </button>
                                                <button title="Corrigir NFe" type="button" class="btn btn-warning btn-sm" onclick="corrigir(
                                                    '{{$item->id}}',
                                                    '{{$item->numero}}',
                                                    '{{$item->numero_serie}}',
                                                    '{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}',
                                                    '{{ $item->cliente ? $item->cliente->info : $item->fornecedor->info }}',
                                                    '{{$item->chave}}'
                                                    )">
                                                    <i class="ri-file-warning-line"></i>
                                                </button>
                                                @endcan
                                                @endif

                                                @if($item->estado == 'aprovado' || $item->estado == 'rejeitado')
                                                <button title="Consultar status" type="button" class="btn btn-dark btn-sm" onclick="info('{{$item->motivo_rejeicao}}', '{{$item->chave}}', '{{$item->estado}}', '{{$item->recibo}}')">
                                                    <i class="ri-file-line"></i>
                                                </button>
                                                @endif

                                                @if($item->estado == 'novo' || $item->estado == 'rejeitado')
                                                @can('devolucao_edit')
                                                <a class="btn btn-warning btn-sm" href="{{ route('devolucao.edit', $item->id) }}">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                @endcan

                                                @if(__isPlanoFiscal())
                                                <a target="_blank" title="XML temporário" class="btn btn-light btn-sm" href="{{ route('nfe.xml-temp', $item->id) }}">
                                                    <i class="ri-file-line"></i>
                                                </a>
                                                @endif

                                                @can('devolucao_delete')
                                                <button title="Remover devolução" type="button" class="btn btn-danger btn-sm btn-delete"><i class="ri-delete-bin-line"></i></button>
                                                @endcan

                                                @if(__isPlanoFiscal())
                                                @can('nfe_transmitir')
                                                <button title="Transmitir NFe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$item->id}}')">
                                                    <i class="ri-send-plane-fill"></i>
                                                </button>
                                                @endcan
                                                @endif
                                                @endif

                                                @if($item->estado == 'aprovado' || $item->estado == 'cancelado')
                                                <button title="Consultar NFe" type="button" class="btn btn-light btn-sm" onclick="consultar('{{$item->id}}', '{{$item->numero}}')">
                                                    <i class="ri-file-search-line"></i>
                                                </button>
                                                @endif

                                                @if(__isPlanoFiscal())
                                                @can('devolucao_edit')
                                                <a title="Alterar estado fiscal devolução" class="btn btn-dark btn-sm" href="{{ route('nfe.alterar-estado', [$item->id, 'tipo=devolucao']) }}">
                                                    <i class="ri-arrow-up-down-line"></i>
                                                </a>
                                                @endcan
                                                @endif

                                                @if($item->estado != 'aprovado')
                                                <a class="btn btn-danger btn-sm" title="DANFE Temporária" target="_blank" href="{{ route('nfe.danfe-temporaria', [$item->id]) }}">
                                                    <i class="ri-printer-fill"></i>
                                                </a>
                                                @endif
                                            </form>
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

                <div class="total-mini-card">
                    <div class="total-mini-icon">
                        <i class="ri-money-dollar-circle-line"></i>
                    </div>

                    <div class="total-mini-info">
                        <span>Total somado</span>
                        <h5>R$ {{ __moeda($data->sum('total')) }}</h5>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

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

    $('#btn-consulta-sefaz').click(() => {
        $.post(path_url + 'api/nfe_painel/consulta-status-sefaz', {
            usuario_id: $('#usuario_id').val(),
            empresa_id: $('#empresa_id').val()
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

</script>
<script type="text/javascript" src="/js/nfe_transmitir.js"></script>
@endsection
