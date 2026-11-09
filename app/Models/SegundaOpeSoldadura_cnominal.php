<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegundaOpeSoldadura_cnominal extends Model
{
    use HasFactory;
    protected $table = 'segundaopesoldadura_cnominal';
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
        'alturaTotal',
        'simetria90G',
        'simetriaLinea_Partida',
    ];
}
