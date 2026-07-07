$(document).off('click', '.fat-drop-btn');
$(document).off('click', '.fat-drop-menu');
$(document).off('click.fatDrop');

function imprimir(id, numero){
    IDNFE = id
    $('.ref-numero').text(numero)
    $('#modal-print').modal('show')
}

function gerarDanfe(tipo){
    if(tipo == 'danfe'){
        window.open('/nfe/imprimir/'+IDNFE)
    }else if(tipo == 'simples'){
        window.open('/nfe/danfe-simples/'+IDNFE)
    }else{
        window.open('/nfe/danfe-etiqueta/'+IDNFE)
    }
    $('#modal-print').modal('hide')
}

function posicionarFatDrop(btn, menu){
    let offset = btn.offset();
    let larguraMenu = menu.outerWidth();
    let alturaBtn = btn.outerHeight();

    menu.css({
        top: offset.top + alturaBtn + 6 - $(window).scrollTop(),
        left: offset.left - larguraMenu + btn.outerWidth()
    });
}

$(document).on('click', '.fat-drop-btn', function(e){
    e.preventDefault();
    e.stopPropagation();

    let btn = $(this);
    let menu = $('#' + btn.data('fat-toggle'));
    let isOpen = menu.hasClass('active');

    $('.fat-drop-menu').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');

    if(!isOpen){
        menu.appendTo('body');
        menu.show().addClass('active');
        btn.addClass('active');
        posicionarFatDrop(btn, menu);
    }
});

$(document).on('click', '.fat-drop-menu', function(e){
    e.stopPropagation();
});

$(document).on('click.fatDrop', function(){
    $('.fat-drop-menu').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');
});

$(window).on('scroll resize', function(){
    $('.fat-drop-menu.active').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');
});

$(document).on('click', '.fat-drop-item', function(){

    $('.fat-drop-menu').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');

});

$(document).on('click', '.btn-transmitir-nfe', function(e){
    e.preventDefault();
    e.stopPropagation();

    let id = $(this).data('id');

    $('.fat-drop-menu').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');

    swal({
        title: "Transmitir NF-e?",
        text: "Deseja realmente transmitir esta NF-e para a SEFAZ?",
        icon: "warning",
        buttons: {
            cancel: {
                text: "Cancelar",
                visible: true,
                closeModal: true
            },
            confirm: {
                text: "Sim, transmitir",
                visible: true,
                closeModal: true
            }
        }
    }).then((confirmou) => {
        if(confirmou){
            transmitir(id);
        }
    });
});

let transmitindoNFe = false;

function transmitir(id){
    console.clear();

    if(transmitindoNFe){
        return;
    }

    transmitindoNFe = true;

    $.ajax({
        url: path_url + 'api/nfe_painel/emitir',
        type: 'POST',
        data: {
            id: id
        },
        success: function(success){
            swal("Sucesso","NFe emitida " + success.recibo + " - chave: [" + success.chave + "]","success").then(() => {
                if($('#nao_imprimir').length == 0){
                    window.open('/nfe/imprimir/' + id, "_blank");
                }

                setTimeout(() => {
                    location.reload();
                }, 100);
            });
        },
        error: function(err){
            let msg = "Erro ao transmitir NFe";

            if(err.responseJSON){
                if(err.responseJSON.error?.protNFe?.infProt){
                    let o = err.responseJSON.error.protNFe.infProt;
                    msg = o.cStat + " - " + o.xMotivo;
                }else if(err.responseJSON.message){
                    msg = err.responseJSON.message;
                }else if(err.responseJSON.xMotivo){
                    msg = err.responseJSON.xMotivo;
                }else if(err.responseJSON.error){
                    msg = typeof err.responseJSON.error === 'string'
                    ? err.responseJSON.error
                    : JSON.stringify(err.responseJSON.error);
                }else if(Array.isArray(err.responseJSON) && err.responseJSON.length > 0){
                    msg = err.responseJSON[0];
                }else if(typeof err.responseJSON === 'string'){
                    msg = err.responseJSON;
                }else{
                    msg = JSON.stringify(err.responseJSON);
                }
            }else if(err.responseText){
                msg = err.responseText;
            }

            swal("Algo deu errado", msg, "error");
        },
        complete: function(){
            transmitindoNFe = false;
        }
    });
}

let IDNFE = null;

$(document).on('click', '.btn-cancelar-nfe', function(e){
    e.preventDefault();
    e.stopPropagation();

    $('.fat-drop-menu').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');

    cancelar(
        $(this).data('id'),
        $(this).data('numero'),
        $(this).data('serie'),
        $(this).data('data'),
        $(this).data('cliente'),
        $(this).data('chave')
    );
});

function cancelar(id, numero, serie, data, cliente, chave){

    IDNFE = id;
    $('.cancel-ref-numero').text(numero);
    $('.cancel-ref-serie').text(serie);
    $('.cancel-ref-data').text(data);
    $('.cancel-ref-cliente').text(cliente);
    $('.cancel-ref-chave').text(chave);

    $('#texto-cancelamento').val('');
    $('#contador-cancelamento').text(0);

    $('#modal-cancelar').modal('show');
}

$(document).on('keyup', '#texto-cancelamento', function(){
    $('#contador-cancelamento').text($(this).val().length);
});

$('#btn-cancelar').click(() => {

    let motivo = $('#texto-cancelamento').val();

    if(motivo.length < 15){
        swal("Alerta", "Informe no mínimo 15 caracteres", "warning");
        return;
    }

    if(IDNFE != null){

        $('#btn-cancelar')
        .prop('disabled', true)
        .html('<i class="ri-loader-4-line spin me-1"></i> Cancelando...');

        $.post(path_url + "api/nfe_painel/cancelar", {
            id: IDNFE,
            motivo: motivo
        })

        .done((success) => {

            $('#modal-cancelar').modal('hide');

            swal(
                "Sucesso",
                "NF-e cancelada com sucesso",
                "success"
            ).then(() => {

                if($('#nao_imprimir').length == 0){
                    window.open(
                        path_url + 'nfe/imprimir-cancela/' + IDNFE,
                        "_blank"
                    );
                }

                setTimeout(() => {
                    location.reload();
                }, 100);

            });

        })

        .fail((err) => {

            console.log(err);

            let msg = "Erro ao cancelar NF-e";

            if(err.responseJSON){
                if(err.responseJSON.message){
                    msg = err.responseJSON.message;
                }else if(typeof err.responseJSON === 'string'){
                    msg = err.responseJSON;
                }else{
                    msg = JSON.stringify(err.responseJSON);
                }
            }

            swal("Algo deu errado", msg, "error");

        })

        .always(() => {

            $('#btn-cancelar')
            .prop('disabled', false)
            .html('<i class="ri-close-circle-line me-1"></i> Transmitir Cancelamento');

        });

    }else{
        swal("Erro", "Nota não selecionada", "error");
    }
});

$(document).on('click', '.btn-corrigir-nfe', function(e){
    e.preventDefault();
    e.stopPropagation();

    $('.fat-drop-menu').removeClass('active').hide();
    $('.fat-drop-btn').removeClass('active');

    corrigir(
        $(this).data('id'),
        $(this).data('numero'),
        $(this).data('serie'),
        $(this).data('data'),
        $(this).data('cliente'),
        $(this).data('chave')
    );
});

function corrigir(id, numero, serie, data, cliente, chave){

    IDNFE = id;
    $('.ref-numero').text(numero);
    $('.ref-serie').text(serie);
    $('.ref-data').text(data);
    $('.ref-cliente').text(cliente);
    $('.ref-chave').text(chave);

    $('#texto-correcao').val('');
    $('#contador-correcao').text(0);

    $('#modal-corrigir').modal('show');
}

$(document).on('keyup', '#texto-correcao', function(){
    $('#contador-correcao').text($(this).val().length);
});

$(document).on('click', '#btn-corrigir', function(){

    let motivo = $('#texto-correcao').val();

    if(motivo.length < 15){
        swal("Alerta", "Informe no mínimo 15 caracteres", "warning");
        return;
    }

    if(IDNFE != null){

        $('#btn-corrigir')
        .prop('disabled', true)
        .html('<i class="ri-loader-4-line spin me-1"></i> Corrigindo...');

        $.post(path_url + "api/nfe_painel/corrigir", {
            id: IDNFE,
            motivo: motivo
        })

        .done((success) => {

            $('#modal-corrigir').modal('hide');

            swal("Sucesso", "NF-e corrigida " + success, "success")
            .then(() => {

                if($('#nao_imprimir').length == 0){
                    window.open(path_url + 'nfe/imprimir-correcao/' + IDNFE, "_blank");
                }

                setTimeout(() => {
                    location.reload();
                }, 100);

            });

        })

        .fail((err) => {

            console.log(err);

            let mensagem = 'Erro ao corrigir NF-e';

            if(err.responseJSON){

                if(typeof err.responseJSON === 'string'){
                    mensagem = err.responseJSON;
                }else if(err.responseJSON.message){
                    mensagem = err.responseJSON.message;
                }else if(err.responseJSON.error){
                    mensagem = err.responseJSON.error;
                }

            }else if(err.responseText){
                mensagem = err.responseText;
            }

            swal({
                title: "Algo deu errado",
                text: mensagem,
                icon: "error"
            });

        })

        .always(() => {

            $('#btn-corrigir')
            .prop('disabled', false)
            .html('<i class="ri-file-edit-line me-1"></i> Transmitir Correção');

        });

    }else{
        swal("Erro", "Nota não selecionada", "error");
    }
});

$(document).on('click', '.btn-ver-rejeicao', function(){

    let motivo = $(this).data('motivo');
    let numero = $(this).data('numero');

    swal({
        title: "NF-e Rejeitada",
        text: "NF-e Nº " + numero + "\n\n" + motivo,
        icon: "warning",
        button: {
            text: "Fechar",
            className: "btn btn-warning"
        }
    });

});
