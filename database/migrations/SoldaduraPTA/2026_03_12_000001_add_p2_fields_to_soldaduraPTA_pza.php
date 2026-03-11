<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Agrega campos de 2da pasada (p2_*) a soldaduraPTA_pza
 *
 * Todos los campos son nullable y se activan solo cuando el operador
 * marca la 2da pasada (p2_activa = true).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {

            // ── Flag de activación ──────────────────────────────────────────
            $table->boolean('p2_activa')->default(false)->after('defecto_pta');

            // ── Datos generales por sub-fila ────────────────────────────────
            $table->decimal('p2_d_conexion_pico', 8, 3)->nullable()->after('p2_activa');
            $table->decimal('p2_d_conexion_obt', 8, 3)->nullable()->after('p2_d_conexion_pico');
            $table->decimal('p2_vl', 8, 3)->nullable()->after('p2_d_conexion_obt');
            $table->tinyInteger('p2_tipo_preparacion')->nullable()->after('p2_vl');  // 1 | 2 | 3
            $table->decimal('p2_perfilado', 8, 3)->nullable()->after('p2_tipo_preparacion');

            // ── Precalentamiento ────────────────────────────────────────────
            $table->decimal('p2_precalentamiento', 8, 2)->nullable()->after('p2_perfilado');  // °C

            // ── Parámetros de Soldadura ─────────────────────────────────────
            $table->decimal('p2_sold_inicial', 8, 3)->nullable()->after('p2_precalentamiento');
            $table->decimal('p2_sold_aplicada', 8, 3)->nullable()->after('p2_sold_inicial');
            $table->decimal('p2_sold_final', 8, 3)->nullable()->after('p2_sold_aplicada');

            // ── Parámetros de Corriente ─────────────────────────────────────
            $table->decimal('p2_corr_inicial', 8, 3)->nullable()->after('p2_sold_final');
            $table->decimal('p2_corr_aplicada', 8, 3)->nullable()->after('p2_corr_inicial');
            $table->decimal('p2_corr_final', 8, 3)->nullable()->after('p2_corr_aplicada');

            // ── Otros parámetros ────────────────────────────────────────────
            $table->decimal('p2_gas_argon', 8, 3)->nullable()->after('p2_corr_final');
            $table->decimal('p2_velocidad_calculada', 8, 3)->nullable()->after('p2_gas_argon');

            // ── Inspección ──────────────────────────────────────────────────
            $table->string('p2_resultado')->nullable()->after('p2_velocidad_calculada');    // 'Bien' | 'Mal'
            $table->string('p2_defecto_pta')->nullable()->after('p2_resultado');            // 'Ninguno' | 'Fundición'
            $table->text('p2_observaciones')->nullable()->after('p2_defecto_pta');
        });
    }

    public function down(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {
            $table->dropColumn([
                'p2_activa',
                'p2_d_conexion_pico',
                'p2_d_conexion_obt',
                'p2_vl',
                'p2_tipo_preparacion',
                'p2_perfilado',
                'p2_precalentamiento',
                'p2_sold_inicial',
                'p2_sold_aplicada',
                'p2_sold_final',
                'p2_corr_inicial',
                'p2_corr_aplicada',
                'p2_corr_final',
                'p2_gas_argon',
                'p2_velocidad_calculada',
                'p2_resultado',
                'p2_defecto_pta',
                'p2_observaciones',
            ]);
        });
    }
};
