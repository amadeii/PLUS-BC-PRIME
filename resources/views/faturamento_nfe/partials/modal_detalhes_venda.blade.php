<div class="modal fade modal-venda-lote" id="modalVendaLote{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content lote-modal-content">

            <div class="lote-modal-header">
                <div>
                    <span class="lote-modal-badge">Pedido #{{ $item->numero_sequencial ?? $item->id }}</span>
                    <h5>Detalhes da venda</h5>
                    <p>Confira as informações antes de selecionar para faturamento.</p>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body lote-modal-body">

                <div class="lote-info-card">
                        <small>Cliente</small>
                        <strong>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</strong>
                    </div>
                <div class="lote-modal-grid mt-1">

                    <div class="lote-info-card">
                        <small>CPF/CNPJ</small>
                        <strong>{{ $item->cliente->cpf_cnpj ?? '--' }}</strong>
                    </div>

                    <div class="lote-info-card">
                        <small>Data do pedido</small>
                        <strong>{{ __data_pt($item->created_at, 0) }} às {{ $item->created_at->format('H:i') }}</strong>
                    </div>

                    <div class="lote-info-card">
                        <small>Usuário</small>
                        <strong>{{ $item->user->name ?? '--' }}</strong>
                    </div>

                    <div class="lote-info-card destaque">
                        <small>Valor total</small>
                        <strong>R$ {{ __moeda($item->total) }}</strong>
                    </div>
                </div>

                <div class="lote-modal-section">
                    <div class="lote-section-title">
                        <i class="ri-shopping-bag-3-line"></i>
                        Produtos da venda
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm lote-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->itens as $it)
                                <tr>
                                    <td>
                                        <strong>{{ $it->produto->nome ?? $it->nome ?? 'Produto' }}</strong>
                                    </td>
                                    <td class="text-center">{{ __moeda($it->quantidade ?? $it->qtd ?? 0) }}</td>
                                    <td class="text-end">R$ {{ __moeda($it->valor_unitario ?? $it->valor ?? 0) }}</td>
                                    <td class="text-end">R$ {{ __moeda($it->sub_total ?? $it->subtotal ?? 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="lote-modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>

                @if(!isset($nao_selecionar))
                <button type="button" class="btn-fat-green btn-selecionar-modal" data-id="{{ $item->id }}" data-bs-dismiss="modal">
                    <i class="ri-checkbox-circle-line"></i> Selecionar venda
                </button>
                @endif
            </div>

        </div>
    </div>
</div>