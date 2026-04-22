<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandadoObturador_tolerancia extends Model
{
    use HasFactory;

    protected $table = 'CandadoObturador_tolerancia';

    protected $fillable = [
        'id_proceso',
        'altura',
        'alturaCandado1',
        'alturaCandado2',
        'alturaAsientoObturador1',
        'alturaAsientoObturador2',
        'profundidadSoldadura1',
        'profundidadSoldadura2',
        'pushUp',
    ];

    public function proceso()
    {
        return $this->belongsTo(CandadoObturador::class, 'id_proceso', 'id_proceso');
    }
}
