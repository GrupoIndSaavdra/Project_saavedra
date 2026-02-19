<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('primeraOperacionCabezaSoplo_pza')) {
            Schema::create('primeraOperacionCabezaSoplo_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza')->unique();
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->string('n_pieza')->nullable();
                $table->decimal('diametro_exterior', 8, 3)->nullable();
                $table->decimal('longitud', 8, 3)->nullable();
                $table->decimal('diametro_candado', 8, 3)->nullable();
                $table->decimal('longitud_candado', 8, 3)->nullable();

                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();

                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('primeraOperacionCabezaSoplo');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('primeraOperacionCabezaSoplo_pza');
    }
};
