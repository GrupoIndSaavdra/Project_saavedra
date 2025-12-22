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
        if (!Schema::hasTable('PrimeraOpeSoldadura_pza')) {
            Schema::create('PrimeraOpeSoldadura_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->string('n_pieza')->nullable();
                $table->decimal('diametro1', 8, 3)->nullable();
                $table->decimal('profundidad1', 8, 3)->nullable();
                $table->decimal('diametro2', 8, 3)->nullable();
                $table->decimal('profundidad2', 8, 3)->nullable();
                $table->decimal('diametro3', 8, 3)->nullable();
                $table->decimal('profundidad3', 8, 3)->nullable();
                $table->decimal('diametroSoldadura', 8, 3)->nullable();
                $table->decimal('profundidadSoldadura', 8, 3)->nullable();
                $table->decimal('diametroBarreno', 8, 3)->nullable();
                $table->decimal('simetriaLinea_partida', 8, 3)->nullable();
                $table->decimal('pernoAlineacion', 8, 3)->nullable();
                $table->decimal('Simetria90G', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('primeraOpeSoldadura');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PrimeraOpeSoldadura_pza');
    }
};
