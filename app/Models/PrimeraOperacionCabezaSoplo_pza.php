<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOperacionCabezaSoplo_pza extends Model
{
    use HasFactory;

    protected $table = 'primeraOperacionCabezaSoplo_pza';

    protected $fillable = [
        'id_pza',
        'id_meta',
        'id_proceso',
        'correcto',
        'estado',
        'n_juego',
        'n_pieza',
        'diametro_exterior',
        'longitud',
        'diametro_candado',
        'longitud_candado',
        'observaciones',
        'error',
    ];

    public function meta()
    {
        return $this->belongsTo(Metas::class, 'id_meta');
    }

    public function proceso()
    {
        return $this->belongsTo(PrimeraOperacionCabezaSoplo::class, 'id_proceso');
    }
}
