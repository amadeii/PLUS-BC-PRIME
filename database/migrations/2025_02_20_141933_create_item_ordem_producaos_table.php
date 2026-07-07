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
        Schema::create('item_ordem_producaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ordem_producao_id')->constrained('ordem_producaos');
            $table->foreignId('item_producao_id')->nullable()->constrained('item_producaos');
            $table->foreignId('produto_id')->constrained('produtos');

            $table->decimal('quantidade', 12,3);
            $table->integer('cliente_id')->nullable();
            $table->boolean('status')->default(0);
            $table->string('observacao', 100)->nullable();
            $table->string('numero_pedido', 100)->nullable();

            // alter table item_ordem_producaos add column cliente_id integer default null;
            // alter table item_ordem_producaos add column numero_pedido varchar(100) default null;
            // alter table item_ordem_producaos MODIFY column item_producao_id bigint unsigned default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_ordem_producaos');
    }
};
