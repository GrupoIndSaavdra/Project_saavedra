<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Imagen asociada a una herramienta Tecamac.
 * Cada registro representa UNA foto con su tipo, nombre y ruta.
 *
 * Tipos permitidos:
 *   herramienta           → foto del inserto / cuerpo de la herramienta
 *   accesorio             → foto del accesorio de la herramienta
 *   tornilleria           → foto de tornillería
 *   tornilleria_accesorio → foto de accesorio de tornillería
 */
class HerramientaImagen extends Model
{
    use HasFactory;

    protected $table = 'herramientas_tecamac_imagenes';

    protected $fillable = [
        'herramienta_id',
        'tipo',
        'nombre',
        'ruta',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function herramienta()
    {
        return $this->belongsTo(HerramientaTecamac::class, 'herramienta_id');
    }
}
