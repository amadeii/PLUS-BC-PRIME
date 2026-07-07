<div class="row g-2 tributacao-fiscal">
    <div class="col-md-4">
        {!! Form::select(
        'cst_csosn_entrada',
        'CST/CSOSN Entrada',
        ['' => 'Selecione'] + App\Models\Produto::listaCSTCSOSN()
        )->attrs([
        'class' => 'select2 cst_csosn',
        'data-fiscal-field' => '1'
        ]) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('cfop_entrada', 'CFOP Entrada')->attrs([
        'class' => 'cfop',
        'data-fiscal-field' => '1'
        ]) !!}
    </div>

    <div class="col-md-4">
        {!! Form::select(
        'cst_csosn_saida',
        'CST/CSOSN Saída',
        ['' => 'Selecione'] + App\Models\Produto::listaCSTCSOSN()
        )->attrs([
        'class' => 'select2 cst_csosn',
        'data-fiscal-field' => '1'
        ]) !!}
    </div>

    <div class="col-md-2">
        {!! Form::tel('cfop_saida', 'CFOP Saída')->attrs([
        'class' => 'cfop',
        'data-fiscal-field' => '1'
        ]) !!}
    </div>

    <hr class="mt-4">

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script>
    function hasValue(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    }

    function toggleFieldRequired($field, required) {
        $field.prop('required', required);

        const $wrapper = $field.closest('.col-md-2, .col-md-4, .form-group, .col-12');
        const $label = $wrapper.find('label').first();

        $label.toggleClass('required', required);
    }

    function validarTributacao() {
        const $cfopEntrada = $('input[name="cfop_entrada"]');
        const $cfopSaida = $('input[name="cfop_saida"]');

        const $cstEntrada = $('select[name="cst_csosn_entrada"]');
        const $cstSaida = $('select[name="cst_csosn_saida"]');

        const cfopEntrada = $cfopEntrada.val();
        const cfopSaida = $cfopSaida.val();
        const cstEntrada = $cstEntrada.val();
        const cstSaida = $cstSaida.val();

        const algumCfop = hasValue(cfopEntrada) || hasValue(cfopSaida);
        const algumCst  = hasValue(cstEntrada) || hasValue(cstSaida);

        // Se começou preencher CFOP, obriga os 2
        toggleFieldRequired($cfopEntrada, algumCfop);
        toggleFieldRequired($cfopSaida, algumCfop);

        // Se começou preencher CST/CSOSN, obriga os 2
        toggleFieldRequired($cstEntrada, algumCst);
        toggleFieldRequired($cstSaida, algumCst);
    }

    $(document).ready(function () {
        validarTributacao();

        $(document).on('input change', 'input[name="cfop_entrada"], input[name="cfop_saida"], select[name="cst_csosn_entrada"], select[name="cst_csosn_saida"]', function () {
            validarTributacao();
        });

        $('form').on('submit', function (e) {
            validarTributacao();

            const cfopEntrada = $('input[name="cfop_entrada"]').val();
            const cfopSaida = $('input[name="cfop_saida"]').val();
            const cstEntrada = $('select[name="cst_csosn_entrada"]').val();
            const cstSaida = $('select[name="cst_csosn_saida"]').val();

            const grupoCfopCompleto = hasValue(cfopEntrada) && hasValue(cfopSaida);
            const grupoCstCompleto = hasValue(cstEntrada) && hasValue(cstSaida);

            if (!grupoCfopCompleto && !grupoCstCompleto) {
                e.preventDefault();

                swal("Atenção", "Preencha os dois CFOPs ou os dois CST/CSOSN.", "warning");
                return false;
            }
        });
    });
</script>
@endsection