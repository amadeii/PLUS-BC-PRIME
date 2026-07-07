@extends('layouts.app', ['title' => 'Orçamentos'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @can('orcamento_create')
                    <div class="col-md-2">
                        <a href="{{ route('nfe.create', ['orcamento=1']) }}" class="btn btn-success">
                            <i class="ri-add-circle-fill"></i>
                            Novo Orçamento
                        </a>
                    </div>
                    @endcan

                    <div class="col-md-8"></div>
                    @can('ordem_separacao_view')
                    <div class="col-md-2 text-end">
                        <a href="{{ route('ordem-separacao.index') }}" class="btn btn-dark">
                            <i class="ri-search-line"></i>
                            Ordens de Separação
                        </a>
                    </div>
                    @endcan

                </div>
                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3">
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
                            {!!Form::select('funcionario_id', 'Funcionário', ['' => 'Todos'] + $funcionarios->pluck('nome', 'id')->all())
                            ->id('funcionario')
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        
                        <div class="col-lg-3 col-12">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('orcamentos.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>CPF/CNPJ</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th>Estado</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr>
                                    <td data-label="#"> {{ $item->numero_sequencial }} </td>
                                    <td data-label="Cliente"> {{ $item->cliente ? $item->cliente->razao_social : "--" }} </td>
                                    <td data-label="CPF/CNPJ"> {{ $item->cliente ? $item->cliente->cpf_cnpj : "--" }} </td>
                                    <td data-label="Valor"> {{ __moeda($item->total) }} </td>
                                    <td data-label="Data"> {{ __data_pt($item->created_at) }} </td>
                                    <td data-label="Estado"> {!! $item->estadoSeparacao() !!} </td>
                                    <td>
                                        <form style="width:200px;" action="{{ route('orcamentos.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            <a class="btn btn-primary btn-sm" href="javascript:void(0)" onclick="abrirModalImpressao('{{ $item->id }}')">
                                                <i class="ri-printer-line"></i>
                                            </a>
                                            @can('orcamento_edit')
                                            <a class="btn btn-warning btn-sm" href="{{ route('orcamentos.edit', $item->id) }}">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            @endcan
                                            @can('orcamento_delete')
                                            <button type="button" class="btn btn-danger btn-sm btn-delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endcan
                                            @can('nfe_create')
                                            <a title="Gerar venda" class="btn btn-dark btn-sm" href="{{ route('orcamentos.show', $item->id) }}">
                                                <i class="ri-file-line"></i>
                                            </a>
                                            @endcan

                                            @can('ordem_producao_create')

                                            @if(!$item->ordemProducao)
                                            <a title="Gerar ordem de produção" class="btn btn-warning btn-sm" href="{{ route('orcamentos.ordem-producao', $item->id) }}">
                                                <i class="ri-settings-4-line"></i>
                                            </a>
                                            @endif
                                            @endcan

                                            @if(env("ORDEMSEPARACAO") == 1)
                                            @if(!$item->ordemSeparacao)
                                            @can('ordem_separacao_create')
                                            <a title="Gerar ordem de separação" class="btn btn-success btn-sm" href="{{ route('ordem-separacao.create', ['orcamento_id='.$item->id]) }}">
                                                <i class="ri-file-search-line"></i>
                                            </a>
                                            @endcan

                                            @else
                                            @can('ordem_separacao_view')
                                            <a title="Ver ordem de separação" class="btn btn-success btn-sm" href="{{ route('ordem-separacao.show', [$item->ordemSeparacao->id]) }}">
                                                <i class="ri-file-search-line"></i>
                                            </a>
                                            @endcan

                                            @endif
                                            @endif

                                            @if($envioWppLink)
                                            <button title="Enviar Mensagem" onclick="enviarWpp('{{$item->id}}', 'nfe')" type="button" class="btn btn-success btn-sm">
                                                <i class="ri-whatsapp-fill"></i>
                                            </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    {!! $data->appends(request()->all())->links() !!}
                </div>
                <h5>Soma: <strong class="text-success">R$ {{ __moeda($data->sum('total')) }}</strong></h5>

                @if(sizeof($data) > 0 && request()->cliente_id)
                <form method="get" action="{{ route('orcamentos.gerar-venda-multipla') }}">
                    @foreach($data as $item)
                    <input type="hidden" value="{{ $item->id }}" name="orcamento_id[]">
                    @endforeach
                    <button class="btn btn-dark" type="submit">
                        Gerar venda dos orçamentos
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-wpp-envio" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Envio de Orçamento WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <h5 class="cliente_info"></h5>
                    <div class="col-md-3">
                        {!!Form::tel('telefone', 'Telefone')
                        ->attrs(['class' => 'fone'])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-12">
                        {!!Form::textarea('mensagem', 'Mensagem')
                        ->required()
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::checkbox('enviar_pedido_a4', 'Enviar Orçamento A4')
                        ->value(1)
                        !!}
                    </div>
                    <br>

                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-success btn-enviar-wpp">Enviar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-impressao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">
                    <i class="ri-printer-line text-primary me-1"></i>
                    Imprimir orçamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="orcamento_impressao_id">

                <div class="row g-3">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary w-100 py-3 rounded-4" onclick="imprimirFormato('a4')">
                            A4
                        </button>
                    </div>

                    <div class="col-6">
                        <button type="button" class="btn btn-dark w-100 py-3 rounded-4" onclick="imprimirFormato('80mm')">
                            80mm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">

    function abrirModalImpressao(id){
        $('#orcamento_impressao_id').val(id);
        $('#modal-impressao').modal('show');
    }

    function imprimirFormato(formato){
        let id = $('#orcamento_impressao_id').val();

        $('#modal-impressao').modal('hide');

        let disp_setting = "toolbar=yes,location=no,";
        disp_setting += "directories=yes,menubar=yes,";
        disp_setting += "scrollbars=yes,width=850,height=600,left=100,top=25";

        let url = path_url + "orcamentos/imprimir/" + id;

        if(formato == '80mm'){
            url = path_url + "orcamentos/imprimir-80mm/" + id;
        }

        let docprint = window.open(url, "", disp_setting);
        docprint.focus();
    }


    var ORCAMENTOID = 0;
    var TIPO = 0;
    function enviarWpp(id, tipo){

        ORCAMENTOID = id
        TIPO = tipo
        $.get(path_url + "api/envio-fatura-wpp", {id: id, tipo: tipo})
        .done((data) => {

            $('#modal-wpp-envio').modal('show')

            if(data.enviar_danfe_wpp_link == 1){
                $('#inp-enviar_danfe').attr('checked', true)
            }
            if(data.enviar_xml_wpp_link == 1){
                $('#inp-enviar_xml').attr('checked', true)
            }
            if(data.enviar_pedido_a4_wpp_link == 1){
                $('#inp-enviar_pedido_a4').attr('checked', true)
            }
            if(!data.telefone){
                swal("Alerta", "Telefone do cliente não cadastrado", "info")
            }
            $('#inp-telefone').val(data.telefone)
            $('#inp-mensagem').val(data.mensagem)
            $('.cliente_info').text(data.cliente_info)
        })
        .fail((err) => {
            console.log(err)
        })
    }

    $('.btn-enviar-wpp').click(() => {
        if(!$('#inp-mensagem').val()){
            toastr.error("Informe a mensagem")
            return
        }
        let telefone = $('#inp-telefone').val()

        if(telefone.length < 14){
            toastr.error("Informe o telefone corretamente")
            return
        }
        let data = {
            mensagem: $('#inp-mensagem').val(),
            telefone: telefone,
            enviar_danfe: $('#inp-enviar_danfe').is(':checked') ? 1 : 0,
            enviar_xml: $('#inp-enviar_xml').is(':checked') ? 1 : 0,
            enviar_pedido_a4: $('#inp-enviar_pedido_a4').is(':checked') ? 1 : 0,
            id: ORCAMENTOID,
            tipo: TIPO
        }

        $.post(path_url + "api/envio-fatura-wpp/create-files-orcamento", data)
        .done((data) => {
            telefone = telefone.replace(/\D/g, '');
            window.open(`https://wa.me/55${telefone}?text=${data}`, "_blank");
        })
        .fail((err) => {
            console.log(err)
        })
    })


</script>
@endsection

