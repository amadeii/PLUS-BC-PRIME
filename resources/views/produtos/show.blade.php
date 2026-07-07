@extends('layouts.app', ['title' => 'Movimentações'])
@section('css')
<style type="text/css">
    @page { size: auto;  margin: 0mm; }

    @media print {
        .print{
            margin: 10px;
        }
    }
    .audit-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: 0.2s;
    }

    .audit-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }

    .audit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .audit-user {
        font-weight: 600;
        color: #374151;
    }

    .audit-action {
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .audit-create {
        background: #dcfce7;
        color: #166534;
    }

    .audit-update {
        background: #fef3c7;
        color: #92400e;
    }

    .audit-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .audit-field {
        font-weight: 600;
        color: #374151;
        margin-bottom: 3px;
    }

    .audit-box-before {
        background: #fff1f2;
        border: 1px solid #fecdd3;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        color: #7f1d1d;
    }

    .audit-box-after {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 8px;
        border-radius: 6px;
        font-size: 13px;
        color: #14532d;
    }

    .audit-date {
        font-size: 12px;
        color: #9ca3af;
    }
</style>
@endsection
@section('content')
<div class="mt-1 print">
    <div class="row">

        <div class="card">
            <div class="card-body">

                <!-- Invoice Logo-->
                <div class="clearfix">
                    <div class="float-start mb-3">
                        <img class="img-60" src="{{ $item->img }}" height="60">
                    </div>
                    <div class="float-end">
                        <h4 class="m-0">{{ $item->nome }}</h4>
                    </div>
                </div>

                <!-- Invoice Detail-->
                <div class="row">
                    <div class="col-sm-6">
                        <div class=" mt-3">
                            <p><b>Total de movimentações: <strong class="text-success">{{ sizeof($data) }}</strong></b></p>
                            <p><b>Categoria: <strong class="text-success">{{ $item->categoria ? $item->categoria->nome : '--' }}</strong></b></p>
                            <p><b>Marca: <strong class="text-success">{{ $item->marca ? $item->marca->nome : '--' }}</strong></b></p>
                        </div>

                    </div><!-- end col -->
                    <div class="col-sm-4 offset-sm-2">
                        <div class="mt-3 float-sm-end">
                            <p class="fs-15"><strong>Valor de venda: </strong>R$ {{ __moeda($item->valor_unitario) }}</p>
                            <p class="fs-15"><strong>Valor de compra: </strong>R$ {{ __moeda($item->valor_compra) }}</p>
                            <p class="fs-15"><strong>Data de cadastro: </strong>{{ __data_pt($item->created_at, 0) }}</p>
                        </div>
                    </div><!-- end col -->
                </div>
                <!-- end row -->

                <div class="row mt-4">
                    <div class="col-8">

                    </div>

                    <div class="col-4">
                        <div class="text-sm-end">
                            {{ $item->codigo_barras }}
                        </div>
                    </div> <!-- end col-->
                </div>    
                <!-- end row -->        

                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-centered table-hover table-borderless mb-0 mt-3">
                                <thead class="border-top border-bottom bg-light-subtle border-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Quantidade modificada</th>
                                        <th>Quantidade estoque</th>
                                        <th>Tipo</th>
                                        <th>Usuário</th>
                                        <th>Data</th>
                                        <th>Tipo</th>
                                        <th>Variação</th>
                                        <th class="d-print-none">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $i)
                                    <tr>
                                        <td>{{ $i->id }}</td>
                                        <td>
                                            @if(!$i->produto->unidadeDecimal())
                                            {{ number_format($i->quantidade, 0, '.', '') }}
                                            @else
                                            {{ number_format($i->quantidade, 3) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($i->estoque_atual)
                                            @if(!$i->produto->unidadeDecimal())
                                            {{ number_format($i->estoque_atual, 0, '.', '') }}
                                            @else
                                            {{ number_format($i->estoque_atual, 3) }}
                                            @endif
                                            @else
                                            --
                                            @endif
                                        </td>
                                        <td>{{ $i->tipoTransacao() }}</td>
                                        <td>{{ $i->user ? $i->user->name : '' }}</td>
                                        <td>{{ __data_pt($i->created_at) }}</td>
                                        <td>{{ $i->tipo == 'incremento' ? 'Incremento' : 'Redução' }}</td>
                                        <td>{{ $i->produtoVariacao ? $i->produtoVariacao->descricao : '--' }}</td>
                                        <td class="d-print-none">
                                            <a class="btn btn-dark btn-sm" href="{{ route('produtos.movimentacao', [$i->id]) }}">
                                                visualizar
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div> <!-- end table-responsive-->
                    </div> <!-- end col -->
                </div>
                <!-- end row -->

                <div class="row">
                    <div class="col-sm-6">
                        <div class="clearfix pt-3">

                        </div>
                    </div> <!-- end col -->
                    <div class="col-sm-6">
                        <div class="float-end mt-3">
                            <p><b>Soma quantidade modificada: </b> 
                                <span class="float-end text-primary" style="margin-left: 3px">
                                    @if(!$item->unidadeDecimal())
                                    {{ number_format($data->sum('quantidade'), 0, '.', '') }}
                                    @else
                                    {{ number_format($data->sum('quantidade'), 3) }}
                                    @endif
                                </span>
                            </p>
                        </div>
                        <div class="clearfix"></div>
                    </div> <!-- end col -->
                </div>
                <!-- end row-->
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary">Fornecedores do produto</h5>

                        <div class="table-responsive">
                            <table class="table table-sm table-centered table-hover table-borderless mb-0 mt-3">
                                <thead class="border-top border-bottom bg-light-subtle border-light">
                                    <tr>
                                        <th>Razão social</th>
                                        <th>CPF/CNPJ</th>
                                        <th>Rua</th>
                                        <th>Número</th>
                                        <th>Bairro</th>
                                        <th>Cidade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->fornecedores as $i)
                                    <tr>
                                        <td>{{ $i->fornecedor->razao_social }}</td>
                                        <td>{{ $i->fornecedor->cpf_cnpj }}</td>
                                        <td>{{ $i->fornecedor->rua }}</td>
                                        <td>{{ $i->fornecedor->numero }}</td>
                                        <td>{{ $i->fornecedor->bairro }}</td>
                                        <td>{{ $i->fornecedor->cidade->info }}</td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary">Serial de entrada</h5>

                        <div class="table-responsive">
                            <table class="table table-sm table-centered table-hover table-borderless mb-0 mt-3">
                                <thead class="border-top border-bottom bg-light-subtle border-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produtoUnico as $i)
                                    @if($i->tipo == 'entrada')
                                    <tr>
                                        <td>{{ $i->codigo }}</td>
                                        <td>{{ $i->observacao ?? '--' }}</td>

                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-primary">Serial de saída</h5>

                        <div class="table-responsive">
                            <table class="table table-sm table-centered table-hover table-borderless mb-0 mt-3">
                                <thead class="border-top border-bottom bg-light-subtle border-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produtoUnico as $i)
                                    @if($i->tipo == 'saida')
                                    <tr>
                                        <td>{{ $i->codigo }}</td>
                                        <td>{{ $i->observacao ?? '--' }}</td>

                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Histórico de alterações</h4>
                    </div>
                    <div class="card-body">

                        @foreach($auditorias as $auditoria)
                        <div class="audit-card">

                            <div class="audit-header">
                                <div>
                                    <span class="audit-user">
                                        {{ $auditoria->usuario->name ?? 'Sistema' }}
                                    </span>

                                    @if($auditoria->acao == 'criar')
                                    <span class="audit-action audit-create">CRIADO</span>
                                    @elseif($auditoria->acao == 'editar')
                                    <span class="audit-action audit-update">EDITADO</span>
                                    @elseif($auditoria->acao == 'excluir')
                                    <span class="audit-action audit-delete">EXCLUÍDO</span>
                                    @endif
                                </div>

                                <div class="audit-date">
                                    {{ $auditoria->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            @if($auditoria->alteracoes_json)
                            @foreach($auditoria->alteracoes_json as $campo => $alteracao)
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <div class="audit-field">
                                        {{ auditoriaLabelCampo($campo) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="audit-box-before">
                                        <strong>Antes</strong><br>
                                        {{ auditoriaValor($alteracao['antes']) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="audit-box-after">
                                        <strong>Depois</strong><br>
                                        {{ auditoriaValor($alteracao['depois']) }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif

                        </div>
                        @endforeach

                    </div>
                </div>
                <div class="d-print-none mt-4">
                    <div class="text-end">
                        <a href="javascript:window.print()" class="btn btn-primary"><i class="ri-printer-line"></i> Imprimir</a>

                    </div>
                </div>   
                <!-- end buttons -->

            </div>
        </div>
    </div>
</div>
@endsection