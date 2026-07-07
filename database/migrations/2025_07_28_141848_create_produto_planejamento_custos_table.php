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
        Schema::create('produto_planejamento_custos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('planejamento_id')->nullable()->constrained('planejamento_custos');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');

            $table->decimal('quantidade', 12,4);
            $table->decimal('valor_unitario', 10,2);
            $table->decimal('sub_total', 10,2);
            $table->boolean('status')->default(0);
            $table->string('observacao', 255)->nullable();

            $table->decimal('largura', 10,2)->nullable();
            $table->decimal('espessura', 10,2)->nullable();
            $table->decimal('comprimento', 10,2)->nullable();
            $table->decimal('peso_especifico', 10,2)->nullable();
            $table->decimal('peso_bruto', 10,2)->nullable();
            $table->decimal('calculo', 14,4)->nullable();

            // alter table produto_planejamento_custos add column peso_especifico decimal(10,2) default null;
            // alter table produto_planejamento_custos add column peso_bruto decimal(10,2) default null;
            // alter table produto_planejamento_custos add column calculo decimal(14,4) default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_planejamento_custos');
    }
};
