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
        Schema::create('retirada_estoques', function (Blueprint $table) {
            $table->id();

            $table->string('motivo', 100);
            $table->string('observacao', 255)->nullable();
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->decimal('quantidade', 10,2);
            $table->integer('local_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retirada_estoques');
    }
};
