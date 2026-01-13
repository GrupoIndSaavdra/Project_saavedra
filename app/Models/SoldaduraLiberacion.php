<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraLiberacion extends Model
{
    use HasFactory;

    protected $table = 'soldadura_liberaciones';

    protected $fillable = [
        'id_unico',
        'bote_id',
        'id_operador',
        'id_liberador',
        'fecha_liberacion',
        'nombre',
        'lote',
        'peso',
        'numero_factura',
        'estado'
    ];

    protected $casts = [
        'fecha_liberacion' => 'date',
        'peso' => 'decimal:2',
    ];

    public function bote()
    {
        return $this->belongsTo(SoldaduraBoteIndividual::class, 'bote_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'id_operador');
    }

    public function liberador()
    {
        return $this->belongsTo(User::class, 'id_liberador');
    }

    public static function generarIdUnico($idBote, $matriculaOperador)
    {
        return $idBote . $matriculaOperador;
    }
}