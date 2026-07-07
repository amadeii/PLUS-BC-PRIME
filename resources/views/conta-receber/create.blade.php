@extends('layouts.app', ['title' => 'Nova Conta Receber'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        @if(isset($diferenca) && $diferenca > 0)
        <h4>Adicionar conta</h4>
        @else
        <h4>Nova Conta Receber</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('conta-receber.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
        @endif
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('conta-receber.store')
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('conta-receber._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>

@include('modals._novo_cliente')

@endsection
