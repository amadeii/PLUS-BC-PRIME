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
        Schema::create('rotina_fabricacao_operacaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rotina_fabricacao_id')->constrained('rotina_fabricacaos')->onDelete('cascade');
            $table->foreignId('operacao_id')->nullable()->constrained('operacaos')->nullOnDelete();
            $table->foreignId('setor_id')->nullable()->constrained('setors')->nullOnDelete();
            $table->foreignId('centro_custo_id')->nullable()->constrained('centro_custos')->nullOnDelete();

            $table->text('descricao')->nullable();

            $table->integer('tempo_minutos')->default(0);
            $table->integer('setup_minutos')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotina_fabricacao_operacaos');
    }
};
