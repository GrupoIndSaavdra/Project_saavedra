<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoMolde_pza extends Model
{
    use HasFactory;
    protected $table = 'acabadoMolde_pza';

    protected $fillable = [
        'diametro_mordaza',
        'diametro_ceja',
        'diametro_sufridera',
        'altura_mordaza',
        'altura_ceja',
        'altura_sufridera',
        'gauge_ceja',
        'altura_total',
        'diametro_conexion_fondo',
        'diametro_llanta',
        'diametro_caja_fondo',
        'altura_conexion_fondo',
        'profundidad_llanta',
        'profundidad_caja_fondo',
        'simetria',
        'observaciones',
    ];
}
