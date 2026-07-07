@extends('layouts.app', ['title' => 'Editar Modelo de Etiqueta'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editar Modelo de Etiqueta</h4>

        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('etiqueta-modelos.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>
                Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        {!!Form::open()->fill($item)
        ->put()
        ->route('etiqueta-modelos.update', [$item->id])
        !!}

        <div class="pl-lg-4">
            @include('etiquetas_modelos._forms')
        </div>

        {!!Form::close()!!}
    </div>
</div>

@endsection