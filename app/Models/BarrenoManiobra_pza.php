<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarrenoManiobra_pza extends Model
{
    use HasFactory;
    protected $table = 'barrenomaniobra_pza';

    protected $fillable = [
        'profundidad_barreno',
        'diametro_machuelo',
        'acetatoBM',
        'observaciones',
    ];
}
