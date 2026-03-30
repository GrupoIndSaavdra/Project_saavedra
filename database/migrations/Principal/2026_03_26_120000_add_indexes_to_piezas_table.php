<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega índices a la tabla piezas para mejorar el rendimiento de consultas.
     * Los índices fueron identificados analizando los patrones de búsqueda más frecuentes
     * en PzasGeneralesController, WOController y ProcessProductionController.
     */
    public function up(): void
    {
        Schema::table('piezas', function (Blueprint $table) {
            // Búsqueda más frecuente: buscar la mitad H/M de un juego por clase+proceso+n_pieza
            $table->index(['id_clase', 'proceso', 'n_pieza'], 'idx_piezas_clase_proceso_npieza');

            // Filtrado por OT + proceso (reportes por orden de trabajo)
            $table->index(['id_ot', 'proceso'], 'idx_piezas_ot_proceso');

            // Filtrado por estado de liberación + error (vista de piezas a liberar)
            $table->index(['liberacion', 'error'], 'idx_piezas_lib_error');

            // Filtros de fecha en reportes
            $table->index('created_at', 'idx_piezas_created_at');

            // Lookup del operador
            $table->index('id_operador', 'idx_piezas_operador');
        });
    }

    public function down(): void
    {
        Schema::table('piezas', function (Blueprint $table) {
            $table->dropIndex('idx_piezas_clase_proceso_npieza');
            $table->dropIndex('idx_piezas_ot_proceso');
            $table->dropIndex('idx_piezas_lib_error');
            $table->dropIndex('idx_piezas_created_at');
            $table->dropIndex('idx_piezas_operador');
        });
    }
};
