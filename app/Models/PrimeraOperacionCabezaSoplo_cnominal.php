<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOperacionCabezaSoplo_cnominal extends Model
{
    use HasFactory;

    protected $table = 'primeraOperacionCabezaSoplo_cnominal';

    protected $fillable = [
        'id_proceso',
        'diametro_exterior',
        'longitud',
        'diametro_candado',
        'longitud_candado'
    ];
}
