<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandadoObturador extends Model
{
    use HasFactory;

    protected $table = 'CandadoObturador';

    protected $fillable = [
        'id_proceso',
        'id_ot',
        'id_clase',
        'operacion',
    ];

    public function orden_trabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'id_ot');
    }
}
