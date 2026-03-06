<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_ot',
        'nombre',
        'tamanio',
        'seccion',
        'piezas',
        'pedido',
        'finalizada',
    ];
    public $timestamps = false;

    /**
     * Piezas asociadas a esta clase
     */
    public function piezas()
    {
        return $this->hasMany(\App\Models\Pieza::class, 'id_clase');
    }
}
