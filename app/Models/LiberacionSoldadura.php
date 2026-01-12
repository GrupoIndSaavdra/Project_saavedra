<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiberacionSoldadura extends Model
{
    use HasFactory;
    protected $table = 'soldadura_liberacion';

    protected $fillable = [
        'id_operador',
        'fecha_entrega',
        'nombre',
        'lote',
        'cantidad',
        'qr_generado_id',
        'estado'
    ];
    
    // Relación con QR generado
    public function qrGenerado()
    {
        return $this->belongsTo(QRGeneradoSoldadura::class, 'qr_generado_id');
    }
    
    // Relación con operador
    public function operador()
    {
        return $this->belongsTo(User::class, 'id_operador');
    }
}