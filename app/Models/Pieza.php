<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PtaResultado;

class Pieza extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'id_ot',
        'id_clase',
        'n_pieza',
        'id_operador',
        'maquina',
        'proceso',
        'error',
        'liberacion',
        'fecha_liberacion',
        'user_liberacion',
        'observacion_liberacion',
        'observacion_operador',
    ];

    /**
     * Relación con el usuario que liberó la pieza
     */
    public function liberador()
    {
        return $this->belongsTo(User::class, 'user_liberacion', 'matricula');
    }

    /**
     * Relación con el operador
     */
    public function operador()
    {
        return $this->belongsTo(User::class, 'id_operador', 'matricula');
    }

    /**
     * Relación con la clase
     */
    public function clase()
    {
        return $this->belongsTo(Clase::class, 'id_clase');
    }

    /**
     * Relación con la orden de trabajo
     */
    public function ordenTrabajo()
    {
        return $this->belongsTo(Orden_trabajo::class, 'id_ot');
    }

    /**
     * Resultado de Soldadura PTA asociado a esta pieza
     */
    public function ptaResultado()
    {
        return $this->hasOne(PtaResultado::class, 'pieza_id');
    }
}
