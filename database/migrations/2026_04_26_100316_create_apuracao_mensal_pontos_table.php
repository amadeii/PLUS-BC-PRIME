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
        Schema::create('apuracao_mensal_pontos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('apuracao_id')
            ->constrained('apuracao_mensals')
            ->onDelete('cascade');

            $table->date('data');
            $table->string('dia_semana', 30);

            $table->string('entrada', 10)->nullable();
            $table->string('intervalo_inicio', 10)->nullable();
            $table->string('intervalo_fim', 10)->nullable();
            $table->string('saida', 10)->nullable();

            $table->string('horas_previstas', 10)->nullable();
            $table->string('horas_trabalhadas', 10)->nullable();
            $table->string('horas_extras', 10)->nullable();
            $table->string('horas_faltas', 10)->nullable();
            $table->string('atraso', 10)->nullable();
            $table->string('saida_antecipada', 10)->nullable();

            $table->string('status', 30)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apuracao_mensal_pontos');
    }
};
