<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrenoManiobra_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'barrenomaniobra_tolerancia';
    protected $fillable = [
        'id_proceso',
        'profundidad_barreno1',
        'profundidad_barreno2',
        'diametro_machuelo1',
        'diametro_machuelo2',
        'acetatoBM',
    ];
}
