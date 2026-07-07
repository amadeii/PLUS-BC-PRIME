@extends('layouts.app', ['title' => 'MDFe'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    @can('mdfe_create')
                    <a href="{{ route('mdfe.create') }}" class="btn btn-success mt-1">
                        <i class="ri-add-circle-fill"></i> Nova MDFe
                    </a>
                    @endcan
                    <a href="{{ route('mdfe.nao-encerrados') }}" type="button" class="btn btn-danger mt-1">
                        <i class="ri-close-fill"></i> Ver documentos não encerrados
                    </a>

                    @can('mdfe_create')
                    <button class="btn btn-dark mt-1" id="btn-importar_nfe" data-bs-toggle="modal" data-bs-target="#modal-importar_nfe">
                        <i class="ri-file-upload-fill"></i> Selecionar Documentos NFe
                    </button>
                    @endcan

                    <a href="{{ route('mdfe-importacao.index') }}" type="button" class="btn btn-warning mt-1">
                        <i class="ri-upload-2-line"></i> Importar XML
                    </a>
                </div>

                @if(session('mdfe_importacao_mensagens'))

                <div class="alert alert-info mt-3">

                    <div class="d-flex align-items-center mb-2">
                        <i class="ri-information-line me-2 fs-5"></i>
                        <strong>Resultado da importação</strong>
                    </div>

                    <div style="max-height: 250px; overflow-y: auto;">

                        @foreach(session('mdfe_importacao_mensagens') as $msg)

                        <div class="border-bottom py-1 small">
                            <i class="ri-checkbox-circle-line text-success"></i>
                            {{ $msg }}
                        </div>

                        @endforeach

                    </div>

                </div>

                @endif

                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data final')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado',
                            ['novo' => 'Nova',
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

                        <div class="col-md-2">
                            {!!Form::select('importado', 'Importado', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('encerrado', 'Encerrado', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('mdfe.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
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
                                        <th>Ações</th>
                                        <th>Data Início da Viagem</th>
                                        <th>Data Cadastro</th>
                                        @if(__countLocalAtivo() > 1)
                                        <th>Local</th>
                                        @endif
                                        <th>CNPJ Contratante</th>
                                        <th>Estado Fiscal</th>
                                        <th>Encerrado</th>
                                        <th>Chave</th>
                                        <th>Número</th>
                                        <th>Veículo Tração</th>
                                        <th>Quantidade Carga</th>
                                        <th>Valor Carga</th>
                                        <th>Local de emissão</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>
                                            <form action="{{ route('mdfe.destroy', $item->id) }}" method="post" id="form-{{$item->id}}" style="width: 350px">
                                                @method('delete')
                                                @csrf
                                                @if($item->estado_emissao == 'aprovado')
                                                <a class="btn btn-primary btn-sm" target="_blank" href="{{ route('mdfe.imprimir', [$item->id]) }}">
                                                    <i class="ri-printer-line"></i>
                                                </a>
                                                <a class="btn btn-light btn-sm" target="_blank" href="{{ route('mdfe.download', [$item->id]) }}">
                                                    <i class="ri-download-2-fill"></i>
                                                </a>
                                                <button title="Cancelar MDFe" type="button" class="btn btn-danger btn-sm" onclick="cancelar('{{$item->id}}', '{{$item->numero}}')">
                                                    <i class="ri-close-circle-line"></i>
                                                </button>
                                                @endif

                                                @if($item->estado_emissao == 'aprovado' || $item->estado_emissao == 'rejeitado')
                                                <button type="button" class="btn btn-info btn-sm" onclick="info('{{$item->motivo_rejeicao}}', '{{$item->chave}}', '{{$item->estado}}', '{{$item->recibo}}')">
                                                    <i class="ri-file-line"></i>
                                                </button>
                                                @endif

                                                @if($item->estado_emissao == 'novo' || $item->estado_emissao == 'rejeitado')
                                                @can('mdfe_edit')
                                                <a class="btn btn-warning btn-sm" href="{{ route('mdfe.edit', $item->id) }}">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                @endcan
                                                @if($item->estado != 'aprovado')
                                                <a target="_blank" title="XML temporário" class="btn btn-light btn-sm" href="{{ route('mdfe.xml-temp', $item->id) }}">
                                                    <i class="ri-file-line"></i>
                                                </a>
                                                <a class="btn btn-danger btn-sm" title="DANFE Temporária" target="_blank" href="{{ route('mdfe.damdfe-temporaria', [$item->id]) }}">
                                                    <i class="ri-printer-fill"></i>
                                                </a>
                                                @endif
                                                @can('mdfe_delete')
                                                <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                @endcan
                                                <button title="Transmitir MDFe" type="button" class="btn btn-success btn-sm" onclick="transmitir('{{$item->id}}')">
                                                    <i class="ri-send-plane-fill"></i>
                                                </button>
                                                @endif

                                                @if($item->estado_emissao == 'aprovado' || $item->estado_emissao == 'cancelado')
                                                <button title="Consultar MDFe" type="button" class="btn btn-light btn-sm" onclick="consultar('{{$item->id}}', '{{$item->numero}}')">
                                                    <i class="ri-file-search-line"></i>
                                                </button>
                                                @endif

                                                <a title="Alterar estado fiscal" class="btn btn-dark btn-sm" href="{{ route('mdfe.alterar-estado', $item->id) }}">
                                                    <i class="ri-arrow-up-down-line"></i>
                                                </a>

                                                @can('mdfe_create')
                                                <a class="btn btn-primary btn-sm" href="{{ route('mdfe.duplicar', $item->id) }}" title="Duplicar MDFe">
                                                    <i class="ri-file-copy-line"></i>
                                                </a>
                                                @endcan

                                                @if($item->estado_emissao == 'aprovado' && !$item->encerrado)

                                                <a title="Encerrar MDF-e"
                                                href="#"
                                                class="btn btn-warning btn-sm btn-encerrar-mdfe"
                                                data-url="{{ route('mdfe.encerrar', ['chave' => $item->chave, 'protocolo' => $item->protocolo, 'empresa_id' => $item->empresa_id]) }}">

                                                <i class="ri-lock-line"></i>

                                            </a>

                                            @endif
                                        </form>
                                    </td>
                                    <td data-label="Data Início da Viagem">{{ __data_pt($item->data_inicio_viagem, 0) }}</td>
                                    <td data-label="Data Criação">{{ __data_pt($item->created_at) }}</td>
                                    @if(__countLocalAtivo() > 1)
                                    <td data-label="Local" class="text-danger">{{ $item->localizacao->descricao }}</td>
                                    @endif
                                    <td data-label="CNPJ Contratante">{{ $item->cnpj_contratante }}</td>
                                    <td data-label="Estado Fiscal">{!! $item->estadoEmissao($item->estado_emissao) !!}</td>
                                    <td data-label="Encerrado">
                                        @if($item->encerrado)
                                        <span class="badge bg-success">
                                            <i class="ri-check-line"></i> Sim
                                        </span>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="ri-time-line"></i> Não
                                        </span>
                                        @endif
                                    </td>
                                    <td data-label="Chave">{{ $item->chave }}</td>
                                    <td data-label="Número">{{ $item->mdfe_numero > 0 ? $item->mdfe_numero : '--' }}</td>
                                    <td data-label="Veículo Tração">{{ $item->veiculoTracao->marca }} - {{ $item->veiculoTracao->placa }}</td>
                                    <td data-label="Quantidade Carga">{{ number_format($item->quantidade_carga, 3, '.', '') }}</td>
                                    <td data-label="Valor Carga">{{ __moeda($item->valor_carga) }}</td>
                                    <td data-label="Local de emissão">
                                        @if($item->api)
                                        <span class="text-success">API</span>
                                        @else
                                        <span class="text-primary">Painel</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">Nada encontrado</td>
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
                <h5 class="modal-title" id="exampleModalLabel">Cancelar MDFe <strong class="ref-numero"></strong></h5>
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

@section('js')
<script type="text/javascript">
    function info(motivo_rejeicao, chave, estado, recibo) {

        if (estado == 'rejeitado') {
            let text = "Motivo: " + motivo_rejeicao + "\n"
            text += "Chave: " + chave + "\n"
            swal("", text, "warning")
        } else {
            let text = "Chave: " + chave + "\n"
            swal("", text, "success")
        }
    }


    $(document).on('click', '.btn-encerrar-mdfe', function(e){

        e.preventDefault();

        let url = $(this).data('url');

        swal({
            title: "Encerrar MDF-e?",
            text: "Após o encerramento a MDF-e será finalizada na SEFAZ.",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "Cancelar",
                    visible: true,
                    className: "swal-button--cancel"
                },
                confirm: {
                    text: "Sim, encerrar",
                    className: "swal-button--confirm"
                }
            },
            dangerMode: false
        }).then((ok) => {

            if(ok){

                $('body').addClass('loading');

                window.location.href = url;
            }

        });

    });

</script>
<script type="text/javascript" src="/js/mdfe.js"></script>
<script type="text/javascript" src="/js/mdfe_transmitir.js"></script>

@endsection

@include('modals._importar_nfe')

@endsection
