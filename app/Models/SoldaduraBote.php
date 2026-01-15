<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraBote extends Model
{
    use HasFactory;

    protected $table = 'soldadura_botes';

    protected $fillable = [
        'lote_id',
        'matricula',
        'numero_bote',
        'peso_kg',
        'estado',
    ];

    protected $casts = [
        'peso_kg' => 'decimal:2',
        'lote_id' => 'integer',
        'numero_bote' => 'integer',
    ];

    // Relaciones
    public function lote()
    {
        return $this->belongsTo(SoldaduraLote::class, 'lote_id');
    }

    public function recepcion()
    {
        return $this->hasOne(SoldaduraRecepcionPlanta::class, 'bote_id');
    }

    public function liberacion()
    {
        return $this->hasOne(SoldaduraLiberacion::class, 'bote_id');
    }

    // Métodos auxiliares
    public static function generarMatricula($matriculaLote, $numeroBote)
    {
        return $matriculaLote . '-' . str_pad($numeroBote, 3, '0', STR_PAD_LEFT);
    }

    // Accesores para obtener datos del lote sin duplicarlos
    public function getNombreSoldaduraAttribute()
    {
        return $this->lote->nombre;
    }

    public function getNumeroLoteAttribute()
    {
        return $this->lote->lote;
    }

    public function getNumeroFacturaAttribute()
    {
        return $this->lote->numero_factura;
    }

    public function getFechaIngresoAttribute()
    {
        return $this->lote->fecha_ingreso;
    }
}