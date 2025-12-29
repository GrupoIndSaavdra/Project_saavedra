<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiberacionSoldadura extends Model
{
    protected $table = 'liberacion_soldadura';

    protected $fillable = [
        'operador_id',
        'fecha_entrega',
        'soldadura_id',
        'cantidad',
    ];
}