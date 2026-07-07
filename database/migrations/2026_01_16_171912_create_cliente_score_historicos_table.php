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
        Schema::create('cliente_score_historicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->integer('score_total');
            $table->enum('categoria', ['ouro','prata','bronze']);
            $table->decimal('limite_credito', 12, 2);

            $table->date('referencia_mes');
            $table->unique(['cliente_id', 'referencia_mes']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_score_historicos');
    }
};
