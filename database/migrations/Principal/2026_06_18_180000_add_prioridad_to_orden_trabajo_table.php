<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade la columna `prioridad` a la tabla `orden_trabajo`.
     *
     * Permite asignar un orden personalizado a las OTs en la vista de
     * "Órdenes de Trabajo en Progreso". Las OTs se ordenan de menor a mayor
     * valor de prioridad; aquellas con NULL se ubican al final.
     *
     * Regla de migraciones: tabla ya en producción → migración de alteración
     * (no se edita el archivo `create_` original).
     */
    public function up(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            // NULL = sin prioridad asignada (va al final en el ordenamiento)
            // El valor 0 se reserva como "máxima urgencia" si se necesita.
            $table->unsignedInteger('prioridad')->nullable()->default(null)->after('id_moldura');

            // Índice para optimizar el ORDER BY prioridad, id en la consulta
            // de showViewPiecesInProgress (ejecutada cada reload y cada poll).
            $table->index('prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_trabajo', function (Blueprint $table) {
            $table->dropIndex(['prioridad']);
            $table->dropColumn('prioridad');
        });
    }
};
