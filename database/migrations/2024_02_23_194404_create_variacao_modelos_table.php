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
        Schema::create('variacao_modelos', function (Blueprint $table) {
            $table->id();

            $table->string('descricao', 200);
            $table->boolean('status')->default(1);
            $table->string('vendizap_id', 50)->nullable();
            $table->foreignId('empresa_id')->constrained('empresas');

            // alter table variacao_modelos add column vendizap_id varchar(50) default null;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variacao_modelos');
    }
};
