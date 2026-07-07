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
        Schema::create('conta_recebers', function (Blueprint $table) {
            $table->id();

            $table->integer('conta_receber_origem_id')->nullable();
            $table->integer('numero_sequencial')->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->foreignId('nfe_id')->nullable()->constrained('nves');
            $table->foreignId('nfce_id')->nullable()->constrained('nfces');
            $table->integer('ordem_servico_id')->nullable();

            $table->foreignId('cliente_id')->nullable()->constrained('clientes');

            $table->string('descricao', 200)->nullable();

            $table->string('referencia', 60)->nullable();
            $table->string('arquivo', 25)->nullable();
            
            $table->decimal('valor_integral', 16, 7);
            $table->decimal('valor_original', 16, 7)->nullable();
            $table->decimal('valor_recebido', 16, 7)->nullable();

            $table->decimal('valor_multa', 16, 7);
            $table->decimal('valor_juros', 16, 7);

            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->boolean('status')->default(0);
            $table->boolean('recebimento_parcial')->default(0);

            $table->string('observacao', 100)->nullable();
            $table->string('observacao2', 100)->nullable();
            $table->string('observacao3', 100)->nullable();
            $table->string('tipo_pagamento', 2)->nullable();

            $table->foreignId('caixa_id')->nullable()->constrained('caixas');
            $table->integer('local_id')->nullable();
            $table->integer('categoria_conta_id')->nullable();

            $table->string('motivo_estorno', 255)->nullable();
            $table->integer('conta_empresa_id')->nullable();

            $table->integer('plano_conta_id')->nullable();

            $table->timestamps();

            // alter table conta_recebers add column caixa_id bigint(20) default null;
            // alter table conta_recebers add column local_id integer default null;
            // alter table conta_recebers add column conta_receber_origem_id integer default null;
            // alter table conta_recebers add column arquivo varchar(25) default null;
            // alter table conta_recebers add column motivo_estorno varchar(255) default null;
            // alter table conta_recebers add column categoria_conta_id integer default null;
            // alter table conta_recebers add column valor_original decimal(16,7) default null;
            // alter table conta_recebers add column recebimento_parcial boolean default 0;

            // alter table conta_recebers add column observacao2 varchar(100) default null;
            // alter table conta_recebers add column observacao3 varchar(100) default null;
            // alter table conta_recebers add column referencia varchar(60) default null;
            // alter table conta_recebers add column conta_empresa_id integer default null;
            // alter table conta_recebers add column ordem_servico_id integer default null;

            // alter table conta_recebers add column plano_conta_id integer default null;

            // alter table conta_recebers add column valor_multa decimal(16,7) default 0;
            // alter table conta_recebers add column valor_juros decimal(16,7) default 0;
            // alter table conta_recebers add column numero_sequencial integer default null;
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_recebers');
    }
};
