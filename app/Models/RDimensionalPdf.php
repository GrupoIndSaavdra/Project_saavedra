<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RDimensionalPdf extends Model
{
    use HasFactory;

    protected $table = 'r_dimensionales_pdfs';

    protected $fillable = [
        'r_dimensional_id',
        'version',
        'nombre_archivo',
        'ruta',
        'creado_por',
    ];

    public function rDimensional()
    {
        return $this->belongsTo(RDimensional::class, 'r_dimensional_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por', 'id');
    }
}
