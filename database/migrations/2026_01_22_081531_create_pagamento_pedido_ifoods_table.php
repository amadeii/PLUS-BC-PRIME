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
        Schema::create('pagamento_pedido_ifoods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')->constrained('pedido_ifoods');
            $table->decimal('valor', 12, 2);
            $table->string('tipo_pagamento', 30);
            $table->boolean('pre_pago')->default(false);

            $table->string('codigo_autorizacao', 50)->nullable();
            $table->string('bandeira_cartao', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamento_pedido_ifoods');
    }
};
