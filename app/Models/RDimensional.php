<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RDimensional extends Model
{
    use HasFactory;

    protected $table = 'r_dimensionales';

    protected $fillable = [
        'ot_id',
        'clase',
        'nombre_moldura',
        'cliente',
        'moldura_numero',
        'codigo_formato',
        'nivel_revision',
        'fecha_elaboracion',
        'fecha_revision',
        'fecha_aprobacion',
        'cantidad_pedido',
        'cantidad_consignacion',
        'cantidad_inspeccionada',
        'porcentaje_inspeccion',
        'inspector_id',
        'archivo_excel_ruta',
        'archivo_excel_nombre',
        'observaciones',
        'estado',
        'valores_nominales',
        'tolerancias',
    ];

    protected $casts = [
        'fecha_elaboracion' => 'date',
        'fecha_revision' => 'date',
        'fecha_aprobacion' => 'date',
        'nivel_revision' => 'integer',
        'cantidad_pedido' => 'integer',
        'cantidad_consignacion' => 'integer',
        'cantidad_inspeccionada' => 'integer',
        'porcentaje_inspeccion' => 'float',
        'valores_nominales' => 'array',
        'tolerancias' => 'array',
    ];

    /**
     * Lista de archivos adjuntos (hasta 4)
     */
    public function getArchivosListAttribute(): array
    {
        if (!$this->archivo_excel_ruta) {
            return [];
        }

        $decoded = json_decode($this->archivo_excel_ruta, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return [
            [
                'id' => 1,
                'nombre' => $this->archivo_excel_nombre ?: basename($this->archivo_excel_ruta),
                'ruta' => $this->archivo_excel_ruta,
                'fecha' => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null,
            ]
        ];
    }

    /**
     * Medidas registradas para este reporte
     */
    public function medidas()
    {
        return $this->hasMany(RDimensionalMedida::class, 'r_dimensional_id')->orderBy('id');
    }

    /**
     * Inspector que elaboró el reporte
     */
    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
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

    /**
     * Orden de Trabajo asociada
     */
    public function ordenTrabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'ot_id', 'id');
    }

    /**
     * Calcular piezas mínimas y máximas sugeridas para muestreo
     */
    public static function calcularRangoMuestreo(int $totalPiezas): array
    {
        if ($totalPiezas <= 0) {
            return ['min' => 0, 'max' => 0, 'porcentaje_min' => 0, 'porcentaje_max' => 0];
        }

        if ($totalPiezas <= 50) {
            $min = max(1, (int) ceil($totalPiezas * 0.50));
            $max = max(1, (int) ceil($totalPiezas * 0.60));
            return ['min' => $min, 'max' => $max, 'porcentaje_min' => 50, 'porcentaje_max' => 60];
        } else {
            $min = max(1, (int) ceil($totalPiezas * 0.30));
            $max = max(1, (int) ceil($totalPiezas * 0.35));
            return ['min' => $min, 'max' => $max, 'porcentaje_min' => 30, 'porcentaje_max' => 35];
        }
    }
}
