<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegundaOperacionCabezaSoplo_cnominal extends Model
{
    use HasFactory;

    protected $table = 'segundaoperacioncabezasoplo_cnominal';

    protected $fillable = [
        'id_proceso',
        'diametro_exterior',
        'longitud',
        'diametro_candado',
        'longitud_candado'
    ];
}
