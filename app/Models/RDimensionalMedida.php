<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RDimensionalMedida extends Model
{
    use HasFactory;

    protected $table = 'r_dimensional_medidas';

    protected $fillable = [
        'r_dimensional_id',
        'numero_pieza',
        'c1',
        'c2',
        'c',
        'cuello',
        'e',
        'd',
        'f1',
        'a_total',
        'f',
        'altura',
        'altura_2',
        'al_cuello',
        'observaciones',
        'altu_1',
        'talon',
        'b',
        'k',
        'l',
        'f2',
        'e2',
        'a',
        'volumen',
        'datos_extra',
    ];

    protected $casts = [
        'datos_extra' => 'array',
    ];

    public function reporte()
    {
        return $this->belongsTo(RDimensional::class, 'r_dimensional_id');
    }
}
