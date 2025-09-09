<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rebajes_pza extends Model
{
    use HasFactory;
    protected $table = 'rebajes_pza';

    protected $fillable = [
        'rebaje1',
        'rebaje2',
        'rebaje3',
        'profundidad_bordonio',
        'vena1',
        'vena2',
        'simetria',
        'observaciones',
    ];
}
