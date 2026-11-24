<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegundaOpeSoldadura_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'segundaopesoldadura_tolerancia';
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
        'alturaTotal1',
        'alturaTotal2',
        'simetria90G1',
        'simetria90G2',
        'simetriaLinea_Partida',
    ];
}
