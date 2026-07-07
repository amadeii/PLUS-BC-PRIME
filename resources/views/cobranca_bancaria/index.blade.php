@extends('layouts.app', ['title' => 'Cobrança Bancária'])

@section('content')

<div class="mt-1">
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8 col-12">
                    <h3 class="mb-0">
                        <i class="ri-bank-line"></i> Cobrança Bancária
                    </h3>
                    <small class="text-muted">
                        Contas a receber sem cobrança gerada
                    </small>
                </div>

                <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                    <a href="{{ route('cobrancas.logs') }}" class="btn btn-dark btn-sm">
                        <i class="ri-file-fill"></i> Logs
                    </a>
                    <a href="{{ route('cobrancas.index') }}" class="btn btn-primary btn-sm">
                        <i class="ri-file-text-line"></i> Cobranças geradas
                    </a>
                </div>
            </div>

            <hr class="mt-3">
            <div class="col-lg-12 mb-2">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3 g-2">
                    <div class="col-md-4">
                        {!!Form::select('cliente_id', 'Pesquisar por nome')->attrs(['class' => 'select2'])
                        ->options($cliente != null ? [$cliente->id => $cliente->info] : [])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-4 col-xl-2 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="ri-search-line"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('cobranca-bancaria.index') }}"><i class="ri-eraser-fill"></i>Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
            </div>


            @if($data->count() > 0)

            <div id="acoes-selecionados" class="alert alert-info d-none mt-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <strong id="qtd-selecionados">0</strong> cobrança(s) selecionada(s)
                        |
                        Total: <strong class="text-success">R$ <span id="total-selecionado">0,00</span></strong>
                    </div>

                    <div>
                        <button type="button" id="btn-gerar-boletos" class="btn btn-success">
                            <i class="ri-bank-card-line"></i> Gerar boletos
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                            </th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Vencimento</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        @php
                        $vencido = \Carbon\Carbon::parse($item->data_vencimento)->isPast();
                        @endphp

                        <tr class="">
                            <td>
                                <div class="form-check form-checkbox-primary mb-2">
                                    <input 
                                    class="form-check-input item-checkbox" 
                                    type="checkbox" 
                                    name="item_check[]" 
                                    value="{{ $item->id }}"
                                    data-valor="{{ $item->valor_integral }}"
                                    >
                                </div>
                            </td>
                            <td>
                                <strong>#{{ $item->numero_sequencial }}</strong>
                            </td>

                            <td>
                                <strong>{{ $item->cliente->razao_social ?? '--' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $item->cliente->cpf_cnpj ?? '' }}
                                </small>
                            </td>

                            <td>
                                <span class="{{ $vencido ? 'text-danger fw-bold' : '' }}">
                                    {{ __data_pt($item->data_vencimento, 0) }}
                                </span>
                            </td>

                            <td>
                                <strong class="text-success">
                                    R$ {{ __moeda($item->valor_integral) }}
                                </strong>
                            </td>

                            <td>
                                @if($vencido)
                                <span class="badge bg-danger">Vencido</span>
                                @else
                                <span class="badge bg-warning">Aberto</span>
                                @endif
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $data->links() }}
            </div>

            @else

            <div class="text-center py-5">
                <i class="ri-checkbox-circle-line text-success" style="font-size: 40px;"></i>
                <h5 class="mt-3">Nenhuma conta pendente</h5>
                <p class="text-muted">Todas as cobranças já foram geradas 👍</p>
            </div>

            @endif



        </div>
    </div>
</div>

@endsection
@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const acoesSelecionados = document.getElementById('acoes-selecionados');
        const qtdSelecionados = document.getElementById('qtd-selecionados');
        const totalSelecionado = document.getElementById('total-selecionado');
        const btnGerarBoletos = document.getElementById('btn-gerar-boletos');

        function formatarMoeda(valor) {
            return Number(valor).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function getSelecionados() {
            return Array.from(document.querySelectorAll('.item-checkbox:checked'));
        }

        function atualizarResumo() {
            const selecionados = getSelecionados();
            let total = 0;

            selecionados.forEach(item => {
                total += parseFloat(item.dataset.valor || 0);
            });

            qtdSelecionados.textContent = selecionados.length;
            totalSelecionado.textContent = formatarMoeda(total);

            if (selecionados.length > 0) {
                acoesSelecionados.classList.remove('d-none');
            } else {
                acoesSelecionados.classList.add('d-none');
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = itemCheckboxes.length > 0 && selecionados.length === itemCheckboxes.length;
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                itemCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });

                atualizarResumo();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                atualizarResumo();
            });
        });

        if (btnGerarBoletos) {
            btnGerarBoletos.addEventListener('click', function () {
                const selecionados = getSelecionados();

                if (selecionados.length === 0) {
                    alert('Selecione ao menos um título.');
                    return;
                }

                const ids = selecionados.map(item => item.value).join(',');

                window.location.href = "{{ route('cobranca-bancaria.create') }}" + '?ids=' + ids;
            });
        }

        atualizarResumo();
    });
</script>
@endsection