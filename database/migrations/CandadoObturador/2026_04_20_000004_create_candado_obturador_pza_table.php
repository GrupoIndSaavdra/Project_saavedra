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
        if (!Schema::hasTable('CandadoObturador_pza')) {
            Schema::create('CandadoObturador_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza')->unique();
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->string('n_pieza')->nullable();
                $table->decimal('altura', 8, 3)->nullable();
                $table->decimal('alturaCandado1', 8, 3)->nullable();
                $table->decimal('alturaCandado2', 8, 3)->nullable();
                $table->decimal('alturaAsientoObturador1', 8, 3)->nullable();
                $table->decimal('alturaAsientoObturador2', 8, 3)->nullable();
                $table->decimal('profundidadSoldadura1', 8, 3)->nullable();
                $table->decimal('profundidadSoldadura2', 8, 3)->nullable();
                $table->decimal('pushUp', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();

                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('CandadoObturador');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CandadoObturador_pza');
    }
};
