<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RInternoSalidaPdf extends Model
{
    use HasFactory;

    protected $table = 'r_interno_salidas_pdfs';

    protected $fillable = [
        'r_interno_salida_id',
        'version',
        'nombre_archivo',
        'ruta',
        'creado_por',
    ];

    public function rInternoSalida()
    {
        return $this->belongsTo(RInternoSalida::class, 'r_interno_salida_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por', 'id');
    }
}

