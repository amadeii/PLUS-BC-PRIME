@extends('layouts.app', ['title' => 'Editar Roteiro de Produção'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editar Roteiro de Produção</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('roteiro-producao.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()->fill($roteiro)
        ->put()
        ->route('roteiro-producao.update',[$roteiro->id])
        !!}
        <div class="pl-lg-4">
            @include('roteiro_producao._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>

@endsection