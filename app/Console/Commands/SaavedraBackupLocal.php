<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaavedraBackupLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saavedra:backup-local 
                            {--path= : Ruta personalizada de almacenamiento del respaldo}
                            {--keep= : Días de retención para eliminar respaldos viejos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo completo de la base de datos local en formato comprimido .sql.gz';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cloudUrl = rtrim((string) config('sync.cloud_url', 'https://api.grupoindsaavedra.com'), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');

        // Si se ejecuta dentro de cPanel en producción, omitir el respaldo local para no saturar el hosting compartido
        if (!empty($appUrl) && $cloudUrl === $appUrl && !$this->option('path')) {
            return self::SUCCESS;
        }

        $startTime = microtime(true);
        $dbName = (string) config('database.connections.mysql.database');
        $dbHost = (string) config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = (string) config('database.connections.mysql.port', '3306');
        $dbUser = (string) config('database.connections.mysql.username', 'root');
        $dbPass = (string) config('database.connections.mysql.password', '');

        $targetDir = $this->option('path') ?: config('sync.backup_path', storage_path('app/backups'));
        $retentionDays = (int) ($this->option('keep') ?: config('sync.backup_retention_days', 15));

        // Normalizar y crear directorio de destino
        $targetDir = rtrim(str_replace('\\', '/', $targetDir), '/');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        if (!is_dir($targetDir) || !is_writable($targetDir)) {
            // Si la ruta configurada (ej. C:/Respaldos_Saavedra) no tiene permisos, fallback a storage
            $targetDir = storage_path('app/backups');
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
        }

        $timestamp = date('Y_m_d_His');
        $gzFilename = "backup_saavedra_{$dbName}_{$timestamp}.sql.gz";
        $gzPath = "{$targetDir}/{$gzFilename}";
        $tempSqlPath = "{$targetDir}/temp_{$timestamp}.sql";

        $this->info("================================================================================");
        $this->info("💾 GENERANDO RESPALDO LOCAL DE BASE DE DATOS");
        $this->info("   Base de datos: {$dbName} ({$dbHost}:{$dbPort})");
        $this->info("   Destino: {$gzPath}");
        $this->info("================================================================================");

        // ── 1. GENERAR DUMP (MYSQLDUMP O MOTOR PHP NATIVO) ───────────────────
        $dumpSuccess = $this->dumpWithMysqldump($tempSqlPath, $dbHost, $dbPort, $dbUser, $dbPass, $dbName);

        if (!$dumpSuccess || !file_exists($tempSqlPath) || filesize($tempSqlPath) === 0) {
            $this->line("   ℹ️  mysqldump no disponible o falló; usando motor PHP nativo...");
            $dumpSuccess = $this->dumpWithNativePhp($tempSqlPath);
        }

        if (!$dumpSuccess || !file_exists($tempSqlPath)) {
            $this->error("❌ Error crítico: No fue posible generar el volcado de la base de datos.");
            $this->logBackup("Fallo al generar respaldo de {$dbName}.");
            return self::FAILURE;
        }

        $rawSizeBytes = filesize($tempSqlPath);
        $rawSizeMb = round($rawSizeBytes / 1024 / 1024, 2);

        // ── 2. COMPRIMIR A .SQL.GZ ───────────────────────────────────────────
        $this->line("   🗜️  Comprimiendo archivo SQL ({$rawSizeMb} MB) a GZIP...");
        $compressed = $this->compressGzip($tempSqlPath, $gzPath);
        @unlink($tempSqlPath); // Eliminar archivo SQL temporal plano

        if (!$compressed || !file_exists($gzPath)) {
            $this->error("❌ Fallo al comprimir el respaldo en formato GZIP.");
            return self::FAILURE;
        }

        $gzSizeBytes = filesize($gzPath);
        $gzSizeMb = round($gzSizeBytes / 1024 / 1024, 2);
        $ratio = $rawSizeBytes > 0 ? round((1 - ($gzSizeBytes / $rawSizeBytes)) * 100, 1) : 0;

        // ── 3. PURGAR RESPALDOS ANTIGUOS (ROTACIÓN) ──────────────────────────
        $purgedCount = $this->purgeOldBackups($targetDir, $retentionDays);

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info("================================================================================");
        $this->info("✅ RESPALDO GENERADO CON ÉXITO");
        $this->info("   📦 Archivo: {$gzFilename}");
        $this->info("   📊 Tamaño final: {$gzSizeMb} MB (reducción del {$ratio}%)");
        $this->info("   🗑️  Respaldos antiguos eliminados (> {$retentionDays} días): {$purgedCount}");
        $this->info("   ⏱️  Tiempo total: {$elapsed}s");
        $this->info("================================================================================");

        $this->logBackup("Respaldo completado: {$gzFilename} ({$gzSizeMb} MB) en {$elapsed}s");

        return self::SUCCESS;
    }

    /**
     * Intenta ejecutar la utilidad del sistema 'mysqldump'.
     */
    protected function dumpWithMysqldump(string $outputFile, string $host, string $port, string $user, string $pass, string $db): bool
    {
        $mysqldumpPath = $this->findMysqldumpBinary();
        if (!$mysqldumpPath) {
            return false;
        }

        $passwordArg = !empty($pass) ? "--password=" . escapeshellarg($pass) : "";
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s --single-transaction --quick --routines --triggers --no-tablespaces %s > %s 2> nul',
            escapeshellarg($mysqldumpPath),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $passwordArg,
            escapeshellarg($db),
            escapeshellarg($outputFile)
        );

        @exec($cmd, $output, $returnCode);

        return $returnCode === 0 && file_exists($outputFile) && filesize($outputFile) > 1024;
    }

    /**
     * Busca la ruta de 'mysqldump' en PATH y rutas típicas de Windows.
     */
    protected function findMysqldumpBinary(): ?string
    {
        // 1. Probar en PATH del sistema
        @exec('where mysqldump 2> nul', $pathOutput, $retCode);
        if ($retCode === 0 && !empty($pathOutput[0]) && file_exists($pathOutput[0])) {
            return $pathOutput[0];
        }

        // 2. Probar rutas conocidas de XAMPP / Laragon / MySQL Server en Windows
        $candidates = [
            'C:/xampp/mysql/bin/mysqldump.exe',
            'D:/xampp/mysql/bin/mysqldump.exe',
            'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe',
            'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe',
            'C:/Program Files/MySQL/MySQL Server 8.2/bin/mysqldump.exe',
            'C:/Program Files/MySQL/MySQL Server 5.7/bin/mysqldump.exe',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Volcado nativo en PHP vía PDO si mysqldump no está disponible.
     */
    protected function dumpWithNativePhp(string $outputFile): bool
    {
        try {
            $handle = fopen($outputFile, 'w');
            if (!$handle) {
                return false;
            }

            fwrite($handle, "-- Project Saavedra Native PHP Database Dump\n");
            fwrite($handle, "-- Generado: " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            $prop = 'Tables_in_' . $dbName;

            foreach ($tables as $tRow) {
                $table = $tRow->{$prop} ?? array_values((array) $tRow)[0] ?? null;
                if (!$table) {
                    continue;
                }

                // Estructura de tabla
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Estructura de tabla para `{$table}`\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                if (!empty($createTable[0]->{'Create Table'})) {
                    fwrite($handle, $createTable[0]->{'Create Table'} . ";\n\n");
                }

                // Datos de la tabla
                fwrite($handle, "-- Volcado de datos para `{$table}`\n");
                $rows = DB::table($table)->get();

                if ($rows->count() > 0) {
                    foreach ($rows->chunk(100) as $chunk) {
                        fwrite($handle, "INSERT INTO `{$table}` VALUES \n");
                        $insertValues = [];

                        foreach ($chunk as $row) {
                            $vals = [];
                            foreach ((array) $row as $val) {
                                if (is_null($val)) {
                                    $vals[] = 'NULL';
                                } elseif (is_numeric($val) && !is_string($val)) {
                                    $vals[] = $val;
                                } else {
                                    $vals[] = "'" . addslashes((string) $val) . "'";
                                }
                            }
                            $insertValues[] = "(" . implode(", ", $vals) . ")";
                        }

                        fwrite($handle, implode(",\n", $insertValues) . ";\n");
                    }
                }

                fwrite($handle, "\n\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            return true;
        } catch (\Throwable $e) {
            $this->error("Error en volcado PHP nativo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Comprime un archivo plano a .gz en bloques de memoria para alta eficiencia.
     */
    protected function compressGzip(string $sourcePath, string $destGzPath): bool
    {
        $srcHandle = fopen($sourcePath, 'rb');
        $gzHandle = gzopen($destGzPath, 'wb9');

        if (!$srcHandle || !$gzHandle) {
            return false;
        }

        while (!feof($srcHandle)) {
            gzwrite($gzHandle, fread($srcHandle, 1024 * 512)); // Bloques de 512 KB
        }

        fclose($srcHandle);
        gzclose($gzHandle);

        return true;
    }

    /**
     * Elimina respaldos con más de N días de antigüedad.
     */
    protected function purgeOldBackups(string $dir, int $retentionDays): int
    {
        $purged = 0;
        $cutoff = time() - ($retentionDays * 86400);

        $files = glob("{$dir}/backup_saavedra_*.sql.gz");
        if ($files) {
            foreach ($files as $file) {
                if (filemtime($file) < $cutoff) {
                    @unlink($file);
                    $purged++;
                }
            }
        }

        return $purged;
    }

    /**
     * Registro en archivo de logs de respaldo y sincronización (storage/logs/sync.log).
     */
    protected function logBackup(string $message): void
    {
        try {
            Log::channel('sync')->info("[LOCAL-BACKUP-OK] {$message}");
        } catch (\Throwable $e) {
            // Silencioso
        }

        try {
            $logPath = storage_path('logs/backup_local.log');
            $time = now()->format('Y-m-d H:i:s');
            file_put_contents($logPath, "[{$time}] {$message}\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            Log::info("[BACKUP] {$message}");
        }
    }
}

