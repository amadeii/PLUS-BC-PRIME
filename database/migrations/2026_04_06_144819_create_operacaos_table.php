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
        Schema::create('operacaos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10);
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('setor_id')->nullable()->constrained('setors');
            $table->string('nome', 200);
            $table->text('descricao');
            $table->integer('tempo_padrao');

            $table->unique(['empresa_id', 'codigo']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operacaos');
    }
};
