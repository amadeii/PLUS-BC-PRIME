<style>
    .ifood-card{
        border: 1px solid #ececec;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 14px rgba(0,0,0,.06);
        transition: .2s ease;
    }

    .ifood-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(0,0,0,.10);
    }

    .ifood-head{
        background: #ea1d2c;
        color: #fff;
        padding: 10px 12px;
    }

    .ifood-id{
        font-size: 12px;
        font-weight: 600;
        opacity: .95;
        margin-bottom: 2px;
    }

    .ifood-title{
        font-size: 17px;
        font-weight: 800;
        line-height: 1.2;
        margin: 0;
        color: #fff;
    }

    .ifood-sub{
        font-size: 13px;
        font-weight: 600;
        margin-top: 4px;
        color: rgba(255,255,255,.95);
    }

    .ifood-body{
        padding: 8px;
        height: 200px;
    }

    .ifood-grid{
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 10px;
    }

    .ifood-mini{
        background: #f8f8f8;
        border-radius: 10px;
        padding: 8px 10px;
    }

    .ifood-mini span{
        display: block;
        font-size: 11px;
        color: #777;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .ifood-mini strong{
        font-size: 14px;
        color: #222;
        font-weight: 800;
    }

    .ifood-line{
        margin-bottom: 8px;
        font-size: 13px;
        line-height: 1.35;
        color: #333;
    }

    .ifood-line b{
        color: #666;
        font-weight: 700;
    }

    .ifood-badge{
        display: inline-block;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .3px;
        text-transform: uppercase;
    }

    .badge-pendente{
        background: #fff3cd;
        color: #856404;
    }

    .badge-preparando{
        background: #d1ecf1;
        color: #0c5460;
    }

    .badge-finalizado{
        background: #d4edda;
        color: #155724;
    }

    .ifood-nota{
        background: #fff7f7;
        border: 1px solid #ffd8dc;
        border-radius: 10px;
        padding: 8px 10px;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .ifood-footer{
        padding: 10px 12px 12px;
        border-top: 1px solid #f1f1f1;
        background: #fff;
        height: 110px;
    }

    .btn-ifood{
        border: 0;
        border-radius: 10px;
        font-weight: 800;
        font-size: 13px;
        padding: 10px 12px;
    }

    .btn-ifood-preparo{
        background: #ffb300;
        color: #fff;
    }

    .btn-ifood-preparo:hover{
        background: #e4a100;
        color: #fff;
    }

    .btn-ifood-finalizar{
        background: #1f9d55;
        color: #fff;
    }

    .btn-ifood-finalizar:hover{
        background: #188447;
        color: #fff;
    }

    .ifood-empty{
        border: 1px dashed #ddd;
        border-radius: 16px;
        background: #fff;
    }
</style>

<div class="row ">
    @forelse($data as $item)
    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
        <div class="ifood-card">
            <form method="get"
                @isset($item->is_cardapio)
                    action="{{ route('pedido-cozinha.update-item', [$item->id]) }}"
                @else
                    action="{{ route('pedidos-delivery.update-item', [$item->id]) }}"
                @endif
                id="form-{{$item->id}}">

                <div class="ifood-head">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="w-100">
                            <div class="ifood-id">ITEM #{{ $item->id }}</div>

                            <h5 class="ifood-title">
                                {{ $item->produto ? $item->produto->nome : '--' }}
                            </h5>

                            <div class="ifood-sub">
                                @isset($item->is_comanda)
                                    Comanda: {{ $item->pedido->comanda }}
                                @else
                                    Pedido: #{{ $item->pedido->id }}
                                @endif
                            </div>
                        </div>

                        <div class="text-end">
                            <span class="ifood-badge
                                @if($item->estado == 'pendente') badge-pendente
                                @elseif($item->estado == 'preparando') badge-preparando
                                @else badge-finalizado
                                @endif">
                                {{ $item->estado }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ifood-body">
                    <div class="ifood-grid">
                        <div class="ifood-mini">
                            <span>Quantidade</span>
                            <strong>{{ number_format($item->quantidade, 2) }}</strong>
                        </div>

                        <div class="ifood-mini">
                            <span>Subtotal</span>
                            <strong>{{ __moeda($item->sub_total) }}</strong>
                        </div>

                        <div class="ifood-mini">
                            <span>Pedido às</span>
                            <strong>{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}</strong>
                        </div>

                        @if($item->tempo_preparo > 0)
                        <div class="ifood-mini">
                            <span>
                                @if($item->tempoPreparoRestante() >= 0)
                                    Restante
                                @else
                                    Atraso
                                @endif
                            </span>
                            <strong class="{{ $item->tempoPreparoRestante() >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ abs($item->tempoPreparoRestante()) }} min
                            </strong>
                        </div>
                        @else
                        <div class="ifood-mini">
                            <span>Status</span>
                            <strong>{{ ucfirst($item->estado) }}</strong>
                        </div>
                        @endif
                    </div>

                    @if(sizeof($item->adicionais) > 0)
                    <div class="ifood-line">
                        <b>Adicionais:</b> {{ $item->getAdicionaisStr() }}
                    </div>
                    @endif

                    @if($item->observacao != '')
                    <div class="ifood-nota">
                        <b>Obs:</b> {{ $item->observacao }}
                    </div>
                    @endif

                    @if(sizeof($item->pizzas) > 0)
                    <div class="ifood-line">
                        <b>Sabores:</b>
                        @foreach($item->pizzas as $pizza)
                            {{ $pizza->sabor->nome }}@if(!$loop->last) | @endif
                        @endforeach
                    </div>

                    @if($item->tamanho)
                    <div class="ifood-line">
                        <b>Tamanho:</b> {{ $item->tamanho->nome }}
                    </div>
                    @endif
                    @endif

                    @if($item->ponto_carne)
                    <div class="ifood-line">
                        <b>Ponto:</b> {{ $item->ponto_carne }}
                    </div>
                    @endif

                    @if(isset($item->pedido->_mesa) && $item->pedido->_mesa)
                    <div class="ifood-line mb-0">
                        <b>Mesa:</b> {{ $item->pedido->_mesa->nome }}
                    </div>
                    @endif

                    @if($item->tempo_preparo > 0)
                    <div class="ifood-line mb-0 mt-1">
                        <b>Entrou em preparo:</b>
                        {{ \Carbon\Carbon::parse($item->updated_at)->format('H:i') }}
                    </div>
                    @endif

                    <input type="hidden" name="estado" value="finalizado">
                </div>

                <div class="ifood-footer">
                    @if($item->estado == 'pendente')
                    <button type="button"
                        class="btn btn-ifood btn-ifood-preparo w-100"
                        data-bs-toggle="modal"
                        data-bs-target="#modal-item-{{ $item->id }}">
                        Entrou em preparo
                    </button>
                    @endif

                    <button type="submit"
                        class="btn btn-ifood btn-ifood-finalizar w-100 {{ $item->estado == 'pendente' ? 'mt-2' : '' }}">
                        Finalizar item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal-item-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content" style="border-radius: 14px; border: 0;">
                <form
                    @isset($item->is_cardapio)
                        action="{{ route('pedido-cozinha.update-item', [$item->id]) }}"
                    @else
                        action="{{ route('pedidos-delivery.update-item', [$item->id]) }}"
                    @endif
                    method="get">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $item->produto ? $item->produto->nome : '--' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="estado" value="preparando">

                        {!! Form::text('tempo_preparo', 'Tempo de preparo')
                            ->attrs(['data-mask' => '000'])
                            ->value($item->produto ? $item->produto->tempo_preparo : '') !!}
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success w-100" style="border-radius: 10px; font-weight: 700;">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card ifood-empty">
            <div class="card-body text-center py-5">
                <h5 class="mb-1">Nenhum item encontrado</h5>
                <p class="text-muted mb-0">Os pedidos vão aparecer aqui.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if(sizeof($data) > 0)
<div class="row mt-3 mb-4">
    <div class="col-md-3 col-12">
        <a href="{{ route('pedido-cozinha.update-all') }}"
           class="btn w-100"
           style="background:#ea1d2c; color:#fff; border-radius:12px; font-weight:800; padding:12px;">
            Finalizar todos
        </a>
    </div>
</div>
@endif