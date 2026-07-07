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
        Schema::create('item_trocas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('troca_id')->constrained('trocas');
            $table->foreignId('produto_id')->constrained('produtos');

            $table->decimal('valor_unitario', 10,2);
            $table->decimal('sub_total', 10,2);

            // alter table item_trocas add column valor_unitario decimal(10,2) default null;
            // alter table item_trocas add column sub_total decimal(10,2) default null;
            
            $table->decimal('quantidade', 7,3);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_trocas');
    }
};
