<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoMolde_cnominal extends Model
{
    use HasFactory;
    protected $table = 'acabadomolde_cnominal';
    protected $fillable = [
        'id_proceso',
        'diametro_mordaza',
        'diametro_ceja',
        'diametro_sufridera',
        'altura_mordaza',
        'altura_ceja',
        'altura_sufridera',
        'diametro_conexion_fondo',
        'diametro_llanta',
        'diametro_caja_fondo',
        'altura_conexion_fondo',
        'profundidad_llanta',
        'profundidad_caja_fondo',
        'simetria',
    ];
}
