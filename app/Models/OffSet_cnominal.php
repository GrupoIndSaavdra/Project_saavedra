<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffSet_cnominal extends Model
{
    use HasFactory;
    protected $table = 'offset_cnominal';
    protected $fillable = [
        'id_proceso',
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
    ];
}
