<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class HerramientaTecamac extends Model
{
    use HasFactory;

    protected $table = 'herramientas_tecamac';

    protected $fillable = [
        // Proceso
        'proceso',
        // Herramienta
        'nombre_herramienta',
        'descripcion_herramienta',
        'descripcion_inserto',
        // Accesorio
        'nombre_accesorio',
        'accesorios',
        // Cantidad
        'cantidad_portaherramientas',
        // Condiciones de corte
        'profundidad_corte',
        'rpm',
        'avances',
        // Stock
        'minimo',
        'maximo',
        // Control
        'activo',
    ];

    protected $casts = [
        'proceso'                    => 'array',
        'activo'                     => 'boolean',
        'cantidad_portaherramientas' => 'integer',
        'profundidad_corte'          => 'float',
        'rpm'                        => 'integer',
        'minimo'                     => 'integer',
        'maximo'                     => 'integer',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function imagenes()
    {
        return $this->hasMany(HerramientaImagen::class, 'herramienta_id')
                    ->orderBy('orden');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Devuelve la colección de imágenes filtrada por tipo.
     * Requiere que el modelo se haya cargado con ->with('imagenes').
     */
    public function imagenesPorTipo(string $tipo): Collection
    {
        return $this->imagenes->where('tipo', $tipo)->values();
    }
}
