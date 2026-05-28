<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
