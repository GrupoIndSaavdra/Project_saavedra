<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrdenLog extends Model
{
    use HasFactory;

    protected $table = 'preorden_logs';

    protected $fillable = [
        'ot',
        'proveedor',
        'accion',
        'pdf_filename',
        'user_id',
        'user_nombre',
    ];
}
