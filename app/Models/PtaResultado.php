<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtaResultado extends Model
{
    use HasFactory;

    protected $table = 'pta_resultados';

    protected $fillable = [
        'ot_id',
        'pieza_id',
        'n_pieza',
        'resultado_pico_llenado',
        'resultado_pico_soldadura',
        'resultado_conexion_llenado',
        'resultado_conexion_soldadura',
        'resultado_perfilado_llenado',
        'resultado_perfilado_soldadura',
        'imagen_pico_soldadura',
        'imagen_conexion_soldadura',
        'imagen_perfilado_soldadura',
        'liberado_por_admin',
    ];

    protected $casts = [
        'liberado_por_admin' => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    /**
     * OT a la que pertenece este resultado
     */
    public function ordenTrabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'ot_id');
    }

    /**
     * Pieza a la que corresponde este resultado
     */
    public function pieza()
    {
        return $this->belongsTo(Pieza::class, 'pieza_id');
    }
}
