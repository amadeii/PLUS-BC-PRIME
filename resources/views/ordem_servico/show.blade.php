@extends('layouts.app', ['title' => 'Ordem de Serviço'])
@section('css')
<style type="text/css">

</style>
@endsection
@section('content')

<div class="card mt-1">
    <div class="card-body">
        <div class="pl-lg-4">
            <div class="">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>
                                <h4 class="mb-1">
                                    OS #{{ $ordem->codigo_sequencial }}
                                </h4>

                                <div>
                                    Estado:
                                    @if($ordem->estado == 'pd')
                                    <span class="badge bg-warning fs-12">PENDENTE</span>
                                    @elseif($ordem->estado == 'ap')
                                    <span class="badge bg-success fs-12">APROVADO</span>
                                    @elseif($ordem->estado == 'rp')
                                    <span class="badge bg-danger fs-12">REPROVADO</span>
                                    @elseif($ordem->estado == 'fz')
                                    <span class="badge bg-info fs-12">FINALIZADO</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-danger btn-sm">
                                    <i class="ri-arrow-left-line"></i> Voltar
                                </a>
                            </div>
                        </div>

                        <hr>

                        <div class="row g-3">

                            <div class="col-md-3 col-6">
                                <small class="text-muted">Início</small>
                                <h5 class="mb-0 text-primary">{{ __data_pt($ordem->data_inicio) }}</h5>
                            </div>

                            <div class="col-md-3 col-6">
                                <small class="text-muted">Total</small>
                                <h5 class="mb-0 text-success">R$ {{ __moeda($ordem->valor) }}</h5>
                            </div>

                            <div class="col-md-3 col-6">
                                <small class="text-muted">Funcionário</small>
                                <h6 class="mb-0">{{ $ordem->funcionario ? $ordem->funcionario->nome : '--' }}</h6>
                            </div>

                            <div class="col-md-3 col-6">
                                <small class="text-muted">Técnico</small>
                                <h6 class="mb-0">{{ $ordem->tecnico ? $ordem->tecnico->nome : '--' }}</h6>
                            </div>

                            <div class="col-md-3 col-6">
                                <small class="text-muted">Usuário</small>
                                <h6 class="mb-0">{{ $ordem->usuario->name }}</h6>
                            </div>

                        </div>

                        @if($configGeral->tipo_ordem_servico == 'assistencia técinica')

                        <hr>

                        <h5 class="mb-3">Informações do Equipamento</h5>

                        <div class="row g-3">

                            <div class="col-md-4">
                                <small class="text-muted">Equipamento</small>
                                <h6 class="text-info">{{ $ordem->equipamento }}</h6>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Marca</small>
                                <h6>{{ $ordem->marca }}</h6>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Modelo</small>
                                <h6>{{ $ordem->modelo }}</h6>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Número de série</small>
                                <h6>{{ $ordem->numero_serie ?? '--' }}</h6>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Cor</small>
                                <h6>{{ $ordem->cor ?? '--' }}</h6>
                            </div>

                        </div>

                        @endif


                        @if($ordem->estado == 'fz' && sizeof($ordem->fatura) > 0)

                        <hr>

                        <h5 class="mb-3">Fatura</h5>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">

                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Vencimento</th>
                                        <th class="text-end">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ordem->fatura as $f)
                                    <tr>
                                        <td>{{ $f->getTipoPagamento() }}</td>
                                        <td>{{ __data_pt($f->data_vencimento, 0) }}</td>
                                        <td class="text-end">R$ {{ __moeda($f->valor) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th class="text-end text-success">
                                            R$ {{ __moeda($ordem->fatura->sum('valor')) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @endif

                        <hr>

                        <div class="d-flex flex-wrap gap-2">

                            <a target="_blank" class="btn btn-dark btn-sm" href="{{ route('ordem-servico.link', $ordem->hash_link) }}">
                                <i class="ri-links-fill"></i>
                                Link Cliente
                            </a>

                            <a href="{{ route('ordem-servico.alterar-estado', [$ordem->id]) }}" class="btn btn-info btn-sm">
                                <i class="ri-refresh-line"></i>
                                Alterar Estado
                            </a>

                            <a target="_blank" class="btn btn-primary btn-sm" onclick="imprimir('{{ $ordem->id }}')">
                                <i class="ri-printer-line"></i>
                                Imprimir
                            </a>

                            @if($ordem->nfe_id == 0)
                            @if($ordem->nfe_id == 0 && $ordem->nfce_id == 0)
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modal-gerar-venda">
                                <i class="ri-file-text-line"></i>
                                Gerar Venda
                            </button>
                            @else
                            @if($ordem->nfce_id > 0)
                            <a href="{{ route('nfce.show', [$ordem->nfce_id]) }}" class="btn btn-success btn-sm">
                                <i class="ri-file-text-line"></i>
                                Ver venda
                            </a>
                            @endif

                            @if($ordem->nfe_id > 0)
                            <a href="{{ route('nfe.show', [$ordem->nfe_id]) }}" class="btn btn-success btn-sm">
                                <i class="ri-file-text-line"></i>
                                Ver venda
                            </a>
                            @endif
                            @endif
                            @endif

                            @if($ordem->oticaOs)
                            <a class="btn btn-warning btn-sm" href="{{ route('ordem-servico.edit', $ordem->id) }}">
                                <i class="ri-edit-line"></i>
                                Editar Receita
                            </a>
                            @endif

                        </div>

                    </div>
                </div>

                <form method="post" class="row g-2 mt-3" action="{{ route('ordem-servico.update-entrega', [$ordem->id])}}">
                    @csrf
                    @method('put')
                    <div class="col-md-2">
                        {!! Form::tel('adiantamento', 'Valor de adiantamento')->attrs(['class' => 'moeda'])
                        ->value(__moeda($ordem->adiantamento)) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('data_entrega', 'Data de entrega')->value(substr($ordem->data_entrega,0, 10)) !!}
                    </div>
                    @if(!__isSegmentoPlanoOtica() && $configGeral->tipo_ordem_servico == 'oficina')
                    <div class="col-md-2">
                        {!! Form::select('veiculo_id', 'Veículo', ['' => 'Selecione'] + $veiculos->pluck('info', 'id')->all())
                        ->attrs(['class' => 'select2'])->id('veiculo_id')
                        ->value($ordem->veiculo_id) !!}
                    </div>
                    @endif

                    <div class="col-md-2">
                        <br>
                        <button class="btn btn-success" href="{{ route('ordem-servico.gerar-nfe', $ordem->id) }}">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
            <hr class="">
            <div class="card border row">
                {!! Form::open()
                ->post()
                ->route('ordem-servico.store-servico')!!}
                <h3 class="m-3">Serviços</h3>
                <div class="row m-2 mt-3">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="col-md-4">
                        {!! Form::select('servico_id', 'Serviço', [null => 'Selecione'] + $servicos->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])->required() !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'moeda'])->required() !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::text('valor', 'Valor unitário')->attrs(['class' => 'moeda'])->required() !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('status', 'Status', [0 => 'Pendente', 1 => 'Finalizado'])->attrs(['class' => 'form-select'])->required() !!}
                    </div>
                    <div class="col-md-2">
                        <br>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-add-line"></i>Adicionar
                        </button>

                    </div>
                </div>
                {!! Form::close() !!}
                <div class="card-body">
                    <div class="table-responsive">
                        <p class="">total de serviços: <strong>{{ sizeof($ordem->servicos) }}</strong></p>
                        <table class="table mb-0 table-striped table-servico">
                            <thead class="table-dark">
                                <tr>
                                    <th>Serviço</th>
                                    <th>Quantidade</th>
                                    <th>Status</th>
                                    <th>Subtotal</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($ordem)
                                @forelse($ordem->servicos as $item)
                                <tr>
                                    <td>
                                        <input style="width: 700px" readonly type="text" name="servico[]" class="form-control" value="{{ $item->servico->nome }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="servico_quantidade[]" class="form-control" value="{{ $item->quantidade }}">
                                    </td>
                                    <td>
                                        @if($item->status)
                                        <span class="badge bg-success">FINALIZADO
                                        </span>
                                        @else
                                        <span class="badge bg-warning">PENDENTE
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="valor[]" class="form-control qtd-item" value="{{ __moeda($item->subtotal) }}">
                                    </td>
                                    <td>
                                        <form action="{{ route('ordem-servico.deletar-servico', $item->id) }}" method="post" id="form-servico-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            <a title="Alterar estado" href="{{ route('ordem-servico.alterar-status-servico', $item->id) }}" class="btn btn-sm btn-dark">
                                                <i class="ri-refresh-line"></i>
                                            </a>

                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum registro</td>
                                </tr>
                                @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr class="mt-3">
            <!-- inicio produtos -->
            <div class="card border row">
                {!! Form::open()
                ->post()
                ->route('ordem-servico.store-produto')!!}

                <h3 class="m-3">Produtos</h3>

                <div class="row m-2">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">

                    <div class="col-md-4">
                        {!! Form::select('produto_id', 'Produto')->attrs(['class' => ''])->required() !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::tel('quantidade_produto', 'Quantidade')->attrs(['class' => 'moeda'])->required() !!}
                    </div>

                    <div class="col-md-2">
                        {!! Form::tel('valor_produto', 'Valor unitário')->attrs(['class' => 'moeda'])->required() !!}
                    </div>

                    <div class="col-md-2">
                        <br>
                        @if(!isset($not_submit))
                        <button type="submit" class="btn btn-success"><i class="ri-add-line"></i>Adicionar</button>
                        @endif
                    </div>
                </div>

                {!! Form::close() !!}

                <div class="card-body">

                    @php
                    $totalProdutos = $ordem->itens->sum('subtotal');
                    $percentualDescontoProdutos = $ordem->percentual_desconto_produtos ?? 0;
                    $valorDescontoProdutos = ($totalProdutos * $percentualDescontoProdutos) / 100;
                    $totalProdutosComDesconto = $totalProdutos - $valorDescontoProdutos;
                    @endphp


                    <div class="table-responsive">
                        <table class="table mb-0 table-striped table-produto">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor</th>
                                    <th>SubTotal</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @isset($ordem)
                                @forelse ($ordem->itens as $item)
                                <tr>
                                    <td>
                                        <input style="width: 700px" readonly type="text" name="produto[]" class="form-control" value="{{ $item->produto->nome }}">
                                    </td>

                                    <td>
                                        <input readonly type="tel" name="produto_quantidade[]" class="form-control" value="{{ $item->quantidade }}">
                                    </td>

                                    <td>
                                        <input readonly type="tel" name="total[]" class="form-control qtd-item" value="{{ __moeda($item->subtotal/$item->quantidade) }}">
                                    </td>

                                    <td>
                                        <input readonly type="tel" name="subtotal[]" class="form-control qtd-item" value="{{ __moeda($item->subtotal) }}">
                                    </td>

                                    <td>
                                        <form action="{{ route('ordem-servico.deletar-produto', $item->id) }}" method="post" id="form-{{$item->id}}">
                                            @method('delete')
                                            @csrf

                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nenhum registro</td>
                                </tr>
                                @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <form method="post" action="{{ route('ordem-servico.update-desconto-produtos', $ordem->id) }}" class="mb-3">
                        @csrf
                        @method('put')

                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                {!! Form::tel('percentual_desconto_produtos', '% Desconto geral')->attrs(['class' => 'percentual'])
                                ->value(($percentualDescontoProdutos)) !!}
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100">Salvar desconto</button>
                            </div>
                        </div>
                    </form>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3 col-6">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <small class="text-muted">Qtd. produtos</small>
                                    <h5 class="mb-0">{{ ($ordem->itens()->sum('quantidade')) }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <small class="text-muted">Total produtos</small>
                                    <h5 class="mb-0 text-primary">R$ {{ __moeda($totalProdutos) }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <small class="text-muted">Desconto {{ __moeda($percentualDescontoProdutos) }}%</small>
                                    <h5 class="mb-0 text-danger">R$ {{ __moeda($valorDescontoProdutos) }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="card bg-light border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <small class="text-muted">Total com desconto</small>
                                    <h5 class="mb-0 text-success">R$ {{ __moeda($totalProdutosComDesconto) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- fim produtos -->
            <hr class="mt-3">
            <div class="card border row">

                <h3 class="text m-3">Relatórios</h3>
                <div class="row m-2">
                    <div class="col-md-3">
                        <a href="{{ route('ordem-servico.add-relatorio', $ordem->id) }}" class="btn btn-success"><i class=" ri-add-line"></i>Adicionar relatório</a>
                    </div>
                    <p class="mt-2">total de relatórios: <strong>{{ sizeof($ordem->relatorios) }}</strong></p>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Usuário</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ordem->relatorios as $item)
                                <tr>
                                    <td>{{ __data_pt($item->created_at) }}</td>
                                    <td>{{ $item->usuario->name }}</td>
                                    <td>
                                        <form action="{{ route('ordem-servico.delete-relatorio', $item->id) }}" method="post" id="form-relatorio-{{$item->id}}">
                                            @method('delete')
                                            @csrf
                                            <a href="{{ route('ordem-servico.edit-relatorio', $item->id) }}" title="Editar" class="btn btn-warning btn-sm text-white">
                                                <i class="ri-pencil-fill"></i>
                                            </a>

                                            <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>

                                            <button type="button" class="btn btn-dark btn-sm" onclick="modalRelatorio('{{ $item->id }}', '{{ $item->texto }}')">
                                                <i class="ri-file-line"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border row">
                <h3 class="text m-3">Descrição/Observação</h3>

                <form method="post" action="{{ route('ordem-servico.update-descricao', [$ordem->id]) }}">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <div class="col-md-12">
                            {!!Form::textarea('descricao', 'Descrição/Observação')
                            ->attrs(['rows' => '6', 'class' => 'tiny'])
                            ->value($ordem->descricao)
                            !!}
                        </div>

                        <div class="col-12 mt-2" style="text-align: right;">
                            <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>

            @if($configGeral->tipo_ordem_servico == 'assistencia técinica')

            <div class="card border row">
                <div class="m-3">
                    <label>Diagnóstico do cliente</label>
                    {!! $ordem->diagnostico_cliente !!}
                </div>
            </div>
            <div class="card border row">
                <h3 class="m-3">Diagnóstico técnico</h3>

                <form method="post" action="{{ route('ordem-servico.update-diagnostico', [$ordem->id]) }}">
                    @csrf
                    @method('put')
                    <div class="card-body">
                        <div class="col-md-12">
                            {!!Form::textarea('diagnostico_tecnico', 'Descrição do diagnóstico')
                            ->attrs(['rows' => '6', 'class' => 'tiny'])
                            ->value($ordem->diagnostico_tecnico)
                            !!}
                        </div>

                        <div class="col-12 mt-2" style="text-align: right;">
                            <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>

            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modal-gerar-venda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ri-file-text-line"></i> Gerar venda da OS
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3 text-muted">
                    Escolha como deseja finalizar esta ordem de serviço.
                </p>

                <div class="row g-2">
                    <div class="col-md-6">
                        <a href="{{ route('ordem-servico.gerar-nfe', $ordem->id) }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="ri-file-list-3-line d-block mb-1 fs-3"></i>
                            Finalizar pedido
                        </a>
                    </div>

                    <div class="col-md-6">
                        <a href="{{ route('ordem-servico.gerar-pdv', $ordem->id) }}" class="btn btn-outline-success w-100 py-3">
                            <i class="ri-shopping-cart-2-line d-block mb-1 fs-3"></i>
                            Finalizar no PDV
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-relatorio" tabindex="-1" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Relatório</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/ordem_servico.js"></script>
<script src="/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    $(function(){
        tinymce.init({ selector: 'textarea.tiny', language: 'pt_BR'})

        setTimeout(() => {
            $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
        }, 500)
    })

    function modalRelatorio(id, texto){
        $('#modal-relatorio').modal('show')
        $('#modal-relatorio .modal-body').html(texto)
    }
</script>
@endsection
@endsection
