@extends('layouts.app', ['title' => 'Galeria'])

@section('css')
<style>
    .gallery-page{ padding:4px; }
    .gallery-header{ display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
    .gallery-title{ display:flex; align-items:center; gap:14px; }
    .gallery-title-icon{ width:46px; height:46px; border-radius:16px; background:#f3f0ff; color:var(--main-color); display:flex; align-items:center; justify-content:center; font-size:24px; }
    .gallery-title h4{ margin:0; font-weight:800; color:#1f2937; }
    .gallery-title p{ margin:2px 0 0; color:#6b7280; font-size:13px; }
    .main-product-card{ display:flex; align-items:center; gap:16px; background:#f8fafc; border:1px solid #eef0f6; border-radius:20px; padding:14px; }
    .main-product-card img{ width:120px; height:120px; object-fit:cover; border-radius:18px; background:#fff; border:1px solid #eef0f6; }
    .main-product-card span{ display:block; font-size:12px; color:#6b7280; margin-bottom:4px; }
    .main-product-card strong{ color:#111827; font-size:16px; }

    .upload-box{ border:1px dashed #d8dce8; background:#fbfcff; border-radius:22px; padding:18px; margin-top:18px; }
    .upload-content{ display:flex; gap:18px; align-items:center; flex-wrap:wrap; }
    .upload-preview{ width:210px; height:210px; border-radius:20px; background:#fff; border:1px solid #eef0f6; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; }
    .upload-preview img{ width:100%; height:100%; object-fit:cover; }
    .btn-remove-preview{ position:absolute; top:10px; right:10px; width:30px; height:30px; border-radius:50%; border:0; background:#ef4444; color:#fff; font-weight:800; display:none; }
    .upload-info{ flex:1; min-width:260px; }
    .upload-info h5{ margin:0 0 6px; font-weight:800; color:#111827; }
    .upload-info p{ margin:0 0 14px; color:#6b7280; font-size:13px; }
    .upload-actions{ display:flex; gap:10px; flex-wrap:wrap; }
    .input-file-hidden{ display:none; }
    .btn-upload-select{ border:1px solid #e7e9f2; background:#fff; color:#374151; border-radius:12px; padding:10px 16px; font-weight:700; cursor:pointer; }
    .btn-upload-save{
        border:0;
        background: #159488 !important;
        color:#fff !important;
        border-radius:14px;
        padding:11px 22px;
        font-weight:800;
        font-size:14px;
        display:inline-flex;
        align-items:center;
        gap:8px;
        transition:all .2s ease;
    }

    .btn-upload-save:hover{
        transform:translateY(-1px);
        filter:brightness(1.03);
        color:#fff !important;
    }

    .gallery-section-title{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin:24px 0 14px; }
    .gallery-section-title h5{ margin:0; font-weight:800; color:#111827; }
    .gallery-count{ background:#f3f0ff; color:var(--main-color); padding:6px 12px; border-radius:999px; font-size:12px; font-weight:800; }

    .gallery-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:16px; }
    .gallery-card{ border:1px solid #eef0f6; border-radius:22px; background:#fff; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,.04); }
    .gallery-card-img{ height:210px; background:#f8fafc; display:flex; align-items:center; justify-content:center; }
    .gallery-card-img img{ width:100%; height:100%; object-fit:cover; }
    .gallery-card-footer{ padding:12px; }
    .btn-delete-image{ width:100%; border:0; background:#fff1f2; color:#dc2626; border-radius:12px; padding:9px 12px; font-weight:800; }
    .btn-delete-image:hover{ background:#fee2e2; color:#b91c1c; }

    .empty-gallery{ border:1px dashed #d8dce8; border-radius:20px; padding:28px; text-align:center; color:#6b7280; background:#fbfcff; }
    @media(max-width:576px){
        .main-product-card{ width:100%; }
        .main-product-card img{ width:95px; height:95px; }
        .upload-preview{ width:100%; height:240px; }
    }
</style>
@endsection

@section('content')
<div class="mt-1 gallery-page">
    <div class="card">
        <div class="card-body">

            <div class="gallery-header">
                <div class="gallery-title">
                    <div class="gallery-title-icon">
                        <i class="ri-image-add-line"></i>
                    </div>
                    <div>
                        <h4>Galeria do produto</h4>
                        <p>Adicione e gerencie as imagens complementares do produto.</p>
                    </div>
                </div>

                <div class="main-product-card">
                    <img src="{{ $item->img }}" alt="{{ $item->nome }}">
                    <div>
                        <span>Imagem principal</span>
                        <strong>{{ $item->nome }}</strong>
                    </div>
                </div>
            </div>

            {!! Form::open()->post()->route('produtos.galeria-store', [$item->id])->multipart()->id('form-image') !!}
            <div class="upload-box">
                <div class="upload-content">
                    <div class="upload-preview">
                        <button type="button" id="btn-remove-preview" class="btn-remove-preview">×</button>
                        <img id="file-ip-1-preview" src="/imgs/no-image.png" alt="Pré-visualização">
                    </div>

                    <div class="upload-info">
                        <h5>Enviar nova imagem</h5>
                        <p>Selecione uma imagem do produto para adicionar na galeria. Após escolher, confira a prévia e clique em salvar.</p>

                        <div class="upload-actions">
                            <label for="file-ip-1" class="btn-upload-select">
                                <i class="ri-upload-cloud-2-line"></i> Escolher imagem
                            </label>

                            <input type="file" id="file-ip-1" name="image" accept="image/*" class="input-file-hidden" onchange="showPreview(event);">

                            <button type="submit" class="btn-upload-save">
                                <i class="ri-check-line"></i>
                                <span>Salvar imagem</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}

            <div class="gallery-section-title">
                <h5>Outras imagens do produto</h5>
                <span class="gallery-count">{{ $item->galeria->count() }} imagem(ns)</span>
            </div>

            @if($item->galeria->count())
            <div class="gallery-grid">
                @foreach($item->galeria as $i)
                <div class="gallery-card">
                    <div class="gallery-card-img">
                        <img src="{{ $i->img }}" alt="Imagem do produto">
                    </div>

                    <div class="gallery-card-footer">
                        <form action="{{ route('produtos.destroy-image', $i->id) }}" method="post" id="form-{{$i->id}}">
                            @method('delete')
                            @csrf
                            <button type="button" class="btn-delete-image btn-delete">
                                <i class="ri-delete-bin-6-line"></i> Remover imagem
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-gallery">
                <i class="ri-image-line" style="font-size:34px;"></i>
                <div class="mt-2">
                    Nenhuma imagem adicional cadastrada para este produto.
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
    function showPreview(event){
        const input = event.target;
        const preview = document.getElementById('file-ip-1-preview');
        const removeBtn = document.getElementById('btn-remove-preview');

        if(input.files && input.files[0]){
            const reader = new FileReader();

            reader.onload = function(e){
                preview.src = e.target.result;
                removeBtn.style.display = 'flex';
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('btn-remove-preview').addEventListener('click', function(){
        document.getElementById('file-ip-1').value = '';
        document.getElementById('file-ip-1-preview').src = '/imgs/no-image.png';
        this.style.display = 'none';
    });
</script>
@endsection