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
        Schema::create('item_pedido_vendi_zaps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')->constrained('pedido_vendi_zaps');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->string('vendizap_produto_id', 30);
            $table->string('descricao', 255);
            $table->text('detalhes');
            $table->string('unidade', 30);
            $table->string('observacao', 255)->nullable();
            $table->string('codigo', 30)->nullable();

            $table->decimal('valor', 12, 2);
            $table->decimal('valor_promociconal', 12, 2)->nullable();
            $table->decimal('quantidade', 12, 2);
            $table->decimal('sub_total', 12, 2);
            $table->decimal('valor_adicionais', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pedido_vendi_zaps');
    }
};
