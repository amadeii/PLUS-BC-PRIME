<div class="row g-2">
    <div class="col-md-4">
        {!!Form::select('cliente_id', 'Cliente', ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all())->attrs(['class' => 'select2'])->required()!!}
    </div>

    <div class="col-md-4">
        {!!Form::text('descricao', 'Descrição')->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::select('forma_pagamento', 'Pagamento', [
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'cartao' => 'Cartão',
            'dinheiro' => 'Dinheiro'
        ])->required()->attrs(['class' => 'form-select'])!!}
    </div>

    <div class="col-md-2">
        {!!Form::select('periodicidade', 'Periodicidade', [
            'mensal' => 'Mensal',
            'bimestral' => 'Bimestral',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual'
        ])->required()->attrs(['class' => 'form-select'])!!}
    </div>

    <div class="col-md-2">
        {!!Form::tel('dia_vencimento', 'Dia vencimento')->attrs(['maxlength' => 2])->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_inicio', 'Data início')
        ->value(isset($item) ? \Carbon\Carbon::parse($item->data_inicio)->format('Y-m-d') : null)
        ->required()!!}
    </div>

    <div class="col-md-2">
        {!!Form::date('data_fim', 'Data fim')!!}
    </div>

    <div class="col-md-2">
        {!!Form::select('status', 'Status', [
            'ativa' => 'Ativa',
            'pausada' => 'Pausada',
            'cancelada' => 'Cancelada',
            'finalizada' => 'Finalizada'
        ])->required()->attrs(['class' => 'form-select'])!!}
    </div>

    <div class="col-md-12 mt-3">
        <div class="card border">
            <div class="card-header">
                <h5 class="mb-0">Serviços da recorrência</h5>
            </div>

            <div class="card-body">
                <div id="servicos-area">
                    @if(isset($item) && sizeof($item->servicos) > 0)
                    @foreach($item->servicos as $s)
                    <div class="row g-2 linha-servico mb-2">
                        <div class="col-md-5">
                            <select name="servico_id[]" class="form-control select2 servico-select">
                                <option value="">Selecione</option>
                                @foreach($servicos as $servico)
                                <option value="{{ $servico->id }}" data-valor="{{ $servico->valor }}" @if($s->servico_id == $servico->id) selected @endif>
                                    {{ $servico->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="tel" name="quantidade[]" class="form-control quantidade" value="{{ number_format($s->quantidade, 0) }}">
                        </div>

                        <div class="col-md-2">
                            <input type="tel" name="valor_unitario[]" class="form-control valor-unitario moeda" value="{{ __moeda($s->valor_unitario) }}">
                        </div>

                        <div class="col-md-2">
                            <input type="tel" name="subtotal[]" class="form-control subtotal moeda" value="{{ __moeda($s->subtotal) }}" readonly>
                        </div>

                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-remove-servico w-100">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="row g-2 linha-servico mb-2">
                        <div class="col-md-5">
                            <select name="servico_id[]" class="form-control select2 servico-select">
                                <option value="">Selecione</option>
                                @foreach($servicos as $servico)
                                <option value="{{ $servico->id }}" data-valor="{{ $servico->valor }}">
                                    {{ $servico->nome }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <input type="tel" name="quantidade[]" class="form-control quantidade" value="1">
                        </div>

                        <div class="col-md-2">
                            <input type="tel" name="valor_unitario[]" class="form-control valor-unitario moeda" value="0,00">
                        </div>

                        <div class="col-md-2">
                            <input type="tel" name="subtotal[]" class="form-control subtotal moeda" value="0,00" readonly>
                        </div>

                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-remove-servico w-100">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <button type="button" class="btn btn-dark btn-sm mt-2" id="btn-add-servico">
                    <i class="ri-add-circle-line"></i> Adicionar serviço
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <div class="row g-2">
            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input type="checkbox" name="gerar_automatico" class="form-check-input" value="1" id="gerar_automatico" @if(!isset($item) || $item->gerar_automatico) checked @endif>
                    <label class="form-check-label" for="gerar_automatico">Gerar automático</label>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input type="checkbox" name="enviar_whatsapp" class="form-check-input" value="1" id="enviar_whatsapp" @if(isset($item) && $item->enviar_whatsapp) checked @endif>
                    <label class="form-check-label" for="enviar_whatsapp">Enviar WhatsApp</label>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input type="checkbox" name="enviar_email" class="form-check-input" value="1" id="enviar_email" @if(isset($item) && $item->enviar_email) checked @endif>
                    <label class="form-check-label" for="enviar_email">Enviar e-mail</label>
                </div>
            </div>

            <!-- <div class="col-md-2">
                <div class="form-check mt-4">
                    <input type="checkbox" name="gera_nfse" class="form-check-input" value="1" id="gera_nfse" @if(isset($item) && $item->gera_nfse) checked @endif>
                    <label class="form-check-label" for="gera_nfse">Gerar NFSe</label>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input type="checkbox" name="gera_nfe" class="form-check-input" value="1" id="gera_nfe" @if(isset($item) && $item->gera_nfe) checked @endif>
                    <label class="form-check-label" for="gera_nfe">Gerar NFe</label>
                </div>
            </div> -->
        </div>
    </div>

    <div class="col-md-12 mt-3">
        {!!Form::textarea('observacao', 'Observação')->attrs(['rows' => '3'])!!}
    </div>

    <hr class="mt-4">

    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script>
    function moedaParaFloat(valor){
        if(!valor) return 0;
        valor = valor.toString().replace(/\./g, '').replace(',', '.');
        return parseFloat(valor) || 0;
    }

    function floatParaMoeda(valor){
        return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function calcularLinha(linha){
        let qtd = moedaParaFloat(linha.find('.quantidade').val());
        let valor = moedaParaFloat(linha.find('.valor-unitario').val());
        linha.find('.subtotal').val(floatParaMoeda(qtd * valor));
        calcularTotal();
    }

    function calcularTotal(){
        let total = 0;
        $('.subtotal').each(function(){
            total += moedaParaFloat($(this).val());
        });
        $('input[name=valor]').val(floatParaMoeda(total));
    }

    $(document).on('change', '.servico-select', function(){
        let linha = $(this).closest('.linha-servico');
        let valor = $(this).find(':selected').data('valor') || 0;
        linha.find('.valor-unitario').val(floatParaMoeda(parseFloat(valor)));
        calcularLinha(linha);
    });

    $(document).on('keyup change', '.quantidade, .valor-unitario', function(){
        calcularLinha($(this).closest('.linha-servico'));
    });

    $('#btn-add-servico').on('click', function(){
        let linha = $('.linha-servico:first').clone();

        linha.find('select').val('');
        linha.find('.quantidade').val('1,00');
        linha.find('.valor-unitario').val('0,00');
        linha.find('.subtotal').val('0,00');

        linha.find('.select2-container').remove();
        linha.find('select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id tabindex aria-hidden');

        $('#servicos-area').append(linha);
        linha.find('.select2').select2();
    });

    $(document).on('click', '.btn-remove-servico', function(){
        if($('.linha-servico').length > 1){
            $(this).closest('.linha-servico').remove();
            calcularTotal();
        }
    });
</script>
@endsection