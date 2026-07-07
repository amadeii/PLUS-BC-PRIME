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
        Schema::create('rastro_xmls', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_nfe_id')->nullable()->constrained('item_nves');
            $table->string('nLote', 100)->nullable();
            $table->decimal('qLote', 12, 3)->nullable();
            $table->date('dFab')->nullable();
            $table->date('dVal')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rastro_xmls');
    }
};
