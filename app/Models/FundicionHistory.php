<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
