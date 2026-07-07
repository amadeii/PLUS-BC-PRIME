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
        Schema::create('imagem_ordem_producaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ordem_producao_id')->constrained('ordem_producaos')->cascadeOnDelete();
            $table->string('imagem');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagem_ordem_producaos');
    }
};
