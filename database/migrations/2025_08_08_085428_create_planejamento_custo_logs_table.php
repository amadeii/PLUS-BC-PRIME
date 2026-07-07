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
        Schema::create('planejamento_custo_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('planejamento_id')->nullable()->constrained('planejamento_custos');
            $table->integer('usuario_id')->nullable();

            $table->string('estado_anterior', 20);
            $table->string('estado_alterado', 20);
            $table->string('observacao', 255);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planejamento_custo_logs');
    }
};
