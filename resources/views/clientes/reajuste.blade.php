@extends('layouts.app', ['title' => 'Reajuste de Clientes'])
@section('css')
<style type="text/css">
    .div-overflow{ width:180px; overflow-x:auto; white-space:nowrap; }

    .table-responsive{ overflow:visible; }

    .tabela-scroll{
        width:100%;
        overflow-x:auto;
        overflow-y:visible;
        position:relative;
    }

    .tabela-scroll table{
        width:max-content;
        min-width:100%;
        border-collapse:separate;
        border-spacing:0;
    }

    .tabela-scroll th,
    .tabela-scroll td{
        white-space:nowrap;
        vertical-align:middle;
    }

    .tabela-scroll th:first-child,
    .tabela-scroll td:first-child{
        position:sticky;
        left:0;
        z-index:4;
        min-width:320px;
        width:320px;
        background:#fff !important;
        box-shadow:3px 0 8px rgba(0,0,0,.08);
    }

    .tabela-scroll thead th:first-child{
        z-index:8;
        background:#212529 !important;
        color:#fff;
    }

    .tabela-scroll tbody tr:nth-of-type(odd) td:first-child{
        background:#f8f9fa !important;
    }

    .tabela-scroll tbody tr:nth-of-type(even) td:first-child{
        background:#fff !important;
    }

    .tabela-scroll td:first-child input{
        width:100% !important;
        min-width:280px;
    }

    .tabela-scroll .form-control,
    .tabela-scroll .form-select{
        max-width:none;
    }
</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">

                <hr class="mt-3">
                <div class="col-lg-12">
                    {!!Form::open()->fill(request()->all())
                    ->get()
                    !!}
                    <div class="row mt-3 g-2">

                        <div class="col-md-2">
                            {!!Form::select('contribuinte', 'Contribuinte', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('consumidor_final', 'Consumidor final', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('pendentes', 'Dados pendentes', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::date('data_cadastro', 'Data de cadastro')
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('tipo_documento', 'Tipo do doc.', ['' => 'Todos', 'cpf' => 'CPF', 'cnpj' => 'CNPJ'])
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        
                        <div class="col-md-2 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('clientes.reajuste') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                            
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>

                @if(sizeof($data) > 0)
                <br>
                <hr>
                <form method="post" action="{{ route('clientes-reajuste.update') }}">
                    @csrf


                    <div class="col-md-12 mt-3">
                        <div class="table-responsive">
                            <div class="tabela-scroll" style="overflow-x:auto;">

                                <h6>Total de registros: <strong>{{ sizeof($data) }}</strong></h6>
                                <table class="table table-striped table-centered mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Razão social</th>
                                            <th>Nome fantasia</th>

                                            <th>CPF/CNPJ</th>
                                            <th>IE</th>
                                            <th>Contribuinte</th>
                                            <th>Consumidor final</th>
                                            <th>Rua</th>
                                            <th>Número</th>
                                            <th>Bairro</th>
                                            <th>Cidade</th>
                                            <th>CEP</th>
                                            <th>Ativo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($data as $item)
                                        <tr>

                                            <td class="sticky-col">
                                                <input class="form-control" type="text" name="razao_social[]" style="width: 300px" value="{{ $item->razao_social }}">
                                            </td>
                                            <input type="hidden" name="cliente_id[]" value="{{ $item->id }}">

                                            <td>
                                                <input class="form-control" type="text" name="nome_fantasia[]" style="width: 300px" value="{{ $item->nome_fantasia }}">
                                            </td>

                                            <td>
                                                <input style="width: 200px" type="tel" class="form-control cpf_cnpj" name="cpf_cnpj[]" value="{{ $item->cpf_cnpj }}">
                                            </td>
                                            <td>
                                                <input style="width: 150px" type="tel" class="form-control ie" name="ie[]" value="{{ $item->ie }}">
                                            </td>

                                            <td>
                                                <select class="form-select contribuinte" name="contribuinte[]">
                                                    <option @if($item->contribuinte == 1) selected @endif value="1">Sim</option>
                                                    <option @if($item->contribuinte == 0) selected @endif value="0">Não</option>
                                                </select>
                                                @if($loop->first)
                                                <a onclick="setContribuinte()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
                                                @endif
                                                <div style="width: 180px;"></div>
                                            </td>
                                            
                                            <td>
                                                <select class="form-select consumidor_final" name="consumidor_final[]">
                                                    <option @if($item->consumidor_final == 1) selected @endif value="1">Sim</option>
                                                    <option @if($item->consumidor_final == 0) selected @endif value="0">Não</option>
                                                </select>
                                                @if($loop->first)
                                                <a onclick="setConsumidorFinal()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
                                                @endif
                                                <div style="width: 180px;"></div>
                                            </td>

                                            <td>
                                                <input style="width: 300px" type="text" class="form-control" name="rua[]" value="{{ $item->rua }}">
                                            </td>
                                            <td>
                                                <input style="width: 100px" type="text" class="form-control" name="numero[]" value="{{ $item->numero }}">
                                            </td>
                                            <td>
                                                <input style="width: 100px" type="text" class="form-control" name="bairro[]" value="{{ $item->bairro }}">
                                            </td>

                                            <td>
                                                <select class="form-select cidade_select2" name="cidade_id[]">
                                                    @if($item->cidade)
                                                    <option value="{{ $item->cidade_id }}">{{ $item->cidade->info }}</option>
                                                    @endif
                                                </select>
                                                
                                                <div style="width: 300px;"></div>
                                            </td>
                                            <td>
                                                <input style="width: 100px" type="text" class="form-control cep" name="cep[]" value="{{ $item->cep }}">
                                            </td>
                                            <td>
                                                <select class="form-select status" name="status[]">
                                                    <option @if($item->status == 1) selected @endif value="1">Sim</option>
                                                    <option @if($item->status == 0) selected @endif value="0">Não</option>
                                                </select>
                                                @if($loop->first)
                                                <a onclick="setStatus()" style="font-size: 12px" href=#!>Definir para os demais itens</a>
                                                @endif
                                                <div style="width: 180px;"></div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="40" class="text-center">Filtre para buscar os clientes</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button type="button" id="scrollToggle2" class="scroll-btn-jidox hidden">
                            <i class="ri-arrow-right-circle-line"></i>
                        </button>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success float-end mt-3">Salvar</button>
                    </div>
                    <br>
                </form>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    function setContribuinte(){
        let v = $('.contribuinte').first().val()
        $('.contribuinte').val(v).change()
    }
    function setConsumidorFinal(){
        let v = $('.consumidor_final').first().val()
        $('.consumidor_final').val(v).change()
    }

    function setStatus(){
        let v = $('.status').first().val()
        $('.status').val(v).change()
    }
</script>
@endsection

