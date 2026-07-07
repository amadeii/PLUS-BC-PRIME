@extends('layouts.app', ['title' => 'Recebimento Físico'])
@section('css')
<style type="text/css">

</style>
@endsection
@section('content')
<div class="mt-1">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0 text-white">Conferência de Recebimento Físico</h4>
            </div>

            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-2">
                        <strong>Nº NFe:</strong><br>
                        {{ $item->numero_nfe ?? $item->numero ?? '--' }}
                    </div>

                    <div class="col-md-2">
                        <strong>Usuário:</strong><br>
                        {{ $item->user ? $item->user->name : '--' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Fornecedor:</strong><br>
                        {{ $item->fornecedor->razao_social ?? $item->fornecedor->nome ?? '--' }}
                    </div>

                    <div class="col-md-3">
                        <strong>Data emissão:</strong><br>
                        {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '--' }}
                    </div>

                    <div class="col-md-2">
                        <strong>Status conferência:</strong><br>
                        @if($conferencia)
                        @if($conferencia->status == 'conferido')
                        <span class="badge bg-success">Conferido</span>
                        @elseif($conferencia->status == 'divergente')
                        <span class="badge bg-danger">Divergente</span>
                        @else
                        <span class="badge bg-warning text-dark">{{ ucfirst($conferencia->status) }}</span>
                        @endif
                        @else
                        <span class="badge bg-secondary">Não conferido</span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('recebimento-fisico.store') }}">
                    @csrf
                    <input type="hidden" name="input_id" value="{{ $item->id }}">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Produto</th>
                                    <th>Referência</th>
                                    <th class="text-center">Qtd XML</th>
                                    <th class="text-">Qtd Conferida</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->itens as $key => $it)
                                @php
                                $itemConferido = $conferencia?->itens?->firstWhere('item_compra_id', $it->id);
                                @endphp
                                <tr>
                                    <td>{{ $it->produto->numero_sequencial }}</td>
                                    <td>
                                        <label style="width: 400px;">
                                            {{ $it->produto->nome ?? $it->nome ?? 'Produto não identificado' }}
                                        </label>
                                    </td>
                                    <td>
                                        {{ $it->produto->referencia ?? $it->referencia ?? '--' }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($it->quantidade, 2, ',', '.') }}

                                        <input type="hidden" name="itens[{{ $key }}][compra_item_id]" value="{{ $it->id }}">
                                        <input type="hidden" name="itens[{{ $key }}][produto_id]" value="{{ $it->produto_id ?? '' }}">
                                        <input type="hidden" name="itens[{{ $key }}][quantidade_xml]" value="{{ $it->quantidade }}">
                                    </td>
                                    <td>

                                        <input
                                        type="tel"
                                        min="0"
                                        placeholder="QTD"
                                        name="itens[{{ $key }}][quantidade_conferida]"
                                        class="form-control qtd-conferida"
                                        @if(!$it->produto->unidadeDecimal())
                                        value="{{ $itemConferido ? number_format($itemConferido->qtd_conferida, 0) : '' }}"
                                        @else
                                        value="{{ $itemConferido ? $itemConferido->qtd_conferida : '' }}"
                                        @endif
                                        data-xml="{{ $it->quantidade }}"
                                        >

                                    </td>
                                    <td>

                                        <input
                                        type="text"
                                        name="itens[{{ $key }}][observacao]"
                                        class="form-control"
                                        value="{{ $itemConferido ? $itemConferido->observacao : '' }}"
                                        placeholder="Observação do item"
                                        >
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum item encontrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label><strong>Observação geral</strong></label>
                            <textarea name="observacao" rows="3" class="form-control" placeholder="Observação geral da conferência">{{ old('observacao', $conferencia->observacao ?? '') }}</textarea>
                        </div>
                    </div>

                    @if($conferencia == null || $conferencia->status != 'conferido')
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ route('recebimento-fisico.impressao', $item->id) }}" target="_blank" class="btn btn-primary">
                            <i class="la la-print"></i> Imprimir
                        </a>

                        <button class="btn btn-success" type="submit">
                            Salvar Conferência
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    $('.qtd-conferida').on('input', function () {
        let valor = $(this).val();
        valor = valor.replace(/[^0-9.,]/g, '');

        $(this).val(valor);
    });
</script>
@endsection


