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
        Schema::create('recorrencia_servicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recorrencia_id')->constrained('recorrencias')->onDelete('cascade');
            $table->foreignId('servico_id')->constrained('servicos')->onDelete('cascade');

            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencia_servicos');
    }
};
