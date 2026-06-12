<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Services\FundicionPaths;

/**
 * Migra los archivos de Fundición de la estructura antigua a la nueva.
 *
 * Transformaciones realizadas:
 *   1. {OT}/{Clase}/*.pdf               → {OT}/{Clase}/Dibujos/*.pdf
 *   2. {OT}/ayudas_visuales/{Clase}/    → {OT}/{Clase}/Ayudas_Visuales/
 *   3. {OT}/Documentos_Aprobados/Liberacion_Modelo/ → {OT}/Documentos_Aprobados/FDLDM/
 *   4. {OT}/Documentos_Rechazados/Liberacion_Modelo/ → {OT}/Documentos_Rechazados/FDRDM/
 *   5. {OT}/Documentos_Rechazados/Scar/ → {OT}/Documentos_Rechazados/SCAR/
 *
 * Se aplica en ALMACEN_FUNDICION y CALIDAD_FUNDICION.
 * Usa --dry-run para ver qué movería sin hacer cambios reales.
 */
class MigrateFundicionPaths extends Command
{
    protected $signature   = 'fundicion:migrate-paths {--dry-run : Show what would be moved without making changes}';
    protected $description = 'Migra archivos de Fundición a la nueva estructura de directorios';

    private bool $dryRun = false;
    private int $moved   = 0;
    private int $skipped = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        if ($this->dryRun) {
            $this->warn('=== DRY-RUN: No se moverá ningún archivo ===');
        }

        $roots = [
            FundicionPaths::ALMACEN_ROOT,
            FundicionPaths::CALIDAD_ROOT,
        ];

        foreach ($roots as $root) {
            if (!Storage::disk('local')->exists($root)) {
                $this->line("Directorio no existe, saltando: {$root}");
                continue;
            }

            $otFolders = Storage::disk('local')->directories($root);
            foreach ($otFolders as $otFolder) {
                $otName = basename($otFolder);
                $this->info("OT: {$otName} [{$root}]");
                $this->migrateOT($root, $otName);
            }
        }

        $this->newLine();
        $this->info("✅ Migración completada. Movidos: {$this->moved} | Saltados (ya existen): {$this->skipped}");
        return self::SUCCESS;
    }

    private function migrateOT(string $root, string $otName): void
    {
        $otBase = $root . '/' . $otName;

        // ─── 1. Dibujos: {OT}/{Clase}/*.pdf → {OT}/{Clase}/Dibujos/*.pdf ─────
        $claseDirs = Storage::disk('local')->directories($otBase);
        $reservedDirs = ['Documentos_Aprobados', 'Documentos_Rechazados', 'ayudas_visuales', 'preordenes'];

        foreach ($claseDirs as $claseDir) {
            $clase = basename($claseDir);
            if (in_array($clase, $reservedDirs)) continue;
            // Skip if the dir is already the new structure (has Dibujos/ or Ayudas_Visuales/ children)
            $claseChildren = array_map('basename', Storage::disk('local')->directories($claseDir));
            if (in_array('Dibujos', $claseChildren) || in_array('Ayudas_Visuales', $claseChildren)) {
                $this->line("  [SKIP clase {$clase}] Ya tiene nueva estructura.");
                continue;
            }

            $pdfs = collect(Storage::disk('local')->files($claseDir))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf');

            foreach ($pdfs as $pdfPath) {
                $filename = basename($pdfPath);
                $newPath  = $otBase . '/' . $clase . '/' . FundicionPaths::DIBUJOS . '/' . $filename;
                $this->moveFile($pdfPath, $newPath);
            }
        }

        // ─── 2. Ayudas Visuales: ayudas_visuales/{Clase}/ → {Clase}/Ayudas_Visuales/ ─
        $ayudasLegacy = $otBase . '/ayudas_visuales';
        if (Storage::disk('local')->exists($ayudasLegacy)) {
            $claseDirs = Storage::disk('local')->directories($ayudasLegacy);
            foreach ($claseDirs as $claseDir) {
                $clase = basename($claseDir);
                if ($clase === 'preordenes') continue; // No mover preordenes

                $files = Storage::disk('local')->files($claseDir);
                foreach ($files as $filePath) {
                    $filename = basename($filePath);
                    $newPath  = $otBase . '/' . $clase . '/' . FundicionPaths::AYUDAS_VISUALES . '/' . $filename;
                    $this->moveFile($filePath, $newPath);
                }
            }
        }

        // ─── 3. FDLDM: Documentos_Aprobados/Liberacion_Modelo → FDLDM ─────────
        $this->renameSubfolder(
            $otBase . '/Documentos_Aprobados/Liberacion_Modelo',
            $otBase . '/Documentos_Aprobados/FDLDM'
        );

        // ─── 4. FDRDM: Documentos_Rechazados/Liberacion_Modelo → FDRDM ────────
        $this->renameSubfolder(
            $otBase . '/Documentos_Rechazados/Liberacion_Modelo',
            $otBase . '/Documentos_Rechazados/FDRDM'
        );

        // ─── 5. SCAR: Documentos_Rechazados/Scar → SCAR ───────────────────────
        $this->renameSubfolder(
            $otBase . '/Documentos_Rechazados/Scar',
            $otBase . '/Documentos_Rechazados/SCAR'
        );
    }

    private function moveFile(string $src, string $dst): void
    {
        if (Storage::disk('local')->exists($dst)) {
            $this->line("    [SKIP] Ya existe: {$dst}");
            $this->skipped++;
            return;
        }

        $dstDir = dirname($dst);
        if (!Storage::disk('local')->exists($dstDir)) {
            if (!$this->dryRun) {
                Storage::disk('local')->makeDirectory($dstDir);
            }
        }

        if ($this->dryRun) {
            $this->line("    [DRY] {$src}\n         → {$dst}");
        } else {
            Storage::disk('local')->copy($src, $dst);
            // Solo borramos el original si la copia fue exitosa
            if (Storage::disk('local')->exists($dst)) {
                Storage::disk('local')->delete($src);
                $this->line("    [OK] Movido: " . basename($src));
            }
        }
        $this->moved++;
    }

    private function renameSubfolder(string $oldPath, string $newPath): void
    {
        if (!Storage::disk('local')->exists($oldPath)) return;
        if (Storage::disk('local')->exists($newPath)) {
            $this->line("  [SKIP rename] Ya existe: " . basename($newPath));
            return;
        }

        $files = Storage::disk('local')->allFiles($oldPath);
        foreach ($files as $filePath) {
            $rel     = ltrim(substr(str_replace('\\', '/', $filePath), strlen(str_replace('\\', '/', $oldPath))), '/');
            $newFile = $newPath . '/' . $rel;
            $this->moveFile($filePath, $newFile);
        }

        if (!$this->dryRun) {
            Storage::disk('local')->deleteDirectory($oldPath);
        }
    }
}
