<div class="modal fade" id="modalFaturar{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <form method="post" action="{{ route('faturamento-nfe.faturar', $item->id) }}">
                @csrf

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            <i class="ri-file-paper-2-line text-primary me-1"></i>
                            Faturar Pedido #{{ $item->numero_sequencial ?? $item->id }}
                        </h5>
                        <small class="text-muted">Confirme os dados antes de enviar para faturamento.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" value="{{ $item->cliente->info ?? 'Consumidor Final' }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">CPF/CNPJ</label>
                            <input type="text" class="form-control" value="{{ $item->cliente->cpf_cnpj ?? '--' }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Valor</label>
                            <input type="text" class="form-control" value="R$ {{ __moeda($item->total) }}" readonly>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Data de faturamento</label>
                            <input type="date" name="data_faturamento" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Observação interna</label>
                            <textarea name="observacao_faturamento" class="form-control" rows="3" placeholder="Ex: cliente pediu envio por e-mail, conferir transportadora, pedido urgente..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-warning border-0 rounded-4 mt-3 mb-0">
                        <strong>Atenção:</strong> ao confirmar, o pedido será enviado para o fluxo de emissão/transmissão da NF-e.
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-send-plane-line me-1"></i>
                        Confirmar Faturamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>