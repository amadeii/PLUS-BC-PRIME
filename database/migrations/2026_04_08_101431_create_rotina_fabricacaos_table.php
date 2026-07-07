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
        Schema::create('rotina_fabricacaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');

            $table->string('imagem')->nullable();
            $table->integer('user_id')->nullable();
            $table->decimal('lote_minimo', 15, 4)->default(1);

            $table->longText('instrucoes_especiais')->nullable();
            $table->longText('checklist_texto')->nullable();
            $table->longText('assinaturas')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotina_fabricacaos');
    }
};
