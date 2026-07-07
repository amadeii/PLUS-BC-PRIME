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
        Schema::create('fatura_clientes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('tipo_pagamento', 2)->nullable(); 
            $table->integer('dias_vencimento'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fatura_clientes');
    }
};
