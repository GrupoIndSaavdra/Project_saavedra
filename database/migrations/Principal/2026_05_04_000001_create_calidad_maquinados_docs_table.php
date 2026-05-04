<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de índice para los documentos de Calidad — Maquinados.
     * Sincronizada automáticamente por el comando calidad:sync-maquinados
     * cada 5 minutos via Task Scheduling.
     *
     * Los archivos físicos NUNCA se borran; los registros que ya no existen
     * en la fuente se marcan como 'inactivo'.
     */
    public function up(): void
    {
        Schema::create('calidad_maquinados_docs', function (Blueprint $table) {
            $table->id();

            // ── Metadatos del archivo ──────────────────────────────────
            /** Nombre del archivo (sin ruta), p.ej. "OT-1234_Bombillo.pdf" */
            $table->string('nombre_archivo', 255);

            /** Ruta relativa dentro del storage/app de Laravel */
            $table->string('ruta_storage', 500);

            /** Tipo: 'dibujo' (DIBUJOS_MAQUINADOS) | 'ayuda' (AYUDAS_MAQUINADOS) */
            $table->enum('tipo', ['dibujo', 'ayuda']);

            /** Estado de sincronización */
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            // ── Metadata extraída para filtros ─────────────────────────
            /** Número / nombre de la Orden de Trabajo */
            $table->string('ot', 100)->nullable();

            /** Clase de pieza (Bombillo, Macho, Hembra, etc.) */
            $table->string('clase', 100)->nullable();

            /** Proceso al que pertenece el documento */
            $table->string('proceso', 150)->nullable();

            /** Fecha de creación / modificación del archivo físico */
            $table->date('fecha_archivo')->nullable();

            // ── Auditoría ──────────────────────────────────────────────
            /** Fecha y hora en que el archivo fue detectado por primera vez */
            $table->timestamp('primera_deteccion_at')->nullable();

            /** Última vez que el archivo fue confirmado presente en la carpeta de origen */
            $table->timestamp('ultima_deteccion_at')->nullable();

            $table->timestamps();

            // ── Índices ────────────────────────────────────────────────
            $table->unique('ruta_storage', 'uq_calidad_mac_ruta');
            $table->index(['tipo', 'estado'], 'idx_tipo_estado');
            $table->index('ot', 'idx_ot');
            $table->index('clase', 'idx_clase');
            $table->index('proceso', 'idx_proceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calidad_maquinados_docs');
    }
};
