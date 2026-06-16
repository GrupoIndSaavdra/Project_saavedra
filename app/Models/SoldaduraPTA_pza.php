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
        'material_soldadura',
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

        // ── 2da Pasada ── (todos nullable, se activan con contraseña PTA2026)
        'p2_activa',           // boolean — ¿se registró 2da pasada en esta pieza?
        'p2_d_conexion_pico',
        'p2_d_conexion_obt',
        'p2_vl',
        'p2_tipo_preparacion',
        'p2_perfilado',
        'p2_precalentamiento', // °C, solo en tipo_medida = 'D_Conexion_pico'
        'p2_sold_inicial',
        'p2_sold_aplicada',
        'p2_sold_final',
        'p2_corr_inicial',
        'p2_corr_aplicada',
        'p2_corr_final',
        'p2_gas_argon',
        'p2_velocidad_calculada',
        'p2_resultado',        // 'Bien' | 'Mal'
        'p2_defecto_pta',      // 'Ninguno' | 'Fundición'
        'p2_observaciones',
    ];

    protected $casts = [
        'p2_activa' => 'boolean',
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
