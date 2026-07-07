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
        Schema::create('pedido_finalizacao_parcials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('pedido_id')->constrained('pedidos');
            $table->foreignId('nfce_id')->nullable()->constrained('nfces');

            $table->decimal('valor_pago', 10, 2);
            $table->decimal('saldo_antes', 10, 2);
            $table->decimal('saldo_depois', 10, 2);

            $table->string('cpf_nota', 20)->nullable();
            $table->text('observacao')->nullable();
            $table->string('status', 30)->default('salvo');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_finalizacao_parcials');
    }
};
