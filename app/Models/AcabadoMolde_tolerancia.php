<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoMolde_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'acabadomolde_tolerancia';
    protected $fillable = [
        'id_proceso',
        'diametro_mordaza1',
        'diametro_mordaza2',
        'diametro_ceja1',
        'diametro_ceja2',
        'diametro_sufridera1',
        'diametro_sufridera2',
        'altura_mordaza1',
        'altura_mordaza2',
        'altura_ceja1',
        'altura_ceja2',
        'altura_sufridera1',
        'altura_sufridera2',
        'diametro_conexion_fondo1',
        'diametro_conexion_fondo1',
        'diametro_llanta1',
        'diametro_llanta2',
        'diametro_caja_fondo1',
        'diametro_caja_fondo2',
        'altura_conexion_fondo1',
        'altura_conexion_fondo2',
        'profundidad_llanta1',
        'profundidad_llanta2',
        'profundidad_caja_fondo1',
        'profundidad_caja_fondo2',
        'simetria1',
        'simetria2',
    ];
}
