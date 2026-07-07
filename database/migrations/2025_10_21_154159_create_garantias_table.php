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
        Schema::create('garantias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('usuario_id')->constrained('users');

            $table->foreignId('produto_id')->constrained('produtos')->nullable();
            $table->foreignId('servico_id')->constrained('servicos')->nullable();

            $table->foreignId('cliente_id')->constrained('clientes');
            $table->integer('nfe_id')->nullable();
            $table->integer('nfce_id')->nullable();
            $table->integer('ordem_servico_id')->nullable();

            $table->date('data_venda')->nullable();
            $table->date('data_solicitacao')->nullable();
            $table->integer('prazo_garantia')->default(0);

            $table->text('descricao_problema');
            $table->text('observacao');

            $table->decimal('valor_reparo', 10, 2)->nullable();

            $table->enum('status', ['registrada', 'em análise', 'aprovada', 'recusada', 'concluída', 'expirada'])->default('registrada');
            $table->timestamps();

            // alter table garantias add column servico_id integer default null;
            // alter table garantias modify column produto_id integer default null;
            // alter table garantias add column ordem_servico_id integer default null;

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantias');
    }
};
