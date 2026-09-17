<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de auditoría de ediciones en reportes "Salida de Molduras".
 * Registra QUIÉN editó, QUÉ campo cambió, y los valores anterior/nuevo.
 * Los registros de log son INMUTABLES (no soft-delete, no update).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salida_molduras_log', function (Blueprint $table) {
            $table->id();

            // Relación con el reporte auditado
            $table->foreignId('salida_moldura_id')
                  ->constrained('salida_molduras')
                  ->onDelete('cascade')
                  ->comment('ID del reporte de Salida de Molduras auditado');

            // Usuario que realizó la edición
            $table->unsignedBigInteger('user_id')->comment('ID del usuario que realizó el cambio');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Descripción de la acción realizada
            $table->string('accion', 100)->comment('Descripción de la acción (ej. "Editar pieza", "Guardar observaciones")');

            // Detalle del campo modificado
            $table->string('campo_editado', 100)->nullable()
                  ->comment('Nombre del campo que fue modificado (ej. "pieza_5_valor_90")');

            // Valores antes y después del cambio
            $table->text('valor_anterior')->nullable()->comment('Valor del campo antes de la edición');
            $table->text('valor_nuevo')->nullable()->comment('Valor del campo después de la edición');

            // IP del cliente para trazabilidad de seguridad
            $table->string('ip', 45)->nullable()->comment('Dirección IP del usuario que realizó el cambio');

            // Solo created_at (los logs son inmutables, no tienen updated_at)
            $table->timestamp('created_at')->useCurrent();

            // Índice para consultas de historial por reporte
            $table->index(['salida_moldura_id', 'created_at'], 'salida_log_reporte_fecha_idx');
            // Índice para consultas de actividad por usuario
            $table->index(['user_id', 'created_at'], 'salida_log_user_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salida_molduras_log');
    }
};
