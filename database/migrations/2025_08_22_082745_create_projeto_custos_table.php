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
        Schema::create('projeto_custos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->integer('numero_sequencial')->nullable();
            $table->integer('numero_sequencial_ano')->nullable();
            $table->string('_id')->nullable();

            $table->text('descricao')->nullable();
            $table->text('observacao')->nullable();

            $table->date('data_prevista_entrega')->nullable();
            $table->date('data_entrega')->nullable();
            $table->string('arquivo', 25)->nullable();
            $table->integer('usuario_id')->nullable();
            $table->enum('estado', ['novo', 'cotacao', 'proposta', 'producao', 'finalizado', 'cancelado'])->default('novo');

            $table->integer('compra_id')->nullable();
            $table->integer('venda_id')->nullable();
            $table->integer('local_id')->nullable();

            $table->decimal('total_custo', 14, 2);
            $table->decimal('total_final', 14, 2);
            $table->decimal('desconto', 14, 2);
            $table->decimal('frete', 14, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projeto_custos');
    }
};
