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
        Schema::create('item_proposta_planejamento_custos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('planejamento_id')->constrained('planejamento_custos');
            $table->string('descricao', 200);
            $table->decimal('quantidade', 12,4);

            $table->decimal('valor_unitario_custo', 10,2);
            $table->decimal('valor_unitario_final', 10,2);
            $table->decimal('sub_total_custo', 10,2);
            $table->decimal('sub_total_final', 10,2);

            $table->string('tipo', 20); // produto, mão de obra, serviço terceiro, custo adicionl
            $table->string('observacao', 255)->nullable();

            $table->integer('servico_id')->nullable();
            $table->integer('produto_id')->nullable();
            $table->boolean('terceiro')->default(0);

            $table->decimal('largura', 10,2)->nullable();
            $table->decimal('espessura', 10,2)->nullable();
            $table->decimal('comprimento', 10,2)->nullable();
            $table->decimal('peso_especifico', 10,2)->nullable();
            $table->decimal('peso_bruto', 10,2)->nullable();
            $table->decimal('calculo', 14,4)->nullable();

            // alter table item_proposta_planejamento_custos add column peso_bruto decimal(10,2) default null;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_proposta_planejamento_custos');
    }
};
