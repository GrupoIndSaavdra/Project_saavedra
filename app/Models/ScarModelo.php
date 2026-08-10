<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int              $id
 * @property string           $ot
 * @property string|null      $no_scar
 * @property string|null      $tipo_modelo
 * @property string|null      $codigo_modelo
 * @property string|null      $proveedor
 * @property string|null      $descripcion_no_conformidad
 * @property string|null      $causa_raiz
 * @property string|null      $acciones_correctivas
 * @property \Carbon\Carbon|null $fecha_emision
 * @property \Carbon\Carbon|null $fecha_compromiso
 * @property string|null      $estatus
 * @property int|null         $user_id
 * @property string|null      $user_nombre
 * @property string|null      $pdf_filename
 * @property string|null      $cliente_empresa
 * @property string|null      $area_solicitante
 * @property string|null      $nombre_solicitante
 * @property string|null      $nombre_moldura
 * @property bool             $evidencia_reporte
 * @property bool             $evidencia_dibujos
 * @property bool             $evidencia_ayudas
 * @property bool             $evidencia_fotos
 * @property bool             $evidencia_otro
 * @property bool             $accion_regreso
 * @property bool             $accion_fabricacion
 * @property bool             $accion_otro
 * @property string|null      $accion_otro_texto
 * @property string|null      $pdf_firmado_filename
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class ScarModelo extends Model
{
    use HasFactory;

    protected $table = 'scar_modelos';

    protected $fillable = [
        'ot',
        'no_scar',
        'tipo_modelo',
        'codigo_modelo',
        'proveedor',
        'descripcion_no_conformidad',
        'causa_raiz',
        'acciones_correctivas',
        'fecha_emision',
        'fecha_compromiso',
        'estatus',
        'user_id',
        'user_nombre',
        'pdf_filename',
        'cliente_empresa',
        'area_solicitante',
        'nombre_solicitante',
        'nombre_moldura',
        'evidencia_reporte',
        'evidencia_dibujos',
        'evidencia_ayudas',
        'evidencia_fotos',
        'evidencia_otro',
        'accion_regreso',
        'accion_fabricacion',
        'accion_otro',
        'accion_otro_texto',
        'pdf_firmado_filename',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_compromiso' => 'date',
        'evidencia_reporte' => 'boolean',
        'evidencia_dibujos' => 'boolean',
        'evidencia_ayudas' => 'boolean',
        'evidencia_fotos' => 'boolean',
        'evidencia_otro' => 'boolean',
        'accion_regreso' => 'boolean',
        'accion_fabricacion' => 'boolean',
        'accion_otro' => 'boolean',
    ];
}
