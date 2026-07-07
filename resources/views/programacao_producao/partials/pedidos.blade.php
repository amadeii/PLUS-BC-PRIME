{{-- resources/views/programacao_producao/partials/pedidos.blade.php --}}

<div class="card border rounded-3 shadow-sm mb-4">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="ri-file-list-3-fill text-warning me-2"></i>
                Programação de Pedidos
            </h5>

            <small class="text-muted">
                Pedidos pendentes de faturamento e produção
            </small>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            <a href="{{ route('programacao-producao.pdf-pedidos', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="ri-file-pdf-line me-1"></i>
                PDF
            </a>

            <span class="badge bg-warning p-2 rounded-pill text-white">
                {{ count($pedidos) }} pedidos
            </span>
        </div>
    </div>

    <div class="card-body pt-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle table-striped">

                <thead class="table-dark">
                    <tr>
                        <!-- <th width="40">Sel.</th> -->
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Qtd</th>
                        <th>Produzido</th>
                        <th width="180">% Produção</th>
                        <th>Data</th>
                        <th>Entrega</th>
                        <th>Status do pedido</th>
                        <th>Status da fatura</th>
                        <th width="110">Itens</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($pedidos as $item)

                    <tr>
                        <!-- <td>
                            <input class="form-check-input pedido-check"
                            type="checkbox"
                            value="{{ $item['id'] }}"
                            data-pedido="{{ $item['pedido'] }}">
                        </td> -->

                        <td>
                            <div class="fw-semibold">
                                #{{ $item['pedido'] }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $item['cliente'] }}
                            </div>
                        </td>

                        <td>
                            <span class="fw-semibold">
                                {{ number_format($item['qtde'], 3, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            <span class="fw-semibold text-success">
                                {{ number_format($item['produzido'], 3, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            <div class="progress rounded-pill" style="height: 8px;">
                                <div class="progress-bar
                                @if($item['percentual'] >= 100)
                                bg-success
                                @elseif($item['status_prazo'] == 'Atrasado')
                                bg-danger
                                @else
                                bg-warning
                                @endif"
                                style="width: {{ $item['percentual'] }}%">
                            </div>
                        </div>

                        <small class="fw-semibold">
                            {{ number_format($item['percentual'], 1, ',', '.') }}%
                        </small>
                    </td>

                    <td>
                        {{ $item['data_emissao'] ? \Carbon\Carbon::parse($item['data_emissao'])->format('d/m/Y H:i') : '-' }}
                    </td>

                    <td>
                        {{ $item['data_entrega'] ? \Carbon\Carbon::parse($item['data_entrega'])->format('d/m/Y') : '-' }}
                    </td>

                    <td>
                        @if($item['status_producao'] == 'Finalizado')
                        <span class="badge bg-success">Finalizado</span>
                        @elseif($item['status_producao'] == 'Andamento')
                        <span class="badge bg-primary">Em produção</span>
                        @else
                        <span class="badge bg-warning text-">Pendente</span>
                        @endif

                        <!-- @if($item['status_prazo'] == 'Atrasado')
                        <span class="badge bg-danger ms-1">Atrasado</span>
                        @else
                        <span class="badge bg-light text-dark border ms-1">Normal</span>
                        @endif -->
                    </td>

                    <td>
                        @if($item['estado_fatura'] == 'pendente')
                        <span class="badge bg-warning">Pendente</span>
                        @elseif($item['estado_fatura'] == 'aprovado')
                        <span class="badge bg-success">Aprovado</span>
                        @else
                        <span class="badge bg-dark">Finalizado</span>
                        @endif
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-1">

                            <button class="btn btn-sm btn-dark rounded-pill" data-bs-toggle="collapse" data-bs-target="#pedido-{{ $item['id'] }}" title="Ver itens do pedido">
                                <i class="ri-eye-fill"></i>
                            </button>

                            <button type="button" class="btn btn-sm btn-success rounded-pill btn-gerar-of" data-bs-toggle="modal" data-bs-target="#modalGerarOf" data-pedido-id="{{ $item['id'] }}" title="Gerar OF" @if(in_array($item['status_producao'], ['Andamento', 'Finalizado'])) disabled @endif>
                                <i class="ri-hammer-fill"></i>
                            </button>

                        </div>
                    </td>
                </tr>

                <tr class="collapse bg-light" id="pedido-{{ $item['id'] }}">
                    <td colspan="10">
                        <div class="p-2">

                            <div class="fw-bold mb-2">
                                Itens do Pedido
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">

                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Categoria</th>
                                            <th width="160">Quantidade</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($item['itens'] as $it)
                                        <tr>
                                            <td>
                                                {{ $it['produto'] }}
                                            </td>

                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $it['categoria'] ?? '-' }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ number_format($it['quantidade'], 3, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="10" class="text-center py-4 text-muted">
                        Nenhum pedido pendente
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</div>

