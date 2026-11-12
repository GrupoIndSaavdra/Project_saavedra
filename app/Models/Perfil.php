<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasFactory;

    public const MASTER = 1;
    public const SUPERVISOR = 3;
    public const OPERADOR = 9;
    public const SISTEMAS = 10;

    protected $table = 'perfiles';

    protected $fillable = [
        'nombre',
        'codigo_perfil',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'perfil_id');
    }
}
