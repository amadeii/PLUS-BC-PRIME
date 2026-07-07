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
        Schema::create('gestao_custo_producao_servicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gestao_custo_id')->constrained('gestao_custo_producaos');

            $table->foreignId('servico_id')->constrained('servicos');
            $table->decimal('quantidade', 12,4);
            $table->decimal('valor_unitario', 10,2);
            $table->decimal('sub_total', 10,2);
            $table->string('observacao', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gestao_custo_producao_servicos');
    }
};
