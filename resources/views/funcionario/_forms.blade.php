<div class="row g-2">
    <div class="col-md-5">
        {!!Form::text('nome', 'Nome')
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone'])
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('rua', 'Rua')
        !!}
    </div>
    <div class="col-md-1">
        {!!Form::tel('numero', 'Número')
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('bairro', 'Bairro')
        !!}
    </div>
    <div class="col-md-3">
        @isset($item)
        {!!Form::select('cidade_id', 'Cidade')
        ->attrs(['class' => 'select2'])->options($item != null ? [$item->cidade_id => $item->cidade->info] : [])
        ->required()
        !!}
        @else
        {!!Form::select('cidade_id', 'Cidade')
        ->attrs(['class' => 'select2'])
        ->required()
        !!}
        @endisset
    </div>
    <div class="col-md-2">
        {!!Form::select('usuario_id', 'Usuário', ['' => 'Selecione'] + $usuario->pluck('name', 'id')->all())
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::tel('comissao', '%Comissão')->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moeda($item->comissao) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('salario', 'Salário')->attrs(['class' => 'moeda'])
        ->value(isset($item) ? __moedaInput($item->salario) : '')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('codigo', 'Código')
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Status', [1 => 'Ativo', 0 => 'Desativado'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('permite_alterar_valor_app', 'Permite alterar valor no App', [1 => 'Sim', 0 => 'Não'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('bater_ponto', 'Controle de ponto', [0 => 'Não', 1 => 'Sim'])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <!-- <div class="col-md-2 campos-ponto d-none">
        {!!Form::tel('carga_horaria_mensal', 'Carga horária mensal')
        ->attrs(['class' => 'form-control', 'data-mask' => '000'])
        ->id('carga_horaria_mensal')
        ->value(isset($item) ? $item->carga_horaria_mensal : 220)
        !!}
    </div>

    <div class="col-md-2 campos-ponto d-none">
        {!!Form::tel('valor_hora_extra', 'Valor hora extra')
        ->attrs(['class' => 'moeda'])
        ->id('valor_hora_extra')
        ->value(isset($item) ? __moeda($item->valor_hora_extra) : '')
        !!}
    </div> -->

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script>
    function controlaCamposPonto(){
        let baterPonto = $('#inp-bater_ponto').val();
        if(baterPonto == '1'){
            $('.campos-ponto').removeClass('d-none');
            $('#inp-salario').attr('required', true);
            $('#carga_horaria_mensal').attr('required', true);
            $('#valor_hora_extra').attr('required', true);
        }else{
            $('.campos-ponto').addClass('d-none');
            $('#inp-salario').removeAttr('required');
            $('#carga_horaria_mensal').removeAttr('required');
            $('#valor_hora_extra').removeAttr('required');
        }
        atualizarRequiredLabels();
    }

    $(document).ready(function(){
        controlaCamposPonto();

        $('#inp-bater_ponto').change(function(){
            controlaCamposPonto();
        });
    });

    function atualizarRequiredLabels(){
        $("label").removeClass("required");

        $("input[required], select[required], textarea[required]").each(function () {
            $(this)
            .closest(".form-group, .mb-3, .col, .col-12, .col-md-6")
            .find("label")
            .first()
            .addClass("required");
        });
    }
</script>
@endsection