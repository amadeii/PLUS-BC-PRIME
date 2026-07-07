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
        Schema::create('credito_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->decimal('valor', 12, 2);

            $table->integer('troca_id')->nullable();
            $table->boolean('status')->default(1);

            // alter table credito_clientes add column troca_id integer default null;
            // alter table credito_clientes add column status boolean default 1;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_clientes');
    }
};
