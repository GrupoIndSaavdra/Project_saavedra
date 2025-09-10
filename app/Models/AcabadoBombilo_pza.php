<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoBombilo_pza extends Model
{
    use HasFactory;
    protected $table = 'acabadoBombillo_pza';

    protected $fillable = [
        'diametro_mordaza',
        'diametro_ceja',
        'diametro_sufridera',
        'altura_mordaza',
        'altura_ceja',
        'altura_sufridera',
        'gauge_ceja',
        'gauge_corona',
        'gauge_llanta',
        'altura_total',
        'diametro_boca',
        'diametro_asiento_corona',
        'diametro_llanta',
        'diametro_caja_corona',
        'profundidad_corona',
        'angulo_30',
        'profundidad_caja_corona',
        'simetria',
        'observaciones',
    ];
}
