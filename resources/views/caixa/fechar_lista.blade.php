@extends('layouts.app', ['title' => 'Fechando caixa'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <div class="d-flex flex-column m-2">
            <span class="text-dark" style="font-size: 20px; font-weight: 600; margin-top: 10px">
                <i class="ri-wallet-3-line text-success"></i>
                Fechar Caixa
            </span>

            @if($item->contaEmpresa)
            <span class="text-muted" style="font-size: 13px;">
                <i class="ri-building-2-line"></i>
                {{ $item->contaEmpresa->nome }}
            </span>
            @endif
        </div>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('caixa.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('caixa.fechar-tipos-pagamento', [$item->id])
        !!}
        <div class="pl-lg-4">
            @include('caixa._listar_pagamentos')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="/js/controla_conta_empresa.js"></script>
<script type="text/javascript" src="/js/conta_empresa.js"></script>
@endsection
