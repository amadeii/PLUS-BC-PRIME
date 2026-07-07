<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config_gerals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->enum('balanca_valor_peso', ['peso', 'valor']);
            $table->integer('balanca_digito_verificador')->nullable();
            $table->boolean('confirmar_itens_prevenda')->default(0);
            $table->boolean('gerenciar_estoque')->default(0);
            $table->boolean('agrupar_itens')->default(0);
            $table->text('notificacoes');
            $table->decimal('margem_combo', 5,2)->default(50);
            $table->decimal('percentual_desconto_orcamento', 5,2)->nullable();
            $table->decimal('percentual_lucro_produto', 10,2)->default(0);

            $table->text('tipos_pagamento_pdv');
            $table->string('tipo_pagamento_padrao', 2)->nullable();
            $table->string('senha_manipula_valor', 20)->nullable();
            $table->boolean('abrir_modal_cartao')->default(1);
            $table->enum('tipo_comissao', ['percentual_vendedor', 'percentual_margem'])->nullable();
            $table->string('modelo', 20)->default('light');
            $table->boolean('alerta_sonoro')->default(1);
            $table->boolean('cabecalho_pdv')->default(1);
            $table->boolean('definir_vendedor_pdv')->default(0);
            $table->boolean('gerar_conta_receber_padrao')->default(1);
            $table->boolean('gerar_conta_pagar_padrao')->default(1);

            $table->boolean('compra_compara_xml')->default(0);

            $table->boolean('atualizar_valor_venda_importacao')->default(1);

            $table->integer('regime_nfse')->nullable();

            $table->string('mercadopago_public_key_pix', 120)->nullable();
            $table->string('mercadopago_access_token_pix', 120)->nullable();
            $table->string('asaas_token', 255)->nullable();
            $table->string('customer_asaas_id', 20)->nullable();

            $table->boolean('definir_vendedor_pdv_off')->default(0);
            $table->boolean('alterar_valor_pdv_off')->default(0);
            $table->string('acessos_pdv_off', 255)->nullable();
            $table->enum('tipo_menu', ['vertical', 'horizontal'])->default('vertical');
            $table->enum('cor_menu', ['light', 'brand', 'dark'])->default('light');
            $table->enum('cor_top_bar', ['light', 'brand', 'dark'])->default('light');
            $table->boolean('usar_ibpt')->default(1);
            $table->integer('casas_decimais_quantidade')->default(2);
            $table->integer('cliente_padrao_pdv_off')->nullable();

            $table->text('mensagem_padrao_impressao_venda');
            $table->text('mensagem_padrao_impressao_os');

            $table->integer('ultimo_codigo_produto')->default(0);
            $table->integer('ultimo_codigo_cliente')->default(0);
            $table->integer('ultimo_codigo_fornecedor')->default(0);
            $table->boolean('app_valor_aprazo')->default(0);
            $table->boolean('impressao_sem_janela_cupom')->default(0);

            $table->string('resp_tec_email', 80)->nullable();
            $table->string('resp_tec_cpf_cnpj', 18)->nullable();
            $table->string('resp_tec_nome', 60)->nullable();
            $table->string('resp_tec_telefone', 20)->nullable();
            $table->string('resp_id_csrt', 20)->nullable();
            $table->string('resp_hash_csrt', 100)->nullable();

            $table->boolean('limitar_credito_cliente')->default(0);
            $table->boolean('limitar_cliente_inadimplente')->default(0);

            $table->boolean('corrigir_numeracao_fiscal')->default(1);
            $table->string('documento_pdv', 4)->default('nfce');
            
            $table->integer('numero_inicial_comanda')->nullable();
            $table->integer('numero_final_comanda')->nullable();
            $table->text('home_componentes');
            $table->string('token_whatsapp', 120)->nullable();
            $table->string('small_header_user', 50)->default('small-4.jpg');

            $table->text('mensagem_wpp_link');
            $table->boolean('status_wpp_link')->default(0);
            $table->boolean('enviar_danfe_wpp_link')->default(0);
            $table->boolean('enviar_xml_wpp_link')->default(0);
            $table->boolean('enviar_pedido_a4_wpp_link')->default(0);

            $table->boolean('produtos_exibe_tabela')->default(1);
            $table->boolean('clientes_exibe_tabela')->default(1);
            $table->integer('itens_por_pagina')->default(30);
            $table->integer('margem_lateral_impressao')->default(0);

            $table->string('tipo_ordem_servico', 50)->default('normal'); // normal, assistencia técinica, oficina

            $table->boolean('usar_dropdown_acoes')->default(0);

            $table->boolean('ticket_troca')->default(0);
            $table->boolean('cadastro_simplificado_cliente')->default(0);
            $table->string('mensagem_ticket_troca', 255)->nullable();

            $table->decimal('perc_multa_padrao', 5,2)->default(0);
            $table->decimal('perc_juros_padrao', 5,2)->default(0);


            // alter table config_gerals add column tipo_pagamento_padrao varchar(2) default null;
            // alter table config_gerals add column tipo_ordem_servico varchar(50) default 'normal';
            
            // alter table config_gerals add column produtos_exibe_tabela boolean default 1;
            // alter table config_gerals add column clientes_exibe_tabela boolean default 1;
            // alter table config_gerals add column itens_por_pagina integer default 30;
            
            // alter table config_gerals add column mensagem_wpp_link text;
            // alter table config_gerals add column status_wpp_link boolean default 0;
            // alter table config_gerals add column compra_compara_xml boolean default 0;
            // alter table config_gerals add column enviar_danfe_wpp_link boolean default 0;
            // alter table config_gerals add column enviar_xml_wpp_link boolean default 0;
            // alter table config_gerals add column enviar_pedido_a4_wpp_link boolean default 0;
            // alter table config_gerals add column cadastro_simplificado_cliente boolean default 0;

            // alter table config_gerals add column small_header_user varchar(50) default 'small-4.jpg';
            // alter table config_gerals add column home_componentes varchar(300) default '[]';
            // alter table config_gerals modify column home_componentes text;
            
            // alter table config_gerals add column corrigir_numeracao_fiscal boolean default 1;
            // alter table config_gerals add column ticket_troca boolean default 0;

            // alter table config_gerals add column numero_inicial_comanda integer default null;
            // alter table config_gerals add column numero_final_comanda integer default null;

            // alter table config_gerals add column documento_pdv varchar(4) default 'nfce';

            // alter table config_gerals add column limitar_credito_cliente boolean default 0;
            // alter table config_gerals add column limitar_cliente_inadimplente boolean default 0;

            // alter table config_gerals add column mensagem_padrao_impressao_venda text;
            // alter table config_gerals add column mensagem_padrao_impressao_os text;

            // alter table config_gerals add column confirmar_itens_prevenda boolean default 0;
            // alter table config_gerals modify column balanca_digito_verificador integer default null;
            // alter table config_gerals add column notificacoes text;
            // alter table config_gerals add column margem_combo decimal(5,2) default 50;
            // alter table config_gerals add column gerenciar_estoque boolean default 0;
            // alter table config_gerals add column percentual_lucro_produto decimal(10,2) default 0;
            // alter table config_gerals add column tipos_pagamento_pdv text;
            // alter table config_gerals add column senha_manipula_valor varchar(20) default null;
            // alter table config_gerals add column abrir_modal_cartao boolean default 1;

            // alter table config_gerals add column percentual_desconto_orcamento decimal(5,2) default null;
            // alter table config_gerals add column agrupar_itens boolean default 0;
            // alter table config_gerals add column alerta_sonoro boolean default 0;
            // alter table config_gerals add column tipo_comissao enum('percentual_vendedor', 'percentual_margem') default 'percentual_vendedor';

            // alter table config_gerals add column modelo varchar(20) default 'light';
            // alter table config_gerals add column cabecalho_pdv boolean default 1;
            // alter table config_gerals add column regime_nfse integer default null;

            // alter table config_gerals add column mercadopago_public_key_pix varchar(120) default null;
            // alter table config_gerals add column mercadopago_access_token_pix varchar(120) default null;

            // alter table config_gerals add column definir_vendedor_pdv_off boolean default 0;
            // alter table config_gerals add column alterar_valor_pdv_off boolean default 0;
            // alter table config_gerals add column definir_vendedor_pdv boolean default 0;
            // alter table config_gerals add column acessos_pdv_off varchar(255) default null;
            // alter table config_gerals add column tipo_menu enum('vertical', 'horizontal') default 'light';
            // alter table config_gerals add column cor_menu enum('light', 'brand', 'dark') default 'light';
            // alter table config_gerals add column cor_top_bar enum('light', 'brand', 'dark') default 'light';
            // alter table config_gerals add column usar_ibpt boolean default 1;
            // alter table config_gerals add column casas_decimais_quantidade integer default 2;
            // alter table config_gerals modify column cliente_padrao_pdv_off integer default null;

            // alter table config_gerals add column gerar_conta_receber_padrao boolean default 1;
            // alter table config_gerals add column gerar_conta_pagar_padrao boolean default 1;
            // alter table config_gerals add column atualizar_valor_venda_importacao boolean default 1;

            // alter table config_gerals add column ultimo_codigo_produto integer default 0;
            // alter table config_gerals add column ultimo_codigo_cliente integer default 0;
            // alter table config_gerals add column ultimo_codigo_fornecedor integer default 0;

            // alter table config_gerals add column app_valor_aprazo boolean default 0;
            // alter table config_gerals add column impressao_sem_janela_cupom boolean default 0;

            // alter table config_gerals add column resp_tec_email varchar(80) default null;
            // alter table config_gerals add column resp_tec_cpf_cnpj varchar(18) default null;
            // alter table config_gerals add column resp_tec_nome varchar(60) default null;
            // alter table config_gerals add column resp_tec_telefone varchar(20) default null;
            // alter table config_gerals add column token_whatsapp varchar(120) default null;
            // alter table config_gerals add column usar_dropdown_acoes boolean default 0;

            // alter table config_gerals add column margem_lateral_impressao integer default 0;
            // alter table config_gerals add column mensagem_ticket_troca varchar(255) default null;

            // alter table config_gerals add column perc_multa_padrao decimal(5,2) default 0;
            // alter table config_gerals add column perc_juros_padrao decimal(5,2) default 0;
            
            // alter table config_gerals add column asaas_token varchar(255) default null;
            // alter table config_gerals add column customer_asaas_id varchar(20) default null;

            // alter table config_gerals add column resp_id_csrt varchar(20) default null;
            // alter table config_gerals add column resp_hash_csrt varchar(100) default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_gerals');
    }
};
