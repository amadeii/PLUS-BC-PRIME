@extends('layouts.app', ['title' => 'Importar arquivo'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Importar arquivos xml para NFe</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('nfe.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    
    <div class="card-footer">
        <hr>
        <form id="form-import" class="row" method="post" action="{{ route('nfe.import-zip-store') }}" enctype="multipart/form-data">
            @csrf
            <p>Importar arquivo zip de xml</p>

            <div class="col-md-5 file-upload" data-hint="">
                {!! Form::file('file', 'Arquivo ZIP')
                ->attrs(['accept' => '.zip'])->id('file') !!}
                <span class="text-danger" id="filename"></span>
            </div>
            
        </form>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    $('#file').change(function() {
        $('#form-import').submit();
        $body = $("body");
        $body.addClass("loading");
        
    });
</script>
@endsection
