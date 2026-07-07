@extends('layouts.app', ['title' => 'Clientes'])
@section('css')
<style type="text/css">
    .img-wrapper {
        height: 180px;
        overflow: hidden;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
        background-color: #f8f9fa;
    }
    .produto-img {
        height: 100%;
        width: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .produto-card {
        border-radius: 1rem;
        transition: all 0.3s ease;
        background-color: #fff;
    }
    .produto-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }
    .produto-card:hover .produto-img {
        transform: scale(1.05);
    }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    @can('clientes_create')
                    <a href="{{ route('clientes.create') }}" class="btn btn-success">
                        <i class="ri-add-circle-fill"></i>
                        Novo Cliente
                    </a>
                    
                    <a href="{{ route('clientes.import') }}" class="btn btn-info pull-right">
                        <i class="ri-file-upload-line"></i>
                        Upload
                    </a>
                    @endcan

                    @can('clientes_edit')
                    <a href="{{ route('clientes.incompleto') }}" class="btn btn-secondary pull-right">
                        <i class="ri-list-settings-fill"></i>
                        Cadastro incompleto
                    </a>

                    <a href="{{ route('clientes.reajuste') }}" class="btn btn-dark pull-right">
                        <i class="ri-file-edit-fill"></i>
                        Reajuste em Grupo
                    </a>
                    @endcan

                    @can('score_clientes_view')
                    <a href="{{ route('clientes-score.index') }}" class="btn btn-primary pull-right">
                        <i class="ri-medal-fill"></i>
                        Score
                    </a>
                    @endcan

                </div>
                <hr class="mt-3">
                <div class="col-lg-12">

                    <button class="btn btn-dark btn-toggle-filtros">
                        <i class="ri-filter-2-line"></i> Filtros
                    </button> 

                    {!!Form::open()->fill(request()->all())
                    ->get()->attrs(['class' => 'filtros-container'])
                    !!}
                    <div class="row mt-3 g-1">
                        <div class="col-md-3">
                            {!!Form::text('razao_social', 'Pesquisar por nome')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::text('cpf_cnpj', 'Pesquisar por CPF/CNPJ')
                            ->attrs(['class' => 'cpf_cnpj'])
                            ->type('tel')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('start_date', 'Data inicial cadastro')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('end_date', 'Data final cadastro')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('ordem', 'Ordenar por', ['razao_social' => 'Razão social', 'numero_sequencial' => 'Código', 'created_at' => 'Data de cadastro'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('valor_credito', 'Valor em crédito', ['' => 'Todos', '1' => 'Sim', '0' => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::select('categoria', 'Categoria', ['' => 'Todos'] + \App\Models\ClienteScore::categorias())
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('consumidor_final', 'Consumidor final', ['' => 'Todos', '1' => 'Sim', '-1' => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-md-3 text-left ">
                            <br>

                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('clientes.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}

                    <div class="card">
                        <div class="">
                            {!! Form::open()->fill(request()->all())->get()->attrs(['class' => 'busca-especifica-form filtros-container']) !!}

                            <div class="row g-2">
                                <div class="col-md-2">
                                    {!! Form::select('nome_campo', 'Busca específica', [
                                    '' => 'Selecione',
                                    'razao_social' => 'Razão Social',
                                    'nome_fantasia' => 'Nome Fantasia',
                                    'telefone' => 'Telefone',
                                    'email' => 'Email',
                                    'numero_sequencial' => 'Código',
                                    'rua' => 'Rua',
                                    'numero' => 'Número',
                                    'bairro' => 'Bairro',
                                    ])
                                    ->attrs(['class' => 'form-select']) !!}
                                </div>

                                <div class="col-md-4">
                                    {!! Form::text('valor_campo', 'Digite o que procura')
                                    ->attrs([
                                    'placeholder' => 'Ex: 7891234567890',
                                    'autocomplete' => 'off'
                                    ]) !!}
                                </div>
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

                @if($tipoExibe == 'tabela')
                @include('clientes.partials.tabela')
                @else
                @include('clientes.partials.card')
                @endif

                <br>
                @can('clientes_delete')
                <form action="{{ route('clientes.destroy-select') }}" method="post" id="form-delete-select">
                    @method('delete')
                    @csrf
                    <div></div>
                    <button type="button" class="btn btn-danger btn-sm btn-delete-all" disabled>
                        <i class="ri-close-circle-line"></i> Remover selecionados
                    </button>
                </form>
                @endcan
                
                <br>
                {!! $data->appends(request()->all())->links() !!}
            </div>
        </div>
    </div>
</div>
@include('modals._crm')
@include('clientes.partials.modal_info')

@endsection
@section('js')
<script type="text/javascript" src="/js/delete_selecionados.js"></script>
<script type="text/javascript">
    function modalCrm(cliente_id){
        $('#cliente_id').val(cliente_id)
        $('#modal_crm').modal('show')
        montaSelect2()
    }

    function openModal(id) {
        $.get(path_url + "clientes-modal/"+id)
        .done((data) => {
            // console.log(data)
            $('#modal-info').modal('show')
            $('#modal-info .modal-content').html(data)
        })
        .fail((e) => {
            console.log(e)
        })
    }

    $('input[name="valor_campo"]').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).closest('form').submit();
        }
    });
</script>
<script type="text/javascript" src="/js/modal_crm.js"></script>

@endsection
