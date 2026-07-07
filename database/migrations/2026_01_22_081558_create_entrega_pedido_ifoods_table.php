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
        Schema::create('entrega_pedido_ifoods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')->constrained('pedido_ifoods');

            $table->string('descricao', 100);
            $table->string('rua', 200);
            $table->string('numero', 20);
            $table->string('bairro', 100);
            $table->string('complemento', 100);
            $table->string('referencia', 100);
            $table->string('cidade', 100);
            $table->string('uf', 2);
            $table->string('cep', 10);

            $table->string('latitude', 20);
            $table->string('longitude', 20);
            $table->string('observacao', 255);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrega_pedido_ifoods');
    }
};
