<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffSet_pza extends Model
{
    use HasFactory;
    protected $table = 'offSet_pza';
    protected $fillable = [
        'anchoRanura',
        'profuTaconHembra',
        'profuTaconMacho',
        'simetriaHembra',
        'simetriaMacho',
        'anchoTacon',
        'barrenoLateralHembra',
        'barrenoLateralMacho',
        'alturaTaconInicial',
        'alturaTaconIntermedia',
        'observaciones',
    ];
}
