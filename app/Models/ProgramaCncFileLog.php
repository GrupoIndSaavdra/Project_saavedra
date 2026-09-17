<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramaCncFileLog extends Model
{
    protected $table = 'programas_maquinados_file_log';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'ruta',
        'archivo',
    ];
}
