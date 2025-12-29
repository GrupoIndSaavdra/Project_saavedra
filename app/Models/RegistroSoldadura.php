<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroSoldadura extends Model
{
    use HasFactory;

    protected $table = 'soldadura_registro';

    protected $fillable = [
        'fecha_ingreso',
        'nombre',
        'lote',
        'kilos',
    ];
}