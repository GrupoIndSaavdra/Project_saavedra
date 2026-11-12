<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    public const DIRECCION_GENERAL = 1;
    public const SISTEMAS = 11;

    protected $table = 'areas';

    protected $fillable = [
        'nombre',
        'planta',
        'codigo_prefijo',
    ];

    public function puestos()
    {
        return $this->hasMany(Puesto::class, 'area_id')->orderBy('codigo_nivel');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'area_id');
    }

    /**
     * Indica si el área opera en la planta indicada (o en ambas).
     */
    public function perteneceAPlanta(?string $planta): bool
    {
        return $this->planta === 'AMBAS' || $this->planta === $planta;
    }
}
