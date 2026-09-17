<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $salida_moldura_id
 * @property int         $numero_pieza
 * @property string|null $valor_simple   Solo formato MOLDES
 * @property string|null $valor_90       Solo formato BOMBILLOS
 * @property string|null $valor_lp       Solo formato BOMBILLOS
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property SalidaMoldura $reporte
 */
class SalidaMolduraPieza extends Model
{
    use HasFactory;

    protected $table = 'salida_molduras_piezas';

    protected $fillable = [
        'salida_moldura_id',
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
    public function reporte()
    {
        return $this->belongsTo(SalidaMoldura::class, 'salida_moldura_id');
    }
}
