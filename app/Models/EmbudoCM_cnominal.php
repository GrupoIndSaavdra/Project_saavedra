<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmbudoCM_cnominal extends Model
{
    use HasFactory;
    protected $table = 'embudocm_cnominal';
    protected $fillable = [
        'id_proceso',
        'conexion_lineaPartida',
        'conexion_90G',
        'altura_conexion',
        'diametro_embudo',
    ];
}
