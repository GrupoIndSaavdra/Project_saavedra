<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOperacionCabezaSoplo_tolerancia extends Model
{
    use HasFactory;

    protected $table = 'primeraOperacionCabezaSoplo_tolerancia';

    protected $fillable = [
        'id_proceso',
        'diametro_exterior1',
        'diametro_exterior2',
        'longitud1',
        'longitud2',
        'diametro_candado1',
        'diametro_candado2',
        'longitud_candado1',
        'longitud_candado2'
    ];
}
