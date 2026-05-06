<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla opcional de registro histórico de Ayudas Visuales
     * que han tenido carpetas/planos creados en el sistema.
     */
    public function up(): void
    {
        Schema::create('ayudas_visuales_history', function (Blueprint $table) {
            $table->id();

            // Nombre del Proceso
            $table->string('proceso', 100);

            // Nombre de la Clase
            $table->string('clase', 100);

            $table->timestamps();

            // Evitar duplicados
            $table->unique(['proceso', 'clase'], 'uq_ayudas_proceso_clase');
            $table->index('proceso', 'idx_ayudas_history_proceso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ayudas_visuales_history');
    }
};
