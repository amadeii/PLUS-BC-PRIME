@forelse($produtos as $p)
<tr>
    <td>
        <img src="{{ $p->img }}" class="img-30">
    </td>
    <td>{{ $p->numero_sequencial }}</td>

    <td>
        <strong>{{ $p->nome }}</strong>
        @if($p->referencia)
            <br>
            <small class="text-muted">Ref: {{ $p->referencia }}</small>
        @endif
    </td>

    <td>{{ $p->categoria->nome ?? '-' }}</td>

    <td>{{ $p->marca->nome ?? '-' }}</td>

    <td>{{ $p->codigo_barras ?? '-' }}</td>
    <td>{{ $p->referencia ?? '-' }}</td>

    <td class="text-end">
        {{ __moeda($p->valor_unitario) }}
    </td>

    <td class="text-">
        <button
            type="button"
            class="btn btn-success btn-sm btn-add-produto"
            data-id="{{ $p->id }}"
            data-nome="{{ $p->nome }}"
            data-preco="{{ $p->valor_unitario }}"
        >
            <i class="ri-shopping-bag-line"></i>
        </button>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center text-muted py-3">
        <i class="ri-information-line"></i>
        Nenhum produto encontrado
    </td>
</tr>
@endforelse
