<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrenoManiobra_cnominal extends Model
{
    use HasFactory;
    protected $table = 'barrenomaniobra_cnominal';
    protected $fillable = [
        'id_proceso',
        'profundidad_barreno',
        'diametro_machuelo',
        'acetatoBM',
    ];
}
