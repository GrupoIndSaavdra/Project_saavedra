<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $r_interno_salida_id
 * @property int         $numero_pieza
 * @property string|null $valor_simple   Solo formato MOLDES
 * @property string|null $valor_90       Solo formato BOMBILLOS
 * @property string|null $valor_lp       Solo formato BOMBILLOS
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property RInternoSalida $reporte
 */
class RInternoSalidaPieza extends Model
{
    use HasFactory;

    protected $table = 'r_interno_salidas_piezas';

    protected $fillable = [
        'r_interno_salida_id',
        'numero_pieza',
        'valor_simple',
        'valor_90',
        'valor_lp',
        'descripcion',
    ];

    protected $casts = [
        'numero_pieza' => 'integer',
    ];

    // ─── Relaciones ────────────────────────────────────────────────

    /** Reporte al que pertenece esta pieza */
    public function reporte(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RInternoSalida::class, 'r_interno_salida_id');
    }
}
