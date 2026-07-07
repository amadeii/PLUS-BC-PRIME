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
        Schema::create('plano_empresas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('plano_id')->constrained('planos');

            $table->date('data_expiracao');
            $table->decimal('valor', 10, 2);
            $table->string('forma_pagamento', 30);

            $table->integer('contador_id')->nullable();

           // alter table plano_empresas add column contador_id integer default null;
            
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_empresas');
    }
};
