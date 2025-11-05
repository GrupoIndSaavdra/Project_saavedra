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
        if (!Schema::hasTable('barrenoProfundidad_pza')) {
            Schema::create('barrenoProfundidad_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->string('n_pieza')->nullable();
                $table->decimal('broca1', 8, 3)->nullable();
                $table->decimal('tiempo1', 8, 3)->nullable();
                $table->decimal('broca2', 8, 3)->nullable();
                $table->decimal('tiempo2', 8, 3)->nullable();
                $table->decimal('broca3', 8, 3)->nullable();
                $table->decimal('tiempo3', 8, 3)->nullable();
                $table->decimal('entrada', 8, 3)->nullable();
                $table->decimal('salida', 8, 3)->nullable();
                $table->decimal('diametro_arrastre1', 8, 3)->nullable();
                $table->decimal('diametro_arrastre2', 8, 3)->nullable();
                $table->decimal('diametro_arrastre3', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('barrenoProfundidad');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barrenoProfundidad_pza');
    }
};
