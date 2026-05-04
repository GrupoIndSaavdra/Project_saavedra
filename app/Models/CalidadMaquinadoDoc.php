<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo de índice para los documentos de Calidad — Maquinados.
 *
 * Sincronizado automáticamente cada 5 min por el Command
 * App\Console\Commands\SyncMaquinadosDocs.
 *
 * @property int         $id
 * @property string      $nombre_archivo
 * @property string      $ruta_storage
 * @property string      $tipo               'dibujo' | 'ayuda'
 * @property string      $estado             'activo'  | 'inactivo'
 * @property string|null $ot
 * @property string|null $clase
 * @property string|null $proceso
 * @property string|null $fecha_archivo
 * @property \Carbon\Carbon|null $primera_deteccion_at
 * @property \Carbon\Carbon|null $ultima_deteccion_at
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 */
class CalidadMaquinadoDoc extends Model
{
    protected $table = 'calidad_maquinados_docs';

    protected $fillable = [
        'nombre_archivo',
        'ruta_storage',
        'tipo',
        'estado',
        'ot',
        'clase',
        'proceso',
        'fecha_archivo',
        'primera_deteccion_at',
        'ultima_deteccion_at',
    ];

    protected $casts = [
        'primera_deteccion_at' => 'datetime',
        'ultima_deteccion_at'  => 'datetime',
        'fecha_archivo'        => 'date',
    ];

    // =========================================================================
    // SCOPES
    // =========================================================================

    /** Solo registros activos */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    /** Solo registros inactivos */
    public function scopeInactivos(Builder $query): Builder
    {
        return $query->where('estado', 'inactivo');
    }

    /** Solo dibujos de maquinados */
    public function scopeDibujos(Builder $query): Builder
    {
        return $query->where('tipo', 'dibujo');
    }

    /** Solo ayudas visuales de maquinados */
    public function scopeAyudas(Builder $query): Builder
    {
        return $query->where('tipo', 'ayuda');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /** ¿El archivo físico de respaldo todavía existe en el storage? */
    public function existeEnStorage(): bool
    {
        return \Illuminate\Support\Facades\Storage::disk('local')->exists($this->ruta_storage);
    }
}
