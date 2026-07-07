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
        Schema::create('carrinho_cardapios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('session_cart_cardapio', 30);
            $table->string('cliente_nome', 50)->nullable();
            $table->decimal('valor_total', 10, 2);
            $table->string('observacao', 200)->nullable();
            $table->enum('estado', ['pendente', 'finalizado']);
            $table->string('session_cart_user', 30)->nullable();
            
            // alter table carrinho_cardapios add column cliente_nome varchar(50) default null;
            // alter table carrinho_cardapios add column session_cart_user varchar(30) default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrinho_cardapios');
    }
};
