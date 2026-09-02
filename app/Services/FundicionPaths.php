<?php

namespace App\Services;

/**
 * Servicio centralizado de rutas para el módulo de Fundición.
 *
 * Todas las rutas son relativas al disco 'local' de Storage (storage/app/).
 *
 * Estructura Nueva (Objetivo):
 * DOCUMENTACION_GIS/
 * └── ALMACEN_FUNDICION/ (o CALIDAD_FUNDICION/)
 *     └── OT [NUMERO] - [NOMBRE]/
 *         └── [NOMBRE_CLASE]/
 *             ├── DIBUJOS_FUNDICION/
 *             ├── AYUDAS_VISUALES_FUNDICION/
 *             ├── PREORDENES/
 *             ├── FORMATOS_LIBERACION/
 *             ├── DOCUMENTOS_APROBADOS/
 *             │   ├── ALMACEN/            # Documentos escaneados de almacén aprobados
 *             │   └── CALIDAD/            # Documentos escaneados de preórdenes aprobados
 *             └── DOCUMENTOS_RECHAZADOS/
 *                 ├── ALMACEN/            # Documentos escaneados de almacén rechazados
 *                 └── CALIDAD/            # Documentos de calidad rechazados
 *                     └── EXTRAS/
 *
 * Estructura Anterior (Legacy / Fallback):
 * DOCUMENTACION_GIS/
 * └── ALMACEN_FUNDICION/ (o CALIDAD_FUNDICION/)
 *     └── OT [NUMERO] - [NOMBRE]/
 *         ├── ayudas_visuales/
 *         │   └── preordenes/
 *         │       ├── documentos_aprobados/
 *         │       └── documentos_rechazados/
 *         ├── [NOMBRE_CLASE]/
 *         │   ├── Ayudas_Visuales/
 *         │   └── Dibujos/
 *         ├── Documentos_Aprobados/
 *         │   ├── [NOMBRE_CLASE]/
 *         │   ├── confirmacion_modelo/
 *         │   ├── FDLDM/
 *         │   └── Preorden_Casting/
 *         └── Documentos_Rechazados/
 *             ├── [NOMBRE_CLASE]/
 *             ├── FDRDM/
 *             └── SCAR/
 */
final class FundicionPaths
{
    // ─── Directorios raíz ────────────────────────────────────────────────────

    public const ALMACEN_ROOT = 'DOCUMENTACION_GIS/ALMACEN_FUNDICION';
    public const CALIDAD_ROOT  = 'DOCUMENTACION_GIS/CALIDAD_FUNDICION';

    // ─── Sub-rutas de tipo de documento ──────────────────────────────────────

    public const DIBUJOS                  = 'DIBUJOS_FUNDICION';
    public const DWG_FUNDICION            = 'DWG_FUNDICION';
    public const AYUDAS_VISUALES          = 'AYUDAS_VISUALES_FUNDICION';
    public const PREORDENES               = 'PREORDENES';
    public const FORMATOS_LIBERACION      = 'FORMATOS_LIBERACION';
    public const DOCUMENTOS_APROBADOS     = 'DOCUMENTOS_APROBADOS';
    public const DOCUMENTOS_RECHAZADOS    = 'DOCUMENTOS_RECHAZADOS';
    public const ALMACEN                  = 'ALMACEN';
    public const CALIDAD                  = 'CALIDAD';
    public const EXTRAS                   = 'EXTRAS';
    public const ESCANEADOS               = 'DOCUMENTOS_ESCANEADOS';

    // Rutas de compatibilidad legacy (solo para fallbacks)
    public const PREORDEN_MODELO   = 'DOCUMENTOS_APROBADOS/PREORDEN_MODELO';
    public const PREORDEN_CASTING  = 'DOCUMENTOS_APROBADOS/PREORDEN_CASTING';
    public const FDLDM             = 'DOCUMENTOS_APROBADOS/FDLDM';
    public const FDRDM             = 'DOCUMENTOS_RECHAZADOS/FDRDM';
    public const SCAR              = 'DOCUMENTOS_RECHAZADOS/SCAR';

    // Subfolder names only (for use when appending to an existing base path)
    public const FDRDM_SUBFOLDER   = 'FDRDM';
    public const SCAR_SUBFOLDER    = 'SCAR';

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
     * Inicializa la estructura completa de subcarpetas para una clase en un root específico.
     */
    public static function crearEstructuraClase(string $otFolder, string $clase, string $root): void
    {
        $otFolderUpper = strtoupper($otFolder);
        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
        if (empty($claseClean)) {
            $claseClean = 'GENERAL';
        }
        
        $basePath = strtoupper($root) . '/' . $otFolderUpper . '/' . $claseClean;
        
        $dirs = [
            $basePath . '/' . self::DIBUJOS,
            $basePath . '/' . self::DIBUJOS . '/' . self::DWG_FUNDICION,
            $basePath . '/' . self::AYUDAS_VISUALES,
            $basePath . '/' . self::PREORDENES,
            $basePath . '/' . self::FORMATOS_LIBERACION,
            $basePath . '/' . self::DOCUMENTOS_APROBADOS,
            $basePath . '/ESCANEADOS',
            $basePath . '/' . self::DOCUMENTOS_RECHAZADOS,
            $basePath . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::EXTRAS,
        ];
        
        foreach ($dirs as $dir) {
            if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($dir)) {
                \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($dir);
            }
        }
    }

    /**
     * Ruta base de la OT dentro del directorio Almacén.
     */
    public static function almacen(string $otFolder): string
    {
        return self::ALMACEN_ROOT . '/' . strtoupper($otFolder);
    }

    /**
     * Ruta base de la OT dentro del directorio Calidad.
     */
    public static function calidad(string $otFolder): string
    {
        return self::CALIDAD_ROOT . '/' . strtoupper($otFolder);
    }

    /**
     * Ruta a la subcarpeta Dibujos de una clase.
     */
    public static function dibujos(string $otFolder, string $clase, string $root = self::ALMACEN_ROOT): string
    {
        return strtoupper($root) . '/' . strtoupper($otFolder) . '/' . strtoupper($clase) . '/' . self::DIBUJOS;
    }

    /**
     * Ruta a la subcarpeta Ayudas_Visuales de una clase.
     */
    public static function ayudasVisuales(string $otFolder, string $clase, string $root = self::ALMACEN_ROOT): string
    {
        return strtoupper($root) . '/' . strtoupper($otFolder) . '/' . strtoupper($clase) . '/' . self::AYUDAS_VISUALES;
    }

    /**
     * Ruta a Documentos_Aprobados/Preorden_Modelo (con o sin Escaneados).
     */
    public static function preordenModelo(string $otFolder, bool $escaneados = false, string $root = self::ALMACEN_ROOT): string
    {
        $base = strtoupper($root) . '/' . strtoupper($otFolder) . '/' . self::PREORDEN_MODELO;
        return $escaneados ? $base . '/Escaneados' : $base;
    }

    /**
     * Ruta a Documentos_Aprobados/Preorden_Casting (con o sin Escaneados).
     */
    public static function preordenCasting(string $otFolder, bool $escaneados = false, string $root = self::ALMACEN_ROOT): string
    {
        $base = strtoupper($root) . '/' . strtoupper($otFolder) . '/' . self::PREORDEN_CASTING;
        return $escaneados ? $base . '/Escaneados' : $base;
    }

    /**
     * Ruta a Documentos_Aprobados/FDLDM (Formato de Liberación de Modelo, con o sin Escaneados).
     */
    public static function fdldm(string $otFolder, bool $escaneados = false, string $root = self::CALIDAD_ROOT): string
    {
        $base = strtoupper($root) . '/' . strtoupper($otFolder) . '/' . self::FDLDM;
        return $escaneados ? $base . '/Escaneados' : $base;
    }

    /**
     * Ruta a Documentos_Rechazados/FDRDM (Formato de Rechazo de Modelo, con o sin Escaneados).
     */
    public static function fdrdm(string $otFolder, bool $escaneados = false, string $root = self::CALIDAD_ROOT): string
    {
        $base = strtoupper($root) . '/' . strtoupper($otFolder) . '/' . self::FDRDM;
        return $escaneados ? $base . '/Escaneados' : $base;
    }

    /**
     * Ruta a Documentos_Rechazados/SCAR (con o sin Escaneados).
     */
    public static function scar(string $otFolder, bool $escaneados = false, string $root = self::CALIDAD_ROOT): string
    {
        $base = strtoupper($root) . '/' . strtoupper($otFolder) . '/' . self::SCAR;
        return $escaneados ? $base . '/Escaneados' : $base;
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
        $base = strtoupper($root . '/' . $otFolder);
        $claseClean = $clase ? strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase)))) : 'GENERAL';
        if (empty($claseClean)) {
            $claseClean = 'GENERAL';
        }

        return match ($tipo) {
            'dibujo' => array_filter([
                $claseClean ? $base . '/' . $claseClean . '/' . self::DIBUJOS : null,  // Nueva: {CLASE}/DIBUJOS_FUNDICION
                $claseClean ? $base . '/' . $claseClean . '/Dibujo' : null,
                $claseClean ? $base . '/' . $claseClean . '/Dibujos_Fundicion' : null,
                $claseClean ? $base . '/' . $claseClean . '/Dibujos' : null,
                $claseClean ? $base . '/' . $claseClean : null,
            ]),
            'ayuda' => array_filter([
                $claseClean ? $base . '/' . $claseClean . '/' . self::AYUDAS_VISUALES : null,  // Nueva: {CLASE}/AYUDAS_VISUALES_FUNDICION
                $claseClean ? $base . '/' . $claseClean . '/Ayuda_Visual' : null,
                $claseClean ? $base . '/' . $claseClean . '/Ayudas_Visuales_Fundicion' : null,
                $claseClean ? $base . '/' . $claseClean . '/Ayudas_Visuales' : null,
                $claseClean ? $base . '/' . self::LEGACY_AYUDAS . '/' . $claseClean : null,
            ]),
            'preorden_modelo', 'preorden_casting' => [
                $base . '/' . $claseClean . '/' . self::PREORDENES,
                $base . '/' . $claseClean . '/Preordenes',
                $base . '/' . self::PREORDEN_MODELO,
                $base . '/' . self::PREORDEN_CASTING,
                $base . '/' . self::LEGACY_PREORDENES,
            ],
            'fdldm' => [
                $base . '/' . $claseClean . '/' . self::FORMATOS_LIBERACION,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_APROBADOS,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_APROBADOS . '/' . self::ALMACEN,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_APROBADOS . '/' . self::CALIDAD,
                $base . '/' . $claseClean . '/Documentos_Aprobados/Calidad',
                $base . '/' . $claseClean . '/Documentos_Aprobados/Almacen',
                $base . '/' . self::FDLDM,
                $base . '/' . self::LEGACY_LIB_MODELO_APR,
                $base . '/' . self::LEGACY_DOC_APROBADOS,
            ],
            'fdrdm', 'scar' => [
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::EXTRAS,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::ALMACEN,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::CALIDAD,
                $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::CALIDAD . '/' . self::EXTRAS,
                $base . '/' . $claseClean . '/Documentos_Rechazados/Calidad',
                $base . '/' . $claseClean . '/Documentos_Rechazados/Almacen',
                $base . '/' . self::FDRDM,
                $base . '/' . self::SCAR,
                $base . '/' . self::LEGACY_LIB_MODELO_REC,
                $base . '/' . self::LEGACY_DOC_RECHAZADOS,
                $base . '/' . self::LEGACY_SCAR,
            ],
            default => [],
        };
    }
    /**
     * Devuelve las posibles rutas relativas para un adjunto (legacy y nuevo).
     */
    public static function getAttachmentPaths(string $relFolder, string $archivoSanitized): array
    {
        $posPaths = [
            self::ALMACEN_ROOT . '/' . $relFolder . '/' . $archivoSanitized,
            self::CALIDAD_ROOT . '/' . $relFolder . '/' . $archivoSanitized,
            self::ALMACEN_ROOT . '/' . $relFolder . '/ayudas_visuales/' . $archivoSanitized,
            self::CALIDAD_ROOT . '/' . $relFolder . '/ayudas_visuales/' . $archivoSanitized,
        ];
        
        if (str_starts_with($archivoSanitized, 'preordenes/')) {
            $subPath = str_replace('preordenes/', '', $archivoSanitized);
            $posPaths[] = self::ALMACEN_ROOT . '/' . $relFolder . '/ayudas_visuales/preordenes/' . $subPath;
            $posPaths[] = self::CALIDAD_ROOT . '/' . $relFolder . '/ayudas_visuales/preordenes/' . $subPath;
        }
        
        return $posPaths;
    }

}