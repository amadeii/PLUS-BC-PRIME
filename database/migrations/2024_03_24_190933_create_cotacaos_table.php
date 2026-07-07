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
        Schema::create('cotacaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('fornecedor_id')->constrained('fornecedors');

            $table->string('responsavel', 50)->nullable();
            $table->string('hash_link', 30);
            $table->string('referencia', 15);
            $table->string('observacao_resposta', 200)->nullable();
            $table->string('observacao', 200)->nullable();
            $table->boolean('status')->default(1);
            $table->decimal('valor_total', 10,2)->nullable();
            $table->decimal('desconto', 10,2)->nullable();
            $table->enum('estado', ['nova', 'respondida', 'aprovada', 'rejeitada']);
            $table->boolean('escolhida')->default(0);
            $table->timestamp('data_resposta')->nullable();
            $table->integer('nfe_id')->nullable();
            
            $table->decimal('valor_frete', 10,2)->nullable();
            $table->string('observacao_frete', 200)->nullable();
            $table->date('previsao_entrega')->nullable();

            $table->integer('planejamento_id')->nullable();

            // alter table cotacaos add column planejamento_id integer default null;
            // alter table cotacaos add column valor_frete decimal(10,2) default null;
            // alter table cotacaos add column observacao_frete varchar(200) default null;
            // alter table cotacaos add column previsao_entrega date default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotacaos');
    }
};
