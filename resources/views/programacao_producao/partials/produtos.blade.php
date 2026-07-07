{{-- resources/views/programacao_producao/partials/produtos.blade.php --}}

<div class="card border rounded-3 shadow-sm mb-4">

    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h5 class="fw-bold mb-1">
                <i class="ri-box-3-fill text-primary me-2"></i>
                Produtos a Produzir
            </h5>

            <small class="text-muted">
                Somente itens de pedidos com status pendente
            </small>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">

            <a href="{{ route('programacao-producao.pdf-produtos', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="ri-file-pdf-line me-1"></i>
                PDF
            </a>

            <span class="badge bg-primary text-white px-3 py-2 rounded-pill fw-semibold">
                {{ count($produtos) }} produtos
            </span>
        </div>
    </div>

    <div class="card-body pt-0">

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="40">Sel.</th>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th class="text-end">Estoque</th>
                        <th class="text-end">Demanda</th>
                        <th class="text-center">Sugestão</th>
                        <th class="text-end">Qtd Produzir</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($produtos as $key => $item)
                    <tr>
                        <td>
                            <input class="form-check-input produto-check" type="checkbox" checked data-key="{{ $key }}">
                        </td>

                        <td>
                            <span class="badge bg-dark">
                                {{ $item['codigo'] }}
                            </span>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $item['descricao'] }}
                            </div>

                            <small class="text-muted">
                                ID: {{ $item['produto_id'] }}
                            </small>
                        </td>

                        <td>
                            <span class="badge bg-info">
                                {{ $item['tipo'] }}
                            </span>
                        </td>

                        <td class="text-end">
                            {{ number_format($item['estoque'], 3, ',', '.') }}
                        </td>

                        <td class="text-end">
                            {{ number_format($item['demanda'], 3, ',', '.') }}
                        </td>

                        <td class="text-center">
                            @if($item['sugestao'] > 0)
                            <span class="badge bg-warning text-dark">
                                Produzir
                            </span>
                            @else
                            <span class="badge bg-success">
                                OK
                            </span>
                            @endif
                        </td>

                        <td class="text-end">
                            <span class="badge bg-light text-dark border px-3 py-2">
                                {{ number_format($item['qtd_produzir'], 3, ',', '.') }}
                            </span>

                            <input type="hidden" class="qtd-produzir" value="{{ $item['qtd_produzir'] }}" data-key="{{ $key }}" data-produto-id="{{ $item['produto_id'] }}" data-descricao="{{ $item['descricao'] }}" data-codigo="{{ $item['codigo'] }}" data-cliente-id="{{ $item['cliente_id'] ?? '' }}" data-numero-pedido="{{ $item['numero_pedido'] ?? '' }}">
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Nenhum produto pendente para produzir
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <small class="text-muted">
            <i class="ri-information-line me-1"></i>
            A quantidade a produzir é uma sugestão automática e não pode ser alterada nesta tela.
        </small>

    </div>
</div>