@extends('layouts.app', ['title' => 'Exportador Balança'])
@section('content')
<div class="mt-1">
    <div class="row">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('balanca.toledo_mgv5') }}" class="btn btn-primary">
                    Exportar para Toledo MGV5
                </a>
                <a href="{{ route('balanca.toledo_mgv6') }}" class="btn btn-primary">
                    Exportar para Toledo MGV6
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
