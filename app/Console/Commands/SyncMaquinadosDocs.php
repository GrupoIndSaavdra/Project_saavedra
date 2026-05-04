<?php

namespace App\Console\Commands;

use App\Models\CalidadMaquinadoDoc;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * SyncMaquinadosDocs
 *
 * Escanea las carpetas de origen en la red/NAS:
 *   - DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS   → tipo: 'dibujo'
 *   - DOCUMENTACION_GIS/AYUDAS_MAQUINADOS    → tipo: 'ayuda'
 *
 * Y copia cada archivo encontrado hacia el directorio de respaldo del
 * storage de Laravel:
 *   - CALIDAD_MAQUINADOS/dibujos/<ot>/<clase>/<archivo>
 *   - CALIDAD_MAQUINADOS/ayudas/<ot>/<clase>/<archivo>
 *
 * Regla de inactivación (NO elimina archivos físicos):
 *   Si un registro existe en BD pero el archivo ya no está en la
 *   carpeta de ORIGEN, el registro se marca como 'inactivo'.
 *   El archivo copiado en el storage de respaldo se CONSERVA.
 *
 * Extracción de metadata (OT, Clase, Proceso, Fecha):
 *   Se infiere desde la estructura de carpetas:
 *     DIBUJOS_MAQUINADOS/<OT>/<Clase>/<Proceso>/archivo.pdf
 *     AYUDAS_MAQUINADOS/<OT>/<Clase>/<Proceso>/archivo.pdf
 *   Si la carpeta tiene menos niveles, los niveles superiores disponibles
 *   se asignan como OT → Clase → Proceso (los restantes quedan null).
 */
class SyncMaquinadosDocs extends Command
{
    protected $signature   = 'calidad:sync-maquinados {--dry-run : Solo muestra los cambios sin aplicarlos}';
    protected $description  = 'Sincroniza Dibujos y Ayudas Visuales de Maquinados con el storage de respaldo de Calidad.';

    /**
     * Mapeo: [directorio de origen en disco local] => tipo de documento
     *
     * AJUSTA estas rutas si los directorios de origen están en un disco
     * compartido de red montado; en ese caso usa Storage::disk('red') o
     * simplemente cambia el disk en getSourceFiles().
     */
    private const SOURCES = [
        'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS' => 'dibujo',
        'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS'  => 'ayuda',
    ];

    /** Directorio raíz de respaldo dentro de storage/app */
    private const BACKUP_ROOT = 'CALIDAD_MAQUINADOS';

    /** Extensiones de archivo permitidas */
    private const ALLOWED_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'dwg', 'dxf'];

    // =========================================================================

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $now    = Carbon::now();

        $this->info('══════════════════════════════════════════════════');
        $this->info('  SYNC CALIDAD — MAQUINADOS  ' . $now->toDateTimeString());
        $this->info('  Modo: ' . ($dryRun ? 'DRY-RUN (sin cambios)' : 'PRODUCCIÓN'));
        $this->info('══════════════════════════════════════════════════');

        $totalNuevos    = 0;
        $totalActualizados = 0;
        $totalInactivados  = 0;

        // ── 1. Recopilar TODOS los archivos presentes en el origen ────────────
        /** @var array<string> $rutasOrigen   Lista de rutas relativas en el disco 'local' */
        $rutasOrigen = [];

        foreach (self::SOURCES as $sourceDir => $tipo) {
            if (!Storage::disk('local')->exists($sourceDir)) {
                $this->warn("  [AVISO] Directorio de origen no encontrado: {$sourceDir}");
                continue;
            }

            $archivos = Storage::disk('local')->allFiles($sourceDir);

            foreach ($archivos as $rutaRelativa) {
                $ext = strtolower(pathinfo($rutaRelativa, PATHINFO_EXTENSION));
                if (!in_array($ext, self::ALLOWED_EXT, true)) {
                    continue;
                }

                $rutasOrigen[] = $rutaRelativa;

                // ── Extraer metadata desde la estructura de carpetas ──────
                $meta = $this->extraerMetadata($rutaRelativa, $sourceDir, $tipo);

                // ── Calcular ruta de respaldo ─────────────────────────────
                $rutaBackup = $this->buildBackupPath($rutaRelativa, $sourceDir, $tipo);

                // ── Upsert en BD ──────────────────────────────────────────
                /** @var CalidadMaquinadoDoc|null $registro */
                $registro = CalidadMaquinadoDoc::query()->where('ruta_storage', $rutaBackup)->first();

                if (!$registro) {
                    // Archivo nuevo: copiar y crear registro
                    if (!$dryRun) {
                        $this->copiarArchivo($rutaRelativa, $rutaBackup);

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
                    // Archivo existente: re-activar si estaba inactivo y actualizar timestamp
                    if (!$dryRun) {
                        // Re-copiar para mantener el backup actualizado
                        $this->copiarArchivo($rutaRelativa, $rutaBackup);

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

        // ── 2. Inactivar registros que ya no están en el origen ──────────────
        $registrosActivos = CalidadMaquinadoDoc::query()->where('estado', 'activo')->get();

        foreach ($registrosActivos as $registro) {
            // Reconstruir la ruta de origen desde la ruta de backup
            $rutaOrigenEstimada = $this->backupToSourcePath($registro->ruta_storage, $registro->tipo);

            // Si la ruta de origen ya no existe en el disco → inactivar
            if (!in_array($rutaOrigenEstimada, $rutasOrigen, true)) {
                if (!$dryRun) {
                    $registro->update(['estado' => 'inactivo']);
                }
                $this->warn("  [INACTIVO] {$registro->ruta_storage}");
                $totalInactivados++;
            }
        }

        // ── 3. Resumen ────────────────────────────────────────────────────────
        $this->newLine();
        $this->info("  Nuevos   : {$totalNuevos}");
        $this->info("  Actualizados : {$totalActualizados}");
        $this->warn("  Inactivados  : {$totalInactivados}");
        $this->info('══════════════════════════════════════════════════');

        return self::SUCCESS;
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Extrae metadata desde la estructura de carpetas según el tipo de documento.
     *
     * Dibujos  → partes[0]=OT,    partes[1]=Clase,   partes[2]=Proceso
     * Ayudas   → partes[0]=Clase, partes[1]=Proceso  (no tienen OT)
     *
     * @param  string $rutaRelativa  Ruta relativa al disco (incluye $sourceDir)
     * @param  string $sourceDir     Directorio raíz de origen
     * @param  string $tipo          'dibujo' | 'ayuda'
     * @return array{ot: string|null, clase: string|null, proceso: string|null, fecha_archivo: string|null}
     */
    private function extraerMetadata(string $rutaRelativa, string $sourceDir, string $tipo): array
    {
        // Eliminar el prefijo del directorio fuente
        $relative = ltrim(str_replace($sourceDir, '', $rutaRelativa), '/\\');

        // Separar en partes (sin el nombre del archivo)
        $partes = explode('/', str_replace('\\', '/', $relative));
        array_pop($partes); // quitar el nombre del archivo

        if ($tipo === 'dibujo') {
            // Dibujos: <OT>/<Clase>/<Proceso>/archivo
            $ot      = $partes[0] ?? null;
            $clase   = $partes[1] ?? null;
            $proceso = $partes[2] ?? null;
        } else {
            // Ayudas: <Clase>/<Proceso>/archivo (sin OT)
            $ot      = null;
            $clase   = $partes[0] ?? null;
            $proceso = $partes[1] ?? null;
        }

        // Intentar obtener la fecha de modificación del archivo
        $fechaArchivo = null;
        try {
            $lastModified = Storage::disk('local')->lastModified($rutaRelativa);
            if ($lastModified) {
                $fechaArchivo = Carbon::createFromTimestamp($lastModified)->toDateString();
            }
        } catch (Throwable) {
            // Si falla, dejar null
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
     *
     * Ejemplo:
     *   Origen:  DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS/OT-123/Bombillo/plano.pdf
     *   Backup:  CALIDAD_MAQUINADOS/dibujos/OT-123/Bombillo/plano.pdf
     */
    private function buildBackupPath(string $rutaRelativa, string $sourceDir, string $tipo): string
    {
        $relative = ltrim(str_replace($sourceDir, '', $rutaRelativa), '/\\');
        $subdir   = $tipo === 'dibujo' ? 'dibujos' : 'ayudas';
        return self::BACKUP_ROOT . '/' . $subdir . '/' . $relative;
    }

    /**
     * Reconstruye la ruta de origen estimada desde la ruta de backup.
     * Se usa para detectar si un backup ya no existe en el origen.
     */
    private function backupToSourcePath(string $rutaBackup, string $tipo): string
    {
        $subdir    = $tipo === 'dibujo' ? 'dibujos' : 'ayudas';
        $prefix    = self::BACKUP_ROOT . '/' . $subdir . '/';
        $relative  = ltrim(str_replace($prefix, '', $rutaBackup), '/\\');
        $sourceDir = $tipo === 'dibujo'
            ? 'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS'
            : 'DOCUMENTACION_GIS/AYUDAS_MAQUINADOS';

        return $sourceDir . '/' . $relative;
    }

    /**
     * Copia un archivo desde la ruta de origen a la ruta de backup,
     * creando los directorios intermedios si es necesario.
     */
    private function copiarArchivo(string $origen, string $destino): void
    {
        try {
            $contenido = Storage::disk('local')->get($origen);
            if ($contenido !== null) {
                Storage::disk('local')->put($destino, $contenido);
            }
        } catch (Throwable $e) {
            $this->error("  [ERROR] No se pudo copiar {$origen} → {$destino}: " . $e->getMessage());
        }
    }
}
