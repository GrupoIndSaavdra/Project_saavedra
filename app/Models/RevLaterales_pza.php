<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevLaterales_pza extends Model
{
    use HasFactory;
    protected $table = 'revlaterales_pza';

    protected $fillable = [
        'desfasamiento_entrada',
        'desfasamiento_salida',
        'ancho_simetriaEntrada',
        'ancho_simetriaSalida',
        'angulo_corte',
        'observaciones',
    ];
}
