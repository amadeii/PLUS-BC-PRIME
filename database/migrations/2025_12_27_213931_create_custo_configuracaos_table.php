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
        Schema::create('custo_configuracaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->decimal('imposto_percentual', 6, 2)->default(0);
            $table->decimal('taxa_cartao_percentual', 6, 2)->default(0);
            $table->decimal('despesas_percentual', 6, 2)->default(0);
            $table->decimal('margem_minima_percentual', 6, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custo_configuracaos');
    }
};
