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
        Schema::create('recorrencia_contratos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('recorrencia_id');
            $table->unsignedBigInteger('modelo_id')->nullable();

            $table->string('numero')->nullable();
            $table->string('titulo')->nullable();
            $table->longText('conteudo');

            $table->date('data_geracao')->nullable();
            $table->date('data_assinatura')->nullable();

            $table->string('status')->default('gerado');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->cascadeOnDelete();
            $table->foreign('recorrencia_id')->references('id')->on('recorrencias')->cascadeOnDelete();
            $table->foreign('modelo_id')->references('id')->on('recorrencia_contrato_modelos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencia_contratos');
    }
};
