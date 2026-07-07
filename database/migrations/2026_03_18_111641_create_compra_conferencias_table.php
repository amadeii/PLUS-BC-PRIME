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
        Schema::create('compra_conferencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->integer('compra_id');
            
            $table->integer('user_id')->nullable();
            $table->enum('status', ['pendente', 'conferido', 'divergente'])->default('pendente');
            $table->text('observacao')->nullable();
            $table->timestamp('conferido_em')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_conferencias');
    }
};
