@extends('layouts.app', ['title' => 'Vendas temporárias'])
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
                            {!!Form::time('start_time', 'Horário inicial')
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::time('end_time', 'Horário final')
                            !!}
                        </div>
                        
                        <div class="col-md-2">
                            {!!Form::select('user_id', 'Usuário', ['' => 'Todos'] + $usuarios->pluck('name', 'id')->all())
                            ->attrs(['class' => 'select2'])
                            !!}
                        </div>

                        <div class="col-md-2">
                            {!!Form::select('estado', 'Estado', ['' => 'Todos'] + \App\Models\VendaTemporaria::estados())
                            ->attrs(['class' => 'form-select'])
                            !!}
                        </div>
                        <div class="col-lg-4 col-12">
                            <br>

                            <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('venda-temporaria.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
                <div class="col-md-12 mt-3">
                    <div class="table-responsive-sm">
                        <table class="table table-striped table-centered mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data</th>
                                    <th>Estado</th>
                                    <th>Cliente</th>
                                    <th>Valor Total</th>
                                    <th>Total de Itens</th>
                                    <th>Total de Itens Removidos</th>
                                    <th>Local</th>
                                    <th>Usuário</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                <tr>
                                    <td>{{ __data_pt($item->created_at) }}</td>
                                    <td>
                                        @if($item->estado == 'em_aberto')
                                        <span class="badge bg-warning text-white">Em aberto</span>
                                        @elseif($item->estado == 'abandonada')
                                        <span class="badge bg-danger text-white">Abandonada</span>
                                        @elseif($item->estado == 'finalizada')
                                        <span class="badge bg-success text-white">Finalizada</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->cliente ?  $item->cliente->razao_social : '--' }}</td>
                                    <td>{{ __moeda($item->total) }}</td>
                                    <td>{{ sizeof($item->itens) }}</td>
                                    <td>{{ sizeof($item->itensRemovidos) }}</td>
                                    <td>
                                        @if($item->tabela == 'pdv')
                                        <span class="badge bg-secondary">PDV</span>
                                        @else
                                        <span class="badge bg-secondary">Pedido</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->usuario->name }}</td>
                                    <td>
                                        <button onclick='verModal(@json($item->load(["cliente","itens.produto","itensRemovidos.produto"])))'  title="Ver detalhes" class="btn btn-sm btn-dark">
                                            Detalhes
                                        </button>

                                        @if($item->vendaVinculada)
                                        @if($item->tabela == 'pdv')
                                        <a title="Ver venda" class="btn btn-sm btn-success" href="{{ route('frontbox.show', [ $item->vendaVinculada->id ]) }}">
                                            Ver venda
                                        </a>
                                        @else
                                        <a title="Ver venda" class="btn btn-sm btn-success" href="{{ route('nfe.show', [ $item->vendaVinculada->id ]) }}">
                                            Ver venda
                                        </a>
                                        @endif
                                        @endif

                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Nada encontrado</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <br>
                        
                    </div>
                </div>
                {!! $data->appends(request()->all())->links() !!}

            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalVendaTemporaria" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white">Detalhes da Venda Temporária</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Data:</strong>
                        <div id="modal_data"></div>
                    </div>
                    <div class="col-md-3">
                        <strong>Cliente:</strong>
                        <div id="modal_cliente"></div>
                    </div>
                    <div class="col-md-3">
                        <strong>Total:</strong>
                        <div id="modal_total"></div>
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong>
                        <div id="modal_estado"></div>
                    </div>
                </div>

                <hr>

                <h6>Itens Ativos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th>Qtd</th>
                                <th>Valor</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="modal_itens"></tbody>
                    </table>
                </div>

                <hr>

                <h6 class="">Itens Removidos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead class="table-danger">
                            <tr>
                                <th>Produto</th>
                                <th>Qtd</th>
                                <th>Valor</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="modal_itens_removidos"></tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
    function verModal(data) {

        // console.log(data);

        $('#modal_data').html(moment(data.created_at).format('DD/MM/YYYY HH:mm'));
        $('#modal_cliente').html(data.cliente ? data.cliente.razao_social : '--');
        $('#modal_total').html('R$ ' + parseFloat(data.total).toFixed(2).replace(".", ","));
        let badge = '';

        if (data.estado === 'em_aberto') {
            badge = '<span class="badge bg-warning text-white">Em aberto</span>';
        }

        if (data.estado === 'abandonada') {
            badge = '<span class="badge bg-danger text-white">Abandonada</span>';
        }

        if (data.estado === 'finalizada') {
            badge = '<span class="badge bg-success text-white">Finalizada</span>';
        }

        $('#modal_estado').html(badge);

        $('#modal_itens').html('');
        $('#modal_itens_removidos').html('');


        if (data.itens && data.itens.length > 0) {
            data.itens.forEach(item => {

                let total = item.quantidade * item.valor;

                $('#modal_itens').append(`
                    <tr>
                    <td>${item.produto ? item.produto.nome : ''}</td>
                    <td>${parseFloat(item.quantidade).toFixed(2).replace(".", ",")}</td>
                    <td>R$ ${parseFloat(item.valor).toFixed(2).replace(".", ",")}</td>
                    <td>R$ ${total.toFixed(2).replace(".", ",")}</td>
                    </tr>
                    `);
            });
        }


        if (data.itens_removidos && data.itens_removidos.length > 0) {
            data.itens_removidos.forEach(item => {

                let total = item.quantidade * item.valor;

                $('#modal_itens_removidos').append(`
                    <tr class="table-">
                    <td>${item.produto ? item.produto.nome : ''}</td>
                    <td>${parseFloat(item.quantidade).toFixed(2).replace(".", ",")}</td>
                    <td>R$ ${parseFloat(item.valor).toFixed(2).replace(".", ",")}</td>
                    <td>R$ ${total.toFixed(2).replace(".", ",")}</td>
                    </tr>
                    `);
            });
        }

        $('#modalVendaTemporaria').modal('show');
    }

</script>
@endsection

