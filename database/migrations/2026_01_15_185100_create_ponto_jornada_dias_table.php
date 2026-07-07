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
        Schema::create('ponto_jornada_dias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jornada_id')->constrained('ponto_jornadas');
            $table->integer('dia_semana');
            $table->time('entrada')->nullable();
            $table->time('intervalo_inicio')->nullable();
            $table->time('intervalo_fim')->nullable();
            $table->time('saida')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ponto_jornada_dias');
    }
};
