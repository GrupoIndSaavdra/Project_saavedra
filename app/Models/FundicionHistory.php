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
    ];

    protected $casts = [
        'alert_sent_at'           => 'datetime',
        'almacen_archivos'        => 'array',
        'ayudas_config'           => 'array',
        'tiene_modelo'            => 'boolean',
        'pre_orden_sent'          => 'boolean',
        'pre_orden_email_sent'    => 'boolean',
        'casting_pdf_generated'   => 'boolean',
        'rechazos_procesados'     => 'boolean',
    ];
}
