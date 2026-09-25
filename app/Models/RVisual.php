<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RVisual extends Model
{
    use HasFactory;

    protected $table = 'r_visuales';

    protected $fillable = [
        'ot_id',
        'clase',
        'nombre_moldura',
        'cliente',
        'codigo',
        'sistema',
        'codigo_formato',
        'nivel_revision',
        'fecha_elaboracion',
        'fecha_revision',
        'fecha_aprobacion',
        'vol_molde_sd',
        'vol_conjunto_fisico',
        'vol_bombillo_sd',
        'vol_conjunto_sd',
        'vol_desplazado_max',
        'vol_desplazado_ideal',
        'vol_desplazado_min',
        'observaciones_generales',
        'archivo_ruta',
        'archivo_nombre',
        'inspector_id',
        'estado',
    ];

    protected $casts = [
        'fecha_elaboracion' => 'date',
        'fecha_revision' => 'date',
        'fecha_aprobacion' => 'date',
        'nivel_revision' => 'integer',
    ];

    /**
     * Lista de archivos adjuntos (hasta 4)
     */
    public function getArchivosListAttribute(): array
    {
        if (!$this->archivo_ruta) {
            return [];
        }

        $decoded = json_decode($this->archivo_ruta, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'id' => 1,
                'nombre' => $this->archivo_nombre ?: basename($this->archivo_ruta),
                'ruta' => $this->archivo_ruta,
                'fecha' => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null,
            ]
        ];
    }

    /**
     * Medidas registradas para este reporte visual
     */
    public function medidas()
    {
        return $this->hasMany(RVisualMedida::class, 'r_visual_id')->orderBy('id');
    }

    /**
     * Inspector que elaboró el reporte
     */
    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Orden de Trabajo asociada
     */
    public function ordenTrabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'ot_id', 'id');
    }

    /**
     * Nombre formateado del inspector
     */
    public function getInspectorNombreCompletoAttribute(): string
    {
        if ($this->inspector) {
            $parts = array_filter([
                $this->inspector->nombre ?? '',
                $this->inspector->a_paterno ?? '',
                $this->inspector->a_materno ?? '',
            ]);
            return count($parts) > 0 ? implode(' ', $parts) : ($this->inspector->matricula ?? '—');
        }
        return '—';
    }
}
