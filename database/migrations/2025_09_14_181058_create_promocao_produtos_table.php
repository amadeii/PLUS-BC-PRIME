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
        Schema::create('promocao_produtos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('produto_id')->constrained('produtos');

            $table->decimal('valor', 12,4);
            $table->decimal('valor_original', 12,4);
            $table->boolean('status');
            $table->date('data_inicio');
            $table->date('data_fim');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocao_produtos');
    }
};
