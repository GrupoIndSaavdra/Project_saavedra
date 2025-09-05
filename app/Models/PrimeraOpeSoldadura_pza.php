<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOpeSoldadura_pza extends Model
{
    use HasFactory;
    protected $table = 'primeraOpeSoldadura_pza';

    protected $fillable = [
        'diametro1',
        'profundidad1',
        'diametro2',
        'profundidad2',
        'diametro3',
        'profundidad3',
        'diametroSoldadura',
        'profundidadSoldadura',
        'diametroBarreno',
        'simetriaLinea_partida',
        'pernoAlineacion',
        'Simetria90G',
        'observaciones',
    ];
}
