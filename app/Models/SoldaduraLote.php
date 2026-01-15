<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraLote extends Model
{
    use HasFactory;

    protected $table = 'soldadura_lotes';

    protected $fillable = [
        'matricula',
        'nombre',
        'lote',
        'numero_factura',
        'peso_total_kg',
        'fecha_ingreso',
        'botes_generados',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'peso_total_kg' => 'decimal:2',
        'botes_generados' => 'integer',
    ];

    // Relaciones
    public function botes()
    {
        return $this->hasMany(SoldaduraBote::class, 'lote_id');
    }

    // Métodos auxiliares
    public static function generarMatricula($nombre, $lote)
    {
        $fecha = now()->format('dmy'); // Día, Mes, Año (2 dígitos)
        $hora = now()->format('Hi'); // Hora y minutos
        $nombreCorto = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nombre), 0, 3));
        $loteCorto = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $lote), 0, 3));
        
        $matricula = "{$fecha}{$hora}{$nombreCorto}{$loteCorto}";
        
        // Si existe, agregar segundos
        $contador = 1;
        $matriculaOriginal = $matricula;
        while (self::where('matricula', $matricula)->exists()) {
            $matricula = $matriculaOriginal . str_pad($contador, 2, '0', STR_PAD_LEFT);
            $contador++;
        }
        
        return $matricula;
    }

    public function cantidadBotesEsperados()
    {
        return (int) floor($this->peso_total_kg / 5);
    }

    public function botesGeneradosCompletados()
    {
        return $this->botes_generados >= $this->cantidadBotesEsperados();
    }
}