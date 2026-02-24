<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraPTA_pza extends Model
{
    use HasFactory;
    protected $table = 'soldaduraPTA_pza';

    protected $fillable = [
        // ── Clave de pieza (M/H) y juego ──
        'n_pieza',            // ej: '1M', '1H'
        'n_juego',            // ej: '1A', '2B'

        // ── Campos heredados (nullable para histórico) ──
        'temp_calentado',
        'temp_dispositivo',
        'limpieza',
        'error',
        'observaciones',

        // ── Identificador de sub-fila ──
        'tipo_medida',        // 'D_Conexion_pico' | 'D_Conexion_obt' | 'Perfilado'

        // ── Datos generales por sub-fila ──
        'd_conexion_pico',
        'd_conexion_obt',
        'vl',
        'tipo_preparacion',   // int: 1 | 2 | 3
        'perfilado',

        // ── Precalentamiento (único por pieza, rowspan=3 en la vista) ──
        'precalentamiento',   // °C, solo en tipo_medida = 'D_Conexion_pico'

        // ── Parámetros de Soldadura ──
        'sold_inicial',
        'sold_aplicada',
        'sold_final',

        // ── Parámetros de Corriente ──
        'corr_inicial',
        'corr_aplicada',
        'corr_final',

        // ── Otros parámetros ──
        'gas_argon',
        'velocidad_calculada',

        // ── Inspección ──
        'resultado',
        'defecto_pta',        // 'Ninguno' | 'Fundición'
    ];

    /**
     * Relación con el proceso padre (soldaduraPTA)
     */
    public function proceso()
    {
        return $this->belongsTo(SoldaduraPTA::class, 'id_proceso');
    }

    /**
     * Scope para filtrar solo las sub-filas de tipo D_Conexion_pico
     * (portadoras del precalentamiento)
     */
    public function scopePrecalentamientoFila($query)
    {
        return $query->where('tipo_medida', 'D_Conexion_pico');
    }
}

