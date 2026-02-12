<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cavidades_cnominal extends Model
{
    use HasFactory;
    protected $table = 'cavidades_cnominal';
    protected $fillable = [
        'id_proceso',
        'profundidad1',
        'diametro1',
        'profundidad2',
        'diametro2',
        'profundidad3',
        'diametro3',
        'altura1',
        'altura2',
        'altura3',
    ];
}
