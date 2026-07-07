<div class="table-responsive">
    <table class="table table-striped table-centered mb-0">
        <thead class="table-dark">
            <tr>
                <th width="40">
                    <div class="form-check form-checkbox-danger mb-0">
                        <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                    </div>
                </th>
                <th>Produto</th>
                <th>Cliente</th>
                <th width="130">Quantidade</th>
                <th>Observação do item</th>
                <th width="160">Nº Pedido</th>
                <th width="130">Status</th>
            </tr>
        </thead>

        <tbody class="tbody-produtos">
            @if(isset($item) && $item->itens->count())
            @foreach($item->itens as $i)
            <tr>
                <td>
                    <div class="form-check form-checkbox-danger mb-0">
                        <input class="form-check-input check-button" type="checkbox" name="item_check[]" value="{{ $i->id }}">
                    </div>
                </td>
                <td>
                    <input type="hidden" name="produto_id[]" value="{{ $i->produto_id }}">
                    <strong>{{ $i->produto->nome ?? 'Produto removido' }}</strong>
                </td>
                <td>
                    <input type="hidden" name="cliente_id[]" value="{{ $i->cliente_id }}">
                    {{ $i->cliente->razao_social ?? $i->cliente->nome ?? '-' }}
                </td>
                <td>
                    <input type="tel" name="qtd[]" class="form-control qtd" value="{{ __moeda($i->quantidade) }}">
                </td>
                <td>
                    <input type="text" name="observacao_item[]" class="form-control" value="{{ $i->observacao }}">
                </td>
                <td>
                    <input type="text" name="numero_pedido[]" class="form-control" value="{{ $i->numero_pedido }}">
                </td>
                <td>
                    <span class="badge bg-{{ $i->status ? 'success' : 'secondary' }}">
                        {{ $i->status ? 'Concluído' : 'Pendente' }}
                    </span>
                </td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>
</div>