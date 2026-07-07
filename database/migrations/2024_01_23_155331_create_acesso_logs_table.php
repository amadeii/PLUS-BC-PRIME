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
        Schema::create('acesso_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->string('ip', 20);
            $table->boolean('acesso_bloqueado')->default(0);

            // alter table acesso_logs add column acesso_bloqueado boolean default null;
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acesso_logs');
    }
};
