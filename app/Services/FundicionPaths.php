<?php

namespace App\Services;

/**
 * Servicio centralizado de rutas para el módulo de Fundición.
 *
 * Todas las rutas son relativas al disco 'local' de Storage (storage/app/).
 *
 * Estructura objetivo:
 *   ALMACEN_FUNDICION / OT {N} - {Proyecto} /
 *       {Clase} / Dibujos /
 *       {Clase} / Ayudas_Visuales /
 *       Documentos_Aprobados / Preorden_Modelo / [Escaneados/]
 *       Documentos_Aprobados / Preorden_Casting / [Escaneados/]
 *       Documentos_Aprobados / FDLDM / [Escaneados/]
 *       Documentos_Rechazados / FDRDM / [Escaneados/]
 *       Documentos_Rechazados / SCAR / [Escaneados/]
 *
 * La misma estructura aplica para CALIDAD_FUNDICION.
 */
final class FundicionPaths
{
    // ─── Directorios raíz ────────────────────────────────────────────────────

    public const ALMACEN_ROOT = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';
    public const CALIDAD_ROOT  = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION';

    // ─── Sub-rutas de tipo de documento ──────────────────────────────────────

    public const DIBUJOS          = 'Dibujos';
    public const AYUDAS_VISUALES  = 'Ayudas_Visuales';

    public const PREORDEN_MODELO   = 'Documentos_Aprobados/Preorden_Modelo';
    public const PREORDEN_CASTING  = 'Documentos_Aprobados/Preorden_Casting';
    public const FDLDM             = 'Documentos_Aprobados/FDLDM';

    public const FDRDM             = 'Documentos_Rechazados/FDRDM';
    public const SCAR              = 'Documentos_Rechazados/SCAR';

    // Subfolder names only (for use when appending to an existing base path)
    public const FDRDM_SUBFOLDER   = 'FDRDM';
    public const SCAR_SUBFOLDER    = 'SCAR';

    public const ESCANEADOS        = 'Escaneados';

    // ─── Rutas de compatibilidad (anteriores) ────────────────────────────────
    // Se usan en serveFile para fallback de lectura de archivos ya existentes.

    public const LEGACY_AYUDAS          = 'ayudas_visuales';
    public const LEGACY_PREORDENES      = 'ayudas_visuales/preordenes';
    public const LEGACY_DOC_APROBADOS   = 'ayudas_visuales/preordenes/documentos_aprobados';
    public const LEGACY_DOC_RECHAZADOS  = 'ayudas_visuales/preordenes/documentos_rechazados';
    public const LEGACY_LIB_MODELO_APR  = 'Documentos_Aprobados/Liberacion_Modelo';
    public const LEGACY_LIB_MODELO_REC  = 'Documentos_Rechazados/Liberacion_Modelo';
    public const LEGACY_SCAR            = 'Documentos_Rechazados/Scar';

    // ─── Helpers de construcción de rutas ────────────────────────────────────

    /**
     * Ruta base de la OT dentro del directorio Almacén.
     */
    public static function almacen(string $otFolder): string
    {
        return self::ALMACEN_ROOT . '/' . $otFolder;
    }

    /**
     * Ruta base de la OT dentro del directorio Calidad.
     */
    public static function calidad(string $otFolder): string
    {
        return self::CALIDAD_ROOT . '/' . $otFolder;
    }

    /**
     * Ruta a la subcarpeta Dibujos de una clase.
     */
    public static function dibujos(string $otFolder, string $clase, string $root = self::ALMACEN_ROOT): string
    {
        return $root . '/' . $otFolder . '/' . $clase . '/' . self::DIBUJOS;
    }

    /**
     * Ruta a la subcarpeta Ayudas_Visuales de una clase.
     */
    public static function ayudasVisuales(string $otFolder, string $clase, string $root = self::ALMACEN_ROOT): string
    {
        return $root . '/' . $otFolder . '/' . $clase . '/' . self::AYUDAS_VISUALES;
    }

    /**
     * Ruta a Documentos_Aprobados/Preorden_Modelo (con o sin Escaneados).
     */
    public static function preordenModelo(string $otFolder, bool $escaneados = false, string $root = self::ALMACEN_ROOT): string
    {
        $base = $root . '/' . $otFolder . '/' . self::PREORDEN_MODELO;
        return $escaneados ? $base . '/' . self::ESCANEADOS : $base;
    }

    /**
     * Ruta a Documentos_Aprobados/Preorden_Casting (con o sin Escaneados).
     */
    public static function preordenCasting(string $otFolder, bool $escaneados = false, string $root = self::ALMACEN_ROOT): string
    {
        $base = $root . '/' . $otFolder . '/' . self::PREORDEN_CASTING;
        return $escaneados ? $base . '/' . self::ESCANEADOS : $base;
    }

    /**
     * Ruta a Documentos_Aprobados/FDLDM (Formato de Liberación de Modelo, con o sin Escaneados).
     */
    public static function fdldm(string $otFolder, bool $escaneados = false, string $root = self::CALIDAD_ROOT): string
    {
        $base = $root . '/' . $otFolder . '/' . self::FDLDM;
        return $escaneados ? $base . '/' . self::ESCANEADOS : $base;
    }

    /**
     * Ruta a Documentos_Rechazados/FDRDM (Formato de Rechazo de Modelo, con o sin Escaneados).
     */
    public static function fdrdm(string $otFolder, bool $escaneados = false, string $root = self::CALIDAD_ROOT): string
    {
        $base = $root . '/' . $otFolder . '/' . self::FDRDM;
        return $escaneados ? $base . '/' . self::ESCANEADOS : $base;
    }

    /**
     * Ruta a Documentos_Rechazados/SCAR (con o sin Escaneados).
     */
    public static function scar(string $otFolder, bool $escaneados = false, string $root = self::CALIDAD_ROOT): string
    {
        $base = $root . '/' . $otFolder . '/' . self::SCAR;
        return $escaneados ? $base . '/' . self::ESCANEADOS : $base;
    }

    /**
     * Devuelve todas las rutas candidatas (nueva + legadas) para buscar un archivo
     * de un tipo dado dentro de la OT. Útil para los métodos serveFile y getFiles.
     *
     * @param string $root    self::ALMACEN_ROOT o self::CALIDAD_ROOT
     * @param string $otFolder
     * @param string $tipo    'dibujo'|'ayuda'|'preorden_modelo'|'preorden_casting'|'fdldm'|'fdrdm'|'scar'
     * @param string|null $clase  (requerido para 'dibujo' y 'ayuda')
     * @return string[]
     */
    public static function candidatos(string $root, string $otFolder, string $tipo, ?string $clase = null): array
    {
        $base = $root . '/' . $otFolder;

        return match ($tipo) {
            'dibujo' => array_filter([
                $clase ? $base . '/' . $clase . '/' . self::DIBUJOS : null,  // Nueva
                $clase ? $base . '/' . $clase : null,                         // Legacy (raíz de clase)
            ]),
            'ayuda' => array_filter([
                $clase ? $base . '/' . $clase . '/' . self::AYUDAS_VISUALES : null,  // Nueva
                $clase ? $base . '/' . self::LEGACY_AYUDAS . '/' . $clase : null,    // Legacy
            ]),
            'preorden_modelo' => [
                $base . '/' . self::PREORDEN_MODELO,
                $base . '/' . self::LEGACY_PREORDENES,
            ],
            'preorden_casting' => [
                $base . '/' . self::PREORDEN_CASTING,
            ],
            'fdldm' => [
                $base . '/' . self::FDLDM,
                $base . '/' . self::LEGACY_LIB_MODELO_APR,
                $base . '/' . self::LEGACY_DOC_APROBADOS,
            ],
            'fdrdm' => [
                $base . '/' . self::FDRDM,
                $base . '/' . self::LEGACY_LIB_MODELO_REC,
                $base . '/' . self::LEGACY_DOC_RECHAZADOS,
            ],
            'scar' => [
                $base . '/' . self::SCAR,
                $base . '/' . self::LEGACY_SCAR,
            ],
            default => [],
        };
    }
}
