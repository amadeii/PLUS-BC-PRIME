(function () {
	'use strict';

	let TEF_CLIENT = null;
	let TEF_PROCESSANDO = false;
	let TEF_LIBERAR_CLICK_PADRAO = false;
	let TEF_ULTIMO_RETORNO = null;

	const TEF_OPTION_CODE = '00';

	function csrf() {
		return $('meta[name="csrf-token"]').attr('content') || '';
	}

	function getPathUrl() {
		if (typeof path_url !== 'undefined') return path_url;
		return window.location.origin + '/';
	}

	function moedaParaFloat(valor) {
		if (typeof convertMoedaToFloat === 'function') {
			return convertMoedaToFloat(valor);
		}

		if (valor == null) return 0;
		return parseFloat(
			String(valor)
			.replace(/\s/g, '')
			.replace(/\./g, '')
			.replace(',', '.')
			.replace(/[^\d.-]/g, '')
			) || 0;
	}

	function floatParaMoeda(valor) {
		if (typeof convertFloatToMoeda === 'function') {
			return convertFloatToMoeda(valor);
		}

		return (parseFloat(valor) || 0).toLocaleString('pt-BR', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		});
	}

	function tefLog(msg) {
		console.log('[TEF4]', msg);

		if (typeof setLog === 'function') {
			setLog(msg);
		}

		const $status = $('#tef-status');
		if ($status.length) {
			$status.html(msg);
		}

		const $log = $('#tef-log');
		if ($log.length) {
			$log.append(`<div>${msg}</div>`);
			$log.scrollTop($log[0].scrollHeight);
		}
	}

	function tefErro(msg) {
		tefLog(msg);

		if (window.Swal) {
			Swal.fire({
				icon: 'error',
				title: 'TEF',
				text: msg,
				confirmButtonColor: '#4254BA'
			});
		} else {
			alert(msg);
		}
	}

	function tefAviso(msg) {
		tefLog(msg);

		if (window.Swal) {
			Swal.fire({
				icon: 'warning',
				title: 'TEF',
				text: msg,
				confirmButtonColor: '#4254BA'
			});
		} else {
			alert(msg);
		}
	}

	function tefInfo(msg) {
		tefLog(msg);
	}

	function getEmpresaId() {
		return $('#empresa_id').val() || '';
	}

	function getUsuarioId() {
		return $('#usuario_id').val() || '';
	}

	function getOperador() {
		return (window.TEF_CONFIG && window.TEF_CONFIG.operador) || getUsuarioId() || '1';
	}

	function getConfigTef() {
		if (!window.TEF_CONFIG) return null;

		const agenteIp = window.TEF_CONFIG.agenteIp || '127.0.0.1';
		const agentePorta = window.TEF_CONFIG.agentePorta || '443';

		return {
			sitefIp: window.TEF_CONFIG.sitefIp || '127.0.0.1',
			storeId: window.TEF_CONFIG.storeId || '00000000',
			terminalId: window.TEF_CONFIG.terminalId || 'REST0001',
			operador: getOperador(),
			agenteUrl: `https://${agenteIp}:${agentePorta}`
		};
	}

	function getTipoPagamentoSelect() {
		return $('#tipo_pagamento_atual');
	}

	function getTipoPagamentoAtual() {
		return String(getTipoPagamentoSelect().val() || '');
	}

	function getTipoPagamentoTexto() {
		const $sel = getTipoPagamentoSelect();
		const txt = $sel.find('option:selected').text() || '';
		return txt.trim();
	}

	function isTefSelecionado() {
		return getTipoPagamentoAtual() === TEF_OPTION_CODE;
	}

	function desabilitarOpcaoTef() {
		const $sel = getTipoPagamentoSelect();
		if (!$sel.length) return;

		const $opt = $sel.find(`option[value="${TEF_OPTION_CODE}"]`);
		if ($opt.length) {
			if ($sel.val() === TEF_OPTION_CODE) {
				$sel.val('').trigger('change');
			}
			$opt.prop('disabled', true);
		}
	}

	function habilitarOpcaoTef() {
		const $sel = getTipoPagamentoSelect();
		if (!$sel.length) return;

		const $opt = $sel.find(`option[value="${TEF_OPTION_CODE}"]`);
		if ($opt.length) {
			$opt.prop('disabled', false);
		}
	}

	function getValorPagamentoAtual() {
		const candidatos = [
		'#valor_pagamento_atual',
		'#valor_pagamento',
		'#pg-valor',
		'#valor-recebimento'
		];

		for (const sel of candidatos) {
			const $el = $(sel);
			if ($el.length) {
				const valor = $el.is('input') ? $el.val() : $el.text();
				const n = moedaParaFloat(valor);
				if (n > 0) return n;
			}
		}

		const restantes = [
		'#valor_restante',
		'#pg-valor-restante',
		'#restante',
		'.valor-restante'
		];

		for (const sel of restantes) {
			const $el = $(sel);
			if ($el.length) {
				const n = moedaParaFloat($el.text());
				if (n > 0) return n;
			}
		}

		const totalTela = $('.valor-total').first().text();
		return moedaParaFloat(totalTela);
	}

	function setValorPagamentoAtual(valor) {
		const candidatos = [
		'#valor_pagamento_atual',
		'#valor_pagamento',
		'#pg-valor',
		'#valor-recebimento'
		];

		for (const sel of candidatos) {
			const $el = $(sel);
			if ($el.length && $el.is('input')) {
				$el.val(floatParaMoeda(valor)).trigger('input').trigger('change');
				return;
			}
		}
	}

	function setTefHash(hash) {
		$('#tef_hash').val(hash || '');
	}

	function getNumeroCupom() {
		return String(Date.now()).slice(-6);
	}

	function getDataFiscal() {
		const d = new Date();
		return [
		d.getFullYear(),
		String(d.getMonth() + 1).padStart(2, '0'),
		String(d.getDate()).padStart(2, '0')
		].join('');
	}

	function getHoraFiscal() {
		const d = new Date();
		return [
		String(d.getHours()).padStart(2, '0'),
		String(d.getMinutes()).padStart(2, '0'),
		String(d.getSeconds()).padStart(2, '0')
		].join('');
	}

	async function swalInput(titulo, texto, valorPadrao = '') {
		if (!window.Swal) {
			const resp = window.prompt(texto || titulo, valorPadrao);
			return resp === null ? null : String(resp);
		}

		const r = await Swal.fire({
			title: titulo || 'TEF',
			input: 'text',
			inputLabel: texto || '',
			inputValue: valorPadrao,
			showCancelButton: true,
			confirmButtonText: 'Confirmar',
			cancelButtonText: 'Cancelar',
			confirmButtonColor: '#4254BA'
		});

		return r.isConfirmed ? String(r.value || '') : null;
	}

	async function swalEscolhaFluxo() {
		if (!window.Swal) {
			return 'debito';
		}

		const r = await Swal.fire({
			title: 'Selecione o tipo TEF',
			html: `
			<div style="display:grid;grid-template-columns:1fr;gap:10px;margin-top:10px;">
			<button type="button" class="swal2-confirm swal2-styled" data-tef-tipo="debito" style="background:#4254BA;">Débito</button>
			<button type="button" class="swal2-confirm swal2-styled" data-tef-tipo="credito" style="background:#1f8f4d;">Crédito</button>
			<button type="button" class="swal2-confirm swal2-styled" data-tef-tipo="pix" style="background:#7c3aed;">PIX</button>
			</div>
			`,
			showConfirmButton: false,
			showCancelButton: true,
			cancelButtonText: 'Cancelar',
			didOpen: () => {
				const popup = Swal.getPopup();
				popup.querySelectorAll('[data-tef-tipo]').forEach((btn) => {
					btn.addEventListener('click', () => {
						Swal.close();
						popup.dataset.result = btn.getAttribute('data-tef-tipo');
					});
				});
			},
			willClose: () => {}
		});

		const popup = Swal.getPopup();
		const resultado = popup?.dataset?.result || document.querySelector('.swal2-popup')?.dataset?.result || null;
		return r.dismiss ? resultado : resultado;
	}

	async function swalEscolhaParcelamentoCredito() {
		if (!window.Swal) {
			return { modo: 'avista', parcelas: 1 };
		}

		const r = await Swal.fire({
			title: 'Crédito',
			html: `
			<select id="swal-tef-modo" class="swal2-input">
			<option value="avista">À vista</option>
			<option value="parcelado_loja">Parcelado loja</option>
			<option value="parcelado_admin">Parcelado administradora</option>
			</select>
			<input id="swal-tef-parcelas" class="swal2-input" type="number" min="1" max="12" value="1" placeholder="Parcelas">
			`,
			showCancelButton: true,
			confirmButtonText: 'Confirmar',
			cancelButtonText: 'Cancelar',
			confirmButtonColor: '#4254BA',
			preConfirm: () => {
				const modo = document.getElementById('swal-tef-modo').value;
				let parcelas = parseInt(document.getElementById('swal-tef-parcelas').value || '1', 10);

				if (!Number.isFinite(parcelas) || parcelas < 1) parcelas = 1;
				if (modo === 'avista') parcelas = 1;

				return { modo, parcelas };
			}
		});

		return r.isConfirmed ? r.value : null;
	}

	class CliSiTefWeb {
		constructor(config = {}) {
			this.config = {
				agenteUrl: config.agenteUrl || 'https://127.0.0.1:443',
				sitefIp: config.sitefIp || '127.0.0.1',
				storeId: config.storeId || '00000000',
				terminalId: config.terminalId || 'REST0001',
				operador: config.operador || '1'
			};

			this.agentPrefix = '/agente/clisitef';
			this.sessionIdBase = null;
			this.currentSession = null;
			this.cancelRequested = false;
			this.loopCount = 0;
			this.MAX_LOOPS = 500;
			this.TIMEOUT_MS = 180000;
			this.transactionStartTime = null;
			this.transcript = [];
			this.comprovantes = { loja: [], cliente: [] };
			this.flow = null;
		}

		async postAgent(path, formObj) {
			const body = new URLSearchParams();

			Object.keys(formObj || {}).forEach((k) => {
				if (formObj[k] !== undefined && formObj[k] !== null) {
					body.append(k, String(formObj[k]));
				}
			});

			const res = await fetch(this.config.agenteUrl + this.agentPrefix + path, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'Accept': 'application/json'
				},
				body
			});

			const json = await res.json().catch(() => ({}));

			if (!res.ok) {
				throw new Error(json.serviceMessage || `Erro HTTP ${res.status} no agente CliSiTef`);
			}

			return json;
		}

		async verificarAgente() {
			try {
				const res = await fetch(this.config.agenteUrl + this.agentPrefix + '/state', {
					method: 'GET',
					headers: { 'Accept': 'application/json' }
				});
				return res.ok;
			} catch (e) {
				return false;
			}
		}

		async garantirSessao() {
			if (this.sessionIdBase) return this.sessionIdBase;

			const res = await this.postAgent('/session', {
				sitefIp: this.config.sitefIp,
				storeId: this.config.storeId,
				terminalId: this.config.terminalId,
				sessionParameters: ''
			});

			if (res.serviceStatus !== 0 || !res.sessionId) {
				throw new Error(res.serviceMessage || 'Falha ao criar sessão CliSiTef');
			}

			this.sessionIdBase = res.sessionId;
			return this.sessionIdBase;
		}

		async verificarPinpad() {
			try {
				const sid = await this.garantirSessao();
				const res = await this.postAgent('/pinpad/isPresent', { sessionId: sid });
				return res.serviceStatus === 0;
			} catch (e) {
				return false;
			}
		}

		async startTransaction(params) {
			return this.postAgent('/startTransaction', params);
		}

		async continueTransaction(params) {
			return this.postAgent('/continueTransaction', params);
		}

		async finishTransaction(params) {
			return this.postAgent('/finishTransaction', params);
		}

		async resetarPinPad() {
			try {
				if (this.sessionIdBase) {
					try {
						await this.postAgent('/pinpad/close', { sessionId: this.sessionIdBase });
					} catch (e) {}
				}

				await fetch(this.config.agenteUrl + this.agentPrefix + '/session', {
					method: 'DELETE',
					headers: { 'Accept': 'application/json' }
				}).catch(() => {});

			} finally {
				this.sessionIdBase = null;
				this.currentSession = null;
				this.cancelRequested = false;
				this.loopCount = 0;
			}
		}

		requestCancel() {
			this.cancelRequested = true;
		}

		async salvarRetornoServidor(tefData) {
			const res = await fetch(window.location.origin + '/tef-store-log', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': csrf()
				},
				body: JSON.stringify({ tef: tefData })
			});

			if (!res.ok) {
				throw new Error(`Erro ao salvar retorno TEF: HTTP ${res.status}`);
			}

			return res.json();
		}

		async processarComando(resp) {
			const commandId = Number(resp.commandId || 0);
			const fieldMaxLength = Number(resp.fieldMaxLength || 0);
			const fieldMinLength = Number(resp.fieldMinLength || 0);
			const fieldId = Number(resp.fieldId || 0);
			const data = resp.data != null ? String(resp.data) : '';

			if ((fieldId === 121 || fieldId === 122) && data.trim() !== '') {
				const linhas = data.split('\n').map(s => s.replace(/\r/g, '').trim()).filter(Boolean);
				if (fieldId === 121) this.comprovantes.cliente.push(...linhas);
				if (fieldId === 122) this.comprovantes.loja.push(...linhas);
			}

			switch (commandId) {
				case 0:
				return '';

				case 1:
				case 2:
				case 3:
				case 4:
				case 22:
				if (data) tefInfo(data);
				return '';

				case 11:
				case 13:
				return '';

				case 20:
				case 21:
				case 30: {
					if (!data && fieldMaxLength <= 0) {
						return '';
					}

					if (this.flow && this.flow.tipo === 'debito') {
						const lower = data.toLowerCase();

						if (lower.includes('vista') || lower.includes('parcel') || lower.includes('saque') || lower.includes('cdc')) {
							return fieldMaxLength === 2 ? '01' : '1';
						}

						if (lower.includes('taxa')) {
							return '0'.padStart(fieldMaxLength || 1, '0');
						}
					}

					if (this.flow && this.flow.tipo === 'credito') {
						const lower = data.toLowerCase();

						if (lower.includes('vista') && lower.includes('parcel')) {
							let resposta = '1';
							if (this.flow.modo === 'parcelado_loja') resposta = '2';
							if (this.flow.modo === 'parcelado_admin') resposta = '3';
							return fieldMaxLength === 2 ? resposta.padStart(2, '0') : resposta;
						}

						if (lower.includes('parcela') && this.flow.parcelas) {
							const parcelas = String(this.flow.parcelas);
							return fieldMaxLength === 2 ? parcelas.padStart(2, '0') : parcelas;
						}

						if (lower.includes('taxa')) {
							return '0'.padStart(fieldMaxLength || 1, '0');
						}
					}

					const resposta = await swalInput('TEF', data || 'Informe o dado solicitado');
					if (resposta === null) return null;

					let out = String(resposta).trim();
					if (fieldMaxLength > 0) {
						out = out.slice(0, fieldMaxLength);
					}

					if (fieldMaxLength === 2 && /^\d$/.test(out)) {
						out = '0' + out;
					}

					if (fieldMinLength > 0 && out.length < fieldMinLength) {
						out = out.padEnd(fieldMinLength, '0');
					}

					return out;
				}

				case 23: {
					if (!data || !data.includes(':')) return '';

					const opcoes = data.split(';').filter(Boolean).map((item) => {
						const idx = item.indexOf(':');
						if (idx === -1) return null;
						return {
							valor: item.slice(0, idx).trim(),
							label: item.slice(idx + 1).trim()
						};
					}).filter(Boolean);

					if (!opcoes.length) return '';

					if (window.Swal) {
						const html = opcoes.map((o) =>
							`<button type="button" class="swal2-confirm swal2-styled tef-opcao-swal" data-valor="${o.valor}" style="background:#4254BA;margin:6px;">${o.label}</button>`
							).join('');

						const r = await Swal.fire({
							title: 'Selecione uma opção',
							html,
							showConfirmButton: false,
							showCancelButton: true,
							cancelButtonText: 'Cancelar',
							didOpen: () => {
								const popup = Swal.getPopup();
								popup.querySelectorAll('.tef-opcao-swal').forEach((btn) => {
									btn.addEventListener('click', () => {
										popup.dataset.result = btn.getAttribute('data-valor');
										Swal.close();
									});
								});
							}
						});

						const popup = Swal.getPopup();
						const escolhido = popup?.dataset?.result || document.querySelector('.swal2-popup')?.dataset?.result || null;
						if (r.dismiss && !escolhido) return null;
						return escolhido || '';
					}

					return opcoes[0].valor;
				}

				default:
				if (data) tefInfo(data);
				return '';
			}
		}

		async processarTransacao(valorCentavos, functionId) {
			this.loopCount = 0;
			this.cancelRequested = false;
			this.transactionStartTime = Date.now();
			this.transcript = [];
			this.comprovantes = { loja: [], cliente: [] };

			const baseSessionId = await this.garantirSessao();

			const startResp = await this.startTransaction({
				sessionId: baseSessionId,
				functionId: functionId,
				trnAmount: String(valorCentavos),
				taxInvoiceNumber: getNumeroCupom(),
				taxInvoiceDate: getDataFiscal(),
				taxInvoiceTime: getHoraFiscal(),
				operator: this.config.operador,
				restrictions: ''
			});

			if (startResp.serviceStatus !== 0) {
				throw new Error(startResp.serviceMessage || 'Não foi possível iniciar a transação TEF');
			}

			this.currentSession = startResp.sessionId || baseSessionId;

			let proximoComando = startResp;

			while (true) {
				this.loopCount++;

				if (this.loopCount > this.MAX_LOOPS) {
					throw new Error('Loop máximo do TEF excedido');
				}

				if ((Date.now() - this.transactionStartTime) > this.TIMEOUT_MS) {
					throw new Error('Tempo limite do TEF excedido');
				}

				this.transcript.push({ resp: proximoComando });

				const respostaOperador = await this.processarComando(proximoComando);

				if (respostaOperador === null || this.cancelRequested) {
					try {
						await this.finishTransaction({
							sessionId: this.currentSession,
							confirm: 0,
							taxInvoiceNumber: getNumeroCupom(),
							taxInvoiceDate: getDataFiscal(),
							taxInvoiceTime: getHoraFiscal()
						});
					} catch (e) {}
					throw new Error('Transação cancelada');
				}

				if (Number(proximoComando.commandId || 0) === 0) {
					break;
				}

				proximoComando = await this.continueTransaction({
					sessionId: this.currentSession,
					data: respostaOperador == null ? '' : respostaOperador,
					continue: 0
				});
			}

			const finishResp = await this.finishTransaction({
				sessionId: this.currentSession,
				confirm: 1,
				taxInvoiceNumber: getNumeroCupom(),
				taxInvoiceDate: getDataFiscal(),
				taxInvoiceTime: getHoraFiscal()
			});

			const retorno = {
				request: {
					functionId,
					valorCentavos,
					flow: this.flow || null
				},
				start: startResp,
				finish: finishResp,
				transcript: this.transcript,
				comprovantes: this.comprovantes,
				hash: finishResp?.sitefHash || finishResp?.hash || finishResp?.sessionId || this.currentSession,
				nsu: finishResp?.nsu || null
			};

			try {
				const salvo = await this.salvarRetornoServidor(retorno);
				retorno.store = salvo;
				retorno.hash = salvo?.hash || retorno.hash;
			} catch (e) {
				console.warn('[TEF4] Não foi possível salvar o retorno no servidor:', e.message);
			}

			this.currentSession = null;
			return retorno;
		}
	}

	function mapearFunctionId(tipo) {
		if (tipo === 'debito') return 2;
		if (tipo === 'credito') return 3;
		if (tipo === 'pix') return 122;
		return 0;
	}

	async function montarFluxoTEF() {
		const tipo = await swalEscolhaFluxo();
		if (!tipo) return null;

		if (tipo === 'debito') {
			return {
				tipo: 'debito',
				descricao: 'Cartão Débito'
			};
		}

		if (tipo === 'pix') {
			return {
				tipo: 'pix',
				descricao: 'PIX'
			};
		}

		const credito = await swalEscolhaParcelamentoCredito();
		if (!credito) return null;

		return {
			tipo: 'credito',
			modo: credito.modo,
			parcelas: credito.parcelas,
			descricao: credito.modo === 'avista'
			? 'Crédito à vista'
			: `Crédito ${credito.modo === 'parcelado_loja' ? 'parcelado loja' : 'parcelado administradora'} ${credito.parcelas}x`
		};
	}

	async function inicializarTEF() {
		const cfg = getConfigTef();

		if (!cfg) {
			console.warn('[TEF4] Configuração TEF não encontrada');
			return;
		}

		TEF_CLIENT = new CliSiTefWeb(cfg);

		const agenteOk = await TEF_CLIENT.verificarAgente();
		if (!agenteOk) {
			desabilitarOpcaoTef();
			tefInfo('TEF indisponível: agente CliSiTef não encontrado.');
			const btn = document.querySelector('.pg-forma[data-codigo="00"]');

			if (btn) {
				btn.classList.add('disabled');
				btn.style.opacity = '0.5';
				btn.style.pointerEvents = 'none';
			}
			return;
		}

		habilitarOpcaoTef();
		tefInfo('TEF inicializado com sucesso.');
	}

	async function garantirClienteEAgente() {
		if (!TEF_CLIENT) {
			await inicializarTEF();
		}

		if (!TEF_CLIENT) {
			throw new Error('TEF não configurado.');
		}

		const agenteOk = await TEF_CLIENT.verificarAgente();
		if (!agenteOk) {
			desabilitarOpcaoTef();
			throw new Error('AgenteCliSiTef não está disponível. Verifique se o agente está rodando.');
		}

		const pinpadOk = await TEF_CLIENT.verificarPinpad();
		if (!pinpadOk) {
			throw new Error('PinPad não encontrado ou indisponível.');
		}

		habilitarOpcaoTef();
	}

	async function processarPagamentoTefAntesDeAdicionar() {
		await garantirClienteEAgente();

		const valor = getValorPagamentoAtual();
		if (!valor || valor <= 0) {
			throw new Error('Informe um valor válido para o pagamento TEF.');
		}

		const fluxo = await montarFluxoTEF();
		if (!fluxo) {
			throw new Error('Operação cancelada pelo usuário.');
		}

		TEF_CLIENT.flow = fluxo;

		tefInfo(`Iniciando TEF: ${fluxo.descricao} | R$ ${floatParaMoeda(valor)}`);

		const functionId = mapearFunctionId(fluxo.tipo);
		const valorCentavos = Math.round(valor * 100);

		const retorno = await TEF_CLIENT.processarTransacao(valorCentavos, functionId);
		TEF_ULTIMO_RETORNO = retorno;

		if (!retorno || !retorno.hash) {
			throw new Error('TEF retornou sem hash da transação.');
		}

		setTefHash(retorno.hash);
		setValorPagamentoAtual(valor);

		tefInfo(`Pagamento TEF aprovado. Hash: ${retorno.hash}`);

		return retorno;
	}

	function religarClickOriginalAdicionar() {
		TEF_LIBERAR_CLICK_PADRAO = true;

		const btn = document.getElementById('adicionar-pagamento');
		if (btn) {
			btn.click();
		}

		setTimeout(() => {
			TEF_LIBERAR_CLICK_PADRAO = false;
		}, 50);
	}

	document.addEventListener('click', async function (e) {
		const btn = e.target.closest('#adicionar-pagamento');
		if (!btn) return;

		if (TEF_LIBERAR_CLICK_PADRAO) {
			return;
		}

		if (!isTefSelecionado()) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();
		if (e.stopImmediatePropagation) e.stopImmediatePropagation();

		if (TEF_PROCESSANDO) {
			return;
		}

		TEF_PROCESSANDO = true;
		btn.disabled = true;

		try {
			await processarPagamentoTefAntesDeAdicionar();
			religarClickOriginalAdicionar();
		} catch (err) {
			const msg = err && err.message ? err.message : 'Falha ao processar TEF.';
			tefErro(msg);
		} finally {
			TEF_PROCESSANDO = false;
			btn.disabled = false;
		}
	}, true);

	$(document).on('change', '#tipo_pagamento_atual', async function () {
		if ($(this).val() !== TEF_OPTION_CODE) return;

		try {
			if (!TEF_CLIENT) {
				await inicializarTEF();
			}

			const agenteOk = TEF_CLIENT ? await TEF_CLIENT.verificarAgente() : false;
			if (!agenteOk) {
				desabilitarOpcaoTef();
				tefAviso('O agente CliSiTef não está disponível. Escolha outra forma de pagamento.');
			}
		} catch (e) {
			desabilitarOpcaoTef();
		}
	});

	$(document).on('click', '#tef-cancelar', function () {
		if (TEF_CLIENT) {
			TEF_CLIENT.requestCancel();
		}
	});

	$(document).ready(function () {
		inicializarTEF();
	});

	window.TEF4 = {
		get client() {
			return TEF_CLIENT;
		},
		get ultimoRetorno() {
			return TEF_ULTIMO_RETORNO;
		},
		inicializarTEF,
		processarPagamentoTefAntesDeAdicionar,
		desabilitarOpcaoTef,
		habilitarOpcaoTef
	};
})();