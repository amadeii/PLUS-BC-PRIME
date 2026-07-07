@section('css')
<style>
    .boleto-card{border:1px solid #eef0f6;border-radius:18px;box-shadow:0 8px 22px rgba(15,23,42,.05);overflow:hidden;margin-bottom:14px;}
    .boleto-card .card-header{background:#fbfcff;border-bottom:1px solid #eef0f6;padding:14px 16px;}
    .boleto-title{font-size:15px;font-weight:800;color:#29324a;margin:0;}
    .boleto-value{font-size:14px;font-weight:700;color:#6b7280;margin:0;}
    .boleto-value strong{color:#e11d48;font-size:17px;}
    .boleto-footer{display:flex;justify-content:flex-end;border-top:1px solid #eef0f6;padding-top:16px;margin-top:10px;}
</style>
@endsection

<div class="row g-2">
    <div class="col-md-3">
        {!!Form::select('conta_boleto', 'Conta', ['' => 'Selecione'] + $contasBoleto->pluck('info', 'id')->all())->required()->attrs(['class' => 'form-select'])->value($contaPadrao != null ? $contaPadrao->id : null)!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('carteira', 'Carteira')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('convenio', 'Convênio')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::select('tipo', 'Tipo', ['Cnab400' => 'Cnab400', 'Cnab240' => 'Cnab240'])->required()->attrs(['class' => 'form-select'])!!}
    </div>

    <div class="col-md-2">
        {!!Form::select('usar_logo', 'Usar logo', [0 => 'Não', 1 => 'Sim'])->required()->attrs(['class' => 'form-select'])!!}
    </div>

    <div class="col-12"><hr></div>

    @php
    $listaContas = sizeof($contas) > 0 ? $contas : collect([$conta]);
    @endphp

    @foreach($listaContas as $conta)
    <div class="col-12">
        <div class="card boleto-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <small class="text-muted d-block mb-1">Cliente</small>
                        <h5 class="boleto-title mb-0">
                            {{ $conta->cliente->info ?? 'Sem cliente' }}
                        </h5>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block mb-1">Valor do boleto</small>
                        <div class="boleto-value mt-2">
                            <strong>R$ {{ __moeda($conta->valor_integral) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" name="conta_id[]" value="{{ $conta->id }}">

                <div class="row g-2">
                    <div class="col-md-2">
                        {!!Form::tel('numero[]', 'Número boleto')->required()->attrs(['class' => 'numero'])!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::tel('numero_documento[]', 'Número documento')->required()->value($conta->nfe ? $conta->nfe->numero_sequencial : ($conta->nfce ? $conta->nfce->numero_sequencial : ''))!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::tel('juros[]', 'Juros')->required()->attrs(['class' => 'percentual juros'])!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::tel('juros_apos[]', 'Juros após')->required()->attrs(['class' => 'juros_apos', 'data-mask' => '000'])!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::tel('multa[]', 'Multa')->required()->attrs(['class' => 'percentual multa'])!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::tel('valor[]', 'Valor')->required()->value(__moeda($conta->valor_integral))->attrs(['class' => 'moeda'])!!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::date('vencimento[]', 'Vencimento')->required()->value($conta->data_vencimento)!!}
                    </div>

                    <div class="col-md-5">
                        {!!Form::tel('instrucoes[]', 'Instruções')->attrs(['class' => 'instrucoes'])!!}
                    </div>

                    <div class="col-md-2 div-sicredi">
                        {!!Form::text('posto', 'Posto')->required()->attrs(['class' => 'posto'])!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <div class="col-12">
        <div class="boleto-footer">
            <button type="submit" class="btn btn-success px-5" id="btn-store">
                <i class="ri-check-line"></i> Salvar
            </button>
        </div>
    </div>
</div>
@section('js')
<script type="text/javascript">
    $(function(){
        setTimeout(() => {
            $('#inp-conta_boleto').change()
        })
    })

    $('body').on('change', '#inp-conta_boleto', function () {
        let conta_boleto = $(this).val()
        if(conta_boleto){
            $.get(path_url + 'api/conta-boleto', {conta_boleto_id: conta_boleto})
            .done((res) => {

                $('#inp-carteira').val(res.carteira)
                $('#inp-convenio').val(res.convenio)
                $('#inp-tipo').val(res.tipo).change()

                $('.juros').val(convertFloatToMoeda(res.juros))
                $('.multa').val(convertFloatToMoeda(res.multa))
                $('.juros_apos').val(res.juros_apos)
                $('.numero').val(res.ultimo_numero+1)
                $('.instrucoes').val(res.instrucoes)
                $('.posto').val(res.posto)

                if(res.banco == 'Sicredi'){
                    $('.div-sicredi').removeClass('d-none')
                    $('.posto').attr('required', 1)
                }else{
                    $('.div-sicredi').addClass('d-none')
                    $('.posto').removeAttr('required')
                }

            })
            .fail((err) => {
                console.log(err)
            })
        }
    })
</script>
@endsection