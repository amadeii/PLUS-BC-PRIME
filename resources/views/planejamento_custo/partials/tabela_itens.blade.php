<div class="table-responsive planejamento-table-wrap">
    <table class="table planejamento-table mb-0">
        <thead>
            <tr>
                <th>{{ $tipo == 'produto' ? 'Produto' : ($tipo == 'adm' ? 'Descrição' : 'Serviço') }}</th>
                <th>Quantidade</th>
                <th>Valor unitário</th>
                <th class="text-end">Sub total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itens as $i)
            <tr>
                <td>
                    @if($tipo == 'produto')
                    {{ $i->descricao() }}
                    @elseif($tipo == 'adm')
                    {{ $i->descricao }}
                    @else
                    {{ $i->servico->nome }}
                    @endif
                </td>
                <td>
                    @if($tipo == 'produto' && !$i->produto->unidadeDecimal())
                    {{ number_format($i->quantidade, 0, '.', '') }}
                    @elseif($tipo == 'produto')
                    {{ number_format($i->quantidade, 3, '.', '') }}
                    @else
                    {{ number_format($i->quantidade, 0, '.', '') }}
                    @endif
                </td>
                <td>R$ {{ __moeda($i->valor_unitario) }}</td>
                <td class="text-end fw-bold">R$ {{ __moeda($i->sub_total) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4">
                    <div class="empty-planejamento">
                        <i class="ri-inbox-line"></i>
                        <p>Nenhum item encontrado</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>