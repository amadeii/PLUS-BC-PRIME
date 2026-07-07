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
        Schema::create('ponto_configuracaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->unique()->constrained('empresas');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('raio_permitido')->default(100); // metros

            $table->boolean('permitir_fora_area')->default(false);
            $table->boolean('exigir_observacao_fora_area')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ponto_configuracaos');
    }
};
