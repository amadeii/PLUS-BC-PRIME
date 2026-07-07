<div class="row g-2">
    <div class="col-md-3">
        {!!Form::text('nome', 'Nome da regra')->required()!!}
    </div>

    <div class="col-md-3">
        {!!Form::select('gatilho', 'Gatilho', [
        '' => 'Selecione',
        'ao_gerar' => 'Ao gerar cobrança',
        'antes_vencimento' => 'Antes do vencimento',
        'no_vencimento' => 'No vencimento',
        'apos_vencimento' => 'Após vencimento'
        ])->attrs(['class' => 'form-select'])->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('dias', 'Dias')->attrs(['data-mask' => '000'])->required()!!}
    </div>

    <div class="col-md-4">
        {!!Form::text('assunto_email', 'Assunto do e-mail')!!}
    </div>

    <div class="col-md-12 mt-3">
        <label class="form-label">Variáveis disponíveis</label>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{nome}}">Nome</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{documento}}">Documento</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{valor}}">Valor</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{vencimento}}">Vencimento</button>
            <!-- <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{link_pagamento}}">Link pagamento</button> -->
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{empresa}}">Empresa</button>
        </div>
        <small class="text-muted">Clique em uma variável para inserir no editor selecionado.</small>
    </div>

    <div class="col-md-12">
        {!!Form::textarea('mensagem_email', 'Mensagem do e-mail')->attrs(['rows' => '5', 'class' => 'tiny'])!!}
    </div>

    <div class="col-md-12">
        {!!Form::textarea('mensagem_whatsapp', 'Mensagem do WhatsApp')->attrs(['rows' => '5', 'class' => 'tiny'])!!}
    </div>

    <div class="col-md-2 mt-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="email_ativo" value="1" id="email_ativo" @isset($item) @if($item->email_ativo) checked @endif @else checked @endif>
            <label class="form-check-label" for="email_ativo">Enviar e-mail</label>
        </div>
    </div>

    <div class="col-md-2 mt-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="whatsapp_ativo" value="1" id="whatsapp_ativo" @isset($item) @if($item->whatsapp_ativo) checked @endif @endif>
            <label class="form-check-label" for="whatsapp_ativo">Enviar WhatsApp</label>
        </div>
    </div>

    <div class="col-md-2 mt-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @isset($item) @if($item->ativo) checked @endif @else checked @endif>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script src="/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    let editorSelecionado = null

    $(function(){
        tinymce.init({
            selector: 'textarea.tiny',
            language: 'pt_BR',
            height: 300,
            setup: function(editor){
                editor.on('focus click keyup', function(){
                    editorSelecionado = editor
                })
            }
        })

        setTimeout(() => {
            $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
        }, 500)

        $('.btn-var').on('click', function(){
            let variavel = $(this).data('var')

            if(editorSelecionado){
                editorSelecionado.execCommand('mceInsertContent', false, variavel)
                return
            }

            let editorEmail = tinymce.get('mensagem_email')
            let editorWhatsapp = tinymce.get('mensagem_whatsapp')

            if(editorEmail){
                editorEmail.execCommand('mceInsertContent', false, variavel)
            }else if(editorWhatsapp){
                editorWhatsapp.execCommand('mceInsertContent', false, variavel)
            }
        })
    })
</script>
@endsection