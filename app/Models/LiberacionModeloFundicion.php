<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int              $id
 * @property string           $ot
 * @property string|null      $estado
 * @property string|null      $decision
 * @property string|null      $tipo_origen
 * @property string|null      $tipo_modelo
 * @property array|null       $medidas_modelo
 * @property array|null       $medidas_plantilla
 * @property array|null       $medidas_fondo
 * @property array|null       $medidas_obturador
 * @property string|null      $observaciones_modelo
 * @property string|null      $observaciones_plantilla
 * @property string|null      $observaciones_fondo
 * @property string|null      $observaciones_obturador
 * @property string|null      $motivo_rechazo
 * @property int|null         $user_id_calidad
 * @property string|null      $user_nombre_calidad
 * @property \Carbon\Carbon|null $fecha_revision
 * @property string|null      $pdf_filename
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class LiberacionModeloFundicion extends Model
{
    use HasFactory;

    protected $table = 'liberacion_modelos_fundicion';

    protected $fillable = [
        'ot',
        'estado',
        'decision',
        'tipo_origen',
        'tipo_modelo',
        // Medidas en JSON
        'medidas_modelo',
        'medidas_plantilla',
        'medidas_fondo',
        'medidas_obturador',
        // Textos
        'observaciones_modelo',
        'observaciones_plantilla',
        'observaciones_fondo',
        'observaciones_obturador',
        'motivo_rechazo',
        // Auditoria
        'user_id_calidad',
        'user_nombre_calidad',
        'fecha_revision',
        'pdf_filename',
        'alerta_enviada',
    ];

    protected $casts = [
        'fecha_revision'    => 'datetime',
        'medidas_modelo'    => 'array',
        'medidas_plantilla' => 'array',
        'medidas_fondo'     => 'array',
        'medidas_obturador' => 'array',
        'alerta_enviada'    => 'boolean',
    ];

    /**
     * Devuelve los items de la tabla Modelo con su etiqueta de descripcion.
     */
    public static function itemsModelo(): array
    {
        return [
            'A'  => 'Altura de la ceja',
            'A1' => 'Altura de sufridera',
            'B'  => 'Altura total',
            'C'  => 'Diam. de ceja',
            'D1' => 'Diam. de mordaza',
            'D2' => 'Laterales',
            'E2' => 'Radio de mordaza',
            'E1' => 'Radio de ceja',
            'G1' => 'Distancia de Vena',
            'G2' => 'Ensamble',
        ];
    }

    /**
     * Devuelve los items de la tabla Plantilla y Templadera.
     */
    public static function itemsPlantilla(): array
    {
        return ['V', 'W', 'X', 'Y', 'Z', 'x1', 'x2', 'y1', 'y2', 'y3', 'z1', 'z2'];
    }

    /**
     * Devuelve los items de la tabla Fondo con su etiqueta.
     * Usada tambien para la tabla Obturador (estructura identica).
     */
    public static function itemsFondo(): array
    {
        return [
            'mayor_diam'   => 'O MAYOR',
            'mayor_altura' => 'ALT. O MAYOR',
            'menor_diam'   => 'O MENOR',
            'menor_altura' => 'ALT. O MENOR',
        ];
    }

    /**
     * Alias de itemsFondo() para mayor legibilidad en el contexto del tipo Obturador.
     */
    public static function itemsObturador(): array
    {
        return self::itemsFondo();
    }

    /**
     * Devuelve las columnas de la matriz Plantilla/Templadera agrupadas por variable principal.
     */
    public static function matrixCols(): array
    {
        return [
            'V' => ['V'],
            'W' => ['W'],
            'X' => ['x1', 'x2'],
            'Y' => ['y1', 'y2', 'y3'],
            'Z' => ['z1', 'z2'],
        ];
    }

    /**
     * Tablas activas segun el tipo de modelo.
     */
    public static function tablasActivas(string $tipoModelo): array
    {
        return match ($tipoModelo) {
            'Fondo'     => ['fondo'],
            'Obturador' => ['obturador'],
            'Molde'     => ['modelo', 'plantilla'],
            'Bombillo'  => ['modelo', 'plantilla'],
            default     => [],
        };
    }

    /**
     * Relacion con el historial de Fundicion (por OT).
     */
    public function historial(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FundicionHistory::class, 'ot', 'ot');
    }
}
