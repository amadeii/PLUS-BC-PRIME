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
        Schema::create('ordem_producaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios');
            $table->foreignId('usuario_id')->constrained('users');
            $table->text('observacao');
            $table->enum('estado', ['novo', 'producao', 'expedicao', 'entregue']);
            $table->date('data_prevista_entrega')->nullable();
            $table->integer('codigo_sequencial')->nullable();
            $table->string('hash_link', 30)->nullable();
            $table->integer('nfe_id')->nullable();
            $table->integer('orcamento_id')->nullable();

            $table->timestamps();

            // alter table ordem_producaos add column hash_link varchar(30) default null;
            // alter table ordem_producaos add column nfe_id integer default null;
            // alter table ordem_producaos add column orcamento_id integer default null;

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_producaos');
    }
};
