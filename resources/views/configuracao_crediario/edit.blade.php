@extends('layouts.app', ['title' => 'Editar Configuração de Crediário'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editar Configuração de Crediário</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('configuracao-crediario.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        {!!Form::open()->fill($item)->put()->route('configuracao-crediario.update', [$item->id])!!}
        <div class="pl-lg-4">
            @include('configuracao_crediario._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection