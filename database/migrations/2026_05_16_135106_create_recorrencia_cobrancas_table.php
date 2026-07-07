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
        Schema::create('recorrencia_cobrancas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('recorrencia_id')->constrained('recorrencias')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            $table->date('data_vencimento');
            $table->decimal('valor', 10, 2);

            $table->enum('status', ['pendente', 'pago', 'vencido', 'cancelado'])->default('pendente');

            $table->enum('forma_pagamento', ['pix', 'boleto', 'cartao', 'dinheiro'])->default('pix');

            $table->string('asaas_id')->nullable();
            $table->string('asaas_invoice_url')->nullable();
            $table->text('pix_payload')->nullable();
            $table->text('pix_qrcode')->nullable();

            $table->dateTime('pago_em')->nullable();
            $table->dateTime('cancelado_em')->nullable();

            $table->foreignId('nfe_id')->nullable()->constrained('nves')->nullOnDelete();
            $table->foreignId('nfse_id')->nullable()->constrained('nota_servicos')->nullOnDelete();

            $table->text('observacao')->nullable();
            $table->foreignId('conta_receber_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencia_cobrancas');
    }
};
