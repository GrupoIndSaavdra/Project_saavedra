<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewPerformanceLog extends Model
{
    use HasFactory;

    protected $table = 'view_performance_logs';

    protected $fillable = [
        'url',
        'ruta',
        'vista',
        'metodo',
        'tiempo_ms',
        'matricula',
        'perfil',
        'status_code',
        'ip',
    ];

    protected $casts = [
        'tiempo_ms' => 'float',
        'status_code' => 'integer',
        'perfil' => 'integer',
    ];
}

