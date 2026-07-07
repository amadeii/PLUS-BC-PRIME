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
        Schema::create('compra_conferencia_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('compra_conferencia_id')->constrained('compra_conferencias');
            $table->integer('item_compra_id');

            $table->decimal('qtd_xml', 15, 4)->default(0);
            $table->decimal('qtd_conferida', 15, 4)->default(0);
            $table->decimal('diferenca', 15, 4)->default(0);
            $table->text('observacao')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_conferencia_items');
    }
};
