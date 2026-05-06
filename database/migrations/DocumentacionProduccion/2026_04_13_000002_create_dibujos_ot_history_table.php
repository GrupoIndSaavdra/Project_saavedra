<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla opcional de registro histórico de OTs y Clases que han tenido
     * carpetas/planos creados en el sistema. Útil para cruce de datos y reportes.
     * No es la fuente de verdad — el filesystem es la fuente de verdad.
     */
    public function up(): void
    {
        Schema::create('dibujos_ot_history', function (Blueprint $table) {
            $table->id();

            // Nombre de la Orden de Trabajo (carpeta raíz nivel 1)
            $table->string('ot', 100);

            // Nombre de la Clase (carpeta nivel 2 dentro de la OT)
            $table->string('clase', 100);

            $table->timestamps();

            // Evitar duplicados de la misma OT+Clase
            $table->unique(['ot', 'clase'], 'uq_dibujos_ot_clase');

            $table->index('ot', 'idx_dibujos_history_ot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dibujos_ot_history');
    }
};
