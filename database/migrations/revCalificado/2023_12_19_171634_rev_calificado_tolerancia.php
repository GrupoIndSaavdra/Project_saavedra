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
        if (!Schema::hasTable('revCalificado_tolerancia')) {
            Schema::create('revCalificado_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_ceja1', 8, 3)->nullable();
                $table->decimal('diametro_ceja2', 8, 3)->nullable();
                $table->decimal('diametro_sufridera1', 8, 3)->nullable();
                $table->decimal('diametro_sufridera2', 8, 3)->nullable();
                $table->decimal('altura_sufridera1', 8, 3)->nullable();
                $table->decimal('altura_sufridera2', 8, 3)->nullable();
                $table->decimal('diametro_conexion1', 8, 3)->nullable();
                $table->decimal('diametro_conexion2', 8, 3)->nullable();
                $table->decimal('altura_conexion1', 8, 3)->nullable();
                $table->decimal('altura_conexion2', 8, 3)->nullable();
                $table->decimal('diametro_caja1', 8, 3)->nullable();
                $table->decimal('diametro_caja2', 8, 3)->nullable();
                $table->decimal('altura_caja1', 8, 3)->nullable();
                $table->decimal('altura_caja2', 8, 3)->nullable();
                $table->decimal('altura_total1', 8, 3)->nullable();
                $table->decimal('altura_total2', 8, 3)->nullable();
                $table->decimal('simetria1', 8, 3)->nullable();
                $table->decimal('simetria2', 8, 3)->nullable();
                $table->timestamps();

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revCalificado_tolerancia');
    }
};
