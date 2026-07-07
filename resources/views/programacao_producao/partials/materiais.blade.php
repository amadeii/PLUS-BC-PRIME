{{-- resources/views/programacao_producao/partials/materiais.blade.php --}}

<div class="card border rounded-3 shadow-sm mb-4">

    <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>
            <h5 class="fw-bold mb-1">
                <i class="ri-tools-fill text-danger me-2"></i>
                Necessidade de Materiais
            </h5>

            <small class="text-muted">
                Materiais necessários com base nos produtos a produzir
            </small>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">

            <a href="{{ route('programacao-producao.pdf-materiais', request()->query()) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3">
                <i class="ri-file-pdf-line me-1"></i>
                PDF
            </a>

            <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold">
                {{ collect($materiais)->where('situacao', 'FALTA')->count() }} críticos
            </span>

        </div>

    </div>

    <div class="card-body pt-0">

        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Composição</th>
                        <th>Tipo</th>
                        <th class="text-end">Necessidade</th>
                        <th class="text-center">Un.</th>
                        <th class="text-end">Estoque</th>
                        <th class="text-end">Falta</th>
                        <th class="text-center">Situação</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($materiais as $item)

                    <tr>
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
                                ID: {{ $item['produto_id'] ?? '-' }}
                            </small>
                        </td>
                        <td>
                            <div class="fw-semibold">
                                {{ $item['composicao_pai'] ?? '-' }}
                            </div>

                            <small class="text-muted">
                                ID: {{ $item['composicao_pai_id'] ?? '-' }}
                            </small>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $item['tipo'] }}
                            </span>
                        </td>

                        <td class="text-end">
                            <span class="fw-semibold">
                                {{ number_format($item['necessidade'], 3, ',', '.') }}
                            </span>
                        </td>

                        <td class="text-center">
                            {{ $item['unidade'] }}
                        </td>

                        <td class="text-end">
                            {{ number_format($item['estoque'], 3, ',', '.') }}
                        </td>

                        <td class="text-end">
                            @if($item['falta'] > 0)
                            <span class="text-danger fw-bold">
                                {{ number_format($item['falta'], 3, ',', '.') }}
                            </span>
                            @else
                            <span class="text-success fw-bold">
                                0,000
                            </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($item['situacao'] == 'FALTA')
                            <span class="badge bg-danger">
                                Falta Material
                            </span>
                            @else
                            <span class="badge bg-success">
                                OK
                            </span>
                            @endif
                        </td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Nenhuma necessidade de material encontrada
                        </td>
                    </tr>

                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="mt-2">
            <small class="text-muted">
                <i class="ri-information-line me-1"></i>
                Materiais com falta indicam necessidade de compra ou produção antes de iniciar as OPs.
            </small>
        </div>

    </div>

</div>