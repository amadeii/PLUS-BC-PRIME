<input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
<div class="col-md-12">
    {!! Form::textarea('texto', 'Descrição do relatório')
    ->attrs(['rows' => '10', 'class' => 'tiny'])
    !!}
</div>
<div class="col-12 mt-3">
    <button class="btn btn-success" type="submit">Salvar</button>
</div>

@section('js')
<script src="/tinymce/tinymce.min.js"></script>
<script>
    $(function(){
        tinymce.init({
            selector: 'textarea.tiny',
            language: 'pt_BR'
        });

        $('form').on('submit', function(e){
            tinymce.triggerSave();

            let texto = $('#inp-texto').val()?.trim() || '';

            if(texto === ''){
                e.preventDefault();
                swal("Atenção", "Informe a descrição do relatório.", "warning");
                return false;
            }
        });

        setTimeout(() => {
            $('.tox-promotion, .tox-statusbar__right-container').addClass('d-none')
        }, 500)
    })
</script>
@endsection
