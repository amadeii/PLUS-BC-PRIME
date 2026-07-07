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
        Schema::create('ordem_separacaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->foreignId('nfe_id')->nullable()->constrained('nves');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->integer('numero_sequencial')->nullable();
            $table->enum('status', ['em_separacao', 'finalizado', 'cancelado']);
            $table->integer('funcionario_id')->nullable();

            $table->text('observacao');
            $table->string('motivo_cancelado', 255)->nullable();
            $table->enum('prioridade', ['normal', 'urgente'])->default('normal');

            $table->foreignId('usuario_id_inicia')->nullable()->constrained('users');
            $table->foreignId('usuario_id_finaliza')->nullable()->constrained('users');

            // alter table ordem_separacaos add column prioridade enum('normal', 'urgente') default 'normal';

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_separacaos');
    }
};
