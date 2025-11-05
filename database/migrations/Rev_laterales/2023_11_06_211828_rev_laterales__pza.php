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
        if (!Schema::hasTable('revLaterales_pza')) {
            Schema::create('revLaterales_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->string('n_pieza')->nullable();
                $table->decimal('desfasamiento_entrada', 8, 3)->nullable();
                $table->decimal('desfasamiento_salida', 8, 3)->nullable();
                $table->decimal('ancho_simetriaEntrada', 8, 3)->nullable();
                $table->decimal('ancho_simetriaSalida', 8, 3)->nullable();
                $table->decimal('angulo_corte', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('revLaterales');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revLaterales_pza');
    }
};
