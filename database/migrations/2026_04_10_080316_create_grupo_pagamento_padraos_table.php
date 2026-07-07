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
        Schema::create('grupo_pagamento_padraos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('nome_pagador')->nullable();
            $table->string('documento_pagador')->nullable();
            $table->decimal('valor_transporte', 15, 2)->nullable();
            $table->string('indicador_pagamento')->nullable();

            // COMPONENTES
            $table->string('tipo_componente')->nullable();
            $table->decimal('valor_componente', 15, 2)->nullable();
            $table->string('descricao')->nullable();

            // PARCELAMENTO
            $table->decimal('valor_parcela', 15, 2)->nullable();
            $table->date('vencimento')->nullable();

            // DADOS BANCÁRIOS
            $table->string('codigo_banco')->nullable();
            $table->string('codigo_agencia')->nullable();
            $table->string('cnpj_iof')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_pagamento_padraos');
    }
};
