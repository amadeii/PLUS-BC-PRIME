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
        Schema::create('item_ordem_separacaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ordem_id')->nullable()->constrained('ordem_separacaos');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->decimal('quantidade', 12,4);
            $table->enum('status', ['pendente', 'separado', 'sem_estoque']);
            $table->string('observacao_item', 255);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_ordem_separacaos');
    }
};
