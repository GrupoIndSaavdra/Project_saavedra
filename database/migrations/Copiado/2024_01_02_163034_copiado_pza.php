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
        if (!Schema::hasTable('copiado_pza')) {
            Schema::create('copiado_pza', function (Blueprint $table) {
                $table->id();
                $table->string('id_pza');
                $table->unsignedBigInteger('id_meta')->nullable();
                $table->unsignedBigInteger('id_proceso');
                $table->integer('correcto')->nullable();
                $table->integer('estado')->default(0);
                $table->string('n_juego')->nullable();
                $table->decimal('diametro1_cilindrado', 8, 3)->nullable();
                $table->decimal('profundidad1_cilindrado', 8, 3)->nullable();
                $table->decimal('diametro2_cilindrado', 8, 3)->nullable();
                $table->decimal('profundidad2_cilindrado', 8, 3)->nullable();
                $table->decimal('diametro_sufridera', 8, 3)->nullable();
                $table->decimal('diametro_ranura', 8, 3)->nullable();
                $table->decimal('profundidad_ranura', 8, 3)->nullable();
                $table->decimal('profundidad_sufridera', 8, 3)->nullable();
                $table->decimal('altura_total', 8, 3)->nullable();
                $table->string('observaciones_cilindrado')->nullable();
                $table->string('error_cilindrado')->nullable();
                $table->decimal('diametro1_cavidades', 8, 3)->nullable();
                $table->decimal('profundidad1_cavidades', 8, 3)->nullable();
                $table->decimal('diametro2_cavidades', 8, 3)->nullable();
                $table->decimal('profundidad2_cavidades', 8, 3)->nullable();
                $table->decimal('diametro3', 8, 3)->nullable();
                $table->decimal('profundidad3', 8, 3)->nullable();
                $table->decimal('diametro4', 8, 3)->nullable();
                $table->decimal('profundidad4', 8, 3)->nullable();
                $table->decimal('volumen', 8, 3)->nullable();
                $table->string('observaciones_cavidades')->nullable();
                $table->string('error_cavidades')->nullable();
                $table->timestamps();
                $table->foreign('id_meta')->references('id')->on('metas');
                $table->foreign('id_proceso')->references('id')->on('copiado');

                $table->unique('id_pza');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copiado_pza');
    }
};
