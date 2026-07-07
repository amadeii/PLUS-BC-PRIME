@extends('layouts.app', ['title' => 'Configuração Sicredi'])
@section('content')
<div class="card mt-1">
    <div class="card-header">
        <h4>Configuração Sicredi</h4>

    </div>
    <div class="card-body">
        {!!Form::open()->fill($item)
        ->post()
        ->route('sicredi-config.store')
        ->multipart()
        !!}
        <div class="pl-lg-4">
            @include('sicredi_config._forms')
        </div>
        {!!Form::close()!!}

    </div>
</div>
@endsection
