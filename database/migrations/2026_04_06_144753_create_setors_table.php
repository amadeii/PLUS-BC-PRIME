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
        Schema::create('setors', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('codigo', 10);
            $table->string('nome', 200);
            $table->text('descricao');
            $table->decimal('horas_dia', 5,2);
            $table->decimal('custo_hora', 12,2);
            $table->decimal('eficiencia', 5,2);

            $table->foreignId('centro_custo_id')->nullable()->constrained('centro_custos');

            $table->unique(['empresa_id', 'codigo']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setors');
    }
};
