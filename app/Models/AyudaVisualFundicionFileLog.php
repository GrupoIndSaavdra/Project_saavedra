<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyudaVisualFundicionFileLog extends Model
{
    use HasFactory;

    protected $table = 'ayudas_visuales_fundicion_file_log';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'ruta',
        'archivo',
    ];
}
