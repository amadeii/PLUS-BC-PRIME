@section('css')
<style type="text/css">
    .logo-drop{
        position: relative;
        border: 2px dashed #8833FF55;
        border-radius: 14px;
        background: #faf7ff;
        min-height: 180px;
        cursor: pointer;
        overflow: hidden;
        transition: .2s;
    }

    .logo-drop:hover{
        border-color: #8833FF;
        background: #f5efff;
    }

    .logo-drop input{
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        inset: 0;
    }

    .logo-content{
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
    }

    .logo-content i{
        font-size: 42px;
        color: #8833FF;
        margin-bottom: 10px;
    }

    .logo-content strong{
        display: block;
        color: #374151;
    }

    .logo-content span{
        font-size: 12px;
        color: #6b7280;
    }

    .logo-preview{
        padding: 10px;
    }

    .logo-preview img{
        max-height: 120px;
        max-width: 100%;
        object-fit: contain;
    }
</style>
@endsection

@extends('layouts.app', ['title' => 'Dados White Label'])
@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Dados White Label</h4>
        <div style="text-align: right; margin-top: -35px;">
            <a href="{{ route('contadores.index') }}" class="btn btn-danger btn-sm px-3">
                <i class="ri-arrow-left-double-fill"></i>Voltar
            </a>
        </div>
    </div>
    <div class="card-body">
        {!!Form::open()
        ->post()
        ->route('contadores.white-label.save', [$item->id])
        ->multipart()
        !!}
        <div class="pl-lg-4">

            <div class="row g-2">

                <div class="col-md-4">
                    {!!Form::text('dominio', 'Domínio')
                    ->attrs(['placeholder' => 'erp.seudominio.com.br'])
                    ->value($item->dominio)
                    !!}
                </div>

                <div class="col-md-4">
                    {!!Form::text('nome_sistema', 'Nome do Sistema')
                    ->value($item->nome_sistema)
                    !!}
                </div>

                <div class="col-md-4">
                    {!!Form::text('site', 'Site')
                    ->attrs(['placeholder' => 'https://seudominio.com.br'])
                    ->value($item->site)
                    !!}
                </div>

                <div class="col-md-3">
                    {!!Form::tel('telefone_suporte', 'Telefone Suporte')
                    ->attrs(['class' => 'fone'])
                    ->value($item->telefone_suporte)
                    !!}
                </div>

                <div class="col-md-3">
                    {!!Form::tel('whatsapp_suporte', 'WhatsApp Suporte')
                    ->attrs(['class' => 'fone'])
                    ->value($item->whatsapp_suporte)
                    !!}
                </div>

                <div class="col-md-4">
                    {!!Form::text('email_suporte', 'Email Suporte')
                    ->type('email')
                    ->value($item->email_suporte)
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::select('white_label', 'White Label', [1 => 'Sim', 0 => 'Não'])
                    ->attrs(['class' => 'form-select'])
                    ->value($item->white_label)
                    !!}
                </div>

                <hr class="mt-4">
                <h5>Logos do Sistema</h5>

                <div class="col-md-6">
                    <label>Logo Login</label>

                    <div class="logo-drop">
                        <input type="file" id="logo_login" name="logo_login" accept="image/*">

                        <div class="logo-content" id="preview_logo_login">

                            @if(isset($item) && $item->logo_login)
                            <img src="{{ asset('uploads/white-label/'.$item->logo_login) }}" class="img-fluid">
                            @else
                            <i class="ri-image-add-line"></i>
                            <h6 class="mb-1">Logo da Tela de Login</h6>
                            <small>Clique ou arraste uma imagem</small>
                            @endif

                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label>Logo Sidebar</label>

                    <div class="logo-drop">
                        <input type="file" id="logo_sidebar" name="logo_sidebar" accept="image/*">

                        <div class="logo-content" id="preview_logo_sidebar">

                            @if(isset($item) && $item->logo_sidebar)
                            <img src="{{ asset('uploads/white-label/'.$item->logo_sidebar) }}" class="img-fluid">
                            @else
                            <i class="ri-layout-left-line"></i>
                            <h6 class="mb-1">Logo do Menu Lateral</h6>
                            <small>Clique ou arraste uma imagem</small>
                            @endif

                        </div>
                    </div>
                </div>

                <hr class="mt-4">
                <div class="col-12" style="text-align: right;">
                    <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(function(){

    function bindLogoUpload(inputId, previewId){

        const input = $('#' + inputId);
        const preview = $('#' + previewId);
        const drop = input.closest('.logo-drop');

        input.on('change', function(){

            if(!this.files.length) return;

            renderPreview(this.files[0], preview);
        });

        drop.on('dragover', function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        drop.on('dragleave', function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        drop.on('drop', function(e){

            e.preventDefault();
            e.stopPropagation();

            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;

            if(!files.length) return;

            input[0].files = files;

            renderPreview(files[0], preview);
        });
    }

    function renderPreview(file, preview){

        if(!file.type.match('image.*')){
            swal("Atenção", "Selecione apenas imagens.", "warning");
            return;
        }

        let reader = new FileReader();

        reader.onload = function(e){

            preview.html(`
                <div class="logo-preview">
                    <img src="${e.target.result}" class="img-fluid">
                </div>
            `);
        };

        reader.readAsDataURL(file);
    }

    bindLogoUpload('logo_login', 'preview_logo_login');
    bindLogoUpload('logo_sidebar', 'preview_logo_sidebar');

});
</script>
@endsection