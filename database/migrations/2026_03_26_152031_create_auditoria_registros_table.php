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
        Schema::create('auditoria_registros', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('entidade', 120);
            $table->unsignedBigInteger('registro_id');
            $table->string('acao', 30);

            $table->json('antes_json')->nullable();
            $table->json('depois_json')->nullable();
            $table->json('alteracoes_json')->nullable();

            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['entidade', 'registro_id']);
            $table->index('usuario_id');
            $table->index('empresa_id');
            $table->index('acao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_registros');
    }
};
