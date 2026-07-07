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
        Schema::create('fechamento_mensal_vendas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fechamento_id')->constrained('fechamento_mensals');

            $table->string('tipo', 10);
            $table->string('codigo', 10);
            $table->string('cliente', 100);
            $table->string('data', 100);
            $table->decimal('valor', 15, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamento_mensal_vendas');
    }
};
