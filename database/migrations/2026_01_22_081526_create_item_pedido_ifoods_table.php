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
        Schema::create('item_pedido_ifoods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')->constrained('pedido_ifoods');
            $table->string('nome', 200);
            $table->string('id_pedido', 50);
            $table->string('id_unico', 50);
            $table->string('codigo_externo', 50);
            $table->string('ean', 50);
            $table->string('unidade', 10);
            $table->decimal('quantidade', 10,2);
            $table->decimal('valor_unitario', 10,2);
            
            $table->decimal('valor_adicionais', 10,2);
            $table->decimal('valor_personalizado', 10,2);

            $table->decimal('sub_total', 10,2);

            $table->string('observacao', 255)->nullable();
            $table->string('imagem_url', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pedido_ifoods');
    }
};
