<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiberacionLog extends Model
{
    use HasFactory;

    protected $table = 'liberacion_logs';

    protected $fillable = [
        'ot',
        'tipo_modelo',
        'accion',
        'pdf_filename',
        'estado_global',
        'user_id',
        'user_nombre',
    ];
}
