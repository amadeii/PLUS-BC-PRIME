@extends('layouts.app', ['title' => 'Importar Xml'])

@section('css')
<style type="text/css">
    input[type="file"]{ display:none; }

    .card-body strong{ color:#8833FF; }

    .drop-xml{
        border:2px dashed #8833FF;
        border-radius:14px;
        padding:40px 20px;
        text-align:center;
        background:#faf7ff;
        cursor:pointer;
        transition:.2s;
    }

    .drop-xml:hover,
    .drop-xml.dragover{
        background:#f2eaff;
        border-color:#6e22d8;
        transform:scale(1.01);
    }

    .drop-xml i{
        font-size:48px;
        color:#8833FF;
        margin-bottom:12px;
        display:block;
    }

    .drop-xml h5{
        font-weight:700;
        color:#333;
        margin-bottom:5px;
    }

    .drop-xml p{
        color:#777;
        margin-bottom:0;
    }

    .btn-upload{
        display:inline-block;
        margin-top:18px;
        background:#8833FF;
        color:#fff;
        padding:10px 20px;
        border-radius:8px;
        font-weight:600;
    }

    #filename{
        display:block;
        margin-top:14px;
        font-weight:600;
    }
</style>
@endsection

@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Importar Xml</h4>

        <div style="text-align:right; margin-top:-35px;">
            <a href="{{ route('compras.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">

        {!! Form::open()
        ->post()
        ->route('compras.store-xml')
        ->multipart()
        ->id('form-xml')
        !!}

        <div class="row justify-content-center">
            <div class="col-md-7">

                <div class="drop-xml" id="drop-xml">

                    <i class="ri-file-upload-line"></i>

                    <h5>Arraste o XML aqui</h5>

                    <p>
                        ou clique para selecionar o arquivo XML da compra
                    </p>

                    <span class="btn-upload">
                        Selecionar XML
                    </span>

                    {!! Form::file('file', 'Arquivo XML')
                    ->attrs([
                        'accept' => '.xml',
                        'id' => 'inp-file'
                    ]) !!}

                    <span class="text-danger" id="filename"></span>

                </div>

            </div>
        </div>

        {!! Form::close() !!}

    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">

    const dropXml = document.getElementById('drop-xml');
    const inputXml = document.getElementById('inp-file');
    const filename = document.getElementById('filename');
    const formXml = document.getElementById('form-xml');

    dropXml.addEventListener('click', function(){
        inputXml.click();
    });

    inputXml.addEventListener('change', function(){

        if(inputXml.files.length > 0){
            validarEnviar(inputXml.files[0]);
        }

    });

    dropXml.addEventListener('dragover', function(e){

        e.preventDefault();
        dropXml.classList.add('dragover');

    });

    dropXml.addEventListener('dragleave', function(e){

        e.preventDefault();
        dropXml.classList.remove('dragover');

    });

    dropXml.addEventListener('drop', function(e){

        e.preventDefault();
        dropXml.classList.remove('dragover');

        if(e.dataTransfer.files.length > 0){

            const file = e.dataTransfer.files[0];

            if(!file.name.toLowerCase().endsWith('.xml')){
                filename.innerHTML = 'Selecione apenas arquivos XML.';
                return;
            }

            const dataTransfer = new DataTransfer();

            dataTransfer.items.add(file);

            inputXml.files = dataTransfer.files;

            validarEnviar(file);
        }

    });

    function validarEnviar(file){

        if(!file.name.toLowerCase().endsWith('.xml')){
            filename.innerHTML = 'Selecione apenas arquivos XML.';
            return;
        }

        filename.innerHTML = 'Enviando: ' + file.name;

        formXml.submit();
    }

</script>
@endsection