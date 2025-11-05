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
        if (!Schema::hasTable('offSet_pza')) {
            Schema::create('offSet_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('anchoRanura', 8, 3)->nullable();
                $table->decimal('profuTaconHembra', 8, 3)->nullable();
                $table->decimal('profuTaconMacho', 8, 3)->nullable();
                $table->decimal('simetriaHembra', 8, 3)->nullable();
                $table->decimal('simetriaMacho', 8, 3)->nullable();
                $table->decimal('anchoTacon', 8, 3)->nullable();
                $table->decimal('barrenoLateralHembra', 8, 3)->nullable();
                $table->decimal('barrenoLateralMacho', 8, 3)->nullable();
                $table->decimal('alturaTaconInicial')->nullable();
                $table->decimal('alturaTaconIntermedia')->nullable();
                $table->string('error')->nullable();
                $table->string('observaciones')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('offSet');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offSet_pza');
    }
};
