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
        if (!Schema::hasTable('acabadoBombillo_pza')) {
            Schema::create('acabadoBombillo_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('diametro_mordaza', 8, 3)->nullable();
                $table->decimal('diametro_ceja', 8, 3)->nullable();
                $table->decimal('diametro_sufridera', 8, 3)->nullable();
                $table->decimal('altura_mordaza', 8, 3)->nullable();
                $table->decimal('altura_ceja', 8, 3)->nullable();
                $table->decimal('altura_sufridera', 8, 3)->nullable();
                $table->char('gauge_ceja')->nullable();
                $table->decimal('gauge_corona', 8, 3)->nullable();
                $table->char('gauge_llanta')->nullable();
                $table->decimal('altura_total', 8, 3)->nullable();
                $table->decimal('diametro_boca', 8, 3)->nullable();
                $table->decimal('diametro_asiento_corona', 8, 3)->nullable();
                $table->decimal('diametro_llanta', 8, 3)->nullable();
                $table->decimal('diametro_caja_corona', 8, 3)->nullable();
                $table->decimal('profundidad_corona', 8, 3)->nullable();
                $table->decimal('angulo_30', 8, 3)->nullable();
                $table->decimal('profundidad_caja_corona', 8, 3)->nullable();
                $table->decimal('simetria', 8, 3)->nullable();
                $table->string('observaciones')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('acabadoBombillo');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acabadoBombillo_pza');
    }
};
