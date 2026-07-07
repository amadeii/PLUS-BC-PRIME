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
        Schema::create('pedido_ifoods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('cliente_nome', 100);
            $table->string('cliente_documento', 20);
            $table->string('ifood_id', 50);
            $table->string('tipo_pedido', 50);
            $table->string('id_exibicao', 50);
            $table->timestamp('data_pedido')->nullable();

            $table->decimal('valor_produtos', 12, 2);
            $table->decimal('valor_entrega', 12, 2);
            $table->decimal('valor_adicional', 12, 2);
            $table->decimal('total', 12, 2);

            $table->string('informacao_adicional', 255)->nullable();

            $table->timestamps();

            // alter table pedido_ifoods add column informacao_adicional varchar(255) default null;

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_ifoods');
    }
};
