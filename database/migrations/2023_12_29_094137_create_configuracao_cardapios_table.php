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
        Schema::create('configuracao_cardapios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->string('nome_restaurante', 100);

            $table->text('descricao_restaurante_pt')->nullable();
            $table->text('descricao_restaurante_en')->nullable();
            $table->text('descricao_restaurante_es')->nullable();

            $table->string('logo', 25);
            $table->string('fav_icon', 25);
            $table->string('telefone', 25);
            $table->string('rua', 80);
            $table->string('numero', 25);
            $table->string('bairro', 25);
            $table->foreignId('cidade_id')->constrained('cidades');
            $table->string('api_token', 25);

            $table->string('link_instagran', 150)->nullable();
            $table->string('link_facebook', 150)->nullable();
            $table->string('link_whatsapp', 150)->nullable();
            $table->boolean('intercionalizar')->default(0);
            $table->boolean('incluir_servico')->default(0);
            $table->boolean('qr_code_mesa')->default(0);
            $table->boolean('confirma_mesa')->default(0);
            $table->enum('valor_pizza', ['divide', 'valor_maior'])->default('divide');
            $table->decimal('percentual_taxa_servico', 5,2)->default(0);
            $table->string('cor_principal', 10)->nullable();
            $table->integer('limite_pessoas_qr_code')->nullable();

            // alter table configuracao_cardapios add column valor_pizza enum('divide', 'valor_maior') default 'divide';
            // alter table configuracao_cardapios add column incluir_servico boolean default 0;
            // alter table configuracao_cardapios add column qr_code_mesa boolean default 0;
            // alter table configuracao_cardapios add column confirma_mesa boolean default 0;
            // alter table configuracao_cardapios add column percentual_taxa_servico decimal(5,2) default 0;
            // alter table configuracao_cardapios add column cor_principal varchar(10) default null;
            // alter table configuracao_cardapios add column limite_pessoas_qr_code integer default null;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracao_cardapios');
    }
};
