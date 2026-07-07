<div class="row">

    <div class="alert alert-info">
        <h5><i class="ri-information-line"></i> Selecione o funcionário para buscar os eventos de pagamento!</h5>        
    </div>

    <div class="row align-items-end">

        <div class="col-md-3 mt-1">
            <label class="col-form-label">Funcionário</label>
            <div class="input-group">
                @isset($item)
                <h4>{{$item->nome}}</h4>
                @else
                <select class="select2 form-control" name="funcionario_id" id="funcionario_id">
                    <option value="">Selecione o funcionário</option>
                    @foreach($funcionarios as $f)
                    <option value="{{$f->id}}">
                        {{ $f->nome }} ({{ $f->cpf_cnpj }})
                    </option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>

        <div class="col-md-2">
            <label>Mês</label>
            <select class="form-select" name="mes" id="mes">
                @foreach(\App\Models\ApuracaoMensal::mesesApuracao() as $key => $m)
                <option value="{{$m}}" @if($key==$mesAtual) selected @endif>
                    {{ $m }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label>Ano</label>
            <select class="form-select" name="ano" id="ano">
                @foreach(\App\Models\ApuracaoMensal::anosApuracao() as $a)
                <option value="{{$a}}">{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label>&nbsp;</label>
            <button type="button" id="btn-buscar-apuracao" class="btn btn-primary w-100">
                Buscar
            </button>
        </div>

    </div>

    <div class="row mt-4">
        <div class="table-responsive">
            <table class="table">
                <thead class="table-dark">
                    <tr>
                        <th></th>
                        <th>Evento</th>
                        <th>Condição</th>
                        <th>Valor</th>
                        <th>Método</th>
                    </tr>
                </thead>
                <tbody id="body" class="datatable-body">
                    <tr>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">

        <div class="col-md-2 col-12">
            {!!Form::select('tipo_pagamento', 'Tipo de Pagamento', ['' => 'Selecione'] + App\Models\ApuracaoMensal::tiposPagamento())->attrs(['class' => 'form-select'])
            ->required()
            !!}
        </div>
        <div class="col-md-2 col-12">
            {!!Form::tel('valor_total', 'Valor total')->attrs(['class' => 'moeda'])->required()
            !!}
        </div>

        <div class="col-md-6 col-12">
            {!!Form::text('observacao', 'Observação')
            !!}
        </div>
    </div>

    <hr class="mt-4">
    <div class="col-12 mt-3 float-end">
        <button disabled type="submit" class="btn btn-success px-5">Salvar</button>
    </div>
</div>


@section('js')
<script type="text/javascript">
    $(function() {
        $('#funcionario_id').val('').change()
        $('#inp-valor_total').val('')
    })
    $('#btn-buscar-apuracao').click(function () {

        let funcionario = $('#funcionario_id').val();
        let mes = $('#mes').val();
        let ano = $('#ano').val();

        if (!funcionario) {
            swal("Atenção", "Selecione um funcionário!", "warning");
            return;
        }

        $('.datatable-body').html('');

        $.get(path_url + 'apuracao-mensal/get-eventos/' + funcionario, {
            mes: mes,
            ano: ano
        })
        .done((html) => {

            if (html == "") {
                swal("Erro", "Funcionário sem eventos cadastrados!", "error");
            } else {
                $('.datatable-body').html(html);

                setTimeout(() => {
                    calcTotal();
                }, 200);
            }

        }).fail((err) => {
            console.log(err);
            swal("Erro", "Erro ao buscar dados!", "error");
        });

    });

    function calcTotal() {
        let total = 0;

        $('.dynamic-form').each(function() {
            let row = $(this);

            let input = row.find('input.value');
            let value = input.val();
            let condicao = row.find('.condicao_chave').val();

            if (!value) {
                return;
            }

            value = convertMoedaToFloat(value);

            let ehHoraExtra = input.data('eh-hora-extra') == 1;
            let horasExtras = parseFloat(input.data('horas-extras')) || 0;

            let ehDescontoFalta = input.data('eh-desconto-falta') == 1;
            let horasFaltas = parseFloat(input.data('horas-faltas')) || 0;

            let valorFinalEvento = value;

            if (ehHoraExtra) {
                valorFinalEvento = value * horasExtras;

                row.find('.resumo-hora-extra').html(
                    'Total: R$ ' + convertFloatToMoeda(valorFinalEvento)
                    );

                row.find('.quantidade-referencia').val(horasExtras);
            }

            if (ehDescontoFalta) {
                valorFinalEvento = value * horasFaltas;

                row.find('.resumo-falta').html(
                    'Total desconto: R$ ' + convertFloatToMoeda(valorFinalEvento)
                    );

                row.find('.quantidade-referencia').val(horasFaltas);
            }

            row.find('.valor-total-evento').val(convertFloatToMoeda(valorFinalEvento));

            if (condicao == "soma") {
                total += valorFinalEvento;
            } else {
                total -= valorFinalEvento;
            }
        });

        $('#inp-valor_total').val(convertFloatToMoeda(total));
        $('.value').addClass('moeda');

        if (total > 0) {
            $('.btn-success').removeAttr('disabled');
        } else {
            $('.btn-success').attr('disabled', true);
        }
    }

    $(".datatable-body").on('click', '.btn-delete-row', function () {
        $(this).closest('tr').remove();
        swal("Sucesso", "Evento removido!", "success")
        calcTotal()
    });


    $(document).on("blur keyup change", ".value", function () {
        calcTotal();
    });

    $('form').on('submit', function () {
        calcTotal();
    });
</script>
@endsection