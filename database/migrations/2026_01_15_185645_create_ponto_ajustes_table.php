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
        Schema::create('ponto_ajustes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ponto_registro_id')->constrained('ponto_registros');
            $table->foreignId('usuario_id')->constrained('users');

            $table->string('motivo', 150);
            $table->text('justificativa')->nullable();

            $table->json('antes_json');
            $table->json('depois_json');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ponto_ajustes');
    }
};
