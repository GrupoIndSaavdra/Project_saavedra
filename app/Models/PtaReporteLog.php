<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtaReporteLog extends Model
{
    protected $table = 'pta_reporte_logs';

    protected $fillable = [
        'ot_id',
        'clase_id',
        'ot_nombre',
        'clase_nombre',
        'destinatario',
        'estado',
        'mensaje_error',
        'enviado_por',
    ];

    /**
     * Relación con la orden de trabajo.
     */
    public function ordenTrabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'ot_id');
    }

    /**
     * Relación con la clase.
     */
    public function clase()
    {
        return $this->belongsTo(Clase::class, 'clase_id');
    }

    /**
     * Relación con el usuario que envió (via matricula).
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'enviado_por', 'matricula');
    }

    /**
     * Nombre completo del usuario que envió el reporte.
     */
    public function getNombreEnviadoPorAttribute(): string
    {
        if ($this->usuario) {
            return trim("{$this->usuario->nombre} {$this->usuario->a_paterno} {$this->usuario->a_materno}");
        }
        return $this->enviado_por ? "Matrícula #{$this->enviado_por}" : '—';
    }
}
