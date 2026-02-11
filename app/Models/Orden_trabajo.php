<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden_trabajo extends Model
{
    use HasFactory;
    protected $table = 'orden_trabajo';

    protected $fillable = [
        'id',
        'id_usuario',
        'id_moldura',
        'fecha',
        'hora_inicio',
        'hora_termino',
    ];
}
