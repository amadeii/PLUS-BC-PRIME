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
        Schema::create('pdv_pesagem_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
            ->constrained('empresas')
            ->cascadeOnDelete();

            $table->foreignId('produto_id')
            ->constrained('produtos')
            ->cascadeOnDelete();
            $table->boolean('sem_peso')->default(false);

            $table->boolean('status')->default(true);
            $table->integer('ordem')->default(0);
            $table->decimal('valor', 12,4);
            
            // alter table pdv_pesagem_items add column sem_peso boolean default 0;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdv_pesagem_items');
    }
};
