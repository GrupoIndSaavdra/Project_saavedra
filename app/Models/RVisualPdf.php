<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RVisualPdf extends Model
{
    use HasFactory;

    protected $table = 'r_visuales_pdfs';

    protected $fillable = [
        'r_visual_id',
        'version',
        'nombre_archivo',
        'ruta',
        'creado_por',
    ];

    public function rVisual()
    {
        return $this->belongsTo(RVisual::class, 'r_visual_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por', 'id');
    }
}
