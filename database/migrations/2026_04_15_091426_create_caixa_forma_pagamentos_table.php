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
        Schema::create('caixa_forma_pagamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('caixa_id')->constrained('caixas')->onDelete('cascade');

            $table->string('nome', 100);
            $table->decimal('valor', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caixa_forma_pagamentos');
    }
};
