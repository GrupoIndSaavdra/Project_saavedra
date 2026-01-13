<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrGenerado extends Model
{
    use HasFactory;

    protected $table = 'qr_generados';

    protected $fillable = [
        'id_operador',
        'id_soldadura',
        'fecha_entrega',
        'cantidad_entregada',
        'contenido_qr',
        'archivo_qr'
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
        'cantidad_entregada' => 'decimal:2'
    ];
}