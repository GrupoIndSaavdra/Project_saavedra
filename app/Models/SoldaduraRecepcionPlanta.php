<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraRecepcionPlanta extends Model
{
    use HasFactory;

    protected $table = 'soldadura_recepciones_planta';

    protected $fillable = [
        'bote_id',
        'recibido_por',
        'fecha_hora_recepcion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_hora_recepcion' => 'datetime',
        'bote_id' => 'integer',
        'recibido_por' => 'integer',
    ];

    // Relaciones
    public function bote()
    {
        return $this->belongsTo(SoldaduraBote::class, 'bote_id');
    }

    public function recibidor()
    {
        return $this->belongsTo(User::class, 'recibido_por');
    }
}