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
        'liberado_por',
        'fecha_liberacion',
        'rechazado_por_admin',
        'rechazado_por',
        'fecha_rechazo',
    ];

    protected $casts = [
        'liberado_por_admin' => 'boolean',
        'fecha_liberacion' => 'datetime',
        'rechazado_por_admin' => 'boolean',
        'fecha_rechazo' => 'datetime',
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

    /**
     * Usuario administrador que liberó este resultado
     */
    public function liberador()
    {
        return $this->belongsTo(User::class, 'liberado_por');
    }

    /**
     * Usuario administrador que rechazó este resultado
     */
    public function rechazador()
    {
        return $this->belongsTo(User::class, 'rechazado_por');
    }
}
