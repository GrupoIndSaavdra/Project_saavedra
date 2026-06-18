<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property string      $ot
 * @property array|null  $ayudas_config
 * @property string|null $status
 * @property \Carbon\Carbon|null $alert_sent_at
 * @property array|null  $almacen_archivos
 * @property bool        $tiene_modelo
 * @property bool        $pre_orden_sent
 * @property bool        $pre_orden_email_sent
 * @property string|null $calidad_revision_status
 * @property bool        $casting_pdf_generated
 * @property bool        $rechazos_procesados
 * @property bool        $dibujos_vistos_almacen
 * @property bool        $pre_orden_autorizada
 * @property bool        $alerta_calidad_sent
 * @property bool        $documentos_revisados_calidad
 * @property bool        $alerta_almacen_2_sent
 * @property bool        $documentos_vistos_almacen_2
 * @property bool        $documentos_firmados_cargados
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FundicionHistory extends Model
{
    use HasFactory;

    protected $table = 'fundicion_history';

    protected $fillable = [
        'ot',
        'ayudas_config',
        'status',
        'alert_sent_at',
        'almacen_archivos',
        'tiene_modelo',
        'pre_orden_sent',
        'pre_orden_email_sent',
        'calidad_revision_status',
        'casting_pdf_generated',
        'rechazos_procesados',
        'dibujos_vistos_almacen',
        'pre_orden_autorizada',
        'alerta_calidad_sent',
        'documentos_revisados_calidad',
        'alerta_almacen_2_sent',
        'documentos_vistos_almacen_2',
        'documentos_firmados_cargados',
    ];

    protected $casts = [
        'alert_sent_at'                  => 'datetime',
        'almacen_archivos'               => 'array',
        'ayudas_config'                  => 'array',
        'tiene_modelo'                   => 'boolean',
        'pre_orden_sent'                 => 'boolean',
        'pre_orden_email_sent'           => 'boolean',
        'casting_pdf_generated'          => 'boolean',
        'rechazos_procesados'            => 'boolean',
        'dibujos_vistos_almacen'         => 'boolean',
        'pre_orden_autorizada'           => 'boolean',
        'alerta_calidad_sent'            => 'boolean',
        'documentos_revisados_calidad'   => 'boolean',
        'alerta_almacen_2_sent'          => 'boolean',
        'documentos_vistos_almacen_2'    => 'boolean',
        'documentos_firmados_cargados'   => 'boolean',
    ];
}
