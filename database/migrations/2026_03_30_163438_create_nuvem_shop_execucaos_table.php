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
        Schema::create('nuvem_shop_execucaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('config_id')->constrained('nuvem_shop_configs');

            $table->integer('pedidos_processados')->default(0);
            $table->integer('pedidos_novos')->default(0);
            $table->integer('pedidos_atualizados')->default(0);

            $table->integer('ordens_separacao_criadas')->default(0);
            $table->integer('ordens_separacao_erro')->default(0);

            $table->string('status', 20)->default('processando');
            $table->text('mensagem')->nullable();

            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('finalizado_em')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nuvem_shop_execucaos');
    }
};
