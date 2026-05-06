<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Actualiza la columna 'action' de las tablas de logs de documentación
     * para permitir nuevas acciones descriptivas (como 'eliminar_carpeta' o 'enviar_alerta').
     * Se cambia de ENUM a STRING para mayor flexibilidad.
     */
    public function up(): void
    {
        $tables = [
            'dibujos_file_log',
            'manuales_file_log',
            'ayudas_visuales_file_log',
            'ayudas_visuales_fundicion_file_log'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('action', 100)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertimos a ENUM para evitar pérdida de datos si ya existen registros con 'eliminar_carpeta'
    }
};
