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
    public const ESCANEADOS               = 'ESCANEADOS';

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
    /**
     * Normaliza un nombre de clase para asegurar consistencia (ej: Molde -> 1 - MOLDES).
     */
    /**
     * Devuelve las palabras clave de búsqueda de clases ordenadas por longitud descendente
     * para evitar falsos positivos en substring (ej: 'candado obturador' antes de 'obturador').
     *
     * @return string[]
     */
    public static function getSearchableClasses(): array
    {
        $clasesConfig = config('global_classes.clases', []);
        $processClasses = config('global_classes.process_classes', []);
        $aliasesConfig = config('global_classes.searchable_aliases', []);
        
        $keywords = [];
        
        foreach ($aliasesConfig as $alias) {
            if (!empty($alias)) {
                $keywords[] = strtolower(trim($alias));
            }
        }
        
        foreach ($processClasses as $pc) {
            if (!empty($pc)) {
                $keywords[] = strtolower(trim($pc));
            }
        }
        
        foreach ($clasesConfig as $nombre) {
            $clean = strtolower(trim($nombre));
            if (!empty($clean)) {
                $keywords[] = $clean;
            }
        }
        
        $keywords = array_values(array_unique(array_filter($keywords)));
        
        // Ordenar por longitud descendente para emparejar frases más largas primero
        usort($keywords, fn($a, $b) => strlen($b) <=> strlen($a));
        
        return $keywords;
    }

    /**
     * Limpia sufijos de clase y reproceso de un nombre de OT (ej: OT_1234_molde_R1 -> OT_1234).
     */
    public static function cleanOtRevision(?string $ot): string
    {
        if (empty($ot)) {
            return '';
        }
        
        $searchable = self::getSearchableClasses();
        $escaped = array_map(function($c) {
            $q = preg_quote($c, '/');
            return preg_replace('/\s+/', '\s+', $q);
        }, $searchable);
        
        $classPattern = implode('|', $escaped);
        $pattern = '/_(?:(?:' . $classPattern . ')(?:_(?:' . $classPattern . '))*_)?R\d+$/iu';
        
        return preg_replace($pattern, '', $ot);
    }

    /**
     * Obtiene el prefijo de código para una clase/modelo (ej: B para Bombillo, TO para Templadera Obturador).
     */
    public static function getClassPrefix(?string $tipoModelo, ?string $claseNombre = null, bool $esTempladera = false): string
    {
        $prefixes = config('global_classes.class_prefixes', []);
        $tLow = strtolower(trim($tipoModelo ?? ''));
        $cLow = strtolower(trim($claseNombre ?? ''));
        $isTempladera = $esTempladera || str_contains($tLow, 'templadera') || str_contains($cLow, 'templadera');

        if ($isTempladera) {
            if (str_contains($tLow, 'obturador') || str_contains($cLow, 'obturador')) return $prefixes['templadera obturador'] ?? 'TO';
            if (str_contains($tLow, 'molde') || str_contains($cLow, 'molde')) return $prefixes['templadera molde'] ?? 'TM';
            if (str_contains($tLow, 'fondo') || str_contains($cLow, 'fondo')) return $prefixes['templadera fondo'] ?? 'TF';
            if (str_contains($tLow, 'bombillo') || str_contains($cLow, 'bombillo')) return $prefixes['templadera bombillo'] ?? 'TB';
            return $prefixes['templadera'] ?? 'T';
        }

        $keys = array_keys($prefixes);
        usort($keys, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($keys as $k) {
            if (str_starts_with($k, 'templadera')) continue;
            if (str_contains($tLow, $k) || str_contains($cLow, $k)) {
                return $prefixes[$k];
            }
        }

        $fallbackStr = !empty($claseNombre) ? $claseNombre : (!empty($tipoModelo) ? $tipoModelo : 'F');
        return strtoupper(substr(trim($fallbackStr), 0, 1));
    }



    /**
     * Obtiene el mapa estandarizado de clases desde la configuración
     */
    public static function getStandardMap(): array
    {
        $clases = config('global_classes.clases', []);
        $map = [];
        foreach ($clases as $id => $nombre) {
            if (empty(trim($nombre))) continue;
            $nombreVal = trim(strtoupper($nombre));
            $nombreOficial = "$id - $nombreVal";
            $map[$nombreVal] = $nombreOficial;
            $map[$nombreOficial] = $nombreOficial;
            
            // Variaciones comunes y plurales
            if ($nombreVal === 'MOLDE') {
                $map['MOLDES'] = $nombreOficial;
            }
            if ($nombreVal === 'FONDO') {
                $map['FONDOS'] = $nombreOficial;
            }
            if ($nombreVal === 'BOMBILLO') {
                $map['BOMBILLOS'] = $nombreOficial;
            }
            if ($nombreVal === 'OBTURADOR') {
                $map['OBTURADORES'] = $nombreOficial;
            }
            if (str_contains($nombreVal, 'GUÍA')) {
                $map['GUIAS'] = $nombreOficial;
                $map['GUÍAS'] = $nombreOficial;
                $map['GUIA'] = $nombreOficial;
                $map['GUÍA'] = $nombreOficial;
            }
            if (str_contains($nombreVal, 'PISTÓN')) {
                $map['PISTONES'] = $nombreOficial;
                $map['PISTON'] = $nombreOficial;
                $map['PISTÓN'] = $nombreOficial;
            }
            
            // Agregar versiones sin tildes para búsqueda flexible
            $sinTilde = str_replace(
                ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü'],
                ['A', 'E', 'I', 'O', 'U', 'U'],
                $nombreVal
            );
            if ($sinTilde !== $nombreVal) {
                $map[$sinTilde] = $nombreOficial;
            }
        }

        // Mapear aliases adicionales de global_classes a su entrada oficial
        $aliases = config('global_classes.searchable_aliases', []);
        foreach ($aliases as $alias) {
            $aUpper = trim(strtoupper($alias));
            if (!isset($map[$aUpper])) {
                foreach ($clases as $id => $nombre) {
                    $nUpper = trim(strtoupper($nombre));
                    if (str_contains($nUpper, $aUpper) || str_contains($aUpper, $nUpper)) {
                        $map[$aUpper] = "$id - $nUpper";
                        break;
                    }
                }
            }
        }

        return $map;
    }


    /**
     * Limpia un nombre de clase eliminando prefijos numéricos ("1 - "), "modelo", "casting",
     * acentos, espacios y sufijos de plural para comparación canónica independiente del formato.
     */
    public static function cleanClassName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }
        // Quitar prefijos "modelo" o "casting"
        $name = preg_replace('/^(?:modelo|casting)\s+/iu', '', trim($name));
        // Quitar prefijo numérico como "1 - ", "01 - ", "1-", "1 "
        $name = preg_replace('/^\d+\s*[-_.]?\s*/u', '', $name);
        // Convertir a minúsculas UTF-8
        $name = mb_strtolower($name, 'UTF-8');
        // Remover acentos
        $name = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'u', 'n'],
            $name
        );
        // Remover caracteres no alfanuméricos
        $name = preg_replace('/[^a-z0-9]/u', '', $name);
        // Singularizar términos comunes en molduras
        if (str_ends_with($name, 'es')) {
            $name = substr($name, 0, -2);
        } elseif (str_ends_with($name, 's')) {
            $name = substr($name, 0, -1);
        }
        return $name;
    }

    /**
     * Compara si dos nombres representan la misma clase (ej: "3 - BOMBILLO" vs "BOMBILLO" vs "bombillo").
     */
    public static function isSameClass(?string $a, ?string $b): bool
    {
        $cleanA = self::cleanClassName($a);
        $cleanB = self::cleanClassName($b);
        return $cleanA !== '' && $cleanA === $cleanB;
    }

    /**
     * Busca si ya existe una carpeta en disco para una clase dada dentro de una OT.
     * Si existen duplicados (ej: "BOMBILLO" y "3 - BOMBILLO"), consolida los archivos y unifica la carpeta.
     */
    public static function findExistingClassFolder(string $baseOtPath, string $clase): ?string
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        if (!$disk->exists($baseOtPath)) {
            return null;
        }

        $reserved = [
            'ayudas_visuales',
            'ayudas_visuales_fundicion',
            'dibujos_fundicion',
            'documentos_aprobados',
            'documentos_rechazados',
            'escaneados',
            'preordenes',
            'formatos_liberacion',
            'scar',
            'fdrdm',
            'fdldm',
            'dwg_fundicion',
            'extras',
        ];

        $subdirs = $disk->directories($baseOtPath);
        $matching = [];

        foreach ($subdirs as $dir) {
            $base = basename($dir);
            if (in_array(strtolower($base), $reserved)) {
                continue;
            }
            if (self::isSameClass($base, $clase)) {
                $matching[] = $base;
            }
        }

        if (empty($matching)) {
            return null;
        }

        if (count($matching) === 1) {
            return $matching[0];
        }

        // Si hay más de una carpeta para la misma clase (duplicados como BOMBILLO y 3 - BOMBILLO):
        $normOfficial = self::normalizeClass($clase);
        foreach ($matching as $mName) {
            if (strtoupper($mName) === strtoupper($normOfficial)) {
                return $mName;
            }
        }

        return $matching[0];
    }

    /**
     * Mueve recursivamente todos los archivos y subdirectorios de una carpeta secundaria a una primaria,
     * y elimina la carpeta secundaria vacía.
     */
    public static function mergeClassDirectories(string $primaryPath, string $secondaryPath): void
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        if (!$disk->exists($secondaryPath)) {
            return;
        }

        $allFiles = $disk->allFiles($secondaryPath);
        $secNorm = trim(str_replace('\\', '/', $secondaryPath), '/');
        $primNorm = trim(str_replace('\\', '/', $primaryPath), '/');

        foreach ($allFiles as $file) {
            $fNorm = trim(str_replace('\\', '/', $file), '/');
            if (str_starts_with(strtolower($fNorm), strtolower($secNorm) . '/')) {
                $rel = substr($fNorm, strlen($secNorm) + 1);
            } else {
                $rel = basename($fNorm);
            }
            $dest = $primNorm . '/' . $rel;
            $destDir = dirname($dest);
            if (!$disk->exists($destDir)) {
                $disk->makeDirectory($destDir);
            }
            if (!$disk->exists($dest)) {
                $disk->move($file, $dest);
            } else {
                $disk->delete($file);
            }
        }

        $disk->deleteDirectory($secondaryPath);
    }

    /**
     * Resuelve el nombre exacto de la carpeta de clase a usar:
     * Si ya existe una carpeta para la clase en la OT (en root propio o alterno), la respeta y reutiliza.
     * Si no existe, usa la versión normalizada oficial.
     */
    public static function resolveClassFolder(string $baseOtPath, string $clase, ?string $altOtPath = null): string
    {
        // 1. Buscar en root propio (respetar la estructura como se haya creado originalmente)
        $existing = self::findExistingClassFolder($baseOtPath, $clase);
        if ($existing !== null) {
            return $existing;
        }

        // 2. Buscar en root alterno si se proporciona (Calidad ↔ Almacén)
        if ($altOtPath !== null) {
            $existingAlt = self::findExistingClassFolder($altOtPath, $clase);
            if ($existingAlt !== null) {
                return $existingAlt;
            }
        }

        // 3. Si no existe aún en disco, usar el catálogo oficial
        return self::normalizeClass($clase);
    }

    /**
     * Devuelve todas las carpetas en disco para una OT que correspondan a una clase.
     * Si no existe ninguna en disco, devuelve los candidatos posibles para búsqueda.
     */
    public static function getClassFoldersForOt(string $baseOtPath, string $clase): array
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        if (!$disk->exists($baseOtPath)) {
            $norm = self::normalizeClass($clase);
            $cleanNoNum = strtoupper(trim(preg_replace('/^\d+\s*[-_.]?\s*/', '', $clase)));
            return array_values(array_unique(array_filter([$norm, $clase, $cleanNoNum])));
        }

        $reserved = [
            'ayudas_visuales',
            'ayudas_visuales_fundicion',
            'dibujos_fundicion',
            'documentos_aprobados',
            'documentos_rechazados',
            'escaneados',
            'preordenes',
            'formatos_liberacion',
            'scar',
            'fdrdm',
            'fdldm',
            'dwg_fundicion',
            'extras',
        ];

        $subdirs = $disk->directories($baseOtPath);
        $matching = [];

        foreach ($subdirs as $dir) {
            $base = basename($dir);
            if (!in_array(strtolower($base), $reserved) && self::isSameClass($base, $clase)) {
                $matching[] = $base;
            }
        }

        if (!empty($matching)) {
            return $matching;
        }

        $norm = self::normalizeClass($clase);
        $cleanNoNum = strtoupper(trim(preg_replace('/^\d+\s*[-_.]?\s*/', '', $clase)));
        return array_values(array_unique(array_filter([$norm, $clase, $cleanNoNum])));
    }

    public static function normalizeClass(string $clase): string
    {
        $claseClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
        if (empty($claseClean)) {
            return 'GENERAL';
        }

        $standardMap = self::getStandardMap();

        // Normalizar nombre sin numeros para mapearlo
        $claseNoNum = strtoupper(trim(preg_replace('/^\d+\s*-\s*/', '', $claseClean)));

        if (array_key_exists($claseNoNum, $standardMap)) {
            return $standardMap[$claseNoNum];
        }

        $claseClean = preg_replace('/^(\d+)([a-zA-Z]+)/', '$1 - $2', $claseClean);
        return strtoupper($claseClean);
    }

    public static function crearEstructuraClase(string $otFolder, string $clase, string $root): string
    {
        $otFolderUpper = strtoupper($otFolder);
        $baseOtPath = strtoupper($root) . '/' . $otFolderUpper;

        $altRoot = (strtoupper($root) === strtoupper(self::CALIDAD_ROOT))
            ? self::ALMACEN_ROOT
            : self::CALIDAD_ROOT;
        $altBaseOtPath = strtoupper($altRoot) . '/' . $otFolderUpper;

        // Respetar la carpeta existente si ya fue creada (ej: BOMBILLO, 3 - BOMBILLO, Molde, etc.)
        $claseClean = self::resolveClassFolder($baseOtPath, $clase, $altBaseOtPath);

        $basePath = $baseOtPath . '/' . $claseClean;

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

        return $claseClean;
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
        $candidates = [];

        $classVariants = [];
        if ($clase) {
            $classVariants = self::getClassFoldersForOt($base, $clase);
            $rawClean = strtoupper(trim(preg_replace('/^modelo\s+/i', '', strtolower($clase))));
            if (!empty($rawClean) && !in_array($rawClean, $classVariants)) {
                $classVariants[] = $rawClean;
            }
        } else {
            $classVariants = ['GENERAL'];
        }

        foreach ($classVariants as $claseClean) {
            switch ($tipo) {
                case 'dibujo':
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DIBUJOS;
                    $candidates[] = $base . '/' . $claseClean . '/Dibujo';
                    $candidates[] = $base . '/' . $claseClean . '/Dibujos_Fundicion';
                    $candidates[] = $base . '/' . $claseClean . '/Dibujos';
                    $candidates[] = $base . '/' . $claseClean;
                    break;
                case 'ayuda':
                    $candidates[] = $base . '/' . $claseClean . '/' . self::AYUDAS_VISUALES;
                    $candidates[] = $base . '/' . $claseClean . '/Ayuda_Visual';
                    $candidates[] = $base . '/' . $claseClean . '/Ayudas_Visuales_Fundicion';
                    $candidates[] = $base . '/' . $claseClean . '/Ayudas_Visuales';
                    $candidates[] = $base . '/' . self::LEGACY_AYUDAS . '/' . $claseClean;
                    break;
                case 'preorden_modelo':
                case 'preorden_casting':
                    $candidates[] = $base . '/' . $claseClean . '/' . self::PREORDENES;
                    $candidates[] = $base . '/' . $claseClean . '/Preordenes';
                    $candidates[] = $base . '/' . self::PREORDEN_MODELO;
                    $candidates[] = $base . '/' . self::PREORDEN_CASTING;
                    $candidates[] = $base . '/' . self::LEGACY_PREORDENES;
                    break;
                case 'fdldm':
                    $candidates[] = $base . '/' . $claseClean . '/' . self::FORMATOS_LIBERACION;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_APROBADOS;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_APROBADOS . '/' . self::ALMACEN;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_APROBADOS . '/' . self::CALIDAD;
                    $candidates[] = $base . '/' . $claseClean . '/Documentos_Aprobados/Calidad';
                    $candidates[] = $base . '/' . $claseClean . '/Documentos_Aprobados/Almacen';
                    $candidates[] = $base . '/' . self::FDLDM;
                    $candidates[] = $base . '/' . self::LEGACY_LIB_MODELO_APR;
                    $candidates[] = $base . '/' . self::LEGACY_DOC_APROBADOS;
                    break;
                case 'fdrdm':
                case 'scar':
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::EXTRAS;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::ALMACEN;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::CALIDAD;
                    $candidates[] = $base . '/' . $claseClean . '/' . self::DOCUMENTOS_RECHAZADOS . '/' . self::CALIDAD . '/' . self::EXTRAS;
                    $candidates[] = $base . '/' . $claseClean . '/Documentos_Rechazados/Calidad';
                    $candidates[] = $base . '/' . $claseClean . '/Documentos_Rechazados/Almacen';
                    $candidates[] = $base . '/' . self::FDRDM;
                    $candidates[] = $base . '/' . self::SCAR;
                    $candidates[] = $base . '/' . self::LEGACY_LIB_MODELO_REC;
                    $candidates[] = $base . '/' . self::LEGACY_DOC_RECHAZADOS;
                    $candidates[] = $base . '/' . self::LEGACY_SCAR;
                    break;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
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