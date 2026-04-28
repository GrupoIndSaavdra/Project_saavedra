<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_matricula',
        'action',
        'details',
        'ot',
        'clase',
        'proceso',
        'maquina',
        'n_pieza',
        'h_inicio',
        'h_termino',
        'id_ot',
        'id_clase',
    ];
}
