<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFileLog extends Model
{
    use HasFactory;

    protected $table = 'manuales_file_log';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'ruta',
        'archivo',
    ];
}
