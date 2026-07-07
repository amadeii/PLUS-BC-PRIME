let transmitindoNFe = false;

$(document).off('click', '.fat-drop-btn');
$(document).off('click', '.fat-drop-menu');
$(document).off('click.fatDrop');

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

function transmitir(id){
	console.clear()
	if(transmitindoNFe){
		return;
	}

	transmitindoNFe = true;
	$.post(path_url + "api/nfce_painel/emitir", {
		id: id,
	})
	.done((success) => {
		console.log(success)
		if(success.recibo == '' && success.contigencia){
			swal("Sucesso", "NFCe emitida em contigência  - chave: [" + success.chave + "]", "success")
			.then(() => {
				if($('#nao_imprimir').length == 0){
					window.open(path_url + 'nfce/imprimir/' + id, "_blank")
				}
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}else{
			swal("Sucesso", "NFCe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
			.then(() => {
				if($('#nao_imprimir').length == 0){
					window.open(path_url + 'nfce/imprimir/' + id, "_blank")
				}
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}
	})
	.fail((err) => {
		console.log(err)
		if(err.responseJSON.message){
			swal("Algo deu errado", err.responseJSON.message, "error")
			.then(() => {
				location.reload()
			})
		}else{
			swal("Algo deu errado", err.responseJSON, "error")
		}
	}).always(() => {
		transmitindoNFe = false;
	});
}

$(document).on('click', '.btn-transmitir-nfce', function(e){
	e.preventDefault();
	e.stopPropagation();

	let id = $(this).data('id');

	$('.fat-drop-menu').removeClass('active').hide();
	$('.fat-drop-btn').removeClass('active');

	swal({
		title: "Transmitir NFC-e?",
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
function transmitir(id){
	console.clear()
	if(transmitindoNFe){
		return;
	}

	transmitindoNFe = true;
	$.post(path_url + "api/nfce_painel/emitir", {
		id: id,
	})
	.done((success) => {
		console.log(success)
		if(success.recibo == '' && success.contigencia){
			swal("Sucesso", "NFCe emitida em contigência  - chave: [" + success.chave + "]", "success")
			.then(() => {
				if($('#nao_imprimir').length == 0){
					window.open(path_url + 'nfce/imprimir/' + id, "_blank")
				}
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}else{
			swal("Sucesso", "NFCe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
			.then(() => {
				if($('#nao_imprimir').length == 0){
					window.open(path_url + 'nfce/imprimir/' + id, "_blank")
				}
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}
	})
	.fail((err) => {
		console.log(err)
		if(err.responseJSON.message){
			swal("Algo deu errado", err.responseJSON.message, "error")
			.then(() => {
				location.reload()
			})
		}else{
			swal("Algo deu errado", err.responseJSON, "error")
		}
	}).always(() => {
		transmitindoNFe = false;
	});
}

function transmitirContigencia(id){

	console.clear()
	$.post(path_url + "api/nfce_painel/transmitir-contigencia", {
		id: id,
	})
	.done((success) => {
		console.log(success)
		if(success.recibo == '' && success.contigencia){
			swal("Sucesso", "NFCe emitida em contigência  - chave: [" + success.chave + "]", "success")
			.then(() => {
				window.open(path_url + 'nfce/imprimir/' + id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}else{
			swal("Sucesso", "NFCe emitida " + success.recibo + " - chave: [" + success.chave + "]", "success")
			.then(() => {
				window.open(path_url + 'nfce/imprimir/' + id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}
	})
	.fail((err) => {
		console.log(err)
		if(err.responseJSON.message){
			swal("Algo deu errado", err.responseJSON.message, "error")
			.then(() => {
				location.reload()
			})
		}else{
			swal("Algo deu errado", err.responseJSON, "error")
		}
	})
}


$(document).on('click', '.btn-cancelar-nfce', function(e){
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

var IDNFE = null
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

function corrigir(id, numero){
	IDNFE = id
	$('.ref-numero').text(numero)
	$('#modal-corrigir').modal('show')
}

$('#btn-cancelar').click(() => {

	if(IDNFE != null){
		$.post(path_url + "api/nfce_painel/cancelar", {
			id: IDNFE,
			motivo: $('#inp-motivo-cancela').val()
		})
		.done((success) => {
			swal("Sucesso", "NFe cancelada " + success, "success")
			.then(() => {
				location.reload()
			})
		})
		.fail((err) => {
			console.log(err)

			swal("Algo deu errado", err.responseJSON, "error")

		})
	}else{
		swal("Erro", "Nota não selecionada", "error")
	}
})


function consultar(id, numero){
	$.post(path_url + "api/nfce_painel/consultar", {
		id: id,
	})
	.done((success) => {
		swal("Sucesso", success, "success")
	})
	.fail((err) => {
		console.log(err)
		swal("Algo deu errado", err.responseJSON, "error")

	})
}

function enviarEmail(id, numero){
	$('.ref-numero').text(numero)
	$('#modal-email').modal('show')
	$('#inp-danfe').prop('checked', 1)
	$('#inp-xml').prop('checked', 1)
	IDNFE = id

}

$(document).on("click", ".btn-delete-drop", function (e) {
	e.preventDefault();
	e.stopPropagation();

	const formId = $(this).data("form");

	swal({
		title: "Você está certo?",
		text: "Uma vez deletado, você não poderá recuperar esse item novamente!",
		icon: "warning",
		buttons: ["Cancelar", "Excluir"],
		dangerMode: true,
	}).then((confirm) => {
		if (confirm) {
			document.getElementById(formId).submit();
		}
	});
});

$('#btn-enviar-email').click(() => {
	let email = $('#inp-email').val()
	let danfe = $('#inp-danfe').is(':checked') ? 1 : 0
	let xml = $('#inp-xml').is(':checked') ? 1 : 0
	let data = {
		email: email,
		id: IDNFE,
		danfe: danfe,
		xml: xml,
	}

	$.post(path_url + "api/nfce_painel/send-mail", data)
	.done((success) => {
		// console.log(success)
		swal("Sucesso", "Email enviado!", "success")
		$('#modal-email').modal('hide')
	})
	.fail((err) => {
		// console.log(err)
		swal("Erro", err.responseJSON, "error")
	})
})