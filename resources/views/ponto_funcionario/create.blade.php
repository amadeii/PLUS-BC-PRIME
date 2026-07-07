@extends('layouts.app', ['title' => 'Novo Vínculo'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Novo Vínculo</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('ponto-funcionario.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('ponto-funcionario.store')

        !!}
        <div class="pl-lg-4">
            @include('ponto_funcionario._forms')
        </div>
        {!!Form::close()!!}
    </div>
</div>
@endsection


