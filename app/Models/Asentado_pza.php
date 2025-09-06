<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asentado_pza extends Model
{
    use HasFactory;
    protected $table = 'asentado_pza';

    protected $fillable = [
        'sin_juego',
        'sin_luz',
        'observaciones',
    ];
}
