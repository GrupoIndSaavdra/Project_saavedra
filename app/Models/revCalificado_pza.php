<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class revCalificado_pza extends Model
{
    use HasFactory;
    protected $table = 'revCalificado_pza';

    protected $fillable = [
        'diametro_ceja',
        'diametro_sufridera',
        'altura_sufridera',
        'diametro_conexion',
        'altura_conexion',
        'diametro_caja',
        'altura_caja',
        'altura_total',
        'simetria',
        'observaciones',
    ];
}
