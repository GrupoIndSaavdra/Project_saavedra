<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Refactorización de Soldadura PTA
 *
 * Agrega columnas técnicas detalladas a la tabla soldaduraPTA_pza
 * para captura por pieza individual (M/H) con estructura de 3 sub-filas:
 *   - D. Conexión pico
 *   - D. Conexión obt
 *   - Perfilado
 *
 * Prerequisito: n_pieza ya debe existir (ver 2026_03_04_174530_add_n_pieza...).
 * Las columnas antiguas (temp_calentado, temp_dispositivo, limpieza)
 * se ponen en nullable para preservar datos históricos, NO se eliminan.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {

            // ── 1. TIPO DE SUB-FILA ─────────────────────────────────────────
            $table->string('tipo_medida')->nullable()->after('n_pieza');

            // ── 2. DATOS GENERALES POR SUB-FILA ────────────────────────────
            $table->decimal('d_conexion_pico', 8, 3)->nullable()->after('tipo_medida');
            $table->decimal('d_conexion_obt', 8, 3)->nullable()->after('d_conexion_pico');
            $table->decimal('vl', 8, 3)->nullable()->after('d_conexion_obt');
            $table->tinyInteger('tipo_preparacion')->nullable()->after('vl');   // 1 | 2 | 3
            $table->decimal('perfilado', 8, 3)->nullable()->after('tipo_preparacion');

            // ── 3. PRECALENTAMIENTO ─────────────────────────────────────────
            $table->decimal('precalentamiento', 8, 2)->nullable()->after('perfilado');  // °C

            // ── 4. PARÁMETROS DE SOLDADURA ──────────────────────────────────
            $table->decimal('sold_inicial', 8, 3)->nullable()->after('precalentamiento');
            $table->decimal('sold_aplicada', 8, 3)->nullable()->after('sold_inicial');
            $table->decimal('sold_final', 8, 3)->nullable()->after('sold_aplicada');

            // ── 5. PARÁMETROS DE CORRIENTE ──────────────────────────────────
            $table->decimal('corr_inicial', 8, 3)->nullable()->after('sold_final');
            $table->decimal('corr_aplicada', 8, 3)->nullable()->after('corr_inicial');
            $table->decimal('corr_final', 8, 3)->nullable()->after('corr_aplicada');

            // ── 6. OTROS PARÁMETROS ─────────────────────────────────────────
            $table->decimal('gas_argon', 8, 3)->nullable()->after('corr_final');
            $table->decimal('velocidad_calculada', 8, 3)->nullable()->after('gas_argon');

            // ── 7. INSPECCIÓN ───────────────────────────────────────────────
            $table->string('resultado')->nullable()->after('velocidad_calculada');
            $table->string('defecto_pta')->nullable()->after('resultado'); // 'Ninguno' | 'Fundición'

            // Nota: p2_* se agregan en 2026_03_12_000001_add_p2_fields_to_soldaduraPTA_pza.php
        });
    }

    public function down(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_medida',
                'd_conexion_pico',
                'd_conexion_obt',
                'vl',
                'tipo_preparacion',
                'perfilado',
                'precalentamiento',
                'sold_inicial',
                'sold_aplicada',
                'sold_final',
                'corr_inicial',
                'corr_aplicada',
                'corr_final',
                'gas_argon',
                'velocidad_calculada',
                'resultado',
                'defecto_pta',
            ]);
        });
    }
};
