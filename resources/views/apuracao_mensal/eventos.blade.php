@php
    function normalizaEventoPonto($texto) {
        $texto = mb_strtolower($texto ?? '');
        $texto = str_replace(
            ['á','à','ã','â','ä','é','ê','è','ë','í','ì','î','ï','ó','ô','õ','ò','ö','ú','ù','û','ü','ç'],
            ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c'],
            $texto
        );

        return trim($texto);
    }
@endphp

{{-- RESUMO MENSAL PARA SALVAR --}}
<input type="hidden" name="horas_previstas" value="{{ $totalHorasPrevistas }}">
<input type="hidden" name="horas_trabalhadas" value="{{ $totalHorasTrabalhadas }}">
<input type="hidden" name="horas_extras" value="{{ $horasExtras }}">
<input type="hidden" name="horas_faltas" value="{{ $totalHorasFaltas }}">
<input type="hidden" name="horas_atrasos" value="{{ $totalHorasAtrasos }}">
<input type="hidden" name="horas_saida_antecipada" value="{{ $totalHorasSaidaAntecipada }}">
<input type="hidden" name="saldo_horas" value="{{ $saldoHoras }}">
<input type="hidden" name="saldo_minutos" value="{{ $saldoMinutos }}">

<input type="hidden" name="faltas" value="{{ $faltas }}">
<input type="hidden" name="dias_com_ponto" value="{{ $diasComPonto }}">
<input type="hidden" name="dias_com_extra" value="{{ $diasComExtra }}">
<input type="hidden" name="dias_incompletos" value="{{ $diasIncompletos }}">

@foreach($item->eventosAtivos as $ev)

@php
    $nomeEventoNormalizado = normalizaEventoPonto($ev->evento->nome ?? '');

    $ehHoraExtra =
        str_contains($nomeEventoNormalizado, 'hora extra') ||
        str_contains($nomeEventoNormalizado, 'horas extra') ||
        str_contains($nomeEventoNormalizado, 'horas extras') ||
        str_contains($nomeEventoNormalizado, 'extra');

    $ehDescontoFalta =
        str_contains($nomeEventoNormalizado, 'falta') ||
        str_contains($nomeEventoNormalizado, 'faltas') ||
        str_contains($nomeEventoNormalizado, 'desconto falta') ||
        str_contains($nomeEventoNormalizado, 'desconto faltas');

    if ($ehDescontoFalta) {
        $valorBaseEvento = $valorHoraFuncionario;
        $valorTotalEvento = $valorDescontoFaltas;
    } else {
        if ($ev->evento->tipo_valor == 'percentual') {
            $valorBaseEvento = $item->salario * ($ev->valor / 100);
        } else {
            $valorBaseEvento = $ev->valor;
        }

        $valorTotalEvento = $ehHoraExtra
            ? ($valorBaseEvento * $horasExtrasDecimal)
            : $valorBaseEvento;
    }

    $quantidadeReferencia = 1;
    $tipoReferencia = '';
    $calculadoAutomaticamente = 0;

    if ($ehHoraExtra) {
        $quantidadeReferencia = $horasExtrasDecimal;
        $tipoReferencia = 'horas_extras';
        $calculadoAutomaticamente = 1;
    }

    if ($ehDescontoFalta) {
        $quantidadeReferencia = $horasFaltasDecimal;
        $tipoReferencia = 'horas_faltas';
        $calculadoAutomaticamente = 1;
    }
@endphp

<tr class="datatable-row dynamic-form">
    <td class="datatable-cell">
        <button type="button" class="btn btn-sm btn-danger btn-delete btn-delete-row">
            <i class="ri-delete-bin-line"></i>
        </button>
    </td>

    <td class="datatable-cell">
        <span class="codigo" style="width: 200px;">
            <select required name="evento[]" class="form-select evento select-disabled">
                <option
                    value="{{ $ev->evento_id }}"
                    data-condicao="{{ $ev->condicao }}"
                    data-metodo="{{ $ev->metodo }}"
                    data-eh-hora-extra="{{ $ehHoraExtra ? 1 : 0 }}"
                    data-eh-desconto-falta="{{ $ehDescontoFalta ? 1 : 0 }}"
                >
                    {{ $ev->evento->nome }}
                </option>
            </select>
        </span>

        @if($ehHoraExtra)
            <small class="text-success d-block mt-1">
                {{ $horasExtras }} hora(s) extra(s) apurada(s)
                <br>
                <span class="text-muted">
                    Base decimal: {{ number_format($horasExtrasDecimal, 2, ',', '.') }}h
                </span>
            </small>
        @endif

        @if($ehDescontoFalta)
            <small class="text-danger d-block mt-1">
                {{ $totalHorasFaltas }} hora(s) faltante(s)
                <br>
                <span class="text-muted">
                    Base decimal: {{ number_format($horasFaltasDecimal, 2, ',', '.') }}h
                </span>
            </small>
        @endif
    </td>

    <td class="datatable-cell">
        <span class="codigo" style="width: 100px;">
            <select required name="condicao[]" class="form-select condicao_chave select-disabled" readonly>
                @if($ev->condicao == "soma")
                    <option value="soma">Soma</option>
                @else
                    <option value="diminui">Diminui</option>
                @endif
            </select>
        </span>
    </td>

    <td class="datatable-cell">
        <span class="codigo" style="width: 180px;">
            <input
                @if($ev->metodo == "fixo") readonly @endif
                value="{{ __moeda($valorBaseEvento) }}"
                required
                type="tel"
                name="valor[]"
                class="form-control value"
                data-eh-hora-extra="{{ $ehHoraExtra ? 1 : 0 }}"
                data-horas-extras="{{ $horasExtrasDecimal }}"
                data-eh-desconto-falta="{{ $ehDescontoFalta ? 1 : 0 }}"
                data-horas-faltas="{{ $horasFaltasDecimal }}"
            >

            <input
                type="hidden"
                name="valor_total_evento[]"
                class="valor-total-evento"
                value="{{ __moeda($valorTotalEvento) }}"
            >

            <input
                type="hidden"
                name="quantidade_referencia[]"
                class="quantidade-referencia"
                value="{{ $quantidadeReferencia }}"
            >

            <input
                type="hidden"
                name="tipo_referencia[]"
                value="{{ $tipoReferencia }}"
            >

            <input
                type="hidden"
                name="calculado_automaticamente[]"
                value="{{ $calculadoAutomaticamente }}"
            >

            @if($ehHoraExtra)
                <small class="text-muted d-block mt-1 resumo-hora-extra">
                    Total: R$ {{ __moeda($valorTotalEvento) }}
                </small>
            @endif

            @if($ehDescontoFalta)
                <small class="text-danger d-block mt-1 resumo-falta">
                    Total desconto: R$ {{ __moeda($valorTotalEvento) }}
                </small>
            @endif
        </span>
    </td>

    <td class="datatable-cell">
        <span class="codigo" style="width: 100px;">
            <select required name="metodo[]" class="form-select metodo select-disabled">
                @if($ev->metodo == "informado")
                    <option value="informado">Informado</option>
                @else
                    <option value="fixo">Fixo</option>
                @endif
            </select>
        </span>
    </td>
</tr>

@endforeach

<tr>
    <td colspan="5">
        <div class="card mt-4 border-0 shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="ri-time-line"></i> Registros de ponto
                    </h5>

                    <span class="badge bg-primary">
                        {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $ano }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Dia</th>
                                <th>Entrada</th>
                                <th>Intervalo início</th>
                                <th>Intervalo fim</th>
                                <th>Saída</th>
                                <th>Previstas</th>
                                <th>Trabalhadas</th>
                                <th>Extras</th>
                                <th>Atraso</th>
                                <th>Saída ant.</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($relatorioPonto as $ponto)

                                @php
                                    $dataBanco = \Carbon\Carbon::createFromFormat('d/m/Y', $ponto['data'])->format('Y-m-d');
                                @endphp

                                <tr>
                                    {{-- DADOS DIÁRIOS PARA SALVAR --}}
                                    <input type="hidden" name="ponto_data[]" value="{{ $dataBanco }}">
                                    <input type="hidden" name="ponto_dia_semana[]" value="{{ $ponto['dia_semana'] }}">
                                    <input type="hidden" name="ponto_entrada[]" value="{{ $ponto['entrada'] }}">
                                    <input type="hidden" name="ponto_intervalo_inicio[]" value="{{ $ponto['intervalo_inicio'] }}">
                                    <input type="hidden" name="ponto_intervalo_fim[]" value="{{ $ponto['intervalo_fim'] }}">
                                    <input type="hidden" name="ponto_saida[]" value="{{ $ponto['saida'] }}">
                                    <input type="hidden" name="ponto_horas_previstas[]" value="{{ $ponto['horas_previstas'] }}">
                                    <input type="hidden" name="ponto_horas_trabalhadas[]" value="{{ $ponto['horas_trabalhadas'] }}">
                                    <input type="hidden" name="ponto_horas_extras[]" value="{{ $ponto['horas_extras'] }}">
                                    <input type="hidden" name="ponto_horas_faltas[]" value="{{ $ponto['horas_faltas'] }}">
                                    <input type="hidden" name="ponto_atraso[]" value="{{ $ponto['atraso'] }}">
                                    <input type="hidden" name="ponto_saida_antecipada[]" value="{{ $ponto['saida_antecipada'] }}">
                                    <input type="hidden" name="ponto_status[]" value="{{ $ponto['status'] }}">

                                    <td>{{ $ponto['data'] }}</td>
                                    <td>{{ $ponto['dia_semana'] }}</td>
                                    <td>{{ $ponto['entrada'] }}</td>
                                    <td>{{ $ponto['intervalo_inicio'] }}</td>
                                    <td>{{ $ponto['intervalo_fim'] }}</td>
                                    <td>{{ $ponto['saida'] }}</td>
                                    <td>{{ $ponto['horas_previstas'] }}</td>

                                    <td>
                                        <strong>{{ $ponto['horas_trabalhadas'] }}</strong>
                                    </td>

                                    <td>
                                        @if($ponto['tem_extra'])
                                            <span class="badge bg-success">{{ $ponto['horas_extras'] }}</span>
                                        @else
                                            <span class="badge bg-light text-dark">00:00</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($ponto['atraso'] != '00:00')
                                            <span class="badge bg-warning text-dark">{{ $ponto['atraso'] }}</span>
                                        @else
                                            <span class="text-muted">00:00</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($ponto['saida_antecipada'] != '00:00')
                                            <span class="badge bg-danger">{{ $ponto['saida_antecipada'] }}</span>
                                        @else
                                            <span class="text-muted">00:00</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($ponto['status'] == 'Completo')
                                            <span class="badge bg-success">Completo</span>
                                        @elseif($ponto['status'] == 'Falta')
                                            <span class="badge bg-danger">Falta</span>
                                        @elseif($ponto['status'] == 'Incompleto')
                                            <span class="badge bg-warning text-dark">Incompleto</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $ponto['status'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">
                                        Nenhum registro de ponto encontrado para este mês.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4 g-3">

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 bg-light">
                            <div class="card-body">
                                <small class="text-muted">Horas previstas</small>
                                <h4 class="mb-0">{{ $totalHorasPrevistas }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 bg-light">
                            <div class="card-body">
                                <small class="text-muted">Horas trabalhadas</small>
                                <h4 class="mb-0">{{ $totalHorasTrabalhadas }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 bg-light">
                            <div class="card-body">
                                <small class="text-muted">Total horas extras</small>
                                <h4 class="mb-0 text-success">{{ $horasExtras }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100 bg-light">
                            <div class="card-body">
                                <small class="text-muted">Saldo mensal</small>

                                @if($saldoMinutos > 0)
                                    <h4 class="mb-0 text-success">+{{ $saldoHoras }}</h4>
                                @elseif($saldoMinutos < 0)
                                    <h4 class="mb-0 text-danger">-{{ $saldoHoras }}</h4>
                                @else
                                    <h4 class="mb-0">00:00</h4>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Faltas</small>
                                <h4 class="mb-0 text-danger">{{ $faltas }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Horas faltantes</small>
                                <h4 class="mb-0 text-danger">{{ $totalHorasFaltas }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Valor desconto faltas</small>
                                <h4 class="mb-0 text-danger">
                                    R$ {{ __moeda($valorDescontoFaltas ?? 0) }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Valor/hora funcionário</small>
                                <h4 class="mb-0">
                                    R$ {{ __moeda($valorHoraFuncionario ?? 0) }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Atrasos</small>
                                <h4 class="mb-0 text-warning">{{ $totalHorasAtrasos }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Saída antecipada</small>
                                <h4 class="mb-0 text-danger">{{ $totalHorasSaidaAntecipada }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Dias com ponto</small>
                                <h4 class="mb-0">{{ $diasComPonto }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Dias com extra</small>
                                <h4 class="mb-0 text-success">{{ $diasComExtra }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Dias incompletos</small>
                                <h4 class="mb-0 text-warning">{{ $diasIncompletos }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <small class="text-muted">Vínculo jornada</small>

                                @if($vinculoPonto && $vinculoPonto->jornada)
                                    <h6 class="mb-0">{{ $vinculoPonto->jornada->descricao ?? 'Jornada vinculada' }}</h6>
                                @else
                                    <h6 class="mb-0 text-danger">Sem jornada</h6>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </td>
</tr>