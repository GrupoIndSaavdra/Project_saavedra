<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_ot',
        'nombre',
        'tamanio',
        'seccion',
        'piezas',
        'pedido',
        'finalizada',
    ];
    public $timestamps = false;
}
