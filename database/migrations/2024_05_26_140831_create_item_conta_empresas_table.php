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
        Schema::create('item_conta_empresas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conta_id')->nullable()->constrained('conta_empresas');

            $table->string('descricao', 150)->nullable();
            $table->integer('caixa_id')->nullable();
            $table->string('tipo_pagamento', 2);
            $table->decimal('valor', 16, 2)->nullable();
            $table->decimal('saldo_atual', 16, 2)->nullable();
            $table->enum('tipo', ['entrada', 'saida']);

            $table->integer('cliente_id')->nullable();
            $table->integer('fornecedor_id')->nullable();
            $table->integer('categoria_id')->nullable();
            $table->string('numero_documento', 100)->nullable();

            $table->integer('conta_pagar_id')->nullable();
            $table->integer('conta_receber_id')->nullable();

            // alter table item_conta_empresas add column cliente_id integer default null;
            // alter table item_conta_empresas add column fornecedor_id integer default null;
            // alter table item_conta_empresas add column categoria_id integer default null;
            // alter table item_conta_empresas add column numero_documento varchar(100) default null;

            // alter table item_conta_empresas add column conta_pagar_id integer default null;
            // alter table item_conta_empresas add column conta_receber_id integer default null;


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_conta_empresas');
    }
};
