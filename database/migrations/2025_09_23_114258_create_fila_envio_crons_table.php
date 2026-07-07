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
        Schema::create('fila_envio_crons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->text('mensagem');

            $table->timestamp('enviado_em')->nullable();
            $table->date('agendar_para')->nullable();

            $table->enum('status', ['pendente', 'enviado', 'erro'])->default('pendente');
            $table->text('erro')->nullable();

            $table->boolean('enviar_whatsapp');
            $table->boolean('enviar_email');
            $table->string('whatsapp', 20)->nullable();
            $table->string('email', 60)->nullable();
            $table->string('tipo', 30); // pos_venda, aniversario, reativacao
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fila_envio_crons');
    }
};
