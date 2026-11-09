<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revCalificado_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'revcalificado_tolerancia';
    
    protected $fillable = [
        'id_proceso',
        'diametro_ceja1',
        'diametro_ceja2',
        'diametro_sufridera1',
        'diametro_sufridera2',
        'altura_sufridera1',
        'altura_sufridera2',
        'diametro_conexion1',
        'diametro_conexion2',
        'altura_conexion1',
        'altura_conexion2',
        'diametro_caja1',
        'diametro_caja2',
        'altura_caja1',
        'altura_caja2',
        'altura_total1',
        'altura_total2',
        'simetria1',
        'simetria2',
    ];
}
