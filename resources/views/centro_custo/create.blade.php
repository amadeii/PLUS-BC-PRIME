@extends('layouts.app', ['title' => 'Novo Centro de Custo'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Centro de Custo</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('centro-custo.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('centro-custo.store')

        !!}
        <div class="pl-lg-4">
            @include('centro_custo._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection


