@extends('layouts.app', ['title' => 'Configuração Asaas'])
@section('content')
<div class="card mt-1">
    <div class="card-header">
        <h4>Configuração Asaas</h4>

    </div>
    <div class="card-body">
        {!!Form::open()->fill($item)
        ->post()
        ->route('asaas-config.store')

        !!}
        <div class="pl-lg-4">
            @include('asaas_config._forms')
        </div>
        {!!Form::close()!!}

    </div>
</div>
@endsection
