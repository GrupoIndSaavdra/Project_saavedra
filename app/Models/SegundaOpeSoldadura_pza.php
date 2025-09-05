<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegundaOpeSoldadura_pza extends Model
{
    use HasFactory;
    protected $table = 'segundaOpeSoldadura_pza';
    protected $fillable = [
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
        'observaciones',
    ];
}
