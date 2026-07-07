let pagamentosParciais = [];

$(function(){
    $('#modal-finalizar-parcial').on('shown.bs.modal', function(){
        setTimeout(() => {
            $('#inp-valor-parcial').focus();
            $('.tipo-parcial-card').removeClass('active')
            $('input[name="tipo_parcial"]').prop('checked', false)
        }, 250);
    });

    $('#modal-finalizar-parcial').on('hidden.bs.modal', function(){
        limparFinalizacaoParcial();
    });

    $(document).on('change', 'input[name="tipo_parcial"]', function(){
        alterarTipoFinalizacaoParcial();
    });

    $(document).on('change', '.check-item-parcial', function(){
        calcularTotalItensParcial();
    });

    $('#inp-valor-parcial').on('keyup blur', function(){
        if(tipoFinalizacaoParcial() == 'valor'){
            pagamentosParciais = [];
            atualizarResumoParcial();
            validarValorParcial();
        }
    });

    $('#btn-add-pagamento-parcial').on('click', function(){
        adicionarPagamentoParcial();
    });

    $('#inp-tipo-pagamento-parcial').on('change', function(){
        preencherValorFaltantePagamentoParcial();
    });

    $('#form-finalizar-parcial').on('submit', function(e){
        e.preventDefault();

        if(!validarFinalizacaoParcial()){
            return;
        }

        salvarFinalizacaoParcial();
    });

    atualizarResumoParcial();
});

function tipoFinalizacaoParcial(){
    return $('input[name="tipo_parcial"]:checked').val() || 'valor';
}

function alterarTipoFinalizacaoParcial(){
    let tipo = tipoFinalizacaoParcial();

    $('.tipo-parcial-card').removeClass('active');
    $('input[name="tipo_parcial"]:checked').closest('.tipo-parcial-card').addClass('active');

    pagamentosParciais = [];
    $('#inp-tipo-pagamento-parcial').val('');
    $('#inp-valor-pagamento-parcial').val('');

    if(tipo == 'itens'){
        $('#area-itens-parcial').removeClass('d-none');
        $('#inp-valor-parcial').prop('readonly', true).val('0,00');
        calcularTotalItensParcial();
    }else{
        $('#area-itens-parcial').addClass('d-none');
        $('.check-item-parcial').prop('checked', false);
        $('#inp-valor-parcial').prop('readonly', false).val('');
        bloquearPagamentosParcial('Informe o valor parcial primeiro.');
    }

    atualizarResumoParcial();
}

function calcularTotalItensParcial(){
    let total = 0;

    $('.check-item-parcial:checked').each(function(){
        total += parseFloat($(this).data('total')) || 0;
    });

    $('#inp-valor-parcial').val(convertFloatToMoeda(total));

    pagamentosParciais = [];
    $('#inp-tipo-pagamento-parcial').val('');
    $('#inp-valor-pagamento-parcial').val('');

    atualizarResumoParcial();
    validarValorParcial();
}

function preencherValorFaltantePagamentoParcial(){
    if(!validarValorParcial()){
        $('#inp-tipo-pagamento-parcial').val('');
        return;
    }

    let valorParcial = convertMoedaToFloat($('#inp-valor-parcial').val());
    let totalPago = pagamentosParciais.reduce((total, item) => total + item.valor, 0);
    let falta = valorParcial - totalPago;

    if(falta > 0){
        $('#inp-valor-pagamento-parcial').val(convertFloatToMoeda(falta));
    }else{
        $('#inp-valor-pagamento-parcial').val('');
    }
}

function totalPedidoParcial(){
    return parseFloat($('#pedido-total-parcial').val()) || 0;
}

function validarValorParcial(){
    let valorParcial = convertMoedaToFloat($('#inp-valor-parcial').val());
    let saldoRestante = totalPedidoParcial();

    if(tipoFinalizacaoParcial() == 'itens' && $('.check-item-parcial:checked').length == 0){
        bloquearPagamentosParcial('Selecione ao menos um item para liberar o pagamento.');
        return false;
    }

    if(valorParcial <= 0){
        bloquearPagamentosParcial('Informe o valor parcial primeiro.');
        return false;
    }

    if(valorParcial > saldoRestante){
        bloquearPagamentosParcial('O valor parcial não pode ser maior que o saldo restante.');
        return false;
    }

    $('#alerta-finalizacao-parcial').addClass('d-none').text('');
    $('#area-pagamentos-parcial').removeClass('area-pagamentos-bloqueada').addClass('area-pagamentos-liberada');

    return true;
}

function bloquearPagamentosParcial(msg){
    $('#area-pagamentos-parcial').addClass('area-pagamentos-bloqueada').removeClass('area-pagamentos-liberada');
    $('.bloqueio-pagamento-msg').text(msg);
    $('#alerta-finalizacao-parcial').removeClass('d-none').text(msg);
}

function adicionarPagamentoParcial(){
    if(!validarValorParcial()){
        return;
    }

    let tipo = $('#inp-tipo-pagamento-parcial').val();
    let tipoTexto = $('#inp-tipo-pagamento-parcial option:selected').text();
    let valor = convertMoedaToFloat($('#inp-valor-pagamento-parcial').val());
    let valorParcial = convertMoedaToFloat($('#inp-valor-parcial').val());
    let totalPago = pagamentosParciais.reduce((total, item) => total + item.valor, 0);

    if(!tipo){
        swal('Ops', 'Selecione o tipo de pagamento.', 'error');
        return;
    }

    if(valor <= 0){
        swal('Ops', 'Informe o valor do pagamento.', 'error');
        return;
    }

    if(parseFloat((totalPago + valor).toFixed(2)) > parseFloat(valorParcial.toFixed(2))){
        swal('Ops', 'O total dos pagamentos não pode passar do valor parcial.', 'error');
        return;
    }

    pagamentosParciais.push({
        tipo: tipo,
        tipo_texto: tipoTexto,
        valor: valor
    });

    $('#inp-tipo-pagamento-parcial').val('');
    $('#inp-valor-pagamento-parcial').val('');

    atualizarResumoParcial();
}

function removerPagamentoParcial(index){
    pagamentosParciais.splice(index, 1);
    atualizarResumoParcial();
}

function atualizarResumoParcial(){
    let valorParcial = convertMoedaToFloat($('#inp-valor-parcial').val());
    let totalPago = pagamentosParciais.reduce((total, item) => total + item.valor, 0);
    let falta = valorParcial - totalPago;

    $('#resumo-total-parcial').text('R$ ' + convertFloatToMoeda(valorParcial));
    $('#resumo-total-pago').text('R$ ' + convertFloatToMoeda(totalPago));
    $('#resumo-total-falta').text('R$ ' + convertFloatToMoeda(falta > 0 ? falta : 0));
    $('#pagamentos-parciais-json').val(JSON.stringify(pagamentosParciais));

    let html = '';

    pagamentosParciais.forEach((item, index) => {
        html += `
        <div class="pagamento-parcial-item">
        <div>
        <strong>${item.tipo_texto}</strong>
        <small>R$ ${convertFloatToMoeda(item.valor)}</small>
        </div>
        <button type="button" onclick="removerPagamentoParcial(${index})">Remover</button>
        </div>
        `;
    });

    if(!html){
        html = `<div class="pagamento-parcial-empty">Nenhum pagamento adicionado.</div>`;
    }

    $('#lista-pagamentos-parciais').html(html);
}

function validarFinalizacaoParcial(){
    if(!validarValorParcial()){
        return false;
    }

    let valorParcial = convertMoedaToFloat($('#inp-valor-parcial').val());
    let totalPago = pagamentosParciais.reduce((total, item) => total + item.valor, 0);

    if(tipoFinalizacaoParcial() == 'itens' && $('.check-item-parcial:checked').length == 0){
        swal('Ops', 'Selecione ao menos um item da comanda.', 'error');
        return false;
    }

    if(pagamentosParciais.length == 0){
        swal('Ops', 'Adicione ao menos uma forma de pagamento.', 'error');
        return false;
    }

    if(parseFloat(totalPago.toFixed(2)) != parseFloat(valorParcial.toFixed(2))){
        swal('Ops', 'O total pago precisa fechar exatamente com o valor parcial.', 'error');
        return false;
    }

    return true;
}

function gerarNfce(id) {

    $('#overlay-emitindo-nfce').css('display', 'flex')
    $.post(path_url + "api/nfce_painel/emitir", {
        id: id,
    })
    .done((success) => {
        $('#overlay-emitindo-nfce').hide()

        swal(
            "Sucesso",
            "NFCe emitida " + success.recibo + " - chave: [" + success.chave + "]",
            "success"
            )
        .then(() => {
            window.open(path_url + 'nfce/imprimir/' + id, "_blank")
            setTimeout(() => {
                if(!update){
                    location.reload()
                }else{
                    location.href = path_url+'frontbox'
                }
            }, 100)
        })

    })
    .fail((err) => {
        $('#overlay-emitindo-nfce').hide()
        if(err.responseJSON.message){

            swal("Algo deu errado", err.responseJSON.message, "error")
            .then(() => {
                location.reload()
            })

        }else{
            swal("Algo deu errado", err.responseJSON, "error")
            .then(() => {
                location.reload()
            })
        }

    })
}

function salvarFinalizacaoParcial(){
    let form = $('#form-finalizar-parcial');
    let btn = $('#btn-confirmar-finalizacao-parcial');

    btn.prop('disabled', true).html('<i class="ri-loader-4-line"></i> Salvando...');

    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: form.serialize(),

        success: function(resp){
            $('#modal-finalizar-parcial').modal('hide');

            swal({
                title: 'Finalização parcial salva',
                text: 'Deseja emitir NFC-e agora?',
                icon: 'success',
                buttons: {
                    cancel: 'Não',
                    confirm: 'Sim, emitir NFC-e'
                }
            }).then((emitir) => {
                if(emitir && resp.redirect_nfce){
                    gerarNfce(resp.nfce_id)
                }else{

                    var disp_setting="toolbar=yes,location=no,";
                    disp_setting+="directories=yes,menubar=yes,";
                    disp_setting+="scrollbars=yes,width=850, height=600, left=100, top=25";

                    var docprint=window.open(path_url+"frontbox/imprimir-nao-fiscal/"+resp.nfce_id,"",disp_setting);
                    docprint.focus();
                    setTimeout(() => {
                        location.reload();
                    })
                }
            });
        },

        error: function(err){
            let msg = 'Não foi possível salvar a finalização parcial.';

            if(err.responseJSON && err.responseJSON.message){
                msg = err.responseJSON.message;
            }

            swal('Erro', msg, 'error');
        },

        complete: function(){
            btn.prop('disabled', false).html('<i class="ri-check-line"></i> Confirmar finalização parcial');
        }
    });
}

function limparFinalizacaoParcial(){
    pagamentosParciais = [];

    $('input[name="tipo_parcial"][value="valor"]').prop('checked', true);
    $('.tipo-parcial-card').removeClass('active');
    $('input[name="tipo_parcial"]:checked').closest('.tipo-parcial-card').addClass('active');

    $('#area-itens-parcial').addClass('d-none');
    $('.check-item-parcial').prop('checked', false);

    $('#inp-valor-parcial').prop('readonly', false).val('');
    $('#inp-cpf-nota-parcial').val('');
    $('#inp-tipo-pagamento-parcial').val('');
    $('#inp-valor-pagamento-parcial').val('');
    $('#pagamentos-parciais-json').val('');
    $('#alerta-finalizacao-parcial').addClass('d-none').text('');

    bloquearPagamentosParcial('Informe o valor parcial primeiro.');
    atualizarResumoParcial();
}