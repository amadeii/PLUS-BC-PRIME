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
        Schema::create('adicional_item_pedido_ifoods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_pedido_id')->constrained('item_pedido_ifoods');
            $table->string('nome', 200);
            $table->string('tipo', 20);

            $table->decimal('quantidade', 10,2);
            $table->decimal('valor_unitario', 10,2);
            $table->decimal('sub_total', 10,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adicional_item_pedido_ifoods');
    }
};
