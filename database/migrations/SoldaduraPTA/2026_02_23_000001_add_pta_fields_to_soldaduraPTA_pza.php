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
 * Las columnas antiguas (temp_calentado, temp_dispositivo, limpieza)
 * se ponen en nullable para preservar datos históricos, NO se eliminan.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('soldaduraPTA_pza', function (Blueprint $table) {

            // -------------------------------------------------------
            // 1. NUEVA COLUMNA CLAVE: define cuál de las 3 sub-filas
            //    representa este registro para un mismo n_pieza.
            //    Valores esperados: 'D_Conexion_pico' | 'D_Conexion_obt' | 'Perfilado'
            //    NOTA: n_pieza ya existe en la migración base (2023_12_13_202404).
            // -------------------------------------------------------
            $table->string('tipo_medida')->nullable()->after('n_pieza');

            // -------------------------------------------------------
            // 2. DATOS GENERALES POR SUB-FILA
            // -------------------------------------------------------
            $table->decimal('d_conexion_pico', 8, 3)->nullable()->after('tipo_medida');
            $table->decimal('d_conexion_obt', 8, 3)->nullable()->after('d_conexion_pico');
            $table->decimal('vl', 8, 3)->nullable()->after('d_conexion_obt');
            $table->tinyInteger('tipo_preparacion')->nullable()->after('vl');   // opciones: 1, 2, 3
            $table->decimal('perfilado', 8, 3)->nullable()->after('tipo_preparacion');

            // -------------------------------------------------------
            // 3. PRECALENTAMIENTO — valor único por pieza (rowspan=3)
            //    Solo se registra en la primera sub-fila (tipo_medida = 'D_Conexion_pico')
            // -------------------------------------------------------
            $table->decimal('precalentamiento', 8, 2)->nullable()->after('perfilado');  // °C

            // -------------------------------------------------------
            // 4. PARÁMETROS DE SOLDADURA
            // -------------------------------------------------------
            $table->decimal('sold_inicial', 8, 3)->nullable()->after('precalentamiento');
            $table->decimal('sold_aplicada', 8, 3)->nullable()->after('sold_inicial');
            $table->decimal('sold_final', 8, 3)->nullable()->after('sold_aplicada');

            // -------------------------------------------------------
            // 5. PARÁMETROS DE CORRIENTE
            // -------------------------------------------------------
            $table->decimal('corr_inicial', 8, 3)->nullable()->after('sold_final');
            $table->decimal('corr_aplicada', 8, 3)->nullable()->after('corr_inicial');
            $table->decimal('corr_final', 8, 3)->nullable()->after('corr_aplicada');

            // -------------------------------------------------------
            // 6. OTROS PARÁMETROS
            // -------------------------------------------------------
            $table->decimal('gas_argon', 8, 3)->nullable()->after('corr_final');
            $table->decimal('velocidad_calculada', 8, 3)->nullable()->after('gas_argon');

            // -------------------------------------------------------
            // 7. INSPECCIÓN
            // -------------------------------------------------------
            $table->string('resultado')->nullable()->after('velocidad_calculada');
            // 'defecto' ya existe como 'error' — renombramos lógicamente
            // pero para no romper histórico, agregamos defecto_pta como nueva columna
            // y mantenemos 'error' intacta.
            $table->string('defecto_pta')->nullable()->after('resultado'); // 'Ninguno' | 'Fundición'

            // -------------------------------------------------------
            // 8. COLUMNAS ANTIGUAS — se marcan nullable para preservar histórico
            //    temp_calentado, temp_dispositivo, limpieza, error, observaciones
            //    ya son nullable en la migración original.
            // -------------------------------------------------------
            // No se necesita hacer nada, ya son nullable.
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
