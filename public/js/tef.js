
class CliSiTefWeb {
    constructor(config = {}) {
        this.sessionIdBase = null;

        // Configuração padrão
        this.config = {
            agenteUrl: config.agenteUrl || 'https://127.0.0.1:443',
            sitefIp: config.sitefIp || '127.0.0.1',
            storeId: config.storeId || '00000000',
            terminalId: config.terminalId || 'REST0001',
            ...config
        };

        this.agentPrefix = '/agente/clisitef';
        this.serverBase = '';

        // Estado da transação atual
        this.currentSession = null;
        this.transcript = [];
        this.comprovantes = { loja: [], cliente: [] };

        // Callbacks para UI
        this.onMessage = null;
        this.onInput = null;
        this.onProgress = null;
        this.onMenu = null; // NOVO: callback para menu

        // Controle de loop
        this.loopCount = 0;
        this.MAX_LOOPS = 500;
        this.cancelRequested = false;

        // Timeout da transação (3 minutos)
        this.TIMEOUT_MS = 180000;
        this.transactionStartTime = null;

        this.tipoTransacaoSelecionado = null;
    }

    /**
     * Configura callbacks de UI
     */
     setCallbacks({ onMessage, onInput, onProgress, onMenu }) {
        this.onMessage = onMessage;
        this.onInput = onInput;
        this.onProgress = onProgress;
        this.onMenu = onMenu;
    }

    /**
     * Requisição POST para o Agente com logs detalhados
     */
     async postAgent(path, formObj) {
        const body = new URLSearchParams();
        Object.keys(formObj || {}).forEach(k => {
            if (formObj[k] !== undefined && formObj[k] !== null) {
                body.append(k, String(formObj[k]));
            }
        });

        // console.log(`[TEF] POST ${path}`, formObj);

        const res = await fetch(this.config.agenteUrl + this.agentPrefix + path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body
        });

        const json = await res.json();
        // console.log(`[TEF] Resposta ${path}:`, json);

        if(json.data){
            if (json.data && json.data.includes('SELECIONADO')) {
                selecionado = 1
                tefLog(json.data)
            }



            // if (json.data && json.data.includes('Aguardando inserir ou aproximar')) {
            //     tefLog(json.data)
            // }
        }

        if (!res.ok) {
            console.error(`[TEF] HTTP ${res.status}`, json);
            throw new Error(`AgenteCliSiTef HTTP ${res.status}: ${json.serviceMessage || 'Erro desconhecido'}`);
        }

        return json;
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

    async verificarAgente() {
        try {
            const res = await fetch(this.config.agenteUrl + this.agentPrefix + '/state', {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            return res.ok;
        } catch (e) {
            console.error('[TEF] Erro ao verificar agente:', e);
            return false;
        }
    }

    async verificarPinpad() {
        try {
            const sid = await this.garantirSessao();
            const res = await this.postAgent('/pinpad/isPresent', { sessionId: sid });
            if (res.serviceStatus === 0) {
                return true;
            }

            if (res.serviceMessage && res.serviceMessage.toLowerCase().includes('sessao nao criada previamente')) {
                console.warn('[TEF] Sessão não criada previamente. Resetando e tentando novamente...');
                await this.resetarPinPad();
                const sid2 = await this.garantirSessao();
                const res2 = await this.postAgent('/pinpad/isPresent', { sessionId: sid2 });
                return res2.serviceStatus === 0;
            }

            return false;
        } catch (e) {
            console.error('[TEF] Erro ao verificar PinPad:', e);
            return false;
        }
    }

    async startTransaction(params) {
        return await this.postAgent('/startTransaction', params);
    }

    async continueTransaction(params) {
        return await this.postAgent('/continueTransaction', params);
    }

    async finishTransaction(params) {
        return await this.postAgent('/finishTransaction', params);
    }

    /**
     * Força finalização da sessão e reseta o PinPad
     * Útil quando o PinPad fica travado
     */
     async resetarPinPad() {
        // console.log('[TEF] ====== RESETANDO PINPAD ======');

        try {
            // 1. Tenta fechar o PinPad se houver sessão
            if (this.sessionIdBase) {
                try {
                    await this.postAgent('/pinpad/close', { sessionId: this.sessionIdBase });
                    // console.log('[TEF] PinPad fechado');
                } catch (e) {
                    console.warn('[TEF] Erro ao fechar PinPad (ignorado):', e.message);
                }
            }

            // 2. Força DELETE da sessão via fetch direto
            const deleteUrl = this.config.agenteUrl + this.agentPrefix + '/session';
            // console.log('[TEF] Deletando sessão:', deleteUrl);

            const res = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json().catch(() => ({}));
            // console.log('[TEF] Resposta DELETE session:', json);

            // 3. Limpa estado interno
            this.sessionIdBase = null;
            this.currentSession = null;
            this.cancelRequested = false;
            this.loopCount = 0;

            // console.log('[TEF] ====== PINPAD RESETADO ======');
            return { success: true, message: 'PinPad resetado com sucesso' };

        } catch (error) {
            console.error('[TEF] Erro ao resetar PinPad:', error);

            // Limpa estado mesmo com erro
            this.sessionIdBase = null;
            this.currentSession = null;

            return { success: false, message: error.message };
        }
    }

    gerarNumeroCupom() {
        return String(Date.now()).slice(-6);
    }

    formatarData() {
        const d = new Date();
        return d.getFullYear().toString() +
        String(d.getMonth() + 1).padStart(2, '0') +
        String(d.getDate()).padStart(2, '0');
    }

    formatarHora() {
        const d = new Date();
        return String(d.getHours()).padStart(2, '0') +
        String(d.getMinutes()).padStart(2, '0') +
        String(d.getSeconds()).padStart(2, '0');
    }

    showMessage(msg) {
        // console.log('[TEF] Mensagem:', msg);
        if (this.onMessage) {
            this.onMessage(msg);
        }
    }

    mostrarBannerDebitoTarja() {
        // Remove banner anterior
        $('#banner-debito-tarja').remove();

        const html = `
        <div id="banner-debito-tarja" class="alert alert-warning text-center" 
        style="position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); 
        z-index: 99999; width: 90%; max-width: 500px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
        <strong>📌 Cartão Dupla Função (Débito):</strong><br>
        1º INSIRA O CHIP (vai dar erro) → 2º PASSE NA TARJA
        </div>`;

        $('body').append(html);
    }

    ocultarBannerDebitoTarja() {
        $('#banner-debito-tarja').remove();
    }

    /**
     * Solicita input do operador
     */
     async requestInput(label, maxLength, minLength = 0) {
        // console.log(`[TEF] Solicitando input: "${label}" (max: ${maxLength}, min: ${minLength})`);

        if (this.onInput) {
            const resultado = await this.onInput(label, maxLength, minLength);
            // console.log(`[TEF] requestInput retornou:`, resultado);
            return resultado;
        }

        // Fallback para prompt nativo
        let input = prompt(label);
        if (input === null) return null;
        return String(input).slice(0, maxLength);
    }

    /**
     * Exibe menu de opções e retorna a escolha
     */
     async requestMenu(titulo, opcoes) {
        // console.log(`[TEF] Menu: "${titulo}"`, opcoes);

        if (this.onMenu) {
            return await this.onMenu(titulo, opcoes);
        }

        // Fallback: mostra as opções em um prompt
        const opcoesTexto = opcoes.map((op, i) => `${i}: ${op}`).join('\n');
        let escolha = prompt(`${titulo}\n\n${opcoesTexto}\n\nDigite o número da opção:`);

        if (escolha === null) return null;
        return String(escolha);
    }

    /**
     * Processa a resposta do continueTransaction baseado no commandId
     */
     async processarComando(contResp, sessionId) {
        const { commandId, data, fieldId, fieldMaxLength, fieldMinLength } = contResp;

        if ((fieldId === 121 || fieldId === 122) && data && String(data).trim() !== '') {
            const texto = String(data);

            // Se quiser guardar como linhas:
            const linhas = texto.split('\n').map(l => l.replace(/\r/g, '')).filter(l => l.trim() !== '');

            if (fieldId === 121) this.comprovantes.cliente.push(...linhas);
            if (fieldId === 122) this.comprovantes.loja.push(...linhas);

            // console.log('[TEF] ✅ Comprovante capturado:', fieldId === 121 ? 'CLIENTE' : 'LOJA', linhas);
        }


        // ✅ Verificar se há erro do PinPad em qualquer resposta
        if (data && typeof data === 'string') {
            const dataLower = data.toLowerCase();
            if (dataLower.includes('erro') || data.match(/^\d+\s*-\s*erro/i)) {
                console.error('[TEF] ❌ Erro detectado no PinPad:', data);
                this.showMessage('Erro no PinPad: ' + data);
                return null;  // Força cancelamento
            }
        }

        switch (commandId) {
            case 0:
                // Fim do processamento - não faz nada, o loop vai terminar
                // console.log('[TEF] commandId 0: Fim do processamento');
                return '';

                case 1:
                case 2: {
                    if (data) {
                        this.lastMsg = String(data);

                    // ✅ NOVO: Detecta cartão dupla função
                    const dataLower = data.toLowerCase();

                    if (dataLower.includes('mastercard') ||
                        dataLower.includes('visa') && !dataLower.includes('electron') && !dataLower.includes('plus')) {
                        this.cartaoDuplaFuncao = true;
                    // console.log('[TEF] Detectado cartão dupla função');
                }

                if (dataLower.includes('moeda') || dataLower.includes('escolher pagar')) {
                    this.dccDetectado = true;
                    // console.log('[TEF] DCC detectado - será respondido automaticamente');
                }

                this.showMessage(data);
            }
            return '';
        }
        case 3:
                // Mensagem no PinPad
                // console.log('[TEF] PinPad display:', data);
                if (data) {
                    const dataLower = data.toLowerCase();

                    // Detecta erro de modo inválido (chip não aceito para débito)
                    if (data.includes('70') && dataLower.includes('modo inv')) {
                        this.showMessage('✅ Agora SIM! PASSE O CARTÃO NA TARJA (leitora lateral).');
                    }
                    // Detecta mensagem de retirar cartão
                    else if (dataLower.includes('retire') && dataLower.includes('cartao')) {
                        this.showMessage('⚠️ Retire o cartão e PASSE NA TARJA (leitora lateral).');
                    }
                    // Detecta mensagem de cartão com chip pedindo inserção
                    else if (dataLower.includes('cartao com chip') && dataLower.includes('insira')) {
                        this.showMessage('⚠️ INSIRA O CHIP primeiro! (vai dar erro, depois passe na TARJA)');
                    }
                    // Detecta mensagem inicial de "Aproxime, insira ou passe" em transação débito
                    else if (dataLower.includes('aproxime') && dataLower.includes('insira') &&
                        dataLower.includes('passe') && this.flow?.tipo === 'debito') {
                        // Se já teve erro antes, orienta a passar na tarja
                    if (this.erroAproximacao) {
                        this.showMessage('⚠️ Agora PASSE NA TARJA (leitora lateral do PinPad)');
                    } else {
                        this.showMessage('📌 Débito com cartão dupla função? INSIRA O CHIP primeiro!');
                    }
                }
                else {
                    this.showMessage(data);
                }
            }
            return '';
            case 4:
                // Mensagem no display do PinPad - apenas log
                // console.log('[TEF] PinPad display:', data);
                return '';

                case 11: {
                    // console.log('[TEF] commandId 11: Confirmação de mensagem', { data, fieldId });

                    const msg = (data && data.trim()) ? data : this.lastMsg;

                    if (msg) {
                        const lower = msg.toLowerCase();
                        if (lower.includes('moeda') ||
                            lower.includes('pais') ||
                            lower.includes('currency') ||
                            lower.includes('conversion') ||
                            lower.includes('escolher pagar')) {
                            // console.log('[TEF] DCC/moeda detectado - respondendo 0 automaticamente');
                        return '0';
                    }
                }

                // Se for confirmação simples (data vazio), confirma automaticamente
                if (!data || !data.trim()) {
                    // console.log('[TEF] Confirmação vazia - respondendo 0');
                    return '0';
                }

                // Confirmação de taxa para cartão dupla função
                if (msg && msg.toLowerCase().includes('taxa') && this.cartaoDuplaFuncao) {
                    // console.log('[TEF] Confirmação de taxa no pinpad - respondendo 0');
                    return '0'; // Responde automaticamente ao invés de pedir pro cliente
                }

                return '';
            }

            case 13:
                // Aguarda confirmação de tecla
                // console.log('[TEF] commandId 13: Aguardando confirmação');
                // Envia string vazia para confirmar
                return '';

                case 20: {
                // Solicita dado do operador
                if (data) this.showMessage(data);

                if (data) {
                    const lower = data.toLowerCase();
                    if (lower.includes('conf.reimpressao') || lower.includes('conf.reimp') ||
                        lower.includes('confirma reimp') || lower.includes('reimpressao')) {
                        // console.log('[TEF] Respondendo automaticamente (confirmação reimpressão): 1');
                    return '0';
                }
            }


            if (this.flow && this.flow.tipo === 'debito' && data) {
                const lower = data.toLowerCase();

                    // ✅ NOVO: Se for cartão dupla função, NÃO responde automaticamente
                    // Deixa o cliente interagir no pinpad
                    if (this.cartaoDuplaFuncao) {
                        // console.log('[TEF] Cartão dupla função - aguardando interação do cliente');

                        // Se o menu está no pinpad (fieldMaxLength = 0), apenas aguarda
                        if (!fieldMaxLength || fieldMaxLength === 0) {
                            return '';
                        }

                        // Se precisa de input do operador, pede via modal
                        const input = await this.requestInput(data || 'Selecione a opção', fieldMaxLength, fieldMinLength || 0);
                        return input === null ? null : input;
                    }

                    // Taxa de embarque/serviço - responde 0
                    if (lower.includes('taxa')) {
                        const zeros = '0'.padStart(fieldMaxLength || 1, '0');
                        // console.log(`[TEF] Respondendo automaticamente (taxa débito): ${zeros}`);
                        return zeros;
                    }

                    // À vista - responde 1 (débito é sempre à vista)
                    if (lower.includes('vista')) {
                        const resposta = fieldMaxLength === 2 ? '01' : '1';
                        // console.log(`[TEF] Respondendo automaticamente (à vista débito): ${resposta}`);
                        return resposta;
                    }

                    // Menu de parcelamento para débito - sempre à vista (opção 1)
                    if (lower.includes('parcel')) {
                        const resposta = fieldMaxLength === 2 ? '01' : '1';
                        // console.log(`[TEF] Respondendo automaticamente (parcelamento débito = à vista): ${resposta}`);
                        return resposta;
                    }
                }

                // ✅ Respostas automáticas para CRÉDITO
                if (this.flow && this.flow.tipo === 'credito' && data) {
                    const lower = data.toLowerCase();

                    // Taxa de embarque/serviço - responde 0
                    if (lower.includes('taxa')) {
                        const zeros = '0'.padStart(fieldMaxLength || 1, '0');
                        // console.log(`[TEF] Respondendo automaticamente (taxa crédito): ${zeros}`);
                        return zeros;
                    }
                }

                // Verifica se é uma pergunta de confirmação (fieldMaxLength = 1)
                if (fieldMaxLength === 1) {
                    const resposta = await this.requestInput(data || 'Confirma? (0=Não, 1=Sim)', 1, 0);
                    if (resposta === null) {
                        // console.log('[TEF] Operador cancelou no input case 20');
                        return null;
                    }
                    return resposta;
                }

                // Input normal - pede ao usuário
                const input20 = await this.requestInput(data || 'Informe o dado solicitado', fieldMaxLength || 20, fieldMinLength || 0);
                if (input20 === null) {
                    // console.log('[TEF] Operador cancelou no input case 20');
                    return null;
                }

                // Pad para 2 dígitos se necessário
                let resultado20 = String(input20).trim();
                if (fieldMaxLength === 2 && resultado20.length === 1 && /^\d$/.test(resultado20)) {
                    resultado20 = '0' + resultado20;
                }

                return resultado20;
            }

            case 21: {
                // Solicita dado do cliente
                if (data) this.showMessage(data);

                // ✅ Resposta automática para CANCELAMENTO (tipo de cancelamento)
                if (this.dadosCancelamento && data) {
                    const lower = data.toLowerCase();

                    // Menu de tipo de cancelamento (Débito, Crédito, etc.)
                    if (lower.includes('cancelamento') && (lower.includes('debito') || lower.includes('credito'))) {
                        const tipo = this.dadosCancelamento.tipo_cancelamento;
                        if (tipo) {
                            const resposta = String(tipo).padStart(fieldMaxLength || 2, '0');
                            // console.log(`[TEF] Respondendo automaticamente (tipo cancelamento): ${resposta}`);
                            return resposta;
                        }
                    }
                }

                if (this.dadosReimpressao && data) {
                    const lower = data.toLowerCase();

                    if (lower.includes('rede') && lower.includes('cielo') && lower.includes('outros') && lower.includes(':')) {
                        const adqTexto = String(this.dadosReimpressao.adquirente || '').toLowerCase();
                        const cod = String(this.dadosReimpressao.adquirenteCodigo || '')
                        .replace(/\D/g, '')
                        .padStart(5, '0');

                        let opcao = '3'; // default = Outros

                        // PRIORIDADE 1: texto salvo no BD ("Redecard", "Cielo", etc)
                        if (adqTexto.includes('rede') || adqTexto.includes('redecard')) {
                            opcao = '1'; // Rede
                        } else if (adqTexto.includes('cielo')) {
                            opcao = '2'; // Cielo
                        } else {
                            // PRIORIDADE 2: fallback por código numérico
                            if (cod === '00005') opcao = '1';  // 0005 = Rede (confirmado pelo seu BD)
                            // outros códigos se necessário...
                        }

                        const resp = opcao.padStart(fieldMaxLength || 2, '0');
                        // console.log(`[TEF] Menu Rede/Cielo/Outros => ${resp} (texto=${adqTexto}, cod=${cod})`);
                        return resp;
                    }
                }


                // ✅ Respostas automáticas para DÉBITO (sempre à vista)
                if (this.flow && this.flow.tipo === 'debito' && data) {
                    const lower = data.toLowerCase();

                    // Taxa de embarque/serviço - responde 0
                    if (lower.includes('taxa')) {
                        const zeros = '0'.padStart(fieldMaxLength || 1, '0');
                        // console.log(`[TEF] Respondendo automaticamente (taxa débito case 21): ${zeros}`);
                        return zeros;
                    }

                    // Menu "à vista / saque / CDC" para débito - sempre à vista (opção 1)
                    if (lower.includes('vista') || lower.includes('saque') || lower.includes('cdc')) {
                        const resposta = fieldMaxLength === 2 ? '01' : '1';
                        // console.log(`[TEF] Respondendo automaticamente (à vista débito case 21): ${resposta}`);
                        return resposta;
                    }

                    // Menu de parcelamento para débito - sempre à vista (opção 1)
                    if (lower.includes('parcel')) {
                        const resposta = fieldMaxLength === 2 ? '01' : '1';
                        // console.log(`[TEF] Respondendo automaticamente (parcelamento débito case 21 = à vista): ${resposta}`);
                        return resposta;
                    }
                }

                // ✅ Respostas automáticas para CRÉDITO
                if (this.flow && this.flow.tipo === 'credito' && data) {
                    const lower = data.toLowerCase();

                    // Taxa de embarque/serviço - responde 0
                    if (lower.includes('taxa')) {
                        const zeros = '0'.padStart(fieldMaxLength || 1, '0');
                        // console.log(`[TEF] Respondendo automaticamente (taxa crédito case 21): ${zeros}`);
                        return zeros;
                    }

                    // Menu "à vista / parcelado" vindo do SiTef - responde automaticamente
                    if (lower.includes('vista') && lower.includes('parcel')) {
                        let resposta;
                        if (this.flow.modo === 'avista') {
                            resposta = '1'; // À Vista
                        } else if (this.flow.modo === 'parcelado_loja') {
                            resposta = '2'; // Parcelado Estabelecimento
                        } else if (this.flow.modo === 'parcelado_admin') {
                            resposta = '3'; // Parcelado Administradora
                        }

                        if (resposta) {
                            // Pad para 2 dígitos se necessário
                            if (fieldMaxLength === 2 && resposta.length === 1) {
                                resposta = '0' + resposta;
                            }
                            // console.log(`[TEF] Respondendo automaticamente (parcelamento crédito): ${resposta}`);
                            return resposta;
                        }
                    }

                    // SiTef pedindo número de parcelas - responde automaticamente
                    if (lower.includes('parcela') && this.flow.parcelas && this.flow.parcelas > 1) {
                        let parcelas = String(this.flow.parcelas);
                        // Pad para 2 dígitos se necessário
                        if (fieldMaxLength === 2 && parcelas.length === 1) {
                            parcelas = '0' + parcelas;
                        }
                        // console.log(`[TEF] Respondendo automaticamente (parcelas crédito): ${parcelas}`);
                        return parcelas;
                    }
                }

                // Verifica se é uma pergunta de confirmação (fieldMaxLength = 1)
                if (fieldMaxLength === 1) {
                    const resposta = await this.requestInput(data || 'Confirma? (0=Não, 1=Sim)', 1, 0);
                    if (resposta === null) {
                        // console.log('[TEF] Operador cancelou no input case 21');
                        return null;
                    }
                    return resposta;
                }

                // Input normal - pede ao usuário
                const input21 = await this.requestInput(data || 'Informe o dado solicitado', fieldMaxLength || 20, fieldMinLength || 0);
                if (input21 === null) {
                    // console.log('[TEF] Operador cancelou no input case 21');
                    return null;
                }

                // Pad para 2 dígitos se necessário (evita "voltar pro menu")
                let resultado21 = String(input21).trim();
                if (fieldMaxLength === 2 && resultado21.length === 1 && /^\d$/.test(resultado21)) {
                    resultado21 = '0' + resultado21;
                }
                return resultado21;
            }

            case 22:
            // console.log('[TEF] commandId 22: Aguardando PinPad...', data);

            if (data) {
                const lower = String(data).toLowerCase();

                    // Detecta erros de aproximação/chip inválido
                    if ((lower.includes('aid') && lower.includes('inval')) ||
                        (lower.includes('modo') && lower.includes('inv')) ||
                        lower.includes('70 - modo') ||
                        (lower.includes('contactless') && lower.includes('negado')) ||
                        (lower.includes('aproximacao') && (lower.includes('erro') || lower.includes('negad'))) ||
                        (lower.includes('chip') && lower.includes('negado'))) {

                        this.lastPinpadHint = 'AID_CHIP_INVALIDO';
                    this.erroAproximacao = true;

                        // Mostra mensagem com instrução clara
                        this.showMessage('⚠️ Erro detectado! Aguarde e PASSE NA TARJA (leitora lateral).');
                    }
                    // Detecta mensagem de "Cartao com chip. Insira o cartao"
                    else if (lower.includes('cartao com chip') && lower.includes('insira')) {
                        this.showMessage('⚠️ INSIRA O CHIP primeiro! (vai dar erro, depois passe na TARJA)');
                    }
                    else {
                        this.showMessage(data);
                    }
                }
                return '';

                case 23: {
                // Menu de opções
                // console.log('[TEF] commandId 23: Menu de opções', { data, fieldMaxLength });

                // Se fieldMaxLength = 0 e data vazio, o menu está no PinPad
                if (!fieldMaxLength || fieldMaxLength === 0) {
                    // console.log('[TEF] Menu exibido no PinPad - aguardando cliente...');
                    return '';
                }

                // ✅ Respostas automáticas para DÉBITO (sempre à vista)
                if (this.flow && this.flow.tipo === 'debito' && data) {
                    const lower = data.toLowerCase();

                    // Menu "à vista / saque / CDC" para débito - sempre à vista (opção 1)
                    if (lower.includes('vista') || lower.includes('saque') || lower.includes('cdc')) {
                        const resposta = fieldMaxLength === 2 ? '01' : '1';
                        // console.log(`[TEF] Respondendo automaticamente (menu débito = à vista): ${resposta}`);
                        return resposta;
                    }

                    // Menu de parcelamento para débito - sempre à vista (opção 1)
                    if (lower.includes('parcel')) {
                        const resposta = fieldMaxLength === 2 ? '01' : '1';
                        // console.log(`[TEF] Respondendo automaticamente (menu parcelamento débito = à vista): ${resposta}`);
                        return resposta;
                    }
                }

                // ✅ Respostas automáticas para CRÉDITO
                if (this.flow && this.flow.tipo === 'credito' && data) {
                    const lower = data.toLowerCase();

                    if (lower.includes('vista') && lower.includes('parcel')) {
                        let resposta;
                        if (this.flow.modo === 'avista') resposta = '1';
                        else if (this.flow.modo === 'parcelado_loja') resposta = '2';
                        else if (this.flow.modo === 'parcelado_admin') resposta = '3';

                        if (resposta) {
                            if (fieldMaxLength === 2 && resposta.length === 1) resposta = '0' + resposta;
                            // console.log(`[TEF] Respondendo automaticamente (menu parcelamento crédito): ${resposta}`);
                            return resposta;
                        }
                    }
                }

                // Se tem fieldMaxLength > 0, precisa de input do operador
                if (data) {
                    const opcao = await this.requestInput(data, fieldMaxLength, 0);
                    if (opcao === null) return null;
                    return opcao;
                }
                return '';
            }

            case 30: {

                // console.log(`[TEF] case 30 - fieldId: ${fieldId}, data: "${data}", max: ${fieldMaxLength}, min: ${fieldMinLength}`);

                // ✅ SUPERVISOR - responde SEMPRE com string vazia (opcional, não obrigatório)
                if (fieldId === 500 || (data && data.toLowerCase().includes('supervisor'))) {
                    // console.log('[TEF] Supervisor solicitado - respondendo com string vazia');
                    return '';  // String vazia = sem código de supervisor
                }

                // console.log(`[TEF] Solicitando input: "${data}" (max: ${fieldMaxLength}, min: ${fieldMinLength})`);

                // ✅ NOVO: Resposta automática para CANCELAMENTO
                if (this.dadosCancelamento) {
                    // Data da transação (fieldId 515) - formato DDMMAAAA
                    if (fieldId === 515 && this.dadosCancelamento.data_sitef) {
                        // Converte de YYYYMMDD para DDMMAAAA
                        const dataSitef = this.dadosCancelamento.data_sitef; // "20260121"
                        const dataFormatada = dataSitef.slice(6, 8) + dataSitef.slice(4, 6) + dataSitef.slice(0, 4); // "21012026"
                        // console.log(`[TEF] Respondendo automaticamente (data cancelamento): ${dataFormatada}`);
                        return dataFormatada;
                    }

                    // NSU da transação original (fieldId pode variar - verificar nos próximos logs)
                    if ((fieldId === 516 || fieldId === 133 || data.toLowerCase().includes('nsu')) && this.dadosCancelamento.nsu) {
                        // console.log(`[TEF] Respondendo automaticamente (NSU cancelamento): ${this.dadosCancelamento.nsu}`);
                        return this.dadosCancelamento.nsu;
                    }
                }

                if (this.dadosReimpressao) {
                    const lower = (data || '').toLowerCase();

                    // ✅ NSU - detecta por texto primeiro (mais confiável que fieldId)
                    if ((lower.includes('nsu') || lower.includes('documento') || lower.includes('numero')) && this.dadosReimpressao.nsu) {
                        // console.log(`[TEF] Respondendo automaticamente (NSU reimpressão): ${this.dadosReimpressao.nsu}`);
                        return this.dadosReimpressao.nsu;
                    }

                    // ✅ Data - detecta por texto
                    if (lower.includes('data') && this.dadosReimpressao.data_sitef) {
                        const ymd = String(this.dadosReimpressao.data_sitef); // YYYYMMDD
                        const ddmm = ymd.slice(6, 8) + ymd.slice(4, 6);       // DDMM
                        const ddmmaaaa = ddmm + ymd.slice(0, 4);              // DDMMAAAA

                        // Se o pinpad pediu ddmm (4)
                        if (fieldMaxLength === 4) return ddmm;

                        // Se pediu ddmmaaaa (8)
                        if (fieldMaxLength === 8) return ddmmaaaa;

                        // fallback: corta no tamanho pedido
                        return ddmmaaaa.slice(0, fieldMaxLength || ddmmaaaa.length);
                    }

                    // ✅ Supervisor (opcional)
                    // if (fieldId === 500 || lower.includes('supervisor')) {
                    //     const codigoSupervisor = this.dadosReimpressao.codigo_supervisor || '';
                    //     console.log(`[TEF] Respondendo automaticamente (supervisor reimpressão): "${codigoSupervisor}"`);
                    //     return codigoSupervisor;
                    // }
                }


                if (fieldMaxLength && fieldMaxLength > 0) {
                    // ✅ Se já temos parcelas definidas, responde automaticamente
                    if (this.flow && this.flow.parcelas && this.flow.parcelas > 1) {
                        let parcelas = String(this.flow.parcelas);
                        if (fieldMaxLength === 2 && parcelas.length === 1) parcelas = '0' + parcelas;
                        // console.log(`[TEF] Respondendo automaticamente (case 30 parcelas): ${parcelas}`);
                        return parcelas;
                    }

                    const input30 = await this.requestInput(data || 'Informe o número de parcelas', fieldMaxLength, fieldMinLength || 0);
                    return input30 === null ? null : input30;
                }
                // Caso contrário, é header de comprovante
                // console.log('[TEF] Comprovante - Header:', data);
                return '';
            }

            case 31:
                // Linha de comprovante
                if (data) {
                    // fieldId pode indicar se é via loja ou cliente
                    this.comprovantes.loja.push(data);
                }
                return '';

                case 32:
                // Imprimir comprovante
                // console.log('[TEF] Comprovante pronto para impressão');
                return '';

                case 34: {
                // ✅ Resposta automática para CANCELAMENTO (valor da transação)

                if (this.dadosReimpressao && fieldId === 601 && this.dadosReimpressao.valor_centavos != null) {
                    const v = String(this.dadosReimpressao.valor_centavos).replace(/\D/g, '');
                    // normalmente pede sem pontuação; respeita fieldMaxLength
                    return fieldMaxLength ? v.padStart(fieldMaxLength, '0') : v;
                }

                if (fieldId === 146 && this.dadosCancelamento && this.dadosCancelamento.valor_centavos) {
                    // O SiTef espera o valor em centavos sem pontuação
                    const valorStr = String(this.dadosCancelamento.valor_centavos);
                    // console.log(`[TEF] Respondendo automaticamente (valor cancelamento): ${valorStr}`);
                    return valorStr;
                }

                if (fieldId === 504 && fieldMaxLength && fieldMaxLength > 0) {
                    // muitos ambientes aceitam "0" como taxa
                    return '0';
                }
                if (fieldId === 130 && fieldMaxLength && fieldMaxLength > 0) {
                    // Se contém \n, é um menu - seleciona opção 1 (VISA ELECTRON, sem saque)
                    if (data && data.includes('\n')) {
                        // console.log('[TEF] Menu VISA ELECTRON/SAQUE - selecionando opção 1 (sem saque)');
                        return '1';  // Seleciona primeira opção (débito normal)
                    }
                    // Se não tem \n, é pergunta de valor de saque
                    // console.log('[TEF] Pergunta de valor de saque - respondendo 0');
                    return '0';
                }
                // Confirmar captura de assinatura
                return '';
            }

            default:
            console.warn(`[TEF] commandId ${commandId} não tratado`, contResp);
            return '';
        }
    }

    
    requestCancel() {
        this.cancelRequested = true;
        // console.log('[TEF] Cancelamento solicitado');
    }

    /**
    * Fluxo completo de transação TEF
    */
    async processarTransacao(valorCentavos, functionId = 0) {
        // Reset de estado
        this.transcript = [];
        this.comprovantes = { loja: [], cliente: [] };
        this.loopCount = 0;
        this.cancelRequested = false;
        this.transactionStartTime = Date.now();
        this.tipoTransacaoSelecionado = null;

        const taxInvoiceNumber = this.gerarNumeroCupom();
        const taxInvoiceDate = this.formatarData();
        const taxInvoiceTime = this.formatarHora();

        // console.log('[TEF] ====== INICIANDO TRANSAÇÃO ======');
        // console.log('[TEF] Valor (centavos):', valorCentavos);
        // console.log('[TEF] functionId:', functionId);
        // console.log('[TEF] Cupom:', taxInvoiceNumber);
        // console.log('[TEF] tipoTransacaoSelecionado:', this.tipoTransacaoSelecionado);
        try {
            // Verifica agente
            this.showMessage('Verificando conexão com o agente...');
            const agenteOk = await this.verificarAgente();

            // ✅ Se o agente não estiver "PRONTO", reseta a sessão antes de começar
            try {
                const st = await fetch(this.config.agenteUrl + this.agentPrefix + '/state', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());

                // serviceState 1 = pronto (no seu curl era 1)
                if (st && st.serviceStatus === 0 && st.serviceState !== 1) {
                    console.warn('[TEF] Agente não está pronto, resetando sessão antes de iniciar...', st);
                    await this.resetarPinPad();
                }
            } catch (e) {
                console.warn('[TEF] Não consegui ler state, seguindo mesmo assim:', e.message);
            }


            if (!agenteOk) {
                $('#tipo_pagamento_atual option[value="00"]').remove();
                throw new Error('AgenteCliSiTef não está disponível. Verifique se o agente está rodando.');
            }

            // Verifica pinpad
            this.showMessage('Verificando PinPad...');
            const pinpadOk = await this.verificarPinpad();
            if (!pinpadOk) {
                throw new Error('PinPad não encontrado ou não conectado');
            }

            // 1) START TRANSACTION
            this.showMessage('Iniciando transação...');
            const startResp = await this.startTransaction({
                sitefIp: this.config.sitefIp,
                storeId: this.config.storeId,
                terminalId: this.config.terminalId,
                functionId: functionId,
                trnAmount: this.formatarValorReais(valorCentavos),
                taxInvoiceNumber,
                taxInvoiceDate,
                taxInvoiceTime,
                cashierOperator: 'CAIXA',
                trnAdditionalParameters: ''
            });

            this.transcript.push({ step: 'startTransaction', resp: startResp });

            if (startResp.serviceStatus !== 0) {
                throw new Error(startResp.serviceMessage || `Erro ao iniciar: serviceStatus=${startResp.serviceStatus}`);
            }

            const sessionId = startResp.sessionId;
            this.currentSession = sessionId;
            let clisitefStatus = startResp.clisitefStatus;

            // console.log('[TEF] Sessão iniciada:', sessionId);
            // console.log('[TEF] clisitefStatus inicial:', clisitefStatus);

            if (clisitefStatus !== 10000) {
                throw new Error(`Erro ao iniciar transação (clisitefStatus=${clisitefStatus})`);
            }

            // 2) CONTINUE LOOP
            let lastData = '';
            let lastCommandId = -1;

            while (clisitefStatus === 10000) {
                this.loopCount++;

                // Proteção contra loop infinito (por contagem)
                if (this.loopCount > this.MAX_LOOPS) {
                    console.error('[TEF] TIMEOUT: Excedeu máximo de iterações');
                    throw new Error('Timeout na transação TEF - muitas iterações');
                }

                // ✅ Proteção contra timeout por tempo
                const elapsed = Date.now() - this.transactionStartTime;
                if (elapsed > this.TIMEOUT_MS) {
                    console.error(`[TEF] TIMEOUT: Transação excedeu ${this.TIMEOUT_MS / 1000}s`);
                    throw new Error('Timeout: transação demorou mais de 3 minutos');
                }

                // Verifica se foi solicitado cancelamento
                if (this.cancelRequested) {
                    // console.log('[TEF] Cancelamento solicitado pelo operador');
                    this.cancelRequested = false;
                    // Envia cancelamento
                    await this.continueTransaction({
                        sessionId: sessionId,
                        data: '',
                        continue: -1
                    });
                    this.currentSession = null;
                    console.warn('Operação cancelada pelo operador');
                    $('#modal-tef').modal('hide')
                }

                // console.log(`[TEF] Loop #${this.loopCount}: Enviando continueTransaction com data="${lastData}"`);

                const contResp = await this.continueTransaction({
                    sessionId: sessionId,
                    data: lastData,
                    continue: 0
                });

                this.transcript.push({
                    step: 'continueTransaction',
                    loop: this.loopCount,
                    resp: contResp
                });

                clisitefStatus = contResp.clisitefStatus;
                lastCommandId = contResp.commandId;

                // console.log(`[TEF] Loop #${this.loopCount}: Resposta clisitefStatus=${clisitefStatus}, commandId=${lastCommandId}`);

                // Se ainda precisa continuar, processa o comando
                if (clisitefStatus === 10000) {
                    const resposta = await this.processarComando(contResp, sessionId);

                    // Operador cancelou via modal/input
                    if (resposta === null) {
                        // console.log('[TEF] Operador cancelou - enviando cancelamento...');

                        // Envia sinal de cancelamento
                        const cancelResp = await this.continueTransaction({
                            sessionId: sessionId,
                            data: '',
                            continue: -1
                        });
                        // console.log('[TEF] Resposta do cancelamento:', cancelResp);

                        // SÓ chama finishTransaction se o SiTef NÃO encerrou a sessão
                        // clisitefStatus = -2 significa que o SiTef já encerrou a sessão
                        if (cancelResp.clisitefStatus !== -2) {
                            try {
                                await this.finishTransaction({
                                    sessionId: sessionId,
                                    taxInvoiceNumber: taxInvoiceNumber,
                                    taxInvoiceDate: taxInvoiceDate,
                                    taxInvoiceTime: taxInvoiceTime,
                                    confirm: 0
                                });
                                // console.log('[TEF] Sessão finalizada após cancelamento');
                            } catch (finishError) {
                                console.warn('[TEF] finishTransaction falhou (ignorado):', finishError.message);
                            }
                        } else {
                            // console.log('[TEF] Sessão já encerrada pelo SiTef (clisitefStatus=-2), finishTransaction não necessário');
                        }

                        this.currentSession = null;
                        console.warn('Operação cancelada pelo operador');
                    }

                    // ✅ Usa a resposta do processarComando como dado para o próximo continue
                    lastData = (resposta === undefined) ? '' : resposta;
                    // console.log('[TEF] Resposta do processarComando:', resposta);
                    if(resposta == '' && selecionado == 0){
                        tefLog('Aguardando inserir ou aproximar')
                    }

                    // Pequeno delay antes do próximo loop
                    await new Promise(r => setTimeout(r, 100));
                }
            }

            // console.log('[TEF] Loop finalizado. clisitefStatus final:', clisitefStatus);

            // 3) FINISH TRANSACTION
            // Só chama finishTransaction se a transação não foi cancelada/abortada pelo SiTef
            // Status negativos (como -43) indicam que o SiTef já encerrou a sessão
            let finishResp = {};

            if (clisitefStatus >= 0) {
                const confirm = (clisitefStatus === 0) ? 1 : 0;
                this.showMessage(confirm ? 'Confirmando transação...' : 'Finalizando transação...');

                finishResp = await this.finishTransaction({
                    sessionId: sessionId,
                    taxInvoiceNumber: taxInvoiceNumber,
                    taxInvoiceDate: taxInvoiceDate,
                    taxInvoiceTime: taxInvoiceTime,
                    confirm: confirm
                });

                this.transcript.push({ step: 'finishTransaction', resp: finishResp });
            } else {
                console.warn('[TEF] Transação já foi encerrada pelo SiTef (status negativo), pulando finishTransaction');
                try {
                    await this.resetarPinPad();
                } catch (e) {
                    console.warn('[TEF] Falha ao resetar PinPad após erro:', e.message);
                }
                this.showMessage('Transação não concluída');
            }

            this.currentSession = null;

            // Monta resultado
            const resultado = {
                aprovado: (clisitefStatus === 0),
                sessionId: sessionId,
                clisitefStatus: clisitefStatus,
                confirm: confirm,
                loopCount: this.loopCount,

                tef: {
                    sessionId: sessionId,
                    clisitefStatus: clisitefStatus,
                    terminalId: this.config.terminalId,
                    storeId: this.config.storeId,
                    sitefIp: this.config.sitefIp,
                    functionId: functionId,
                    controle: taxInvoiceNumber,
                    nsu: finishResp.nsu || this.extrairCampo('nsu'),
                    codigoAutorizacao: finishResp.authorizationCode || this.extrairCampo('authorizationCode'),
                    bandeira: finishResp.cardBrand || this.extrairCampo('cardBrand'),
                    adquirente: finishResp.acquirer || this.extrairCampo('acquirer'),
                    comprovantes: this.comprovantes,
                    transcript: this.transcript,
                    tipoTransacaoSelecionado: this.tipoTransacaoSelecionado
                },

                mensagem: clisitefStatus === 0
                ? 'Transação aprovada'
                : `Transação não aprovada (código: ${clisitefStatus})`
            };

            // console.log('[TEF] ====== TRANSAÇÃO FINALIZADA ======');
            // console.log('[TEF] Resultado:', resultado);

            if (resultado.mensagem.includes('Transação não aprovada')) {
                tefLog(resultado.mensagem)
                tefStatus('err', resultado.mensagem)
            }

            //salvar log api
            if(resultado.aprovado && resultado.clisitefStatus === 0 && resultado.mensagem == 'Transação aprovada'){
                $('#modal-tef').modal('hide')

                if(consultaPendencia == 1){

                }else{

                    if(TEF_CLIENT.dadosCancelamento == null){

                        swal("Sucesso", "Transação aprovada!", "success")
                        .then(() => {

                            SESSIONIDTEF = resultado.tef.sessionId
                            finalizarVendaModal()

                            TEF_CLIENT.salvarRetornoServidor(resultado.tef)
                            .then(res => {
                            // console.log('[TEF] Retorno salvo:', res)
                        })
                            .catch(err => console.error('[TEF] Erro ao salvar retorno:', err));

                        })
                    }
                }
                consultaPendencia = 0
            }
            return resultado
            this.showMessage(resultado.mensagem);

        } catch (error) {
            console.error('[TEF] ====== ERRO NA TRANSAÇÃO ======');
            console.error('[TEF] Erro:', error.message);
            console.error('[TEF] Transcript:', this.transcript);

            // Tenta cancelar a sessão se ainda estiver ativa
            // ✅ Só tenta se a sessão ainda existir (não foi cancelada antes)
            const sessionToClose = this.currentSession;
            this.currentSession = null;

            if (sessionToClose) {
                try {
                    // console.log('[TEF] Tentando fechar sessão após erro...');
                    await this.continueTransaction({
                        sessionId: sessionToClose,
                        data: '',
                        continue: -1
                    });
                } catch (e) {
                    // ✅ Log mais descritivo
                    console.warn('[TEF] Sessão já foi encerrada ou erro ao fechar:', e.message);
                }
            } else {
                // console.log('[TEF] Sessão já estava encerrada, nada a fazer.');
            }

            throw error;
        }
    }

    /**
     * Formata valor de centavos para reais (ex: 1500 -> "15,00")
     */
     formatarValorReais(valorCentavos) {
        const v = (valorCentavos / 100).toFixed(2);
        return v.replace('.', ',');
    }

    /**
     * Extrai campo do transcript
     */
    // extrairCampo(campo) {
    //     for (let i = this.transcript.length - 1; i >= 0; i--) {
    //         const item = this.transcript[i];
    //         if (item.resp && item.resp[campo]) {
    //             return item.resp[campo];
    //         }
    //     }
    //     return null;
    // }


    // confirmar depois em producao esses campos - em homologacao nao esta vindo
    extrairCampo(campo) {
        const fieldIdMap = {
            'nsu': [121, 122, 131, 134],
            'authorizationCode': [130, 134, 135],
            'cardBrand': [2010, 2011],
            'acquirer': [140, 141, 142]
        };

        const fieldIds = fieldIdMap[campo] || [];

        // Primeiro tenta buscar pelo nome do campo no response
        for (let i = this.transcript.length - 1; i >= 0; i--) {
            const item = this.transcript[i];
            if (item.resp && item.resp[campo]) {
                return item.resp[campo];
            }
        }

        // Depois tenta buscar por fieldId
        for (let i = this.transcript.length - 1; i >= 0; i--) {
            const item = this.transcript[i];
            if (item.resp && fieldIds.includes(item.resp.fieldId) && item.resp.data) {
                return item.resp.data;
            }
        }

        return null;
    }

    async consultarPendenciasTerminal() {
        const resultado = await this.processarTransacao(0, 130);

        const info = this.parsePendenciasFromTranscript(resultado?.tef?.transcript || []);
        return { resultado, ...info };
    }


    parsePendenciasFromTranscript(transcript = []) {
        let totalInformado = null;
        const pendencias = [];
        let atual = null;

        const pushAtual = () => {
            if (!atual) return;
            // Só adiciona se pelo menos tiver cupom+data (mínimo útil)
            if (atual.cupomFiscal || atual.dataFiscal || atual.horaFiscal) {
                pendencias.push(atual);
            }
            atual = null;
        };

        for (const item of transcript) {
            const r = item?.resp;
            if (!r || r.fieldId === undefined || r.fieldId === null) continue;

            const fid = Number(r.fieldId);
            const data = (r.data !== undefined && r.data !== null) ? String(r.data).trim() : '';

            // total de pendências
            if (fid === 210) {
                const n = parseInt(data, 10);
                totalInformado = Number.isFinite(n) ? n : 0;
                continue;
            }

            // início de “bloco” de pendência (cada pendência começa com 160)
            if (fid === 160) {
                pushAtual();
                atual = { cupomFiscal: data || null };
                continue;
            }

            // se por algum motivo vier um campo antes do 160, cria objeto mesmo assim
            if (!atual) atual = {};

            switch (fid) {
                case 161:
                atual.pagamentoIndex = data || null;
                break;
                case 163:
                atual.dataFiscal = data || null;
                break;
                case 164:
                atual.horaFiscal = data || null;
                break;
                case 211:
                atual.funcaoOriginal = data || null;
                break;
                case 1319:
                atual.valorOriginal = data || null;
                break;
                default:

                break;
            }
        }

        pushAtual();

        return {
            totalInformado,
            pendencias,
            totalDetectado: pendencias.length,
            temPendencias: (totalInformado ?? 0) > 0 || pendencias.length > 0
        };
    }
    
    async cancelarTransacao() {
        if (this.currentSession) {
            this.requestCancel();
        }else{
            // console.log("não possui sessão")
            $('#modal-tef').modal('hide')
        }
    }

    getServerBase() {
        if (!this.serverBase) {
            this.serverBase = window.location.origin;
        }
        return this.serverBase;
    }

    async salvarRetornoServidor(tefData) {
        const res = await fetch(this.getServerBase() + '/tef-store-log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: JSON.stringify({
                tef: tefData
            }),
        });

        if (!res.ok) {
            throw new Error(`Erro ao salvar retorno TEF: HTTP ${res.status}`);
        }

        return await res.json();
    }
}

var selecionado = 0
var TEF_CLIENT = null;
var TEF_CONFIG = {
    habilitado: false,
    sitefIp: '',
    storeId: '',
    terminalId: '',
    agenteUrl: 'https://127.0.0.1'
};

$('#tef-cancelar').on('click', async function () {
    TEF_CLIENT.cancelarTransacao()
})

$('.tef-opcao').on('click', async function () {
    resetModalStartTef()
    const btn = $(this);
    if (btn.prop('disabled')) return;

    if (!window.TEF_CONFIG) {
        tefStatus('err', 'TEF não configurado');
        return;
    }
    const tipo = btn.data('tipo');

    let tipoStr = ''
    if(tipo == 'credito'){
        tipoStr = `Tipo de pagamento <strong>Crédito</strong>`
    }else if(tipo == 'debito'){
        tipoStr = `Tipo de pagamento <strong>Débito</strong>`
    }else if(tipo == 'voucher'){
        tipoStr = `Tipo de pagamento <strong>Voucher</strong>`
    }
    tefStatus('info', tipoStr)
    tefLog(tipoStr)
    let total = convertMoedaToFloat($('#painel-total-venda').text())
    processarPagamentoTEF(total, tipo)
    $('.tef-opcao').attr('disabled', 'disabled').addClass('disabled')
    $('#tef-cancelar').attr('disabled', 'disabled').addClass('disabled')

});

function resetModalStartTef(){
    $('.tef-opcao').removeAttr('disabled', 'disabled').removeClass('disabled')
    $('#tef-cancelar').removeAttr('disabled', 'disabled').removeClass('disabled')
}

function tefStatus(tipo, msg) {

    const map = {
        info: 'alert-info',
        warn: 'alert-warning',
        ok: 'alert-success',
        err: 'alert-danger'
    };

    $('#tef-status')
    .removeClass()
    .addClass('alert ' + (map[tipo] || 'alert-secondary'))
    .html(msg);
}

let lastTefMsg = null;
function tefLog(msg) {
    if (msg === lastTefMsg) {
        return;
    }

    lastTefMsg = msg;
    const el = $('#tef-log');

    if (el.text().length > 5000) {
        el.text(el.text().slice(-3000));
    }

    el.append("<br>"+msg);
    el.scrollTop(el[0].scrollHeight);

    if(msg.includes('SELECIONADO')){
        tefLog('Processando pagamento, aguarde!')
    }
}

async function coletarFluxoTEF(valorReais) {
    // 1) Tipo principal: Débito / Crédito / PIX
    // const tipo = await mostrarModalTEFOpcoes(
    //     "1:Cartão Débito;2:Cartão Crédito;3:PIX",
    //     "Selecione o Tipo de Pagamento"
    //     );

    if (tipo === null) return null;

    // Débito
    if (tipo === '1') {
        return { tipo: 'debito', descricao: 'Cartão Débito' };
    }

    // PIX
    if (tipo === '3') {
        return { tipo: 'pix', descricao: 'PIX' };
    }

    // Crédito - precisa escolher forma de parcelamento
    if (tipo === '2') {
        const modo = await mostrarModalTEFOpcoes(
            "1:Crédito à Vista;2:Parcelado pelo Estabelecimento;3:Parcelado pela Administradora",
            "Selecione a Forma de Pagamento"
            );

        if (modo === null) return null;

        // Crédito à Vista
        if (modo === '1') {
            return {
                tipo: 'credito',
                modo: 'avista',
                parcelas: 1,
                descricao: 'Crédito à Vista'
            };
        }

        // Parcelado Loja
        if (modo === '2') {
            const parcelas = await mostrarModalParcelas('Selecione o número de parcelas', 12, valorReais);
            if (parcelas === null) return null;

            return {
                tipo: 'credito',
                modo: 'parcelado_loja',
                parcelas: parseInt(parcelas),
                descricao: `Crédito Parcelado Loja ${parcelas}x`
            };
        }

        // Parcelado Administradora
        if (modo === '3') {
            const parcelas = await mostrarModalParcelas('Selecione o número de parcelas', 12, valorReais);
            if (parcelas === null) return null;

            return {
                tipo: 'credito',
                modo: 'parcelado_admin',
                parcelas: parseInt(parcelas),
                descricao: `Crédito Parcelado Admin ${parcelas}x`
            };
        }
    }

    return null;
}

async function processarPagamentoTEF(valorReais, tipo) {
    console.clear()
    if (!TEF_CLIENT) {
        throw new Error('TEF não está configurado');
    }
    TEF_CLIENT.retryAttempt = 0;
    // 1) Coleta todas as escolhas ANTES de chamar o PinPad
    // const op = await coletarFluxoTEF(valorReais);
    let op = null
    if (tipo === 'credito') {
        const modo = await mostrarModalTEFOpcoes(
            "1:Crédito à Vista;2:Parcelado pelo Estabelecimento;3:Parcelado pela Administradora",
            "Selecione a Forma de Pagamento"
            );


        if (modo === null) op = null;

        // Crédito à Vista
        if (modo === '1') {
            op = {
                tipo: 'credito',
                modo: 'avista',
                parcelas: 1,
                descricao: 'Crédito à Vista'
            };
        }

        // Parcelado Loja
        if (modo === '2') {
            const parcelas = await mostrarModalParcelas('Selecione o número de parcelas', 12, valorReais);
            if (parcelas === null) op = null;

            op = {
                tipo: 'credito',
                modo: 'parcelado_loja',
                parcelas: parseInt(parcelas),
                descricao: `Crédito Parcelado Loja ${parcelas}x`
            };
        }

        // Parcelado Administradora
        if (modo === '3') {
            const parcelas = await mostrarModalParcelas('Selecione o número de parcelas', 12, valorReais);
            if (parcelas === null) op = null;

            op = {
                tipo: 'credito',
                modo: 'parcelado_admin',
                parcelas: parseInt(parcelas),
                descricao: `Crédito Parcelado Admin ${parcelas}x`
            };
        }
    }
    // console.log(op)
    // 2) Mapeia para o functionId correto
    const functionId = mapearFunctionId(tipo);
    // console.log(`[TEF] Fluxo coletado:`, op);
    // console.log(`[TEF] functionId mapeado:`, functionId);

    // 3) Guarda o flow no client para responder automaticamente depois
    TEF_CLIENT.flow = op;

    // 4) Só agora chama o PinPad
    const valorCentavos = Math.round(valorReais * 100);
    return await TEF_CLIENT.processarTransacao(valorCentavos, functionId);
}

function mapearFunctionId(op) {
    if (!op) return 0;

    if (op === 'debito') {
        return 2;
    }
    if (op === 'credito') {
        return 3;
    }
    if (op === 'pix') {
        return 122;
    }

    return 0; // Menu genérico
}

function isMenuOpcoes(label) {
    if (!label) return false;
    // Verifica se tem o padrão "número:texto;número:texto"
    return /\d+:[^;]+;/.test(label) || /\d+:[^;]+$/.test(label);
}

function detectarTipoMenu(label) {
    const lower = label.toLowerCase();
    if (lower.includes('cheque') && lower.includes('debito')) return 'pagamento';
    if (lower.includes('a vista') && lower.includes('parcelado')) return 'parcelamento';
    if (lower.includes('parcela')) return 'parcelas';
    return 'generico';
}
function mostrarModalTEFOpcoes(opcoes, titulo = null) {
    return new Promise((resolve) => {
        // Esconde modal de status para não conflitar
        const modalStatus = $('#modal-tef-status');
        const hadStatus = modalStatus.length > 0 && modalStatus.hasClass('show');
        if (hadStatus) {
            try { document.activeElement && document.activeElement.blur(); } catch (e) { }
            modalStatus.removeAttr('aria-hidden');
            modalStatus.modal('hide');
        }
        const backdropsExistentes = $('.modal-backdrop').length;
        // Detecta o tipo de menu para personalizar
        const tipoMenu = detectarTipoMenu(opcoes);

        // Define título e ícone baseado no tipo
        let tituloModal = titulo;
        let icone = 'la-list';
        let corHeader = 'bg-primary';

        if (!tituloModal) {
            switch (tipoMenu) {
                case 'pagamento':
                tituloModal = 'Selecione o Tipo de Pagamento';
                icone = 'la-credit-card';
                corHeader = 'bg-primary';
                break;
                case 'parcelamento':
                tituloModal = 'Selecione a Forma de Pagamento';
                icone = 'la-calendar';
                corHeader = 'bg-success';
                break;
                case 'parcelas':
                tituloModal = 'Selecione o Número de Parcelas';
                icone = 'la-calculator';
                corHeader = 'bg-info';
                break;
                default:
                tituloModal = 'Selecione uma Opção';
                icone = 'la-hand-pointer';
                corHeader = 'bg-secondary';
            }
        }

        // Parse das opções: "1:Opção A;2:Opção B;..."
        const itens = opcoes.split(';').filter(o => o.trim());

        let botoesHtml = '';
        itens.forEach(item => {
            const partes = item.split(':');
            if (partes.length >= 2) {
                const num = partes[0].trim();
                const nome = partes.slice(1).join(':').trim();

                // Define cor e ícone do botão baseado no conteúdo
                let classe = 'btn-secondary';
                let btnIcone = 'la-circle';
                const nomeLower = nome.toLowerCase();

                // Tipos de pagamento
                if (nomeLower.includes('debito')) { classe = 'btn-primary'; btnIcone = 'la-credit-card'; }
                else if (nomeLower.includes('credito')) { classe = 'btn-success'; btnIcone = 'la-credit-card'; }
                else if (nomeLower.includes('cheque')) { classe = 'btn-warning'; btnIcone = 'la-money-check'; }
                else if (nomeLower.includes('voucher') || nomeLower.includes('vale')) { classe = 'btn-info'; btnIcone = 'la-ticket-alt'; }
                else if (nomeLower.includes('private') || nomeLower.includes('label')) { classe = 'btn-secondary'; btnIcone = 'la-tag'; }

                // Formas de parcelamento
                else if (nomeLower.includes('a vista') || nomeLower.includes('à vista')) { classe = 'btn-success'; btnIcone = 'la-check-circle'; }
                else if (nomeLower.includes('estabelecimento')) { classe = 'btn-primary'; btnIcone = 'la-store'; }
                else if (nomeLower.includes('administradora')) { classe = 'btn-info'; btnIcone = 'la-university'; }
                else if (nomeLower.includes('consulta')) { classe = 'btn-outline-secondary'; btnIcone = 'la-search'; }

                // Número de parcelas
                else if (/^\d+x?$/i.test(nome.trim()) || nomeLower.includes('parcela')) {
                    classe = 'btn-primary';
                    btnIcone = 'la-calculator';
                }

                botoesHtml += `
                <button type="button" class="btn ${classe} btn-lg m-2 btn-tef-opcao" data-valor="${num}" style="width: 400px;">
                <span class="badge bg-dark mr-2" style="font-size: 14px;">${num}</span>
                ${nome}
                </button><br>`;
            }
        });

        const modalHtml = `
        <div class="modal fade" id="modal-tef-opcao" data-backdrop="static" data-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="width: 600px">
        <div class="modal-content">
        <div class="modal-header ${corHeader}">
        <h5 class="modal-title text-white"><i class="la ${icone} mr-2 text-white"></i>${tituloModal}</h5>
        </div>
        <div class="modal-body text-center py-4">
        ${botoesHtml}
        </div>
        <div class="modal-footer justify-content-center flex-column">
        <button type="button" class="btn btn-outline-danger btn-lg" id="btn-tef-opcao-cancelar">
        <i class="la la-times mr-2"></i>Cancelar <small class="text-muted">(ESC)</small>
        </button>
        <small class="text-muted mt-2"><i class="la la-keyboard mr-1"></i>Pressione o número para selecionar</small>
        </div>
        </div>
        </div>
        </div>`;

        // Remove modal anterior se existir
        $('#modal-tef-opcao').remove();
        $('body').append(modalHtml);

        // ✅ Handler de teclado para atalhos numéricos
        const handleKeyPress = (e) => {
            const tecla = e.key;
            // Verifica se é um número (1-9)
            if (/^[1-9]$/.test(tecla)) {
                const botao = $(`.btn-tef-opcao[data-valor="${tecla}"]`);
                if (botao.length) {
                    e.preventDefault();
                    botao.trigger('click');
                }
            }
            // ESC para cancelar
            if (e.key === 'Escape') {
                e.preventDefault();
                $('#btn-tef-opcao-cancelar').trigger('click');
            }
        };

        // Adiciona listener ao abrir modal
        $(document).on('keydown.tefOpcao', handleKeyPress);

        // Função para fechar modal e restaurar status
        const finalizar = (resultado) => {
            // Remove listener de teclado
            $(document).off('keydown.tefOpcao');

            $('#modal-tef-opcao').modal('hide');
            setTimeout(() => {
                $('#modal-tef-opcao').remove();

                // ✅ NOVO: Remove apenas o backdrop que este modal criou
                const backdropsAtuais = $('.modal-backdrop');
                if (backdropsAtuais.length > backdropsExistentes) {
                    backdropsAtuais.last().remove();
                }

                // ✅ MELHORADO: Só restaura o modal de status se não estiver cancelando
                // E se o resultado NÃO for null (usuário selecionou algo)
                if (hadStatus && resultado !== null && $('#modal-tef-status').length > 0) {
                    $('#modal-tef-status').modal('show');
                }

                resolve(resultado);
            }, 400);
        };

        // Evento de clique nos botões
        $('.btn-tef-opcao').on('click', function () {
            const valor = $(this).data('valor');
            const nome = $(this).text().trim();

            // console.log('[TEF] Opção selecionada no modal:', valor);
            finalizar(String(valor));
        });

        // Evento de cancelamento
        $('#btn-tef-opcao-cancelar').on('click', function () {
            // console.log('[TEF] Seleção cancelada');
            resetModalStartTef()
            finalizar(null);
        });

        // Mostra o modal (aguarda o status esconder)
        setTimeout(() => {
            $('#modal-tef-opcao').modal('show');
        }, hadStatus ? 200 : 0);
    });
}

function getTefServerBase() {
    return window.location.origin;
}

async function inicializarTEF() {
    try {
        const baseUrl = getTefServerBase();

        if (!baseUrl) {
            console.warn('[TEF] URL base não encontrada');
            return;
        }

        const empresaId = $('#empresa_id').val();
        const usuarioId = $('#usuario_id').val();

        const res = await fetch(`${baseUrl}/sitef-get-config?empresa_id=${empresaId}&usuario_id=${usuarioId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
        });

        if (res.ok) {
            TEF_CONFIG = await res.json();

            if (TEF_CONFIG.habilitado) {
                TEF_CLIENT = new CliSiTefWeb(TEF_CONFIG);

                // Configura callbacks padrão
                TEF_CLIENT.setCallbacks({
                    onMessage: (msg) => {
                        // console.log('[TEF UI] Mensagem:', msg);
                        // Aqui você pode atualizar um modal ou div na tela
                        if (typeof atualizarMensagemTEF === 'function') {
                            atualizarMensagemTEF(msg);
                        }
                    },
                    onInput: async (label, maxLength, minLength) => {
                        // Usa modal custom para menus; prompt para demais inputs

                        if (label && isMenuOpcoes(label)) {

                            // console.log('[TEF] Detectado menu de opções, abrindo modal:', label);
                            // return await mostrarModalTEFOpcoes(label);
                            return 1
                        }
                        if (label && label.toLowerCase().includes('parcela')) {
                            return await mostrarModalParcelas(label, maxLength);
                        }

                        if (maxLength && maxLength <= 2 && minLength >= 1) {
                            return prompt(label);
                        }

                        return prompt(label);
                    },
                    onMenu: async (titulo, opcoes) => {
                        return prompt(titulo);
                    }
                });

                // console.log('[TEF] Cliente inicializado com sucesso', TEF_CONFIG);
            } else {
                // console.log('[TEF] TEF não está habilitado para esta empresa');
            }
        }
    } catch (e) {
        console.error('[TEF] Erro ao carregar configuração:', e);
    }
}

function mostrarModalParcelas(label, maxParcelas = 12, valorTotal = null) {
    return new Promise((resolve) => {
        // Esconde modal de status para não conflitar

        const LIMITE_MINIMO_PARCELA = 5.00;

        const modalStatus = $('#modal-tef-status');
        const hadStatus = modalStatus.length > 0 && modalStatus.hasClass('show');
        if (hadStatus) {
            try { document.activeElement && document.activeElement.blur(); } catch (e) { }
            modalStatus.removeAttr('aria-hidden');
            modalStatus.modal('hide');
        }

        // Gera botões de 2 até maxParcelas
        let botoesHtml = '<div class="d-flex flex-wrap justify-content-center">';
        const limite = 12;
        for (let i = 2; i <= limite; i++) {

            let disabled = '';
            let classeExtra = '';
            let titulo = '';

            if (valorTotal) {
                const valorParcela = valorTotal / i;
                if (valorParcela < LIMITE_MINIMO_PARCELA) {
                    disabled = 'disabled';
                    classeExtra = 'btn-outline-secondary';
                    titulo = `title="Valor mínimo por parcela: R$ ${LIMITE_MINIMO_PARCELA.toFixed(2)}"`;
                }
            }

            const classe = disabled ? classeExtra : 'btn-outline-primary';

            botoesHtml += `
            <button type="button" class="btn ${classe} btn-lg m-2 btn-parcela-opcao" data-valor="${i}" style="min-width: 80px;" ${disabled} ${titulo}>
            ${i}x
            </button>`;
        }
        botoesHtml += '</div>';

        // Adiciona aviso se houver parcelas desabilitadas
        let avisoHtml = '';
        if (valorTotal) {
            const maxParcelasValidas = Math.floor(valorTotal / LIMITE_MINIMO_PARCELA);
            if (maxParcelasValidas < 12) {
                avisoHtml = `<p class="text-warning small mt-2">
                <i class="la la-exclamation-triangle"></i> 
                Parcelas desabilitadas: valor mínimo por parcela é R$ ${LIMITE_MINIMO_PARCELA.toFixed(2)}
                </p>`;
            }
        }

        const modalHtml = `
        <div class="modal fade" id="modal-tef-parcelas" data-backdrop="static" data-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-info text-white">
        <h5 class="modal-title text-white"><i class="la la-calculator mr-2 text-white"></i>Selecione o Número de Parcelas</h5>
        </div>
        <div class="modal-body text-center py-4">
        <p class="text-success mb-3">${label || 'Informe o número de parcelas'}</p>
        ${botoesHtml}
        ${avisoHtml}
        </div>
        <div class="modal-footer justify-content-center flex-column">
        <button type="button" class="btn btn-outline-danger btn-lg" id="btn-parcelas-cancelar">
        <i class="la la-times mr-2"></i>Cancelar <small class="text-muted">(ESC)</small>
        </button>
        <small class="text-muted mt-2"><i class="la la-keyboard mr-1"></i>Teclas: 2-9, 0=10x, -=11x, ==12x</small>
        </div>
        </div>
        </div>
        </div>`;

        // Remove modal anterior se existir
        $('#modal-tef-parcelas').remove();
        $('body').append(modalHtml);

        // ✅ Handler de teclado para atalhos numéricos nas parcelas
        const handleKeyPressParcelas = (e) => {
            const tecla = e.key;
            let valor = null;

            // Números 2-9 direto
            if (/^[2-9]$/.test(tecla)) {
                valor = tecla;
            }
            // Tecla 0 = 10 parcelas
            else if (tecla === '0') {
                valor = '10';
            }
            // Tecla - = 11 parcelas
            else if (tecla === '-') {
                valor = '11';
            }
            // Tecla = = 12 parcelas
            else if (tecla === '=') {
                valor = '12';
            }

            if (valor) {
                const botao = $(`.btn-parcela-opcao[data-valor="${valor}"]`);
                if (botao.length && !botao.prop('disabled')) {
                    e.preventDefault();
                    botao.trigger('click');
                }
            }

            // ESC para cancelar
            if (e.key === 'Escape') {
                e.preventDefault();
                $('#btn-parcelas-cancelar').trigger('click');
            }
        };

        // Adiciona listener ao abrir modal
        $(document).on('keydown.tefParcelas', handleKeyPressParcelas);

        // Função para fechar modal e restaurar status
        const finalizar = (resultado) => {
            // ✅ Remove listener de teclado
            $(document).off('keydown.tefParcelas');

            $('#modal-tef-parcelas').modal('hide');
            setTimeout(() => {
                $('#modal-tef-parcelas').remove();
                if (hadStatus && $('#modal-tef-status').length > 0) {
                    $('#modal-tef-status').modal('show');
                }
                resolve(resultado);
            }, 400);
        };

        // Evento de clique nos botões de parcela
        $('.btn-parcela-opcao').on('click', function () {
            const valor = $(this).data('valor');
            // console.log('[TEF] Parcelas selecionadas:', valor);
            finalizar(String(valor));
        });

        // Evento de cancelamento
        $('#btn-parcelas-cancelar').on('click', function () {
            // console.log('[TEF] Seleção de parcelas cancelada');
            finalizar(null);
        });

        // Mostra o modal
        setTimeout(() => {
            $('#modal-tef-parcelas').modal('show');
        }, hadStatus ? 200 : 0);
    });
}

//remover depois
$(document).ready(function () {
    inicializarTEF();
    // carregarVendasTEF()
    // adicionar produto
    // addProdutos(2)
    // setTimeout(() => {
    //     $('.efetuar_pagamento').trigger('click')
    //     setTimeout(() => {
    //         $('#tipo_pagamento_atual').val('00').change()
    //         setTimeout(() => {
    //             $('#adicionar-pagamento').trigger('click')
    //             setTimeout(() => {
    //                 $('#finalizar_venda_tab').trigger('click')
    //             }, 500)
    //         }, 500)
    //     }, 500)
    // }, 2000)

    // getProdutoCodBarras("7891000619162", (data) => {
        // setTimeout(() => {
    //         addItem();
    //         $('#tipo-pagamento').val('00').change()
    //         // setTimeout(() => {
    //         //     $('#finalizar-venda').trigger('click')
    //         // }, 400)
        // }, 400)
    // })

});

var vendaTEFSelecionada = null
function abrirModalCancelamento(venda) {
    vendaTEFSelecionada = venda;

    const tef = venda.tef_log || {};

    // Preenche os dados no modal
    $('#cancel-venda-numero').text(`#${venda.numero_sequencial || venda.id}`);
    $('#cancel-venda-valor').text(`R$ ${ convertFloatToMoeda(venda.total) }`);
    $('#cancel-venda-nsu').text(tef.tef_nsu || '-');
    $('#cancel-venda-bandeira').text(tef.tef_bandeira ? nomeBandeiraTEF(tef.tef_bandeira) : '-');

    let tipoTexto = '-';
    if (tef.tef_function_id == 2) {
        tipoTexto = 'Débito';
    } else if (tef.tef_function_id == 3) {
        tipoTexto = `Crédito${tef.parcelas > 1 ? ` ${tef.parcelas}x` : ''}`;
    }
    $('#cancel-venda-tipo').text(tipoTexto);

    // Abre o modal
    $('#modal-tef-confirma-cancelamento').modal('show');
}

async function reimprimirComprovantePorVenda(venda) {
    window.open(
        path_url + 'nfce/imprimir-tef/' + venda.id,
        'comprovanteTef',
        'width=500,height=600,top=100,left=100,scrollbars=yes,resizable=no'
        );
}

async function executarCancelamentoTEFAutomatico(cancelarVenda) {
    console.clear()
    if (!vendaTEFSelecionada || !vendaTEFSelecionada.tef_log) {
        swal("Erro", "Dados da venda não encontrados", "error");
        return;
    }

    const venda = vendaTEFSelecionada;
    const tef = venda.tef_log;

    const raw = JSON.parse(tef.tef_raw);
    let data_sitef = getDataSitef(raw.transcript);

    // Fecha modais
    $('#modal-tef-confirma-cancelamento').modal('hide');
    $('#modal-tef-operacoes').modal('hide');

    // Mostra modal de processamento (com delay para evitar conflito de modais)
    // await new Promise(r => setTimeout(r, 300));
    // tefLog('Processando cancelamento...');

    try {
        // Configura os dados de cancelamento no cliente TEF

        const valorCentavos = Math.round(venda.total * 100);
        TEF_CLIENT.dadosCancelamento = {
            nsu: tef.tef_nsu,
            valor: venda.total,
            valor_centavos: valorCentavos,
            tipo_cancelamento: tef.tipo_cancelamento,
            data_sitef: data_sitef,
            fatura_id: tef.fatura_id,
            venda_id: venda.id,
            cancelar_venda: cancelarVenda
        };

        // console.log('[TEF] Dados de cancelamento:', TEF_CLIENT.dadosCancelamento);
        mostrarModalLoadingTEF()

        // Chama a transação de cancelamento (functionId 200)
        const resultado = await TEF_CLIENT.processarTransacao(valorCentavos, 200);
        // await fecharModalTEF();
        // console.log(resultado)
        fecharModalLoadingTEF();
        if (resultado.aprovado) {
            // Marca como cancelado no BD
            try {
                await fetch(path_url+'tef-marcar-cancelado', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        venda_id: venda.id,
                        cancelar_venda: cancelarVenda
                    })
                });
            } catch (e) {

                console.warn('[TEF] Erro ao marcar cancelado no BD:', e);
            }

            // Mostra comprovantes se tiver
            if (resultado.tef?.comprovantes) {
                await mostrarModalImpressaoTEF(resultado.tef.comprovantes);

            }

            const msg = cancelarVenda
            ? "Cancelamento do TEF e da venda realizado com sucesso!"
            : "Cancelamento do TEF realizado com sucesso!";

            swal("Sucesso", msg, "success").then(() => {
                // Recarrega a lista de vendas
                carregarVendasTEF();
                $('#modal-tef-operacoes').modal('show');
            });
        } else {
            swal("Erro", resultado.mensagem || "Cancelamento não aprovado", "error");
        }

    } catch (error) {
        fecharModalLoadingTEF();

        console.error('[TEF] Erro no cancelamento:', error);
        swal("Erro", error.message || "Erro ao processar cancelamento", "error");
    } finally {
        // Limpa dados de cancelamento
        TEF_CLIENT.dadosCancelamento = null;
        vendaTEFSelecionada = null;
    }
}

function mostrarModalLoadingTEF(mensagem = 'Aguarde... processando cancelamento') {

    // Remove se existir
    $('#modal-tef-loading').remove();

    const html = `
    <div class="modal fade" id="modal-tef-loading" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
    <div class="modal-body py-5">
    <div class="spinner-border text-primary mb-4" style="width: 4rem; height: 4rem;"></div>
    <h5 class="mb-2">${mensagem}</h5>
    <small class="text-muted">Não desligue o terminal</small>
    </div>
    </div>
    </div>
    </div>`;

    $('body').append(html);

    $('#modal-tef-loading').modal('show');

    setTimeout(() => {
        $('#modal-tef-loading').css('z-index', 30000);
        $('.modal-backdrop').css('z-index', 29990);
    }, 300);
}

function fecharModalLoadingTEF() {
    $('#modal-tef-loading').modal('hide');
    setTimeout(() => $('#modal-tef-loading').remove(), 300);
}

function tefResetModals() {
    try { document.activeElement && document.activeElement.blur(); } catch (e) { }

    // Fecha qualquer modal bootstrap que esteja "preso"
    $('.modal:not(#modal-tef-impressao)').modal('hide');

    // Limpa backdrops e estado do body
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');

    // Remove atributos que às vezes ficam travados
    // $('#modal-tef-status').removeAttr('aria-hidden').css('display', '');
    $('#modal-tef-status').removeAttr('aria-hidden').css('display', '');
    $('#modal-tef-impressao').removeAttr('aria-hidden').css('display', '');
}

function mostrarModalImpressaoTEF(comprovantes) {
    // console.log('[TEF DEBUG] mostrarModalImpressaoTEF chamada', comprovantes);
    return new Promise((resolve) => {

        tefResetModals();
        // Remove modal anterior
        $('#modal-tef-impressao').remove();

        const temLoja = comprovantes.loja && comprovantes.loja.length > 0;
        const temCliente = comprovantes.cliente && comprovantes.cliente.length > 0;

        if (!temLoja && !temCliente) {
            console.warn('[TEF] Nenhum comprovante disponível para impressão');
            resolve(null);
            return;
        }

        const html = `
        <div class="modal fade" id="modal-tef-impressao" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white">
        <i class="la la-print mr-2 text-white"></i>Imprimir Comprovantes TEF
        </h5>
        </div>
        <div class="modal-body text-center py-4">
        <p class="mb-4">A transação foi aprovada. Deseja imprimir os comprovantes?</p>

        <div class="d-flex flex-wrap justify-content-center">
        ${temLoja && temCliente ? `
            <button class="btn btn-primary btn-lg m-2 btn-impressao-tef" data-opcao="ambos">
            <i class="la la-copy mr-2"></i>Via Loja + Cliente
            </button>` : ''}

            ${temLoja ? `
                <button class="btn btn-outline-primary btn-lg m-2 btn-impressao-tef" data-opcao="loja">
                <i class="la la-store mr-2"></i>Só Via Loja
                </button>` : ''}

                ${temCliente ? `
                    <button class="btn btn-outline-primary btn-lg m-2 btn-impressao-tef" data-opcao="cliente">
                    <i class="la la-user mr-2"></i>Só Via Cliente
                    </button>` : ''}
                    </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                    <button class="btn btn-outline-secondary btn-lg" id="btn-tef-nao-imprimir">
                    <i class="la la-times mr-2"></i>Não Imprimir
                    </button>
                    </div>
                    </div>
                    </div>
                    </div>`;

                    $('body').append(html);

        // Handlers
        $('.btn-impressao-tef').on('click', function () {
            const opcao = $(this).data('opcao');
            try { document.activeElement && document.activeElement.blur(); } catch (e) { }
            $('#modal-tef-impressao').removeAttr('aria-hidden');
            $('#modal-tef-impressao').modal('hide');
            setTimeout(() => {
                $('#modal-tef-impressao').remove();
                imprimirComprovanteTEF(comprovantes, opcao).then(() => resolve(opcao));
            }, 400);
        });

        $('#btn-tef-nao-imprimir').on('click', function () {
            try { document.activeElement && document.activeElement.blur(); } catch (e) { }
            $('#modal-tef-impressao').removeAttr('aria-hidden');
            $('#modal-tef-impressao').modal('hide');
            setTimeout(() => {
                $('#modal-tef-impressao').remove();
                resolve(null);
            }, 400);
        });

        setTimeout(() => {
            $('#modal-tef-impressao').modal('show');
            setTimeout(() => {
                $('#modal-tef-impressao').css('z-index', 20050);
                $('.modal-backdrop').css('z-index', 20040);
            }, 400);
        }, 450);
    });
}

function imprimirComprovanteTEF(comprovantes, opcao = 'ambos') {
    // console.log("comp", comprovantes)

    return new Promise((resolve) => {
        let htmlFinal = '';

        if (opcao === 'ambos' || opcao === 'loja') {
            if (comprovantes.loja && comprovantes.loja.length > 0) {
                htmlFinal += gerarHTMLComprovante('LOJA', comprovantes.loja);
                if (opcao === 'ambos') {
                    htmlFinal += '<div style="page-break-after: always;"></div>';
                }
            }
        }

        if (opcao === 'ambos' || opcao === 'cliente') {
            if (comprovantes.cliente && comprovantes.cliente.length > 0) {
                htmlFinal += gerarHTMLComprovante('CLIENTE', comprovantes.cliente);
            }
        }

        // Abre janela de impressão
        const janelaImpressao = window.open('', '_blank', 'width=400,height=600');
        janelaImpressao.document.write(htmlFinal);
        janelaImpressao.document.close();


        setTimeout(() => {
            // janelaImpressao.print();
            resolve(true);
        }, 300);
    });
}

function formatarComprovanteTEF(linhas) {
    return linhas.map(linha => {
        // Remove prefixos SiTef (SI, NU, LA, DO, etc) do início
        // Formato típico: "SI                texto aqui"
        return linha.replace(/^[A-Z]{2}\s+/, '').trim();
    }).filter(l => l.length > 0);
}

function mostrarDetalhesTEF(venda) {
    // console.log(venda)
    const tef = venda.tef_log || {};

    let tipoLabel = '-';
    if (tef.tef_function_id == 2) {
        tipoLabel = '<span class="badge bg-success">Débito</span>';
    } else if (tef.tef_function_id == 3) {
        tipoLabel = `<span class="badge bg-primary">Crédito${tef.parcelas > 1 ? ` ${tef.parcelas}x` : ''}</span>`;
    } else {
        tipoLabel = '<span class="badge bg-info">PIX</span>';
    }
    const html = `
    <table class="table table-sm table-bordered">
    <tr><th width="40%">Venda Nº</th><td>#${venda.numero_sequencial || venda.id}</td></tr>
    <tr><th>Data/Hora</th><td>${ formatarDataHoraBR(venda.created_at) }</td></tr>
    <tr><th>Cliente</th><td>${ venda.cliente ? venda.cliente.razao_social : '--' }</td></tr>
    <tr><th>Valor Total</th><td>R$ ${ convertFloatToMoeda(venda.total) }</td></tr>
    <tr><th colspan="2" class="bg-light text-center">Dados TEF</th></tr>
    <tr><th>NSU</th><td><code>${tef.tef_nsu || '-'}</code></td></tr>
    <tr><th>Código Autorização</th><td>${tef.tef_codigo_autorizacao || '-'}</td></tr>
    <tr><th>Bandeira</th><td>${ tef.tef_bandeira ? nomeBandeiraTEF(tef.tef_bandeira) : '-' }</td></tr>
    <tr><th>Tipo</th><td>${tipoLabel}</td></tr>
    <tr><th>Parcelas</th><td>${tef.parcelas || '1'}</td></tr>

    <tr><th>Data TEF</th><td>${ formatarDataHoraBR(tef.created_at)}</td></tr>
    <tr><th>Status TEF</th><td>${tef.cancelado ? '<span class="text-danger">Cancelado</span>' : '<span class="text-success">Ativo</span>'}</td></tr>
    </table>
    `;

    $('#modal-tef-detalhes-body').html(html);
    $('#modal-tef-detalhes').modal('show');
}

function gerarHTMLComprovante(tipo, linhas, dadosVenda = {}) {
    const linhasFormatadas = formatarComprovanteTEF(linhas);

    return `
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <title>Comprovante TEF - Via ${tipo}</title>
    <style>
    @page { 
        size: 80mm auto; 
        margin: 2mm; 
    }
    body {
        font-family: 'Courier New', monospace;
        font-size: 10px;
        line-height: 1.2;
        width: 76mm;
        margin: 0 auto;
        padding: 2mm;
    }
    .linha { 
        white-space: pre-wrap; 
        word-wrap: break-word;
    }
    .separador {
        border-bottom: 1px dashed #000;
        margin: 3mm 0;
    }
    .titulo {
        text-align: center;
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 3mm;
    }
    .via {
        text-align: center;
        font-size: 9px;
        margin-top: 3mm;
    }
    @media print {
        body { -webkit-print-color-adjust: exact; }
    }
    </style>
    </head>
    <body>
    <div class="titulo">COMPROVANTE TEF</div>
    <div class="separador"></div>
    ${linhasFormatadas.map(l => `<div class="linha">${l}</div>`).join('')}
    <div class="separador"></div>
    <div class="via">*** VIA ${tipo.toUpperCase()} ***</div>
    </body>
    </html>`;
}

function getDataSitef(transcript) {
    if (!Array.isArray(transcript)) return null;

    const item = transcript.find(t =>
        t?.resp?.fieldId === 105 &&
        typeof t?.resp?.data === 'string' &&
        t.resp.data.length === 14
        );

    if (!item) return null;

    const d = item.resp.data;
    return d
    // return `${d.slice(0,4)}-${d.slice(4,6)}-${d.slice(6,8)} ` +
    // `${d.slice(8,10)}:${d.slice(10,12)}:${d.slice(12,14)}`;
}

async function carregarVendasTEF() {
    const data = $('#tef-filtro-data').val() || new Date().toISOString().split('T')[0];
    $('#modal-tef-operacoes').modal('show')
    $('#tbody-vendas-tef').html(`
        <tr>
        <td colspan="9" class="text-center py-4">
        <i class="la la-spinner la-spin la-2x"></i>
        <br>Carregando vendas TEF...
        </td>
        </tr>
        `);

    try {
        const response = await fetch(path_url+`tef-vendas?data=${data}`);
        const resultado = await response.json();

        if (resultado.success) {
            renderizarTabelaVendasTEF(resultado.vendas);
        } else {
            // console.log(resultado)
            $('#tbody-vendas-tef').html(`
                <tr>
                <td colspan="9" class="text-center text-danger py-4">
                <i class="la la-exclamation-circle la-2x"></i>
                <br>Erro ao carregar vendas: ${resultado.error || 'Erro desconhecido'}
                </td>
                </tr>
                `);
        }
    } catch (error) {
        console.error('[TEF] Erro ao carregar vendas:', error);
        $('#tbody-vendas-tef').html(`
            <tr>
            <td colspan="9" class="text-center text-danger py-4">
            <i class="la la-exclamation-circle la-2x"></i>
            <br>Erro de conexão ao carregar vendas
            </td>
            </tr>
            `);
    }
}

function formatarDataHoraBR(data) {
    if (!data) return '';

    // aceita "2026-02-04 10:55:00" ou ISO
    const d = new Date(data.replace(' ', 'T'));

    if (isNaN(d.getTime())) return data;

    const dia  = String(d.getDate()).padStart(2, '0');
    const mes  = String(d.getMonth() + 1).padStart(2, '0');
    const ano  = d.getFullYear();
    const hora = String(d.getHours()).padStart(2, '0');
    const min  = String(d.getMinutes()).padStart(2, '0');

    return `${dia}/${mes}/${ano} ${hora}:${min}`;
}

function renderizarTabelaVendasTEF(vendas) {
    if (!vendas || vendas.length === 0) {
        $('#tbody-vendas-tef').html(`
            <tr>
            <td colspan="9" class="text-center text-muted py-4">
            <i class="la la-inbox la-3x"></i>
            <br>Nenhuma venda TEF encontrada para esta data
            </td>
            </tr>
            `);
        $('#tef-total-vendas').text('');
        return;
    }

    let html = '';
    let totalValor = 0;

    // ✅ NOVO: Identificar a última transação TEF não cancelada (primeira da lista, pois vem ordenada desc)
    const ultimaTransacaoId = vendas.find(v => {
        const tef = v.tef || {};
        return !tef.cancelado && v.estado !== 'CANCELADO';
    })?.id || null;

    vendas.forEach((venda, index) => {

        // console.log("venda", venda)
        const tef = venda.tef_log || {};
        const isCancelado = tef.cancelado || venda.estado === 'CANCELADO';

        // ✅ NOVO: Verifica se é a última transação
        const isUltimaTransacao = (venda.id === ultimaTransacaoId);

        // ✅ NOVO: Verifica se permite reimpressão específica (apenas Cielo e Rede)
        // const adquirentePermiteReimpressao = verificarAdquirentePermiteReimpressao(tef.adquirente, tef.adquirenteCodigo);
        const adquirentePermiteReimpressao = true;

        // Tipo de pagamento formatado
        let tipoLabel = '-';
        if (tef.tef_function_id == 2) {
            tipoLabel = '<span class="badge bg-success">Débito</span>';
        } else if (tef.tef_function_id == 3) {
            tipoLabel = `<span class="badge bg-primary">Crédito${tef.parcelas > 1 ? ` ${tef.parcelas}x` : ''}</span>`;
        } else {
            tipoLabel = '<span class="badge bg-info">PIX</span>';
        }

        // Status
        let statusLabel = '<span class="badge bg-success"><i class="la la-check text-white"></i> Aprovado</span>';
        if (isCancelado) {
            statusLabel = '<span class="badge bg-danger"><i class="la la-times text-white"></i> Cancelado</span>';
        }

        totalValor += parseFloat(venda.total) || 0;

        // ✅ NOVO: Monta o objeto venda com flags adicionais
        const vendaComFlags = {
            ...venda,
            _isUltimaTransacao: isUltimaTransacao,
            _permiteReimpressaoEspecifica: adquirentePermiteReimpressao
        };

        // ✅ NOVO: Badge indicando última transação
        const badgeUltima = isUltimaTransacao ? '<span class="badge bg-warning ml-1" title="Última transação"><i class="la la-clock text-white"></i></span>' : '';

        html += `
        <tr>
        <td>${ formatarDataHoraBR(venda.created_at) }</td>
        <td><strong>#${venda.numero_sequencial || venda.id}</strong>${badgeUltima}</td>
        <td>${venda.cliente ? venda.cliente.razao_social : 'Não identificado'}</td>
        <td class="text-right">R$ ${ convertFloatToMoeda(venda.total) }</td>
        <td><code>${tef.tef_nsu || '-'}</code></td>
        <td>${ tef.tef_bandeira ? nomeBandeiraTEF(tef.tef_bandeira) : '-' }</td>
        <td>${tipoLabel}</td>
        <td>${statusLabel}</td>
        <td class="">
        <div class="">


        <button
        class="btn btn-sm btn-danger" type="button"
        title="Cancelar TEF"
        ${isCancelado ? 'disabled' : ''}
        onclick='abrirModalCancelamento(${JSON.stringify(venda).replace(/'/g, "\\'")})'>
        <i class="ri-close-circle-line"></i>
        </button>

        <button
        class="btn btn-sm btn-primary" type="button"
        title="Reimprimir comprovante"
        onclick='reimprimirComprovantePorVenda(${JSON.stringify(vendaComFlags).replace(/'/g, "\\'")})'>
        <i class="ri-printer-line"></i>
        </button>

        <button
        class="btn btn-sm btn-secondary" type="button"
        title="Ver detalhes"
        onclick='mostrarDetalhesTEF(${JSON.stringify(venda).replace(/'/g, "\\'")})'>
        <i class="ri-file-line"></i>
        </button>

        </div>

        </td>
        </tr>
        `;
    });

    $('#tbody-vendas-tef').html(html);
    $('#tef-total-vendas').html(`
        <strong>${vendas.length}</strong> venda(s) TEF | 
        Total: <strong>R$ ${totalValor.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</strong>
        `);
}

function nomeBandeiraTEF(codigo) {
    const bandeiras = {
        '00': 'Visa',
        '01': 'Mastercard',
        '02': 'Elo',
        '03': 'American Express',
        '04': 'Hipercard',
        '05': 'Aura',
        '06': 'Diners Club',
        '07': 'Discover',
        '08': 'JCB',
        '09': 'Banescard',
        '10': 'Cabal',
        '11': 'Credishop',
        '12': 'Sorocred',
        '13': 'UnionPay',
        '99': 'Outros'
    };

    return bandeiras[String(codigo).padStart(2, '0')] || 'Desconhecida';
}

$(function(){
    //consultar pendencias
    setTimeout(() => {
        consultaPendenciaInicial()
    }, 1000)
})

async function consultaPendenciaInicial() {
    consultaPendencia = 1
    const { temPendencias, pendencias } = await TEF_CLIENT.consultarPendenciasTerminal();
    window.TEF_PENDENCIAS = pendencias || [];
    // console.log(pendencias)
    pendencias.forEach((p, idx) => {
        pendenciaTefEstornar(idx)
    });
}

async function pendenciaTefEstornar(index) {
    const pend = (window.TEF_PENDENCIAS || [])[index];
    if (!pend) return;

    const cupom = pend.cupomFiscal || '';
    const data = pend.dataFiscal || '';
    const hora = pend.horaFiscal || '';

    try {
        // ✅ estorno "fora do fluxo": sem sessionId
        const resp = await TEF_CLIENT.finishTransaction({
            sitefIp: TEF_CLIENT.config.sitefIp,
            storeId: TEF_CLIENT.config.storeId,
            terminalId: TEF_CLIENT.config.terminalId,
            taxInvoiceNumber: cupom,
            taxInvoiceDate: data,
            taxInvoiceTime: hora,
            confirm: 0
        });

        if (resp && resp.serviceStatus === 0) {
            // swal("Sucesso", "Pendência estornada com sucesso.", "success");

            // Remove da lista e re-renderiza
            window.TEF_PENDENCIAS.splice(index, 1);

            // Opcional: se zerou, você pode fechar e voltar pra operações
            // if (window.TEF_PENDENCIAS.length === 0) { $('#modal-tef-pendencias').modal('hide'); $('#modal-tef-operacoes').modal('show'); }
        } else {
            // swal("Aviso", resp?.serviceMessage || "Não foi possível estornar.", "warning");
        }

    } catch (e) {
        // await fecharModalTEF();
        console.error('[TEF] Erro ao estornar pendência:', e);
        // swal("Erro", e.message || "Erro ao estornar pendência", "error");
    }
}

var consultaPendencia = 0
async function iniciarConsultaPendencias() {
    $('#modal-tef-operacoes').modal('hide');
    mostrarModalLoadingTEF('Aguarde... pesquisando pendencias')
    await new Promise(r => setTimeout(r, 300)); // Espera fechar o menu
    // mostrarModalTEF('Consultando pendências...');

    try {
        consultaPendencia = 1
        const { temPendencias, pendencias } = await TEF_CLIENT.consultarPendenciasTerminal();

        // CRITICAL: ESPERA a limpeza terminar DE VERDADE
        window.TEF_PENDENCIAS = pendencias || [];
        renderizarPendenciasTEF(window.TEF_PENDENCIAS);
        fecharModalLoadingTEF();
        // console.log(pendencias)
        if (!temPendencias) {
            swal("Sucesso", "Nenhuma pendência encontrada.", "success");
            return;
        }

        // Agora sim, abre o novo modal com o DOM limpo
        setTimeout(() => {
            $('#modal-tef-pendencias').modal('show');
        }, 100);

    } catch (error) {

        swal("Erro", error.message, "error");
    }
}

async function estornarPendenciaTEF(index) {
    const pend = (window.TEF_PENDENCIAS || [])[index];
    if (!pend) return;

    const cupom = pend.cupomFiscal || '';
    const data = pend.dataFiscal || '';
    const hora = pend.horaFiscal || '';

    const ok = await swal({
        title: "Confirmar estorno?",
        text: `Cupom: ${cupom}\nData: ${ formatarDataPendencia(data)}\nHora: ${formatarHoraPendencia(hora)}\n\nIsso fará o TEF NÃO confirmar (estornar).`,
        icon: "warning",
        buttons: ["Cancelar", "Sim, estornar"],
        dangerMode: true
    });

    if (!ok) return;

    // mostrarModalTEF('Estornando pendência...');

    try {
        // ✅ estorno "fora do fluxo": sem sessionId
        const resp = await TEF_CLIENT.finishTransaction({
            sitefIp: TEF_CLIENT.config.sitefIp,
            storeId: TEF_CLIENT.config.storeId,
            terminalId: TEF_CLIENT.config.terminalId,
            taxInvoiceNumber: cupom,
            taxInvoiceDate: data,
            taxInvoiceTime: hora,
            confirm: 0
        });

        // await fecharModalTEF();

        if (resp && resp.serviceStatus === 0) {
            swal("Sucesso", "Pendência estornada com sucesso.", "success");

            // Remove da lista e re-renderiza
            window.TEF_PENDENCIAS.splice(index, 1);
            renderizarPendenciasTEF(window.TEF_PENDENCIAS);

            // Opcional: se zerou, você pode fechar e voltar pra operações
            // if (window.TEF_PENDENCIAS.length === 0) { $('#modal-tef-pendencias').modal('hide'); $('#modal-tef-operacoes').modal('show'); }
        } else {
            swal("Aviso", resp?.serviceMessage || "Não foi possível estornar.", "warning");
        }

    } catch (e) {
        // await fecharModalTEF();
        console.error('[TEF] Erro ao estornar pendência:', e);
        swal("Erro", e.message || "Erro ao estornar pendência", "error");
    }
}

function formatarValorCentavos(v) {
    const n = parseInt(String(v || '0').replace(/\D/g, ''), 10);
    if (!Number.isFinite(n)) return '-';
    return (n / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}

function renderizarPendenciasTEF(pendencias) {
    const $tb = $('#tbody-tef-pendencias');

    if (!pendencias || pendencias.length === 0) {
        $tb.html(`
          <tr>
          <td colspan="7" class="text-center py-4 text-success">
          <i class="la la-check-circle la-2x"></i><br>
          Nenhuma pendência encontrada no terminal.
          </td>
          </tr>
          `);
        return;
    }

    let html = '';
    pendencias.forEach((p, idx) => {
        const cupom = p.cupomFiscal || '-';
        const data = p.dataFiscal || '-';
        const hora = p.horaFiscal || '-';
        const func = p.funcaoOriginal || '-';
        const valor = (p.valorOriginal != null) ? `R$ ${formatarValorCentavos(p.valorOriginal)}` : '-';

        html += `
        <tr>
        <td>${idx + 1}</td>
        <td><code>${cupom}</code></td>
        <td>${ formatarDataPendencia(data) }</td>
        <td>${ formatarHoraPendencia(hora) }</td>
        <td>${func}</td>
        <td class="text-right">${valor}</td>
        <td>
        <button type="button" class="btn btn-sm btn-danger"
        onclick="estornarPendenciaTEF(${idx})">
        <i class="la la-undo mr-1"></i>Estornar
        </button>
        </td>
        </tr>
        `;
    });

    $tb.html(html);
}

function formatarDataPendencia(data) {
    if (!data || data.length !== 8) return '';

    const ano = data.substring(0, 4);
    const mes = data.substring(4, 6);
    const dia = data.substring(6, 8);

    return `${dia}/${mes}/${ano}`;
}

function formatarHoraPendencia(hora) {
    if (!hora || hora.length !== 6) return '';

    const h = hora.substring(0, 2);
    const m = hora.substring(2, 4);
    const s = hora.substring(4, 6);

    return `${h}:${m}:${s}`;
}

