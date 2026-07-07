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
        Schema::create('score_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained();
            $table->json('pagamentos');
            $table->json('volume');
            $table->json('tempo');
            $table->json('ticket');
            $table->json('penalidades');
            $table->json('categorias');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_configs');
    }
};
