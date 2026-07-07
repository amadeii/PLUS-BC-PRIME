@extends('layouts.app', ['title' => 'Importar MDF-e'])
@section('content')

<div class="card mt-1">
    
    <div class="card-header">

        <h4>
            <i class="ri-file-zip-line text-primary"></i>
            Importar MDF-es
        </h4>

        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('mdfe.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>
                Voltar
            </a>
        </div>

    </div>

    <div class="card-body">

        <div class="alert alert-primary d-flex align-items-start gap-2">

            <i class="ri-information-line fs-4"></i>

            <div>
                <strong>Importação de MDF-e de outro sistema</strong>

                <div class="mt-1">
                    Selecione um arquivo <strong>.ZIP</strong> contendo os XMLs de MDF-e autorizados.
                    O sistema irá localizar automaticamente os XMLs válidos, importar os MDF-es,
                    cadastrar veículos e motoristas caso necessário e evitar duplicidades pela chave.
                </div>
            </div>

        </div>

    </div>

    <div class="card-footer">

        <hr>

        <form id="form-import"
        class="row"
        method="post"
        action="{{ route('mdfe-importacao.store') }}"
        enctype="multipart/form-data">

        @csrf

        <div class="col-md-6 file-upload">

            {!! Form::file('arquivo', 'Arquivo ZIP')
            ->attrs([
                'accept' => '.zip'
            ])->id('arquivo') !!}

            <small class="text-muted">
                Formatos aceitos: .zip
            </small>

            <div class="mt-2">
                <span class="text-primary fw-bold" id="filename"></span>
            </div>

        </div>

    </form>

</div>

</div>

@endsection

@section('js')
<script>

    $('#arquivo').change(function(){

        let fileName = $(this).val().split('\\').pop();

        $('#filename').html(`
            <i class="ri-file-zip-line"></i>
            ${fileName}
        `);

        $('#form-import').submit();

        $('body').addClass('loading');
    });

</script>
@endsection