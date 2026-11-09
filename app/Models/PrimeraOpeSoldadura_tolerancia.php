<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOpeSoldadura_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'primeraopesoldadura_tolerancia';
    
    protected $fillable = [
        'id_proceso',
        'diametro1',
        'profundidad1',
        'diametro2',
        'profundidad2',
        'diametro3',
        'profundidad3',
        'diametroSoldadura',
        'profundidadSoldadura',
        'diametroBarreno1',
        'diametroBarreno2', 
        'simetriaLinea_partida1',
        'simetriaLinea_partida2', 
        'pernoAlineacion', 
        'Simetria90G', 
    ];
}
