<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cavidades_tolerancia extends Model
{
    use HasFactory;
    protected $table = 'cavidades_tolerancia';
    protected $fillable = [
        'id_proceso',
        'profundidad1_1',
        'profundidad2_1',
        'diametro1_1',
        'diametro2_1',
        'profundidad1_2',
        'profundidad2_2',
        'diametro1_2',
        'diametro2_2',
        'profundidad1_3',
        'profundidad2_3',
        'diametro1_3',
        'diametro2_3',
        'altura1',
        'altura2',
        'altura3',
    ];
}
