<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de piezas individuales del reporte "Salida de Molduras".
 * Cada fila representa una celda del grid (una pieza numerada).
 * - Formato MOLDES: se usa solo 'valor_simple'
 * - Formato BOMBILLOS: se usan 'valor_90' y 'valor_lp'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salida_molduras_piezas', function (Blueprint $table) {
            $table->id();

            // Relación con el reporte padre (cascade: si se borra el reporte, se borran las piezas)
            $table->foreignId('salida_moldura_id')
                  ->constrained('salida_molduras')
                  ->onDelete('cascade')
                  ->comment('ID del reporte de Salida de Molduras al que pertenece esta pieza');

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

            $table->timestamps();

            // Índice compuesto para búsquedas rápidas por reporte + número de pieza
            $table->unique(['salida_moldura_id', 'numero_pieza'], 'salida_piezas_reporte_num_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salida_molduras_piezas');
    }
};
