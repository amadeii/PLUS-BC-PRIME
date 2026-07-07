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
        Schema::create('pdv_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->string('acao', 30); // desconto, acrescimo, item removido

            $table->decimal('valor_desconto', 10, 2)->nullable();
            $table->decimal('valor_acrescimo', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdv_logs');
    }
};
