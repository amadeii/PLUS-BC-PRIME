@extends('layouts.app', ['title' => 'Editor de Etiqueta'])

@section('css')
<style>
    .toolbox{ background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:18px; box-shadow:0 8px 25px rgba(0,0,0,.06); }
    .toolbox h5{ font-size:16px; font-weight:700; margin-bottom:15px; color:#374151; }
    .field-item{ background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:12px 14px; margin-bottom:10px; cursor:grab; font-size:14px; font-weight:600; transition:.2s; }
    .field-item:hover{ background:#eef2ff; border-color:#6366f1; transform:translateY(-2px); }
    .etiqueta-wrap{ background:#f8fafc; padding:25px; border-radius:20px; display:inline-block; }
    .etiqueta-area{ background:#fff; border:2px solid #d1d5db; border-radius:12px; position:relative; overflow:hidden; box-shadow:0 8px 20px rgba(0,0,0,.08); background-image:linear-gradient(to right,#f1f5f9 1px,transparent 1px),linear-gradient(to bottom,#f1f5f9 1px,transparent 1px); background-size:10px 10px; }
    .etiqueta-campo{ position:absolute; cursor:move; padding:0; margin:0; border-radius:0; background:transparent; border:1px dashed rgba(99,102,241,.35); font-size:{{ (int)($item->fonte_padrao ?? 13) }}px; font-weight:700; color:#000; user-select:none; line-height:.9; min-width:auto; min-height:auto; box-shadow:none; white-space:nowrap; }
    .etiqueta-campo:hover{ border-color:#6366f1; box-shadow:none; }
    .etiqueta-campo .remove-campo{ position:absolute; top:-8px; right:-8px; width:18px; height:18px; border-radius:50%; background:#ef4444; color:#fff; border:0; font-size:11px; line-height:18px; text-align:center; cursor:pointer; display:none; padding:0; }
    .etiqueta-campo:hover .remove-campo{ display:block; }
    #btnSalvar{ height:46px; border-radius:12px; font-weight:700; font-size:14px; }
</style>
@endsection

@section('content')

<div class="card mt-1">
    <div class="card-header">
        <h4>Editor Visual - {{ $item->nome }}</h4>
        <div style="text-align:right;margin-top:-35px;">
            <a href="{{ route('etiqueta-modelos.index') }}" class="btn btn-danger btn-sm"><i class="ri-arrow-left-double-fill"></i> Voltar</a>
        </div>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-3">
                <div class="toolbox">
                    <h5>Campos</h5>

                    <div class="field-item" data-tipo="produto_nome">Nome Produto</div>
                    <div class="field-item" data-tipo="produto_valor">Valor</div>
                    <div class="field-item" data-tipo="produto_codigo">Código</div>
                    <div class="field-item" data-tipo="produto_referencia">Referência</div>
                    <div class="field-item" data-tipo="codigo_barras">Código Barras</div>
                    <div class="field-item" data-tipo="empresa_nome">Empresa</div>

                    <hr>

                    <button type="button" class="btn btn-success w-100" id="btnSalvar">Salvar Layout</button>
                </div>
            </div>

            <div class="col-md-9">
                <div class="mb-3">
                    <h5 class="mb-1">Etiqueta {{ $item->largura }} x {{ $item->altura }} mm</h5>
                    <small class="text-muted">Arraste os campos para montar o layout.</small>
                </div>

                <div class="etiqueta-wrap">
                    <div id="etiqueta" class="etiqueta-area" style="width:{{ $item->largura * 4 }}px;height:{{ $item->altura * 4 }}px;"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<input type="hidden" id="layoutJson">

@endsection

@section('js')
<script>
    let layoutSalvo = @json(json_decode($item->layout_json ?? '[]', true));
    let fontePadrao = {{ (int)($item->fonte_padrao ?? 13) }};
    let campoArrastado = null;
    let movendo = null;
    let diffX = 0;
    let diffY = 0;

    $(document).ready(function(){
        $('.field-item').attr('draggable', true);
        carregarLayoutSalvo();
    });

    function carregarLayoutSalvo(){
        if(!layoutSalvo || layoutSalvo.length === 0) return;

        layoutSalvo.forEach(function(campo){
            adicionarCampoNaEtiqueta(
                campo.tipo,
                campo.texto,
                parseInt(campo.x || 0),
                parseInt(campo.y || 0),
                fontePadrao,
                campo.bold ? true : false
                );
        });

        atualizarJson();
    }

    function adicionarCampoNaEtiqueta(tipo, texto, x, y, fontSize = fontePadrao, bold = true){
        let html = `
        <div class="etiqueta-campo" data-tipo="${tipo}" style="left:${x}px;top:${y}px;font-size:${fontSize}px;font-weight:${bold ? '700' : '400'};line-height:.9;">
        <span class="campo-texto">${textoPreview(tipo, texto)}</span>
        <button type="button" class="remove-campo">×</button>
        </div>
        `;

        $('#etiqueta').append(html);
    }

    $(document).on('dragstart', '.field-item', function(){
        campoArrastado = {
            tipo: $(this).data('tipo'),
            texto: $(this).text().trim()
        };
    });

    $('#etiqueta').on('dragover', function(e){
        e.preventDefault();
    });

    $('#etiqueta').on('drop', function(e){
        e.preventDefault();

        if(!campoArrastado) return;

        let offset = $('#etiqueta').offset();
        let x = e.originalEvent.pageX - offset.left;
        let y = e.originalEvent.pageY - offset.top;

        adicionarCampoNaEtiqueta(campoArrastado.tipo, campoArrastado.texto, x, y, fontePadrao, true);

        campoArrastado = null;
        atualizarJson();
    });

    $(document).on('mousedown', '.etiqueta-campo', function(e){
        movendo = $(this);

        let pos = movendo.position();
        let etiquetaOffset = $('#etiqueta').offset();

        diffX = e.pageX - etiquetaOffset.left - pos.left;
        diffY = e.pageY - etiquetaOffset.top - pos.top;

        e.preventDefault();
    });

    $(document).on('mousemove', function(e){
        if(!movendo) return;

        let etiqueta = $('#etiqueta');
        let etiquetaOffset = etiqueta.offset();

        let x = e.pageX - etiquetaOffset.left - diffX;
        let y = e.pageY - etiquetaOffset.top - diffY;

        let maxX = etiqueta.width() - movendo.outerWidth();
        let maxY = etiqueta.height() - movendo.outerHeight();

        x = Math.max(0, Math.min(x, maxX));
        y = Math.max(0, Math.min(y, maxY));

        movendo.css({ left:x + 'px', top:y + 'px' });

        atualizarJson();
    });

    $(document).on('mouseup', function(){
        movendo = null;
    });

    $(document).on('dblclick', '.etiqueta-campo', function(){
        $(this).remove();
        atualizarJson();
    });

    $(document).on('click', '.remove-campo', function(e){
        e.preventDefault();
        e.stopPropagation();

        $(this).closest('.etiqueta-campo').remove();
        atualizarJson();
    });

    function atualizarJson(){
        let layout = [];

        $('#etiqueta .etiqueta-campo').each(function(){
            layout.push({
                tipo: $(this).data('tipo'),
                texto: $(this).find('.campo-texto').text().trim(),
                x: parseInt($(this).position().left),
                y: parseInt($(this).position().top),
                fontSize: parseInt($(this).css('font-size')) || fontePadrao,
                bold: true
            });
        });

        $('#layoutJson').val(JSON.stringify(layout));
    }

    $('#btnSalvar').on('click', function(){
        atualizarJson();

        $.ajax({
            url: "{{ route('etiqueta-modelos.salvar-layout', $item->id) }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                layout_json: $('#layoutJson').val()
            },
            success: function(){
                swal("Sucesso", "Layout salvo com sucesso!", "success");
            },
            error: function(xhr){
                console.log(xhr.responseText);

                let msg = 'Não foi possível salvar o layout';

                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }

                swal("Erro", msg, "error");
            }
        });
    });

    function textoPreview(tipo, texto){
        switch(tipo){
            case 'produto_nome': return 'Produto de teste';
            case 'produto_valor': return 'R$ 20,00';
            case 'produto_codigo': return '2';
            case 'produto_referencia': return 'REF001';

            case 'codigo_barras':
            return `
            <div style="width:{{ ($item->largura_codigo_barras ?? 38) * 4 }}px;text-align:center;">
            <img src="https://barcode.tec-it.com/barcode.ashx?data=9788542212341&code=Code128&dpi=96" style="width:{{ ($item->largura_codigo_barras ?? 38) * 4 }}px;height:{{ ($item->altura_codigo_barras ?? 10) * 4 }}px;display:block;margin:0 auto;">
            <div style="font-size:8px;line-height:1;margin-top:2px;font-weight:400;">9788542212341</div>
            </div>
            `;

            case 'empresa_nome': return '{{$item->empresa->nome}}';
            default: return texto;
        }
    }
</script>
@endsection