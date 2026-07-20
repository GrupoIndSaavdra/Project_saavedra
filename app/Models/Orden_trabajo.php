<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PtaResultado;
use App\Models\SoldaduraPTA;
use App\Models\Moldura;

/**
 * @property string          $id
 * @property int              $id_moldura
 * @property int|null         $prioridad   Orden de prioridad en la vista de progreso (1 = mayor urgencia, NULL = sin asignar)
 * @property \Carbon\Carbon   $created_at
 * @property \Carbon\Carbon   $updated_at
 */
class Orden_trabajo extends Model
{
    use HasFactory;
    protected $table = 'orden_trabajo';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'id_usuario',
        'id_moldura',
        'fecha',
        'hora_inicio',
        'hora_termino',
        'prioridad',
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

    /**
     * Clases asociadas a esta OT
     */
    public function clases()
    {
        return $this->hasMany(\App\Models\Clase::class, 'id_ot');
    }
}
