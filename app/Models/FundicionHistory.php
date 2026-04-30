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
    ];

    protected $casts = [
        'alert_sent_at'    => 'datetime',
        'almacen_archivos' => 'array',
        'ayudas_config'    => 'array',
    ];
}
