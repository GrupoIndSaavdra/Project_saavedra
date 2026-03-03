<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla pta_resultados
 *
 * Almacena los resultados técnicos de Soldadura PTA por pieza individual.
 * Cada registro corresponde a UNA pieza de UNA OT.
 * Incluye 6 resultados (Si/No/No Aplica), 3 imágenes opcionales
 * y control de liberación por administrador.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pta_resultados')) {
            Schema::create('pta_resultados', function (Blueprint $table) {
                $table->id();

                // ── Claves foráneas ──────────────────────────────────────
                $table->string('ot_id');
                $table->foreign('ot_id')->references('id')->on('orden_trabajo')->onDelete('cascade');

                $table->unsignedBigInteger('pieza_id');
                $table->foreign('pieza_id')->references('id')->on('piezas')->onDelete('cascade');

                // ── Identificador de pieza (ej: 1M, 1H, 2J) ─────────────
                $table->string('n_pieza');

                // ── Resultados técnicos (Si / No / No Aplica) ────────────
                $table->enum('resultado_pico_llenado', ['Si', 'No', 'No Aplica'])->nullable();
                $table->enum('resultado_pico_soldadura', ['Si', 'No', 'No Aplica'])->nullable();
                $table->enum('resultado_conexion_llenado', ['Si', 'No', 'No Aplica'])->nullable();
                $table->enum('resultado_conexion_soldadura', ['Si', 'No', 'No Aplica'])->nullable();
                $table->enum('resultado_perfilado_llenado', ['Si', 'No', 'No Aplica'])->nullable();
                $table->enum('resultado_perfilado_soldadura', ['Si', 'No', 'No Aplica'])->nullable();

                // ── Imágenes (ruta relativa en storage/app/public/) ───────
                $table->string('imagen_pico_soldadura')->nullable();
                $table->string('imagen_conexion_soldadura')->nullable();
                $table->string('imagen_perfilado_soldadura')->nullable();

                // ── Control de liberación por administrador ────────────────
                $table->boolean('liberado_por_admin')->default(false);
                $table->unsignedBigInteger('liberado_por')->nullable();    // FK a users.id
                $table->foreign('liberado_por')->references('id')->on('users')->onDelete('set null');
                $table->timestamp('fecha_liberacion')->nullable();

                // ── Control de rechazo por administrador ──────────────────
                $table->boolean('rechazado_por_admin')->default(false);
                $table->unsignedBigInteger('rechazado_por')->nullable();   // FK a users.id
                $table->foreign('rechazado_por')->references('id')->on('users')->onDelete('set null');
                $table->timestamp('fecha_rechazo')->nullable();

                $table->timestamps();

                // Garantizar unicidad: una pieza solo tiene UN resultado por OT
                $table->unique(['ot_id', 'pieza_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pta_resultados');
    }
};
