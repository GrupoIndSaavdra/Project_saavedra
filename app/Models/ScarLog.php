<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScarLog extends Model
{
    use HasFactory;

    protected $table = 'scar_logs';

    protected $fillable = [
        'ot',
        'tipo_modelo',
        'no_scar',
        'accion',
        'pdf_filename',
        'proveedor',
        'user_id',
        'user_nombre',
    ];
}
