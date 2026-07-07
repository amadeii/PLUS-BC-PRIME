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
        Schema::create('cliente_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->integer('score_total')->default(0);

            $table->integer('score_pagamentos')->default(0);
            $table->integer('score_volume')->default(0);
            $table->integer('score_tempo')->default(0);
            $table->integer('score_ticket')->default(0);
            $table->integer('score_penalidades')->default(0);

            $table->enum('categoria', ['ouro','prata','bronze'])->default('bronze');
            $table->decimal('limite_credito', 12, 2)->default(0);

            $table->unique('cliente_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_scores');
    }
};
