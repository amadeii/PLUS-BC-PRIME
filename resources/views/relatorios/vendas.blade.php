@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless" style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Data</th>
            <th>Tipo</th>
            <th>Valor</th>
            @if(__countLocalAtivo() > 1)
            <th>Local</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php $total = 0; @endphp
        @foreach($data as $key => $item)
        <tr class="@if($key%2 == 0) pure-table-odd @endif">
            <td>
                {{ $item['id'] }}
            </td>
            <td>
                {{ $item['cliente'] }}
            </td>
            <td>
                {{ __data_pt($item['data']) }}
            </td>
            <td>
                {{ $item['tipo'] }}
            </td>
            <td>
                {{ __moeda($item['total']) }}
            </td>
            @if(__countLocalAtivo() > 1)
            <td class="text-danger">{{ $item['localizacao']->descricao }}</td>
            @endif

            @php $total += $item['total']; @endphp

        </tr>

        @if(sizeof($item['fatura']) > 0)
        @foreach($item['fatura'] as $f)
        <tr style="background-color: #EEF1FF;">
            <td colspan="2"></td>
            <td style="color: #3B4CA7; font-weight: bold;">
                {{ \App\Models\Nfce::getTipoPagamento($f['tipo_pagamento']) }}
            </td>
            <td style="color: #3B4CA7; font-weight: bold;">{{ __data_pt($f['data_vencimento'], 0) }}</td>
            <td style="color: #3B4CA7; font-weight: bold;">
                {{ __moeda($f['valor']) }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="6" style="padding: 3px 0;">
                <div style="border-top: 1px dashed #CBD5E1;"></div>
            </td>
        </tr>
        @endif

        @endforeach
    </tbody>
</table>

<div style="margin-top: 10px; margin-bottom: 15px; padding: 10px 12px; background: #F8FAFF; border-left: 4px solid #4254BA;">
    <div style="font-size: 12px; color: #6B7280;">
        Total de Registros: <strong style="color: #1F2937;">{{ sizeof($data) }}</strong>
    </div>
    <div style="font-size: 18px; color: #4557BB; font-weight: bold; margin-top: 10px;">
        Total de Vendas: R$ {{ __moeda($total) }}
    </div>
</div>

@if(count($totaisPagamento) > 0)
<br>
<br>
<table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 18px; border: 1px solid #D8E1F0; border-radius: 6px;">
    <tr>
        <td colspan="2" style="background-color: #4254BA; color: #fff; font-size: 13px; font-weight: bold;">
            Resumo por tipo de pagamento
        </td>
    </tr>

    @php $totalPagamentos = 0; @endphp

    @foreach($totaisPagamento as $tipo => $valor)
    @php $totalPagamentos += $valor; @endphp
    <tr style="background-color: {{ $loop->even ? '#F8FAFF' : '#FFFFFF' }};">
        <td style="padding: 9px 12px; border-bottom: 1px solid #E5E7EB; font-size: 12px; color: #1F2937;">
            {{ $tipo == 'OUTROS' ? 'Outros / sem fatura' : \App\Models\Nfce::getTipoPagamento($tipo) }}
        </td>
        <td style="padding: 9px 12px; border-bottom: 1px solid #E5E7EB; font-size: 12px; font-weight: bold; text-align: right;">
            R$ {{ __moeda($valor) }}
        </td>
    </tr>
    @endforeach
</table>
@endif
@endsection
