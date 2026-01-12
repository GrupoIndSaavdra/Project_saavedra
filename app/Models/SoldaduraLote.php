<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraLote extends Model
{
    use HasFactory;

    protected $table = 'soldadura_lotes';

    protected $fillable = [
        'id_unico',
        'fecha_ingreso',
        'nombre',
        'lote',
        'kilos_totales',
        'numero_factura',
        'botes_generados',
        'estado'
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'kilos_totales' => 'decimal:2',
    ];

    public function botes()
    {
        return $this->hasMany(SoldaduraBoteIndividual::class, 'lote_id');
    }

    public static function generarIdUnico($nombre, $lote)
    {
        do {
            $fecha = now()->format('dm');
            $hora = now()->format('Hi');
            $nombreCorto = strtoupper(substr($nombre, 0, 2));
            $loteCorto = strtoupper(substr($lote, 0, 2));
            
            $idUnico = $fecha . $hora . $nombreCorto . $loteCorto;
            
            // Si ya existe, agregar segundos para hacerlo único
            if (self::where('id_unico', $idUnico)->exists()) {
                $segundos = now()->format('s');
                $idUnico = $fecha . $hora . $segundos . $nombreCorto . $loteCorto;
            }
        } while (self::where('id_unico', $idUnico)->exists());
        
        return $idUnico;
    }
}