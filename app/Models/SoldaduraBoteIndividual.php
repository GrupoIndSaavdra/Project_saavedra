<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraBoteIndividual extends Model
{
    use HasFactory;

    protected $table = 'soldadura_botes_individuales';

    protected $fillable = [
        'id_unico',
        'lote_id',
        'nombre',
        'lote',
        'peso',
        'numero_factura',
        'numero_bote',
        'estado'
    ];

    protected $casts = [
        'peso' => 'decimal:2',
    ];

    public function loteOriginal()
    {
        return $this->belongsTo(SoldaduraLote::class, 'lote_id');
    }

    public function liberacion()
    {
        return $this->hasOne(SoldaduraLiberacion::class, 'bote_id');
    }

    public static function generarIdUnico($idLote, $numeroBote)
    {
        return $idLote . str_pad($numeroBote, 2, '0', STR_PAD_LEFT);
    }
}