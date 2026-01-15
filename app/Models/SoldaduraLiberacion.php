<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraLiberacion extends Model
{
    use HasFactory;

    protected $table = 'soldadura_liberaciones';

    protected $fillable = [
        'bote_id',
        'operador_id',
        'liberado_por',
        'matricula_liberacion',
        'fecha_hora_liberacion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_hora_liberacion' => 'datetime',
        'bote_id' => 'integer',
        'operador_id' => 'integer',
        'liberado_por' => 'integer',
    ];

    // Relaciones
    public function bote()
    {
        return $this->belongsTo(SoldaduraBote::class, 'bote_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function liberador()
    {
        return $this->belongsTo(User::class, 'liberado_por');
    }

    // Métodos auxiliares
    public static function generarMatriculaLiberacion($matriculaBote, $matriculaOperador)
    {
        return $matriculaBote . '-OP' . $matriculaOperador;
    }
}