<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PtaResultado;
use App\Models\SoldaduraPTA;
use App\Models\Moldura;

class Orden_trabajo extends Model
{
    use HasFactory;
    protected $table = 'orden_trabajo';

    protected $fillable = [
        'id',
        'id_usuario',
        'id_moldura',
        'fecha',
        'hora_inicio',
        'hora_termino',
    ];

    /**
     * Resultados de Soldadura PTA asociados a esta OT
     */
    public function ptaResultados()
    {
        return $this->hasMany(PtaResultado::class, 'ot_id');
    }

    /**
     * Registros principales de Soldadura PTA de esta OT
     */
    public function soldaduraPTA()
    {
        return $this->hasMany(SoldaduraPTA::class, 'id_ot');
    }

    /**
     * Moldura asociada a esta OT
     */
    public function moldura()
    {
        return $this->belongsTo(Moldura::class, 'id_moldura');
    }
}
