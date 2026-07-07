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
        Schema::create('fechamento_mensals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('mes', 7);
            $table->decimal('total_vendas', 12, 2)->default(0);
            $table->decimal('total_despesas', 12, 2)->default(0);
            $table->decimal('lucro_estimado', 12, 2)->default(0);
            $table->decimal('ticket_medio', 12, 2)->default(0);
            $table->json('dados')->nullable();
            $table->timestamp('fechado_em')->nullable();
            $table->unsignedBigInteger('fechado_por')->nullable();

            $table->unique(['empresa_id', 'mes']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fechamento_mensals');
    }
};
