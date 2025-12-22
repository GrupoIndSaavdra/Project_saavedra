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
        if (!Schema::hasTable('revCalificado_pza')) {
            Schema::create('revCalificado_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('diametro_ceja', 8, 3)->nullable();
                $table->decimal('diametro_sufridera', 8, 3)->nullable();
                $table->decimal('altura_sufridera', 8, 3)->nullable();
                $table->decimal('diametro_conexion', 8, 3)->nullable();
                $table->decimal('altura_conexion', 8, 3)->nullable();
                $table->decimal('diametro_caja', 8, 3)->nullable();
                $table->decimal('altura_caja', 8, 3)->nullable();
                $table->decimal('altura_total', 8, 3)->nullable();
                $table->decimal('simetria', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('revCalificado');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revCalificado_pza');
    }
};
