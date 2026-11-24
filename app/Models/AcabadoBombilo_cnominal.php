<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoBombilo_cnominal extends Model
{
    use HasFactory;
    protected $table = 'acabadobombillo_cnominal';
    protected $fillable = [
        'id_proceso',
        'diametro_mordaza',
        'diametro_ceja',
        'diametro_sufridera',
        'altura_mordaza',
        'altura_ceja',
        'altura_sufridera',
        'diametro_boca',
        'diametro_asiento_corona',
        'diametro_llanta',
        'diametro_caja_corona',
        'profundidad_corona',
        'angulo_30',
        'profundidad_caja_corona',
        'simetria',
    ];
}
