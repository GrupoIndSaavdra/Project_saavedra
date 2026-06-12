<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RechazoLog extends Model
{
    use HasFactory;

    protected $table = 'rechazo_logs';

    protected $fillable = [
        'ot',
        'tipo_modelo',
        'accion',
        'pdf_filename',
        'motivo_rechazo',
        'user_id',
        'user_nombre',
    ];
}
