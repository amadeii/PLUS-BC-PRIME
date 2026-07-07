@extends('layouts.app', ['title' => 'Novo Roteiro de Produção'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Roteiro de Produção</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('roteiro-producao.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('roteiro-producao.store')
        !!}
        <div class="pl-lg-4">
            @include('roteiro_producao._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>

@endsection