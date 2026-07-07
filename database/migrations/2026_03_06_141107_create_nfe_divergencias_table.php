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
        Schema::create('nfe_divergencias', function (Blueprint $table) {
            $table->id();

            $table->integer('nfe_id');
            $table->string('tipo');
            $table->string('produto')->nullable();

            $table->string('status');

            $table->decimal('valor_xml', 15,2)->nullable();
            $table->decimal('valor_compra', 15,2)->nullable();

            $table->decimal('quantidade_xml', 15,2)->nullable();
            $table->decimal('quantidade_compra', 15,2)->nullable();

            $table->date('vencimento_xml')->nullable();
            $table->date('vencimento_compra')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_divergencias');
    }
};
