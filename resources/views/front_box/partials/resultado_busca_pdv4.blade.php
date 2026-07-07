@if($produtos->isEmpty())
<div class="busca-produto-vazio">Nenhum produto encontrado</div>
@else
@foreach($produtos as $p)
<div class="busca-produto-item {{ $p->gerenciar_estoque && ($p->estoque?->quantidade ?? 0) <= 0 ? 'bloqueado' : '' }}"
    data-id="{{ $p->id }}"
    data-nome="{{ $p->nome }}"
    data-valor="{{ $p->valor_tabela ?? $p->valor_unitario }}"
    data-gerenciar_estoque="{{ $p->gerenciar_estoque }}"
    data-disponivel="{{ $p->estoque?->quantidade ?? 0 }}"
    data-img="{{ $p->imgApp ?? '' }}">

    <div class="busca-produto-thumb">
        <img 
            src="{{ $p->img }}"
            alt="{{ $p->nome }}"
        >
    </div>

    <div class="busca-produto-body">
        <div class="busca-produto-header">
            <div class="busca-produto-titulo-area">
                <div class="busca-produto-titulo">{{ $p->nome }}</div>
                <div class="busca-produto-ref">Ref: {{ $p->referencia ?: '-' }}</div>
            </div>

            <div class="busca-produto-preco">
                R$ {{ number_format(($p->valor_tabela ?? $p->valor_unitario), 2, ',', '.') }}
            </div>
        </div>

        <div class="busca-produto-meta">
            <span class="busca-produto-tag">
                <strong>Cód. barras:</strong> {{ $p->codigo_barras ?: '-' }}
            </span>

            <span class="busca-produto-tag">
                <strong>Marca:</strong> {{ $p->marca?->nome ?: '-' }}
            </span>

            <span class="busca-produto-tag {{ ($p->estoque?->quantidade ?? 0) <= 0 ? 'sem-estoque' : '' }}">
                <strong>Estoque:</strong> {{ number_format($p->estoque?->quantidade ?? 0, 0, ',', '.') }} Un
            </span>
        </div>
    </div>
</div>
@endforeach
@endif