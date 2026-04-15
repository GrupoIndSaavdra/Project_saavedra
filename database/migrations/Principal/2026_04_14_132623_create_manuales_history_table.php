<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla opcional de registro histórico de Procesos que han tenido
     * manuales creados.
     */
    public function up(): void
    {
        Schema::create('manuales_history', function (Blueprint $table) {
            $table->id();

            // Nombre del Proceso
            $table->string('proceso', 100);

            $table->timestamps();

            $table->unique('proceso', 'uq_manuales_proceso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manuales_history');
    }
};
