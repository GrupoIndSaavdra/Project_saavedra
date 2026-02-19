<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('segundaOperacionCabezaSoplo_cnominal')) {
            Schema::create('segundaOperacionCabezaSoplo_cnominal', function (Blueprint $table) {
                $table->id();
                $table->string('id_proceso');
                $table->decimal('diametro_exterior', 8, 3)->nullable();
                $table->decimal('longitud', 8, 3)->nullable();
                $table->decimal('diametro_candado', 8, 3)->nullable();
                $table->decimal('longitud_candado', 8, 3)->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('segundaOperacionCabezaSoplo_cnominal');
    }
};
