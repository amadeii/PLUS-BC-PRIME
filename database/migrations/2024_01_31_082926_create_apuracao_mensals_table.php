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
        Schema::create('apuracao_mensals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('funcionario_id')->constrained('funcionarios')->onDelete('cascade');
            $table->string('mes', 20);
            $table->integer('ano');
            $table->decimal('valor_final', 10, 2);
            $table->string('forma_pagamento', 30);
            $table->string('observacao', 100)->nullable();
            $table->integer('conta_pagar_id')->default(0);

            $table->string('horas_previstas', 10)->nullable();
            $table->string('horas_trabalhadas', 10)->nullable();
            $table->string('horas_extras', 10)->nullable();
            $table->string('horas_faltas', 10)->nullable();
            $table->string('horas_atrasos', 10)->nullable();
            $table->string('horas_saida_antecipada', 10)->nullable();
            $table->string('saldo_horas', 10)->nullable();

            $table->integer('saldo_minutos')->default(0);
            $table->integer('faltas')->default(0);
            $table->integer('dias_com_ponto')->default(0);
            $table->integer('dias_com_extra')->default(0);
            $table->integer('dias_incompletos')->default(0);

            // alter table apuracao_mensals add column horas_previstas varchar(10) default null;
            // alter table apuracao_mensals add column horas_trabalhadas varchar(10) default null;
            // alter table apuracao_mensals add column horas_extras varchar(10) default null;
            // alter table apuracao_mensals add column horas_faltas varchar(10) default null;
            // alter table apuracao_mensals add column horas_atrasos varchar(10) default null;
            // alter table apuracao_mensals add column horas_saida_antecipada varchar(10) default null;
            // alter table apuracao_mensals add column saldo_horas varchar(10) default null;

            // alter table apuracao_mensals add column saldo_minutos int default 0;
            // alter table apuracao_mensals add column faltas int default 0;
            // alter table apuracao_mensals add column dias_com_ponto int default 0;
            // alter table apuracao_mensals add column dias_com_extra int default 0;
            // alter table apuracao_mensals add column dias_incompletos int default 0;

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apuracao_mensals');
    }
};
