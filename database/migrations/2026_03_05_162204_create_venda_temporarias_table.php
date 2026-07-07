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
        Schema::create('venda_temporarias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->integer('usuario_id');
            $table->integer('cliente_id');
            $table->integer('venda_vinculada')->nullable();

            $table->string('tabela', 20);
            $table->enum('estado', ['em_aberto', 'abandonada', 'finalizada'])->default('em_aberto');
            $table->decimal('total', 20, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venda_temporarias');
    }
};
