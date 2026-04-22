<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandadoObturador_pza extends Model
{
    use HasFactory;

    protected $table = 'CandadoObturador_pza';

    protected $fillable = [
        'id_pza',
        'id_meta',
        'id_proceso',
        'correcto',
        'estado',
        'n_juego',
        'n_pieza',
        'altura',
        'alturaCandado1',
        'alturaCandado2',
        'alturaAsientoObturador1',
        'alturaAsientoObturador2',
        'profundidadSoldadura1',
        'profundidadSoldadura2',
        'pushUp',
        'observaciones',
        'error',
    ];

    public function meta()
    {
        return $this->belongsTo(Metas::class, 'id_meta');
    }

    public function proceso()
    {
        return $this->belongsTo(CandadoObturador::class, 'id_proceso');
    }
}
