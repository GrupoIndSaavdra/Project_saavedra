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
        if (!Schema::hasTable('cepillado_pza')) {
            Schema::create('cepillado_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_pieza');
                $table->string('n_juego');
                $table->decimal('radiof_mordaza', 8, 3)->nullable();
                $table->decimal('radiof_mayor', 8, 3)->nullable();
                $table->decimal('radiof_sufridera', 8, 3)->nullable();
                $table->decimal('profuFinal_CFC', 8, 3)->nullable();
                $table->decimal('profuFinal_mitadMB', 8, 3)->nullable();
                $table->decimal('profuFinal_PCO', 8, 3)->nullable();
                $table->string('acetato_MB')->nullable();
                $table->decimal('ensamble', 8, 3)->nullable();
                $table->decimal('distancia_barrenoAli', 8, 3)->nullable();
                $table->decimal('profu_barrenoAliHembra', 8, 3)->nullable();
                $table->decimal('profu_barrenoAliMacho', 8, 3)->nullable();
                $table->decimal('altura_venaHembra', 8, 3)->nullable();
                $table->decimal('altura_venaMacho', 8, 3)->nullable();
                $table->decimal('ancho_vena', 8, 3)->nullable();
                $table->decimal('pin1', 8, 3)->nullable();
                $table->decimal('pin2', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('cepillado');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cepillado_pza');
    }
};
