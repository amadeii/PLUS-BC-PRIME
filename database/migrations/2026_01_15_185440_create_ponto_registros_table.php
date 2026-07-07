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
        Schema::create('ponto_registros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('funcionario_id')->constrained('funcionarios');

            $table->dateTime('data_hora');

            $table->enum('tipo', [
                'entrada',
                'saida',
                'intervalo_inicio',
                'intervalo_fim'
            ]);

            $table->string('ip', 45)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('status', [
                'valido',
                'suspeito',
                'ajustado'
            ])->default('valido');
            $table->string('hash_integridade', 64);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ponto_registros');
    }
};
