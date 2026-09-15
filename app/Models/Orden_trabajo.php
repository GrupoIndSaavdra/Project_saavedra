<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PtaResultado;
use App\Models\SoldaduraPTA;
use App\Models\Moldura;

/**
 * @property string           $id
 * @property int|null         $id_usuario
 * @property int              $id_moldura
 * @property string|null      $fecha
 * @property string|null      $hora_inicio
 * @property string|null      $hora_termino
 * @property int|null         $prioridad   Orden de prioridad en la vista de progreso (1 = mayor urgencia, NULL = sin asignar)
 * @property string|null      $fecha_compra
 * @property string|null      $orden_compra
 * @property string|null      $cliente
 * @property string|null      $nombre_producto
 * @property int|null         $cantidad
 * @property string|null      $proveedor_material
 * @property string|null      $material
 * @property string|null      $fecha_entrega_fundicion
 * @property string|null      $semana_entrega_cliente
 * @property string|null      $fecha_entrega_cliente
 * @property string|null      $fecha_real
 * @property string|null      $forma_grabados
 * @property string|null      $entrega_tecamac
 * @property string|null      $observaciones_prioridad
 * @property \Carbon\Carbon   $created_at
 * @property \Carbon\Carbon   $updated_at
 * @property Moldura|null     $moldura
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Clase[] $clases
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
        'fecha_compra',
        'orden_compra',
        'cliente',
        'nombre_producto',
        'cantidad',
        'proveedor_material',
        'material',
        'fecha_entrega_fundicion',
        'semana_entrega_cliente',
        'fecha_entrega_cliente',
        'fecha_real',
        'forma_grabados',
        'entrega_tecamac',
        'observaciones_prioridad',
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
