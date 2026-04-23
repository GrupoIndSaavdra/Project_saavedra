<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundicionFileLog extends Model
{
    use HasFactory;

    protected $table = 'fundicion_file_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'ruta',
        'archivo',
    ];
}
