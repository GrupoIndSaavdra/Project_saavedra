<?php

namespace App\Enums;

enum FundicionEstadoFlujo: string
{
    case NUEVO = 'recibido';
    case TIENE_MODELO = 'tiene_modelo';
    case PRE_ORDEN = 'pre_orden';
    case PROCESO_PARCIAL = 'proceso_parcial';
    case CORREO_ENVIADO = 'correo_enviado';
    case REVISANDO = 'revisando';
    case APROBADO = 'aprobado';
    case RECHAZADO = 'rechazado';
    case MIXTO = 'mixto';
    
    case EN_ALMACEN = 'en_almacen';
    case LIBERADO_ALMACEN = 'liberado_almacen';
    case RECHAZADO_ALMACEN = 'rechazado_almacen';
    case MIXTO_ALMACEN = 'mixto_almacen';
    
    case REPROCESO_RECHAZADO = 'reproceso_rechazado';
    case RECHAZOS_PROCESADOS_APROBADO = 'rechazos_procesados_aprobado';
    case RECHAZOS_PROCESADOS_RECHAZADO = 'rechazos_procesados_rechazado';
    
    case CASTING = 'casting';
    case CASTING_APROBADO = 'casting_aprobado';

    public function label(): string
    {
        return match($this) {
            self::NUEVO => 'Nuevo',
            self::TIENE_MODELO => 'Tengo Modelo',
            self::PRE_ORDEN => 'Pre-Orden',
            self::PROCESO_PARCIAL => 'Proceso Parcial',
            self::CORREO_ENVIADO => 'Correo Enviado',
            self::REVISANDO => 'En Revisión',
            self::APROBADO => 'Aprobado',
            self::RECHAZADO => 'Rechazado',
            self::MIXTO => 'Mixto',
            self::EN_ALMACEN => 'Dictamen en Almacén',
            self::LIBERADO_ALMACEN => 'Liberado por Almacén',
            self::RECHAZADO_ALMACEN => 'Rechazado por Almacén',
            self::MIXTO_ALMACEN => 'Mixto por Almacén',
            self::REPROCESO_RECHAZADO => 'Reproceso Rechazado',
            self::RECHAZOS_PROCESADOS_APROBADO => 'Reprocesos Aprobados',
            self::RECHAZOS_PROCESADOS_RECHAZADO => 'Reprocesos Rechazados',
            self::CASTING => 'Casting',
            self::CASTING_APROBADO => 'Enviado a Proveedor',
        };
    }
}
