<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de auditoría inmutable — registra cada edición hecha en un reporte.
 * NO tiene SoftDeletes ni updated_at por diseño (log inmutable).
 *
 * @property int         $id
 * @property int         $salida_moldura_id
 * @property int         $user_id
 * @property string      $accion
 * @property string|null $campo_editado
 * @property string|null $valor_anterior
 * @property string|null $valor_nuevo
 * @property string|null $ip
 * @property \Carbon\Carbon $created_at
 * @property SalidaMoldura $reporte
 * @property User          $usuario
 */
class SalidaMolduraLog extends Model
{
    // Sin updated_at — los registros de log son inmutables
    const UPDATED_AT = null;

    protected $table = 'salida_molduras_log';

    protected $fillable = [
        'salida_moldura_id',
        'user_id',
        'accion',
        'campo_editado',
        'valor_anterior',
        'valor_nuevo',
        'ip',
    ];

    // ─── Relaciones ────────────────────────────────────────────────

    /** Reporte al que pertenece este log */
    public function reporte()
    {
        return $this->belongsTo(SalidaMoldura::class, 'salida_moldura_id');
    }

    /** Usuario que realizó la edición */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ─── Helper estático ───────────────────────────────────────────

    /**
     * Registra una entrada de auditoría.
     *
     * @param int         $reporteId    ID del reporte editado
     * @param string      $accion       Descripción de la acción
     * @param string|null $campo        Nombre del campo modificado
     * @param mixed       $anterior     Valor anterior
     * @param mixed       $nuevo        Valor nuevo
     * @param string|null $ip           IP del cliente
     */
    public static function registrar(
        int    $reporteId,
        string $accion,
        ?string $campo     = null,
        mixed  $anterior   = null,
        mixed  $nuevo      = null,
        ?string $ip        = null
    ): void {
        static::create([
            'salida_moldura_id' => $reporteId,
            'user_id'           => auth()->id(),
            'accion'            => $accion,
            'campo_editado'     => $campo,
            'valor_anterior'    => $anterior !== null ? (string) $anterior : null,
            'valor_nuevo'       => $nuevo    !== null ? (string) $nuevo    : null,
            'ip'                => $ip,
        ]);
    }
}
