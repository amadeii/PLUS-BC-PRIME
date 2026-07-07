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
        Schema::create('tef_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->integer('venda_id')->nullable();

            $table->string('tef_session_id', 80)->nullable();
            $table->string('tef_terminal_id', 60)->nullable();
            $table->string('tef_store_id', 60)->nullable();

            $table->string('tef_clisitef_status', 40)->nullable();
            $table->string('tef_function_id', 40)->nullable();
            $table->string('tef_controle', 60)->nullable();
            $table->string('tef_sitef_ip', 45)->nullable();

            $table->string('tef_nsu', 40)->nullable();
            $table->string('tef_codigo_autorizacao', 60)->nullable();
            $table->string('tef_bandeira', 40)->nullable();
            $table->string('tef_adquirente', 60)->nullable();
            $table->json('tef_raw')->nullable();
            $table->json('comprovantes')->nullable();

            $table->boolean('cancelado')->deafult(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tef_logs');
    }
};
