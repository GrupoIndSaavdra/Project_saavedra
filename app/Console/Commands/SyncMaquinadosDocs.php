<?php

namespace App\Console\Commands;

use App\Models\CalidadMaquinadoDoc;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * SyncMaquinadosDocs
 *
 * Escanea las carpetas de origen en la red/NAS o storage local:
 *   - DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS   → tipo: 'dibujo'
 *   - DOCUMENTACION_GIS/AYUDAS_MAQUINADOS    → tipo: 'ayuda'
 *   - DIBUJOS_GIS (directorio legado)        → tipo: 'dibujo'
 *   - AYUDAS_GIS (directorio legado)         → tipo: 'ayuda'
 *
 * Y mantiene sincronizado el directorio de respaldo de Calidad:
 *   - CALIDAD_MAQUINADOS/dibujos/<ot>/<clase>/<archivo>
 *   - CALIDAD_MAQUINADOS/ayudas/<clase_o_proceso>/<archivo>
 *
 * Reglas de optimización:
 *   - Copia por streams para no saturar memoria RAM en archivos pesados.
 *   - Omite re-copiar archivos idénticos (mismo tamaño y mtime).
 *   - Soporta sincronización puntual inmediata vía syncSingleFile().
 *   - Inactivación segura sin falsos positivos por barras o mayúsculas/minúsculas.
 */
class SyncMaquinadosDocs extends Command
{
    protected $signature   = 'calidad:sync-maquinados {--dry-run : Solo muestra los cambios sin aplicarlos}';
    protected $description = 'Sincroniza Dibujos y Ayudas Visuales de Maquinados con el storage de respaldo de Calidad.';

    /**
     * Mapeo de directorios de origen soportados (actuales y legados)
     */
    public const SOURCES = [
        'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS' => 'dibujo',
        'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS'  => 'ayuda',
        'DIBUJOS_GIS'                          => 'dibujo',
        'AYUDAS_GIS'                           => 'ayuda',
    ];

    /** Directorio raíz de respaldo dentro de storage/app */
    public const BACKUP_ROOT = 'CALIDAD_MAQUINADOS';

    /** Extensiones de archivo permitidas */
    public const ALLOWED_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'dwg', 'dxf'];

    // =========================================================================

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now    = Carbon::now();

        $this->info('══════════════════════════════════════════════════');
        $this->info('  SYNC CALIDAD — MAQUINADOS  ' . $now->toDateTimeString());
        $this->info('  Modo: ' . ($dryRun ? 'DRY-RUN (sin cambios)' : 'PRODUCCIÓN'));
        $this->info('══════════════════════════════════════════════════');

        $totalNuevos       = 0;
        $totalActualizados = 0;
        $totalInactivados  = 0;

        // ── 1. Recopilar TODOS los archivos presentes en los orígenes ────────
        /** @var array<string, string> $rutasOrigenMap [ruta_normalizada_lower => ruta_relativa] */
        $rutasOrigenMap = [];

        foreach (self::SOURCES as $sourceDir => $tipo) {
            if (!Storage::disk('local')->exists($sourceDir)) {
                continue;
            }

            $archivos = Storage::disk('local')->allFiles($sourceDir);

            foreach ($archivos as $rutaRelativa) {
                $ext = strtolower(pathinfo($rutaRelativa, PATHINFO_EXTENSION));
                if (!in_array($ext, self::ALLOWED_EXT, true)) {
                    continue;
                }

                $normPath = strtolower(str_replace('\\', '/', $rutaRelativa));
                $rutasOrigenMap[$normPath] = $rutaRelativa;

                // ── Extraer metadata ──────
                $meta = self::extraerMetadataStatic($rutaRelativa, $sourceDir, $tipo);

                // ── Calcular ruta de respaldo ──
                $rutaBackup = self::buildBackupPathStatic($rutaRelativa, $sourceDir, $tipo);

                // ── Upsert en BD ──────────
                /** @var CalidadMaquinadoDoc|null $registro */
                $registro = CalidadMaquinadoDoc::query()->where('ruta_storage', $rutaBackup)->first();

                if (!$registro) {
                    // Archivo nuevo: copiar y registrar
                    if (!$dryRun) {
                        self::copiarArchivoStatic($rutaRelativa, $rutaBackup);

                        CalidadMaquinadoDoc::create([
                            'nombre_archivo'       => basename($rutaRelativa),
                            'ruta_storage'         => $rutaBackup,
                            'tipo'                 => $tipo,
                            'estado'               => 'activo',
                            'ot'                   => $meta['ot'],
                            'clase'                => $meta['clase'],
                            'proceso'              => $meta['proceso'],
                            'fecha_archivo'        => $meta['fecha_archivo'],
                            'primera_deteccion_at' => $now,
                            'ultima_deteccion_at'  => $now,
                        ]);
                    }
                    $this->line("  [NUEVO] {$rutaBackup}");
                    $totalNuevos++;
                } else {
                    // Archivo existente: asegurar copia y re-activar si estaba inactivo
                    if (!$dryRun) {
                        self::copiarArchivoStatic($rutaRelativa, $rutaBackup);

                        $registro->update([
                            'estado'              => 'activo',
                            'ultima_deteccion_at' => $now,
                            'ot'                  => $meta['ot']     ?? $registro->ot,
                            'clase'               => $meta['clase']  ?? $registro->clase,
                            'proceso'             => $meta['proceso'] ?? $registro->proceso,
                            'fecha_archivo'       => $meta['fecha_archivo'] ?? $registro->fecha_archivo,
                        ]);
                    }
                    $totalActualizados++;
                }
            }
        }

        // ── 2. Inactivar registros que ya no están en ningún origen ───────────
        $registrosActivos = CalidadMaquinadoDoc::query()->where('estado', 'activo')->get();

        foreach ($registrosActivos as $registro) {
            $rutasCandidatas = self::backupToSourceCandidates($registro->ruta_storage, $registro->tipo);
            $existeEnOrigen = false;

            foreach ($rutasCandidatas as $candidata) {
                $candidataNorm = strtolower(str_replace('\\', '/', $candidata));
                if (isset($rutasOrigenMap[$candidataNorm]) || Storage::disk('local')->exists($candidata)) {
                    $existeEnOrigen = true;
                    break;
                }
            }

            // Si el archivo físico ya no existe en el origen, marcar inactivo
            if (!$existeEnOrigen) {
                if (!$dryRun) {
                    $registro->update(['estado' => 'inactivo']);
                }
                $this->warn("  [INACTIVO] {$registro->ruta_storage}");
                $totalInactivados++;
            }
        }

        // ── 3. Resumen ────────────────────────────────────────────────────────
        $this->newLine();
        $this->info("  Nuevos       : {$totalNuevos}");
        $this->info("  Actualizados : {$totalActualizados}");
        $this->warn("  Inactivados  : {$totalInactivados}");
        $this->info('══════════════════════════════════════════════════');

        return self::SUCCESS;
    }

    // =========================================================================
    // MÉTODOS PÚBLICOS ESTÁTICOS (SINCRONIZACIÓN PUNTUAL INMEDIATA)
    // =========================================================================

    /**
     * Sincroniza un único archivo de forma instantánea al subirlo o eliminarlo.
     * Llamado directamente desde DibujosPdfController, AyudasVisualesPdfController o SyncController.
     */
    public static function syncSingleFile(string $sourcePath, string $tipo, string $action = 'upsert'): bool
    {
        try {
            $normPath = str_replace('\\', '/', trim($sourcePath, '/'));
            $ext = strtolower(pathinfo($normPath, PATHINFO_EXTENSION));

            if (!in_array($ext, self::ALLOWED_EXT, true)) {
                return false;
            }

            // Identificar el directorio base correspondiente
            $matchedSourceDir = null;
            foreach (self::SOURCES as $sourceDir => $srcTipo) {
                if ($srcTipo === $tipo && str_starts_with($normPath, $sourceDir)) {
                    $matchedSourceDir = $sourceDir;
                    break;
                }
            }

            if (!$matchedSourceDir) {
                // Fallback automático según el tipo
                $matchedSourceDir = $tipo === 'dibujo'
                    ? 'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS'
                    : 'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS';
            }

            $rutaBackup = self::buildBackupPathStatic($normPath, $matchedSourceDir, $tipo);
            $meta = self::extraerMetadataStatic($normPath, $matchedSourceDir, $tipo);
            $now = Carbon::now();

            if ($action === 'delete') {
                CalidadMaquinadoDoc::query()
                    ->where('ruta_storage', $rutaBackup)
                    ->update(['estado' => 'inactivo', 'ultima_deteccion_at' => $now]);
                return true;
            }

            // Upsert / Reemplazar
            self::copiarArchivoStatic($normPath, $rutaBackup);

            CalidadMaquinadoDoc::updateOrCreate(
                ['ruta_storage' => $rutaBackup],
                [
                    'nombre_archivo'       => basename($normPath),
                    'tipo'                 => $tipo,
                    'estado'               => 'activo',
                    'ot'                   => $meta['ot'],
                    'clase'                => $meta['clase'],
                    'proceso'              => $meta['proceso'],
                    'fecha_archivo'        => $meta['fecha_archivo'],
                    'ultima_deteccion_at'  => $now,
                ]
            );

            return true;
        } catch (Throwable $e) {
            Log::warning("Fallo en syncSingleFile ({$sourcePath}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extrae metadata desde la estructura de carpetas según el tipo de documento.
     *
     * Dibujos:
     *   <OT>/<Clase>/<Proceso>/archivo.pdf → ot, clase, proceso
     *   <OT>/<Clase>/archivo.pdf           → ot, clase, proceso=null
     *   <OT>/archivo.pdf                   → ot, clase='--', proceso=null
     *
     * Ayudas:
     *   <Proceso>/archivo.pdf              → ot=null, clase='General', proceso=<Proceso>
     *   <Clase>/<Proceso>/archivo.pdf      → ot=null, clase=<Clase>,   proceso=<Proceso>
     */
    public static function extraerMetadataStatic(string $rutaRelativa, string $sourceDir, string $tipo): array
    {
        $cleanSource = trim(str_replace('\\', '/', $sourceDir), '/');
        $cleanRuta   = trim(str_replace('\\', '/', $rutaRelativa), '/');

        if (str_starts_with($cleanRuta, $cleanSource)) {
            $relative = ltrim(substr($cleanRuta, strlen($cleanSource)), '/');
        } else {
            $relative = $cleanRuta;
        }

        $partes = explode('/', $relative);
        array_pop($partes); // Quitar el nombre del archivo

        if ($tipo === 'dibujo') {
            $count = count($partes);
            if ($count >= 3) {
                $ot      = $partes[0];
                $clase   = $partes[1];
                $proceso = $partes[2];
            } elseif ($count === 2) {
                $ot      = $partes[0];
                $clase   = $partes[1];
                $proceso = null;
            } elseif ($count === 1) {
                $ot      = $partes[0];
                $clase   = '--';
                $proceso = null;
            } else {
                $ot      = 'General';
                $clase   = '--';
                $proceso = null;
            }
        } else {
            // Ayudas Visuales
            $count = count($partes);
            $ot = null;
            if ($count >= 2) {
                $clase   = $partes[0];
                $proceso = $partes[1];
            } elseif ($count === 1) {
                $clase   = 'General';
                $proceso = $partes[0];
            } else {
                $clase   = 'General';
                $proceso = 'General';
            }
        }

        $fechaArchivo = null;
        try {
            $lastModified = Storage::disk('local')->lastModified($rutaRelativa);
            if ($lastModified) {
                $fechaArchivo = Carbon::createFromTimestamp($lastModified)->toDateString();
            }
        } catch (Throwable) {
        }

        return [
            'ot'            => $ot,
            'clase'         => $clase,
            'proceso'       => $proceso,
            'fecha_archivo' => $fechaArchivo,
        ];
    }

    /**
     * Construye la ruta de respaldo dentro de CALIDAD_MAQUINADOS.
     */
    public static function buildBackupPathStatic(string $rutaRelativa, string $sourceDir, string $tipo): string
    {
        $cleanSource = trim(str_replace('\\', '/', $sourceDir), '/');
        $cleanRuta   = trim(str_replace('\\', '/', $rutaRelativa), '/');

        if (str_starts_with($cleanRuta, $cleanSource)) {
            $relative = ltrim(substr($cleanRuta, strlen($cleanSource)), '/');
        } else {
            $relative = $cleanRuta;
        }

        $subdir = $tipo === 'dibujo' ? 'dibujos' : 'ayudas';
        return self::BACKUP_ROOT . '/' . $subdir . '/' . $relative;
    }

    /**
     * Devuelve las posibles rutas de origen estimadas para un backup dado
     * (considerando directorio principal y legado).
     */
    public static function backupToSourceCandidates(string $rutaBackup, string $tipo): array
    {
        $cleanBackup = str_replace('\\', '/', $rutaBackup);
        $subdir = $tipo === 'dibujo' ? 'dibujos' : 'ayudas';
        $prefix = self::BACKUP_ROOT . '/' . $subdir . '/';

        $relative = ltrim(str_replace($prefix, '', $cleanBackup), '/');

        if ($tipo === 'dibujo') {
            return [
                'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS/' . $relative,
                'DIBUJOS_GIS/' . $relative,
            ];
        }

        return [
            'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS/' . $relative,
            'AYUDAS_GIS/' . $relative,
        ];
    }

    /**
     * Copia eficiente usando streams y comprobación de integridad.
     * Evita sobrecargar la memoria RAM de PHP en archivos pesados.
     */
    public static function copiarArchivoStatic(string $origen, string $destino): void
    {
        try {
            $disk = Storage::disk('local');
            if (!$disk->exists($origen)) {
                return;
            }

            // Si el destino ya existe y es idéntico, omitir copia
            if ($disk->exists($destino)) {
                try {
                    $sizeOrig = $disk->size($origen);
                    $sizeDest = $disk->size($destino);
                    $mtimeOrig = $disk->lastModified($origen);
                    $mtimeDest = $disk->lastModified($destino);

                    if ($sizeOrig === $sizeDest && $mtimeOrig <= $mtimeDest) {
                        return;
                    }
                } catch (Throwable) {
                }
            }

            // Copia eficiente por streaming
            $stream = $disk->readStream($origen);
            if ($stream) {
                $disk->put($destino, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        } catch (Throwable $e) {
            Log::warning("No se pudo copiar {$origen} → {$destino}: " . $e->getMessage());
        }
    }
}
