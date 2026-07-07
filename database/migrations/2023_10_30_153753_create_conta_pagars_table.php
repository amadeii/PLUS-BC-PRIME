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
        Schema::create('conta_pagars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->foreignId('nfe_id')->nullable()->constrained('nves');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedors');

            $table->string('descricao', 200)->nullable();
            $table->string('referencia', 200)->nullable();
            $table->string('arquivo', 25)->nullable();

            $table->decimal('valor_integral', 16, 7);
            $table->decimal('valor_pago', 16, 7)->nullable();
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->boolean('status')->default(0);

            $table->string('observacao', 100)->nullable();
            $table->string('observacao2', 100)->nullable();
            $table->string('observacao3', 100)->nullable();
            $table->string('tipo_pagamento', 2)->nullable();

            $table->foreignId('caixa_id')->nullable()->constrained('caixas');
            $table->integer('local_id')->nullable();
            $table->string('motivo_estorno', 255)->nullable();
            $table->integer('categoria_conta_id')->nullable();

            $table->decimal('desconto', 16, 7)->nullable();
            $table->decimal('acrescimo', 16, 7)->nullable();
            $table->integer('conta_empresa_id')->nullable();
            $table->integer('plano_conta_id')->nullable();


            $table->boolean('desativado')->default(0);

            $table->timestamps();

            // alter table conta_pagars add column caixa_id bigint(20) default null;
            // alter table conta_pagars add column local_id integer default null;
            // alter table conta_pagars add column arquivo varchar(25) default null;
            // alter table conta_pagars add column motivo_estorno varchar(255) default null;
            // alter table conta_pagars add column categoria_conta_id integer default null;
            // alter table conta_pagars add column desconto decimal(16, 7) default null;
            // alter table conta_pagars add column acrescimo decimal(16, 7) default null;

            // alter table conta_pagars add column observacao2 varchar(100) default null;
            // alter table conta_pagars add column observacao3 varchar(100) default null;
            // alter table conta_pagars add column referencia varchar(60) default null;
            // alter table conta_pagars add column conta_empresa_id integer default null;

            // alter table conta_pagars add column plano_conta_id integer default null;
            // alter table conta_pagars add column desativado boolean default 0;
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_pagars');
    }
};
