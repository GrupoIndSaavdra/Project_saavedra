<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de piezas individuales del reporte "RInternoSalidas".
 * Cada fila representa una celda del grid (una pieza numerada).
 * - Formato MOLDES: se usa solo 'valor_simple'
 * - Formato BOMBILLOS: se usan 'valor_90' y 'valor_lp'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('r_interno_salidas_piezas', function (Blueprint $table) {
            $table->id();

            // Relación con el reporte padre (cascade: si se borra el reporte, se borran las piezas)
            $table->foreignId('r_interno_salida_id')
                  ->constrained('r_interno_salidas')
                  ->onDelete('cascade')
                  ->comment('ID del reporte de RInternoSalidas al que pertenece esta pieza');

            // Número secuencial de la pieza en el reporte (1, 2, 3...)
            $table->integer('numero_pieza')->comment('Número de pieza en el reporte (posición en el grid)');

            // Campo para Formato MOLDES (valor único por pieza)
            $table->string('valor_simple', 100)->nullable()
                  ->comment('Valor del número de trazabilidad (formato MOLDES)');

            // Campos para Formato BOMBILLOS (dos valores por pieza: 90° y LP)
            $table->string('valor_90', 100)->nullable()
                  ->comment('Medición a 90° del bombillo');
            $table->string('valor_lp', 100)->nullable()
                  ->comment('Lectura de Punta (LP) del bombillo');

            $table->string('descripcion', 255)->nullable()
                  ->comment('Motivo o descripción de la pieza');

            $table->timestamps();

            // Índice compuesto para búsquedas rápidas por reporte + número de pieza
            $table->unique(['r_interno_salida_id', 'numero_pieza'], 'salida_piezas_reporte_num_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_interno_salidas_piezas');
    }
};
