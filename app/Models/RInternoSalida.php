<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property string      $ot_id
 * @property string      $clase
 * @property string      $nombre_moldura
 * @property int         $inspector_id
 * @property string|null $cliente
 * @property int         $cantidad_pedido
 * @property int         $cantidad_consignacion
 * @property string|null $fecha_inicio
 * @property string|null $observaciones
 * @property string      $formato        'moldes' | 'bombillos'
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property User|null $inspector
 * @property \Illuminate\Database\Eloquent\Collection|RInternoSalidaPieza[] $piezas
 * @property \Illuminate\Database\Eloquent\Collection|RInternoSalidaLog[]   $logs
 * @property \Illuminate\Database\Eloquent\Collection|RInternoSalidaPdf[]   $pdfs
 */
class RInternoSalida extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'r_interno_salidas';

    protected $fillable = [
        'ot_id',
        'clase',
        'nombre_moldura',
        'inspector_id',
        'cliente',
        'cantidad_pedido',
        'cantidad_consignacion',
        'fecha_inicio',
        'observaciones',
        'formato',

        'pdf_generado_count',
    ];

    protected $casts = [
        'fecha_inicio'          => 'date',
        'cantidad_pedido'       => 'integer',
        'cantidad_consignacion' => 'integer',
    ];

    // ─── Relaciones ────────────────────────────────────────────────

    /** Inspector (usuario Calidad o Admin) que llena el formato */
    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /** Piezas registradas en este reporte */
    public function piezas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RInternoSalidaPieza::class, 'r_interno_salida_id')
                    ->orderBy('numero_pieza');
    }

    /** Historial de auditoría de ediciones de este reporte */
    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RInternoSalidaLog::class, 'r_interno_salida_id')
                    ->orderBy('created_at', 'desc');
    }

    /** Historial de PDFs generados */
    public function pdfs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RInternoSalidaPdf::class, 'r_interno_salida_id')
                    ->orderBy('version', 'desc');
    }

    // ─── Scopes ────────────────────────────────────────────────────

    /** Filtrar reportes por OT */
    public function scopeParaOt(\Illuminate\Database\Eloquent\Builder $query, string $otId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('ot_id', '=', $otId, 'and');
    }

    /** Filtrar reportes por formato */
    public function scopeFormato(\Illuminate\Database\Eloquent\Builder $query, string $formato): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('formato', '=', $formato, 'and');
    }

    // ─── Helpers ───────────────────────────────────────────────────

    /**
     * Calcula el total de piezas disponibles en el reporte.
     * Si hay consignación (cantidad_consignacion > 0), dicha cantidad representa el total final.
     * De lo contrario, se utiliza la cantidad del pedido original.
     */
    public function getTotalPiezasAttribute(): int
    {
        return $this->cantidad_consignacion > 0
            ? $this->cantidad_consignacion
            : $this->cantidad_pedido;
    }

    /**
     * Detecta el formato automáticamente según la clase de la OT.
     * - Contiene 'MOLDE' o '1 - MOLDES'       → 'moldes'
     * - Contiene 'BOMBILLO' o '3 - BOMBILLOS' → 'bombillos'
     */
    public static function detectarFormato(string $clase): string
    {
        $claseUpper = strtoupper($clase);
        if (str_contains($claseUpper, 'BOMBILLO') || str_contains($claseUpper, 'BOMBILLOS')) {
            return 'bombillos';
        }
        if (str_contains($claseUpper, 'MOLDE') || str_contains($claseUpper, 'MOLDES') ||
            str_contains($claseUpper, 'FONDO') || str_contains($claseUpper, 'OBTURADOR') || str_contains($claseUpper, 'EMBUDO')) {
            return 'moldes';
        }
        return 'moldes'; // Por defecto
    }
}
