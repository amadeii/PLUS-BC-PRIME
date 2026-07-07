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
        Schema::create('asaas_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('token', 255);
            $table->boolean('sandbox')->default(1);

            $table->integer('ultimo_numero_boleto')->default(1);
            $table->decimal('juros_padrao', 5,2)->default(0);
            $table->decimal('multa_padrao', 5,2)->default(0);
            $table->string('observacao_padrao', 80)->nullable();
            $table->boolean('status')->default(1);
            
            // alter table asaas_configs add column status boolean default 1;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asaas_configs');
    }
};
