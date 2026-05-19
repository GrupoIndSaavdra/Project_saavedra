<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
