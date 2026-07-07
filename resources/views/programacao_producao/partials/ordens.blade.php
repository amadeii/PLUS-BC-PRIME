{{-- resources/views/programacao_producao/partials/ordens.blade.php --}}

<div class="card border rounded-3 shadow-sm mb-4">

    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h5 class="fw-bold mb-1">
                <i class="ri-settings-5-fill text-success me-2"></i>
                Ordens de Produção
            </h5>

            <small class="text-muted">
                Ordens abertas pela programação de produção
            </small>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">

            <a href="{{ route('programacao-producao.pdf-ordens', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="ri-file-pdf-line me-1"></i>
                PDF
            </a>

            <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold">
                {{ count($ordens) }} ordens
            </span>

        </div>

    </div>

    <div class="card-body pt-0">

        <div class="table-responsive">

            <table class="table table-hover table-striped align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>OP</th>
                        <th>Pedido Pai</th>
                        <th>Itens</th>
                        <th>Qtd Programada</th>
                        <th>Status</th>
                        <th>Data Abertura</th>
                        <th width="100">Ações</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($ordens as $ordem)

                    <tr>

                        <td>
                            <span class="badge bg-dark">
                                #{{ $ordem['codigo'] }}
                            </span>
                        </td>

                        <td>

                            @php
                            $pedidoPai = collect($ordem['itens'])
                            ->pluck('pedido_pai')
                            ->filter()
                            ->first();
                            @endphp

                            @if($pedidoPai)

                            <span class="badge bg-primary">
                                {{ $pedidoPai }}
                            </span>

                            @else

                            <span class="text-muted">
                                -
                            </span>

                            @endif

                        </td>

                        <td>

                            @foreach($ordem['itens'] as $it)

                            <div class="border rounded-3 p-2 mb-2 bg-light">

                                <div class="fw-semibold">
                                    {{ $it['produto'] }}
                                </div>

                                <small class="text-muted">
                                    Produto ID: {{ $it['produto_id'] }}
                                </small>

                            </div>

                            @endforeach

                        </td>

                        <td>

                            @foreach($ordem['itens'] as $it)

                            <div class="border rounded-3 p-2 mb-2 text-end">

                                <span class="fw-bold">
                                    {{ number_format($it['quantidade_programada'], 3, ',', '.') }}
                                </span>

                            </div>

                            @endforeach

                        </td>

                        <td>

                            @foreach($ordem['itens'] as $it)

                            <div class="border rounded-3 p-2 mb-2 text-center">

                                @if($it['status'] == 'Finalizado')

                                <span class="badge bg-success">
                                    Finalizado
                                </span>

                                @else

                                <span class="badge bg-warning text-dark">
                                    Produção
                                </span>

                                @endif

                            </div>

                            @endforeach

                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($ordem['data_abertura'])->format('d/m/Y H:i') }}
                        </td>

                        <td>

                            <a href="{{ route('ordem-producao.show', $ordem['id']) }}"
                            class="btn btn-sm btn-dark rounded-pill"
                            title="Visualizar OP">

                            <i class="ri-eye-fill"></i>

                        </a>

                    </td>

                </tr>

                @empty

                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        Nenhuma OP encontrada
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</div>