<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pza_cepillado extends Model
{
    use HasFactory;
    protected $table = 'cepillado_pza';

    protected $fillable = [
        'id_pza',
        'id_meta',
        'id_proceso',
        'n_pieza',
        'n_juego',
        'estado',
        'error',
        'radiof_mordaza',
        'radiof_mayor',
        'radiof_sufridera',
        'profuFinal_CFC',
        'profuFinal_mitadMB',
        'profuFinal_PCO',
        'acetato_MB',
        'ensamble',
        'distancia_barrenoAli',
        'profu_barrenoAliHembra',
        'profu_barrenoAliMacho',
        'altura_venaHembra',
        'altura_venaMacho',
        'ancho_vena',
        'laterales',
        'pin1',
        'pin2',
        'observaciones',
    ];
}
