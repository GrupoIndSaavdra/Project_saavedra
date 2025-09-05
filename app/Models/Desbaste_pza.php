<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desbaste_pza extends Model
{
    use HasFactory;
    protected $table = 'desbaste_pza';

    protected $fillable = [
        'diametro_mordaza',
        'diametro_ceja',
        'diametro_sufrideraExtra',
        'simetria_ceja',
        'simetria_mordaza',
        'altura_ceja',
        'altura_sufridera',
        'observaciones',
    ];
}
