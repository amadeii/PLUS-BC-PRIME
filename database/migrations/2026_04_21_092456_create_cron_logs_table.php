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
        Schema::create('cron_logs', function (Blueprint $table) {
            $table->id();

            $table->string('comando'); // boletos:verificar / boletos:verificar-asaas
            $table->string('origem')->nullable(); // sicredi / asaas

            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('boleto_id')->nullable();

            $table->string('status'); // SUCESSO / ERRO / INFO
            $table->text('mensagem')->nullable();

            $table->json('payload')->nullable();

            $table->timestamp('executado_em');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_logs');
    }
};
