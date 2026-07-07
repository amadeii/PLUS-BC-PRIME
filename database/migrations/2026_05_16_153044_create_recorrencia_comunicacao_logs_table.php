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
        Schema::create('recorrencia_comunicacao_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->integer('recorrencia_cobranca_id');
            $table->integer('regra_id');

            $table->string('canal', 20);
            // email, whatsapp

            $table->string('destino')->nullable();
            $table->date('data_referencia')->nullable();
            $table->string('status', 30)->default('enviado'); 
            // enviado, erro

            $table->text('erro')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->unique([
                'recorrencia_cobranca_id',
                'regra_id',
                'canal',
                'data_referencia'
            ], 'recorrencia_comunicacao_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencia_comunicacao_logs');
    }
};
