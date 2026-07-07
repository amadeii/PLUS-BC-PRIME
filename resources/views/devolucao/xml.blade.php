@extends('layouts.app', ['title' => 'Importar XML Devolução'])

@section('css')
<style type="text/css">
    input[type="file"]{ display:none; }
    .card-body strong{ color:#8833FF; }
    .drop-xml{ border:2px dashed #8833FF; border-radius:14px; padding:35px 20px; text-align:center; background:#faf7ff; cursor:pointer; transition:.2s; }
    .drop-xml:hover,.drop-xml.dragover{ background:#f1e8ff; border-color:#6f20d8; transform:scale(1.01); }
    .drop-xml i{ font-size:42px; color:#8833FF; display:block; margin-bottom:10px; }
    .drop-xml h5{ font-weight:700; color:#333; margin-bottom:5px; }
    .drop-xml p{ color:#777; margin-bottom:0; }
    .drop-xml .btn-upload{ margin-top:15px; background:#8833FF; color:#fff; border-radius:8px; padding:8px 18px; font-weight:600; display:inline-block; }
    #filename{ display:block; margin-top:12px; font-weight:600; }
</style>
@endsection

@section('content')
<div class="card mt-1">
    <div class="card-header">
        <h4>Importar XML Devolução</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('devolucao.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i> Voltar
            </a>
        </div>
    </div>

    <div class="card-body">
        {!! Form::open()
        ->post()
        ->route('devolucao.store-xml')
        ->multipart()
        ->id('form-xml')
        !!}

        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="drop-xml" id="drop-xml">
                    <i class="ri-upload-cloud-2-line"></i>
                    <h5>Arraste o XML aqui</h5>
                    <p>ou clique para selecionar o arquivo de devolução</p>
                    <span class="btn-upload">Selecionar XML</span>

                    {!! Form::file('file', 'XML')->attrs(['accept' => '.xml', 'id' => 'inp-file']) !!}

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
                filename.innerHTML = 'Selecione apenas arquivo XML.';
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
            filename.innerHTML = 'Selecione apenas arquivo XML.';
            return;
        }

        filename.innerHTML = 'Enviando: ' + file.name;
        formXml.submit();
    }
</script>
@endsection