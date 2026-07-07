<div class="row g-2">

    <div class="col-md-4">
        {!!Form::text('nome', 'Nome do modelo')->required()!!}
    </div>

    <div class="col-md-2 mt-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @isset($item) @if($item->ativo) checked @endif @else checked @endif>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <label class="form-label">Variáveis disponíveis</label>

        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{cliente_nome}}">Cliente</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{cliente_documento}}">Documento</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{cliente_endereco}}">Endereço</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{empresa_nome}}">Empresa</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{empresa_documento}}">CNPJ Empresa</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{servicos}}">Serviços</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{valor_total}}">Valor total</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{dia_vencimento}}">Dia vencimento</button>
            <button type="button" class="btn btn-sm btn-outline-primary btn-var" data-var="@{{data_inicio}}">Data início</button>
        </div>

        <small class="text-muted">Clique em uma variável para inserir no conteúdo do contrato.</small>
    </div>

    <div class="col-md-12">
        {!!Form::textarea('conteudo', 'Conteúdo do contrato')
        ->attrs([
            'rows' => '18',
            'class' => 'tiny'
        ])
        !!}
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
            height: 500,
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

            let editorConteudo = tinymce.get('conteudo')

            if(editorConteudo){
                editorConteudo.execCommand('mceInsertContent', false, variavel)
            }
        })
    })
</script>
@endsection