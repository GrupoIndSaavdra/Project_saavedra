<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiberacionSoldadura extends Model
{
    use HasFactory;
    protected $table = 'soldadura_liberacion';

    protected $fillable = [
        'id_operador',
        'fecha_entrega',
        'nombre',
        'lote',
        'cantidad'
    ];
}