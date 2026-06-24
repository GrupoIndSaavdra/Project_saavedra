<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TratamientoTermico extends Model
{
    protected $fillable = [
        'id_clase',
        'archivo',
        'descripcion',
        'registrado_por'
    ];

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'id_clase');
    }
}
