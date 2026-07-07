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
        Schema::create('pedido_finalizacao_parcial_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pedido_finalizacao_parcial_id');
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('item_pedido_id');
            $table->unsignedBigInteger('produto_id')->nullable();

            $table->decimal('quantidade', 10, 4)->default(1);
            $table->decimal('valor_unitario', 16, 7)->default(0);
            $table->decimal('sub_total', 16, 7)->default(0);

            $table->timestamps();

            $table->foreign('pedido_finalizacao_parcial_id', 'pfp_item_parcial_fk')
            ->references('id')
            ->on('pedido_finalizacao_parcials')
            ->cascadeOnDelete();

            $table->foreign('pedido_id', 'pfp_pedido_fk')
            ->references('id')
            ->on('pedidos')
            ->cascadeOnDelete();

            $table->foreign('item_pedido_id', 'pfp_item_fk')
            ->references('id')
            ->on('item_pedidos')
            ->cascadeOnDelete();

            $table->foreign('produto_id', 'pfp_produto_fk')
            ->references('id')
            ->on('produtos')
            ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_finalizacao_parcial_items');
    }
};
