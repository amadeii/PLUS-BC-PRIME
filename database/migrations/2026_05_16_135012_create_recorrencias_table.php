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
        Schema::create('recorrencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            $table->string('descricao', 100);
            $table->decimal('valor', 10, 2);

            $table->enum('periodicidade', ['mensal', 'bimestral', 'trimestral', 'semestral', 'anual'])->default('mensal');

            $table->integer('dia_vencimento')->default(10);
            $table->date('data_inicio');
            $table->date('proxima_cobranca')->nullable();
            $table->date('data_fim')->nullable();

            $table->enum('forma_pagamento', ['pix', 'boleto', 'cartao', 'dinheiro'])->default('pix');

            $table->boolean('gerar_automatico')->default(1);
            $table->boolean('enviar_whatsapp')->default(0);
            $table->boolean('enviar_email')->default(0);

            $table->enum('status', ['ativa', 'pausada', 'cancelada', 'finalizada'])->default('ativa');

            $table->text('observacao')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencias');
    }
};
