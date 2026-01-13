<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRGeneradoSoldadura extends Model
{
    use HasFactory;

    protected $table = 'soldadura_qr_generados';

    protected $fillable = [
        'id_operador',
        'fecha_generacion',
        'nombre',
        'lote',
        'kilos',
        'qr_content',
        'estado'
    ];

    protected $casts = [
        'fecha_generacion' => 'date',
        'kilos' => 'decimal:2'
    ];

    // Estados válidos para el QR
    const ESTADO_GENERADO = 'generado';
    const ESTADO_LIBERADO = 'liberado';
    const ESTADO_CANCELADO = 'cancelado';

    // Mutador para asegurar que el estado sea válido
    public function setEstadoAttribute($value)
    {
        $estadosValidos = [self::ESTADO_GENERADO, self::ESTADO_LIBERADO, self::ESTADO_CANCELADO, null, ''];
        
        if (!in_array($value, $estadosValidos)) {
            throw new \InvalidArgumentException("Estado inválido: {$value}");
        }
        
        $this->attributes['estado'] = $value;
    }

    // Accessor para obtener el estado con valor por defecto
    public function getEstadoAttribute($value)
    {
        return $value ?? self::ESTADO_GENERADO;
    }

    // Relación con el operador
    public function operador()
    {
        return $this->belongsTo(User::class, 'id_operador');
    }

    // Relación con liberaciones
    public function liberaciones()
    {
        return $this->hasMany(LiberacionSoldadura::class, 'qr_generado_id');
    }
}