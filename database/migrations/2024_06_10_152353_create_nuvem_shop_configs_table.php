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
        Schema::create('nuvem_shop_configs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('client_id', 10)->default('');
            $table->string('client_secret', 150)->default('');
            $table->string('email', 80)->default('');

            $table->string('store_id', 30)->nullable();
            $table->text('access_token')->nullable();
            $table->string('user_id_nuvemshop', 30)->nullable();
            $table->text('scope')->nullable();

            $table->boolean('autenticado')->default(false);
            $table->boolean('cron_para_separacao')->default(false);
            $table->timestamp('token_gerado_em')->nullable();
            $table->timestamp('ultimo_sync')->nullable();

            // alter table nuvem_shop_configs add column store_id varchar(30) default null;
            // alter table nuvem_shop_configs add column user_id_nuvemshop varchar(30) default null;
            // alter table nuvem_shop_configs add column access_token text;
            // alter table nuvem_shop_configs add column scope text;
            // alter table nuvem_shop_configs add column autenticado boolean default 0;
            // alter table nuvem_shop_configs add column cron_para_separacao boolean default 0;
            // alter table nuvem_shop_configs add column token_gerado_em timestamp default null;
            // alter table nuvem_shop_configs add column ultimo_sync timestamp default null;


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nuvem_shop_configs');
    }
};
