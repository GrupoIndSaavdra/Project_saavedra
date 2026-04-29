<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cavidades_pza extends Model
{
    use HasFactory;
    protected $table = 'cavidades_pza';

    protected $fillable = [
        'id_pza',
        'id_meta',
        'id_proceso',
        'n_pieza',
        'n_juego',
        'estado',
        'error',
        'profundidad1',
        'diametro1',
        'profundidad2',
        'diametro2',
        'profundidad3',
        'diametro3',
        'altura1',
        'altura2',
        'altura3',
        'acetatoBM',
        'observaciones',
    ];
}
