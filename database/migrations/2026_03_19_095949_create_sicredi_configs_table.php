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
        Schema::create('sicredi_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('x_api_key');
            $table->string('codigo_beneficiario', 20);
            $table->string('cooperativa', 10);
            $table->string('posto', 10);
            $table->string('username', 50);
            $table->text('password');

            $table->string('tipo_cobranca', 50)->default('NORMAL');
            $table->string('especie_documento', 50)->default('DUPLICATA_MERCANTIL_INDICACAO');
            $table->integer('ultimo_numero_boleto')->default(1);

            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            $table->decimal('juros_padrao', 5,2)->default(0);
            $table->decimal('multa_padrao', 5,2)->default(0);
            $table->string('observacao_padrao', 80)->nullable();

            $table->boolean('status')->default(1);

            // alter table sicredi_configs add column status boolean default 1;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sicredi_configs');
    }
};
