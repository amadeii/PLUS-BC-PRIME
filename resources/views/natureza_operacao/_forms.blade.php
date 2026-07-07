<div class="row">
    <div class="col-md-4">
        {!!Form::text('descricao', 'Descrição')->required()
        ->attrs(['data-contador' => true, 'maxlength' => 60])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('padrao', 'Padrão', [0 => 'Não', 1 => 'Sim'])
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('sobrescrever_cfop', 'Sobrescrever CFOP', [0 => 'Não', 1 => 'Sim'])
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::select('movimentar_estoque', 'Movimentar Estoque', [1 => 'Sim', 0 => 'Não'])
        ->required()
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <div class="col-md-2">
        {!! Form::select('indPres', 'Indicador de presença', [
        '1' => 'Operação presencial',
        '0' => 'Não se aplica',
        '2' => 'Operação não presencial - Internet',
        '3' => 'Operação não presencial - Teleatendimento',
        '4' => 'NFC-e com entrega a domicílio',
        '5' => 'Operação presencial fora do estabelecimento',
        '9' => 'Operação não presencial - Outros'
        ])
        ->attrs(['class' => 'form-select'])
        !!}
    </div>

    <hr class="mt-2">

    <div class="card">
        <div class="card-header border-0 pb-2">

            <div class="alert alert-info mt-4">
                <i class="ri-information-line"></i>
                Os campos abaixo são opcionais. Se preenchidos, irão sobrescrever os dados do cadastro do produto para gerar o XML.
            </div>

            <hr>
            <h5 class="fw-bold text-primary">Dados Para Emissão</h5>
        </div>

        <div class="card-body" style="margin-top: -30px">
            <div class="row g-2">
                <div class="col-md-6">
                    {!!Form::select('cst_csosn', 'CST/CSOSN', ['' => 'Selecione'] + $listaCTSCSOSN)
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-6">
                    {!!Form::select('cst_pis', 'CST PIS', ['' => 'Selecione'] + App\Models\Produto::listaCST_PIS_COFINS())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-6">
                    {!!Form::select('cst_cofins', 'CST COFINS', ['' => 'Selecione'] + App\Models\Produto::listaCST_PIS_COFINS())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>
                <div class="col-md-6">
                    {!!Form::select('cst_ipi', 'CST IPI', ['' => 'Selecione'] + App\Models\Produto::listaCST_IPI())
                    ->attrs(['class' => 'form-select'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('perc_icms', '% ICMS')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('perc_pis', '% PIS')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('perc_cofins', '% COFINS')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('perc_ipi', '% IPI')
                    ->attrs(['class' => 'percentual'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('cfop_estadual', 'CFOP Estadual')
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('cfop_outro_estado', 'CFOP Inter Estadual')
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>

                <div class="col-md-2">
                    {!!Form::tel('cfop_entrada_estadual', 'CFOP Entrada Estadual')
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::tel('cfop_entrada_outro_estado', 'CFOP Entrada Inter Estadual')
                    ->attrs(['class' => 'cfop'])
                    !!}
                </div>

                @if(isset($temPlanoConta) && $temPlanoConta)
                <div class="col-md-4">
                    {!!Form::select('plano_conta_id', 'Plano de conta')
                    ->attrs(['class' => 'form-select'])
                    ->options(isset($item) && $item->plano_conta_id ? [$item->plano_conta_id => $item->planoConta->descricao] : [])
                    !!}
                </div>
                @endif
            </div>
        </div>
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>
@section('js')
<script type="text/javascript">

    $(document).on("blur", "#inp-cfop_estadual", function () {

        let v = $(this).val().substring(1,4)
        $("#inp-cfop_outro_estado").val('6'+v)
        $("#inp-cfop_entrada_estadual").val('1'+v)
        $("#inp-cfop_entrada_outro_estado").val('2'+v)
    })
</script>
@endsection