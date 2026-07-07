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
        Schema::create('mensagem_padrao_crms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('titulo', 100);
            $table->text('mensagem');
            $table->boolean('status');
            $table->string('tipo', 30); // pos_venda, aniversario, reativacao

            $table->boolean('enviar_whatsapp');
            $table->boolean('enviar_email');
            $table->time('horario_envio')->nullable(); 
            $table->integer('dias_apos_venda')->nullable(); 
            $table->integer('dias_apos_agendamento')->nullable(); 
            $table->integer('dias_ultima_venda')->nullable();

            $table->boolean('mensagem_para_agendamento')->default(0);

            // alter table mensagem_padrao_crms add column mensagem_para_agendamento boolean default 0;
            // alter table mensagem_padrao_crms add column dias_apos_agendamento integer default 0;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensagem_padrao_crms');
    }
};
