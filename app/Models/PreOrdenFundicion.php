<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int              $id
 * @property string           $ot
 * @property string|null      $folio
 * @property string|null      $proveedor
 * @property \Carbon\Carbon|null $fecha_creacion
 * @property \Carbon\Carbon|null $fecha_entrega
 * @property string|null      $moldura
 * @property string|null      $observaciones
 * @property array|null       $filas
 * @property string|null      $pdf_filename
 * @property int|null         $version
 * @property int|null         $user_id
 * @property string|null      $user_nombre
 * @property bool             $is_sent
 * @property \Carbon\Carbon   $created_at
 * @property \Carbon\Carbon   $updated_at
 */
class PreOrdenFundicion extends Model
{
    use HasFactory;

    protected $table = 'pre_ordenes_fundicion';

    protected $fillable = [
        'ot',
        'folio',
        'proveedor',
        'fecha_creacion',
        'fecha_entrega',
        'moldura',
        'observaciones',
        'filas',
        'pdf_filename',
        'is_sent',
        'version',
        'user_id',
        'user_nombre',
    ];

    protected $casts = [
        'filas'  => 'array',
        'fecha_creacion'  => 'date',
        'fecha_entrega' => 'date',
    ];
}
