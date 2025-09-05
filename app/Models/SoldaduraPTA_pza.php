<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraPTA_pza extends Model
{
    use HasFactory;
    protected $table = 'soldaduraPTA_pza';

    protected $fillable = [
        'temp_calentado',
        'temp_dispositivo',
        'limpieza',
        'error',
        'observaciones',
    ];
}
