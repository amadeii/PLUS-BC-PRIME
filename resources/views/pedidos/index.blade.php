@extends('layouts.app', ['title' => 'Pedidos (Comandas)'])
@section('css')
<style type="text/css">
    .card-comanda{
        border-radius:14px;
        border:none;
        transition:all .2s ease;
        box-shadow:0 3px 10px rgba(0,0,0,0.08);
        cursor:pointer;
    }

    .card-comanda:hover{
        transform:translateY(-4px);
        box-shadow:0 10px 25px rgba(0,0,0,0.15);
    }

    .comanda-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:10px;
    }

    .comanda-numero{
        font-size:22px;
        font-weight:700;
        color:#111827;
    }

    .comanda-valor{
        font-size:24px;
        font-weight:700;
        color:#F97316;
    }

    .comanda-info{
        font-size:14px;
        color:#6B7280;
    }

    .comanda-mesa{
        font-size:14px;
        font-weight:600;
        color:#374151;
    }

    .badge-status{
        background:#FEF3C7;
        color:#92400E;
        font-size:12px;
        font-weight:600;
    }

    .btn-liberar{
        border-radius:8px;
    }
</style>
@endsection
@section('content')

<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <hr>
                <button class="btn btn-success px-3" type="button" data-bs-toggle="modal" data-bs-target="#modal-comanda">
                    <i class="ri-add-circle-fill"></i>
                    Abrir comanda
                </button>
                <div class="row mt-3">

                    @foreach($data as $item)

                    <div class="col-12 col-xl-3 col-md-6 col-lg-4 mb-3">

                        <div class="card card-comanda" onclick="window.location='{{ route('pedidos-cardapio.show',$item->id) }}'">

                            <div class="card-body">

                                <div class="comanda-header">

                                    <div class="comanda-numero">
                                        #{{ $item->comanda ?: '--' }}
                                    </div>

                                    @if(!$item->em_atendimento)
                                    <span class="badge badge-status">
                                        Pedindo conta
                                    </span>
                                    @endif

                                </div>


                                <div class="comanda-valor mb-2">
                                    R$ {{ __moeda($item->total) }}
                                </div>


                                <div class="comanda-info mb-1">
                                    <i class="ri-box-2-line"></i> {{ $item->itens->count() }} produtos
                                </div>

                                <div class="comanda-info mb-1 text-danger">
                                    @if($item->totalClientes() > 1)

                                    Mais de 1 cliente
                                    @else
                                    <i class="ri-user-line"></i> {{ $item->cliente_nome ?: 'Cliente não identificado' }}
                                    @endif
                                </div>
                                @if($item->_mesa)
                                <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2 rounded-pill">
                                    <i class="bi bi-grid-3x3-gap me-1"></i>
                                    {{ $item->_mesa->nome }}
                                </span>
                                @else
                                <span class="badge bg-light text-muted fw-semibold px-3 py-2 rounded-pill border">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Sem mesa
                                </span>
                                @endif

                                @if(!$item->confirma_mesa)
                                <form action="{{ route('pedidos-cardapio.liberar',$item->id) }}"
                                    method="get"
                                    class="mt-3"
                                    onclick="event.stopPropagation()">

                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-dark btn-sm btn-liberar w-100">
                                        <i class="ri-check-line"></i>
                                        Liberar mesa
                                    </button>
                                </form>
                                @endif
                            </div>


                            @if(__isAdmin())

                            <div class="card-footer border-0 pt-0" onclick="event.stopPropagation()">
                                <form action="{{ route('pedidos-cardapio.destroy', [$item->id]) }}" method="post" id="form-{{ $item->id }}">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-outline-danger btn-sm w-100 btn-delete">
                                        <i class="ri-delete-bin-line"></i>
                                        Remover comanda

                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>

                    @endforeach
                </div>

                <!-- balança -->

                @if(isset($balanca) && $balanca->count() > 0)

                <div class="mt-5">

                    <div class="d-flex align-items-center justify-content-between mb-3">

                        <div>
                            <h4 class="fw-bold text-warning mb-1">
                                <i class="ri-scales-3-line me-1"></i>
                                Pedidos da Balança
                            </h4>

                            <small class="text-muted">
                                Comandas geradas automaticamente pela balança
                            </small>
                        </div>

                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                            {{ $balanca->count() }} pedidos balança
                        </span>

                    </div>

                    <div class="row">
                        @if(__isAdmin())
                        <form action="{{ route('pedidos-cardapio.remover-todos-balanca') }}" method="post" class="m-2" id="form-remover-balanca">
                            @csrf
                            @method('delete')

                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removerTodosBalanca()">
                                <i class="ri-delete-bin-line"></i>
                                Remover todos
                            </button>
                        </form>
                        @endif
                        @foreach($balanca as $item)
                        <div class="col-12 col-xl-2 col-md-6 col-lg-3 mb-3">
                            <div class="card card-comanda border-warning" onclick="window.location='{{ route('pedidos-cardapio.show',$item->id) }}'">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">

                                        <div class="comanda-numero">
                                            #{{ $item->comanda ?: '--' }}
                                        </div>

                                        <span class="badge bg-warning text-dark">
                                            BALANÇA
                                        </span>
                                    </div>

                                    <div class="comanda-valor mb-2">
                                        R$ {{ __moeda($item->total) }}
                                    </div>

                                    <div class="comanda-info mb-1">
                                        <i class="ri-box-2-line"></i>
                                        {{ $item->itens->count() }} produtos
                                    </div>

                                    <div class="comanda-info text-muted">
                                        <i class="ri-time-line"></i>
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </div>

                                </div>

                                @if(__isAdmin())

                                <div class="card-footer border-0 pt-0" onclick="event.stopPropagation()">

                                    <form action="{{ route('pedidos-cardapio.destroy', [$item->id]) }}" method="post" id="form-balanca-{{ $item->id }}">
                                        @csrf
                                        @method('delete')

                                        <button class="btn btn-outline-danger btn-sm w-100 btn-delete">
                                            <i class="ri-delete-bin-line"></i>
                                            Remover pedido
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-comanda" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('pedidos-cardapio.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Abertura de Comanda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">

                        <div class="col-md-2">
                            {!!Form::text('comanda', 'Número comanda')
                            ->required()
                            ->attrs(['data-mask' => 'AAAAAAAA'])
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::select('mesa_id', 'Mesa', ['' => 'Selecione'] + $mesas->pluck('nome', 'id')->all())
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-4">
                            {!!Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2'])
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::text('cliente_nome', 'Cliente nome')
                            !!}
                        </div>

                        <div class="col-md-3">
                            {!!Form::text('cliente_fone', 'Cliente telefone')
                            ->attrs(['class' => 'fone'])
                            !!}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Abrir</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">

    function removerTodosBalanca(){
        swal({
            title: "Remover todos?",
            text: "Todos os pedidos da balança serão removidos.",
            icon: "warning",
            buttons: ["Cancelar", "Sim, remover"],
            dangerMode: true,
        }).then((ok) => {
            if(ok){
                document.getElementById('form-remover-balanca').submit();
            }
        });
    }

    $(".btn-liberar").on("click", function (e) {
        e.preventDefault();
        var form = $(this).parents("form").attr("id");
        swal({
            title: "Liberar mesa?",
            text: "O cliente vai contiar fazendo pedidos por seu aparelho!",
            icon: "warning",
            buttons: true,
            buttons: ["Cancelar", "OK"],
            dangerMode: true,
        }).then((isConfirm) => {
            if (isConfirm) {
                document.getElementById(form).submit();
            } else {
                swal("", "Este pedido não foi alterado", "info");
            }
        });
    });
    $(function(){

        setTimeout(() => {

            $('.modal #inp-cliente_id').each(function () {
                $(this).select2({
                    minimumInputLength: 2,
                    dropdownParent: $(this).parent(),
                    language: "pt-BR",
                    placeholder: "Digite para buscar o cliente",
                    theme: "bootstrap4",

                    ajax: {
                        cache: true,
                        url: path_url + "api/clientes/pesquisa",
                        dataType: "json",
                        data: function (params) {
                            console.clear();
                            var query = {
                                pesquisa: params.term,
                                empresa_id: $("#empresa_id").val(),
                            };
                            return query;
                        },
                        processResults: function (response) {
                            var results = [];

                            $.each(response, function (i, v) {
                                var o = {};
                                o.id = v.id;

                                o.text = v.razao_social + " - " + v.cpf_cnpj;
                                o.value = v.id;
                                results.push(o);
                            });
                            return {
                                results: results,
                            };
                        },
                    },
                });
            });
        }, 10)
    })

    $('body').on('change', '#inp-cliente_id', function () {
        let id = $(this).val()
        $.get(path_url + 'api/clientes/find/'+id)
        .done((success) => {
            $('#inp-cliente_nome').val(success.razao_social)
            $('#inp-cliente_fone').val(success.telefone)
        })
        .fail((err) => {
            console.log(err)
        })
    });
</script>
@endsection

