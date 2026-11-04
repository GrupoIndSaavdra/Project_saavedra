<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class ViewPerformanceLog extends Model
{
    use HasFactory, MassPrunable;

    protected $table = 'view_performance_logs';

    /**
     * Define la consulta para depuración automática de modelos (registros con más de 3 días de antigüedad).
     */
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(3));
    }

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

