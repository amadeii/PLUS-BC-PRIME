@extends('layouts.app', ['title' => 'Novo Motorista'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Motorista</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('motoristas.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('motoristas.store')
        !!}
        <div class="pl-lg-4">
            @include('motorista._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection

@section('js')

@endsection
