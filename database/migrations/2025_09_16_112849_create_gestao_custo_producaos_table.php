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
        Schema::create('gestao_custo_producaos', function (Blueprint $table) {
            $table->id();

            $table->integer('numero_sequencial')->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');

            $table->date('data_finalizacao')->nullable();
            $table->boolean('status');

            $table->decimal('total_custo_produtos', 14, 2);
            $table->decimal('total_custo_servicos', 14, 2);
            $table->decimal('total_custo_outros', 14, 2);
            $table->decimal('desconto', 14, 2)->nullable();
            $table->decimal('total_final', 14, 2);
            $table->decimal('frete', 14, 2)->nullable();
            $table->decimal('quantidade', 12,4);
            $table->integer('usuario_id')->nullable();
            $table->string('observacao')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestao_custo_producaos');
    }
};
