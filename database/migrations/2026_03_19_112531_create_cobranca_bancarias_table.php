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
        Schema::create('cobranca_bancarias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('conta_receber_id')->constrained('conta_recebers');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');

            $table->string('banco', 30)->nullable();
            $table->string('status_banco', 100)->nullable();

            // identificação do boleto
            $table->string('nosso_numero', 100)->nullable();
            $table->string('seu_numero', 100)->nullable();
            $table->string('numero_boleto', 100)->nullable();
            $table->string('codigo_barras', 255)->nullable();
            $table->string('linha_digitavel', 255)->nullable();

            // valores
            $table->decimal('valor', 16, 2);
            $table->decimal('valor_recebido', 16, 2)->nullable();
            $table->decimal('valor_multa', 16, 2)->default(0);
            $table->decimal('valor_juros', 16, 2)->default(0);
            $table->decimal('valor_desconto', 16, 2)->default(0);

            // datas
            $table->date('data_emissao')->nullable();
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->date('data_baixa')->nullable();

            // retorno do banco
            $table->text('url_pdf')->nullable();
            $table->text('url_boleto')->nullable();
            $table->longText('pdf_base64')->nullable();

            // controle técnico
            $table->json('payload_envio')->nullable();
            $table->json('payload_retorno')->nullable();
            $table->text('mensagem_erro')->nullable();
            $table->timestamp('ultima_consulta_em')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobranca_bancarias');
    }
};
