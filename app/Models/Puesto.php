<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    use HasFactory;

    protected $table = 'puestos';

    protected $fillable = [
        'area_id',
        'nombre',
        'codigo_nivel',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'puesto_id');
    }
}
