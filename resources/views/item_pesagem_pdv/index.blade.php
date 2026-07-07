@extends('layouts.app', ['title' => 'Itens de Pesagem PDV'])

@section('content')
<div class="page-content mt-1">

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1 text-primary fw-bold"><i class="ri-scales-3-line me-2"></i> Itens de Pesagem PDV</h4>
                    <small class="text-muted">Configure os produtos que aparecerão na tela de pesagem do PDV</small>
                </div>
            </div>

            @can('item_pesagem_pdv_edit')
            <form method="post" action="{{ route('item-pesagem-pdv.store') }}">
                @csrf

                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Produto</label>
                        <select name="produto_id" class="form-select select2" required>
                            <option value="">Selecione</option>
                            @foreach($produtos as $p)
                            <option value="{{ $p->id }}" data-valor="{{ $p->valor_unitario }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Valor KG</label>
                        <input type="text" id="valor" name="valor" class="form-control moeda" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ordem</label>
                        <input type="number" name="ordem" class="form-control" value="0">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tipo</label>

                        <select name="sem_peso" class="form-select">
                            <option value="0">Por KG</option>
                            <option value="1">Preço fixo</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-success w-100"><i class="ri-add-line me-1"></i> Adicionar</button>
                    </div>
                </div>
            </form>
            @endcan

            <hr>

            <div class="col-lg-12 mb-4">
                {!! Form::open()->fill(request()->all())->get() !!}

                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        {!! Form::text('pesquisa','Pesquisar produto') !!}
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary" type="submit"><i class="ri-search-line"></i> Pesquisar</button>
                        <a class="btn btn-danger" href="{{ route('item-pesagem-pdv.index') }}"><i class="ri-eraser-fill"></i> Limpar</a>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Produto</th>
                            <th width="140">Tipo</th>
                            <th width="140">Valor</th>
                            <th width="100">Ordem</th>
                            <th width="120">Status</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $item)
                        <tr>

                            <td>
                                <div class="fw-semibold">
                                    {{ $item->produto->nome ?? '--' }}
                                </div>

                                <small class="text-muted">
                                    {{ $item->produto->codigo_barras ?? '' }}
                                </small>
                            </td>

                            <td>
                                @if($item->sem_peso)
                                <span class="badge bg-warning text-dark">
                                    Preço fixo
                                </span>
                                @else
                                <span class="badge bg-info">
                                    Por KG
                                </span>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-success fs-6">
                                    {{ __moeda($item->valor) }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-dark">
                                    {{ $item->ordem }}
                                </span>
                            </td>

                            <td>
                                @if($item->status)
                                <span class="badge bg-primary">
                                    Ativo
                                </span>
                                @else
                                <span class="badge bg-danger">
                                    Inativo
                                </span>
                                @endif
                            </td>

                            <td>
                                <form action="{{ route('item-pesagem-pdv.destroy', $item->id) }}"
                                    method="post"
                                    id="form-{{ $item->id }}">

                                    @csrf
                                    @method('delete')

                                    <button type="button"
                                    class="btn btn-danger btn-sm btn-delete">

                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">

                            <div class="text-muted">
                                <i class="ri-inbox-archive-line fs-1 d-block mb-2"></i>

                                Nenhum item configurado
                            </div>

                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {!! $data->appends(request()->all())->links() !!}
        </div>

        <div class="mt-4 pt-4 border-top">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-primary mb-1"><i class="ri-printer-line me-1"></i> Itens fixos da comanda</h5>
                    <small class="text-muted">Configure os produtos que serão impressos como opções fixas na comanda</small>
                </div>
            </div>

            @can('item_pesagem_pdv_edit')
            <form method="post" action="{{ route('item-pesagem-pdv.comanda.store') }}" class="bg-light rounded-4 p-3 mb-4">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Produto</label>
                        <select name="produto_id" class="form-select select2" required>
                            <option value="">Selecione</option>
                            @foreach($produtos as $p)
                            <option value="{{ $p->id }}">{{ $p->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ordem</label>
                        <input type="number" name="ordem" class="form-control" value="0">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="ativo" class="form-select">
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100"><i class="ri-add-line me-1"></i> Adicionar na comanda</button>
                    </div>
                </div>
            </form>
            @endcan

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Produto</th>
                            <th width="100">Ordem</th>
                            <th width="120">Status</th>
                            <th width="120">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($itensComanda as $item)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $item->produto->nome ?? '--' }}</div>
                                <small class="text-muted">{{ $item->produto->codigo_barras ?? '' }}</small>
                            </td>

                            <td>
                                <span class="badge bg-dark">{{ $item->ordem }}</span>
                            </td>

                            <td>
                                @if($item->ativo)
                                <span class="badge bg-primary">Ativo</span>
                                @else
                                <span class="badge bg-danger">Inativo</span>
                                @endif
                            </td>

                            <td>
                                <form action="{{ route('item-pesagem-pdv.comanda.destroy', $item->id) }}" method="post" id="form-comanda-{{ $item->id }}">
                                    @csrf
                                    @method('delete')
                                    <button type="button" class="btn btn-danger btn-sm btn-delete"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="ri-inbox-archive-line fs-1 d-block mb-2"></i>
                                    Nenhum item fixo configurado para a comanda
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

</div>
@endsection

@section('js')
<script type="text/javascript">
    $(function(){

        $('select[name="produto_id"]').change(function(){
            let valor = $(this).find(':selected').data('valor');

            if(valor != undefined){
                $('#valor').val(parseFloat(valor).toFixed(2).replace('.', ','));
            }
        });

    });
</script>
@endsection