<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RVisualMedida extends Model
{
    use HasFactory;

    protected $table = 'r_visual_medidas';

    protected $fillable = [
        'r_visual_id',
        'numero_pieza',
        'temp_agua',
        'vol_real',
        'dif_vs_vol_ideal',
        'observaciones',
    ];

    /**
     * Reporte visual al que pertenece esta medida
     */
    public function reporte()
    {
        return $this->belongsTo(RVisual::class, 'r_visual_id');
    }
}
