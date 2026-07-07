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
        Schema::create('planejamento_custos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->integer('numero_sequencial')->nullable();

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

            $table->string('codigo_material', 60)->nullable();
            $table->string('equipamento', 200)->nullable();
            $table->string('desenho', 200)->nullable();
            $table->string('material', 200)->nullable();
            $table->decimal('quantidade', 10, 2);
            $table->string('unidade', 20)->nullable();
            $table->integer('projeto_id')->nullable();

            // alter table planejamento_custos add column desconto decimal(14,2) default null;
            // alter table planejamento_custos add column frete decimal(14,2) default null;

            // alter table planejamento_custos add column codigo_material varchar(60) default null;
            // alter table planejamento_custos add column equipamento varchar(200) default null;
            // alter table planejamento_custos add column desenho varchar(200) default null;
            // alter table planejamento_custos add column material varchar(200) default null;
            // alter table planejamento_custos add column quantidade decimal(10,2) default null;
            // alter table planejamento_custos add column unidade varchar(20) default null;
            // alter table planejamento_custos add column projeto_id integer default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planejamento_custos');
    }
};
