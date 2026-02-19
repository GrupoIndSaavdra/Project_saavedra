<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('primeraOperacionCabezaSoplo_tolerancia')) {
            Schema::create('primeraOperacionCabezaSoplo_tolerancia', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_exterior1', 8, 3)->nullable();
                $table->decimal('diametro_exterior2', 8, 3)->nullable();
                $table->decimal('longitud1', 8, 3)->nullable();
                $table->decimal('longitud2', 8, 3)->nullable();
                $table->decimal('diametro_candado1', 8, 3)->nullable();
                $table->decimal('diametro_candado2', 8, 3)->nullable();
                $table->decimal('longitud_candado1', 8, 3)->nullable();
                $table->decimal('longitud_candado2', 8, 3)->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('primeraOperacionCabezaSoplo_tolerancia');
    }
};
