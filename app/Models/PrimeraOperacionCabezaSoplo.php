<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOperacionCabezaSoplo extends Model
{
    use HasFactory;

    protected $table = 'primeraOperacionCabezaSoplo';

    protected $fillable = [
        'id_proceso',
        'id_ot',
    ];

    public function orden_trabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'id_ot');
    }
}
