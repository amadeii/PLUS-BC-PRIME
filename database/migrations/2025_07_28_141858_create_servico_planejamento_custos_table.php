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
        Schema::create('servico_planejamento_custos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('planejamento_id')->nullable()->constrained('planejamento_custos');
            $table->foreignId('servico_id')->nullable()->constrained('servicos');

            $table->decimal('quantidade', 12,4);
            $table->decimal('valor_unitario', 10,2);
            $table->decimal('sub_total', 10,2);
            $table->boolean('status')->default(0);
            $table->boolean('terceiro')->default(0);
            $table->string('observacao', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servico_planejamento_custos');
    }
};
