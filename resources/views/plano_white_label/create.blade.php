@extends('layouts.app', ['title' => 'Novo plano White Label'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Plano White Label</h4>

        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('planos-white-label.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('planos-white-label.store')
        !!}
        <div class="pl-lg-4">
            @include('plano_white_label._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection