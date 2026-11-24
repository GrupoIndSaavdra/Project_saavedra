<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmbudoCM_pza extends Model
{
    use HasFactory;
    protected $table = 'embudocm_pza';

    protected $fillable = [
        'id_pza',
        'id_proceso	',
        'n_juego',
        'conexion_lineaPartida',
        'conexion_90G',
        'altura_conexion',
        'diametro_embudo',
        'observaciones',
    ];
}
