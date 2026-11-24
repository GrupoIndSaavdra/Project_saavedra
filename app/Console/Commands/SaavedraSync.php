<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SaavedraSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saavedra:sync 
                            {--direction=both : Dirección de sincronización (push, pull, both)}
                            {--table= : Sincronizar únicamente esta tabla específica}
                            {--force : Ignorar fecha del checkpoint y sincronizar desde el inicio}
                            {--no-files : Omitir sincronización de archivos físicos}
                            {--only-files : Sincronizar únicamente archivos físicos (sin BD)}
                            {--dry-run : Ejecutar en modo simulación sin guardar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza datos bidireccionalmente entre el servidor local (planta) y la nube';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        $cloudUrl = rtrim((string) config('sync.cloud_url', 'https://api.grupoindsaavedra.com'), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');

        // Si este comando se ejecuta dentro del servidor de la nube (cPanel), no debe sincronizarse consigo mismo
        if (!empty($appUrl) && $cloudUrl === $appUrl) {
            return self::SUCCESS;
        }

        $devToken = (string) config('sync.dev_token', '');
        $prodToken = (string) config('sync.token', '');
        $isDevExplicit = (bool) config('sync.is_dev', false);

        // Se considera modo Dev si se activa SYNC_IS_DEV, si no hay token prod pero sí dev,
        // o si el token principal configurado en este cliente es el token de dev
        $isDevMode = $isDevExplicit || (empty($prodToken) && !empty($devToken)) || (!empty($devToken) && $prodToken === $devToken);
        $token = $isDevMode ? $devToken : $prodToken;

        $rawDirection = $this->option('direction');
        if ($isDevMode) {
            $direction = strtolower((string) ($rawDirection ?: 'pull'));
            if (in_array($direction, ['push', 'both'], true)) {
                $this->warn("   ⚠️  [MODO DEV] El token de desarrollador solo permite descarga (PULL).");
                $this->warn("      Se omite la subida (PUSH) a producción para proteger los datos en la nube.");
                $direction = 'pull';
            }
        } else {
            $direction = strtolower((string) ($rawDirection ?: 'both'));
        }

        $singleTable = $this->option('table');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("================================================================================");
        $this->info("🔄 INICIANDO MOTOR DE SINCRONIZACIÓN SAAVEDRA");
        $this->info("   Servidor remoto: {$cloudUrl}");
        $this->info("   Dirección: " . strtoupper($direction));
        if ($isDevMode) {
            $this->comment("   PERFIL: DESARROLLADOR (PULL MANUAL / PROTEGIDO)");
        } else {
            $this->line("   PERFIL: SERVIDOR DE PRODUCCIÓN (BIDIRECCIONAL)");
        }
        if ($dryRun) {
            $this->warn("   MODO SIMULACIÓN (DRY-RUN) ACTIVO — No se aplicarán cambios reales.");
        }
        $this->info("================================================================================");

        $this->logSync("[LOCAL-SYNC-START] Iniciando sincronización con {$cloudUrl} (Dirección: " . strtoupper($direction) . ($dryRun ? ', DRY-RUN' : '') . ")");

        // ── 1. COMPROBAR CONECTIVIDAD (PING / HEARTBEAT) ─────────────────────
        if (!$this->checkConnectivity($cloudUrl, $token)) {
            $this->warn("⚠️  [OFFLINE] No hay conexión a internet o el servidor en la nube no responde.");
            $this->line("   El sistema continuará operando normalmente en la red local de la planta.");
            $this->logSync("[LOCAL-SYNC-OFFLINE] Conexión fallida con {$cloudUrl}. Modo local activo.", 'warning');
            return self::SUCCESS;
        }

        $this->info("✅ Conexión con la nube verificada exitosamente.");

        $noFiles = (bool) $this->option('no-files');
        $onlyFiles = (bool) $this->option('only-files');

        // ── 2. OBTENER TABLAS A SINCRONIZAR ──────────────────────────────────
        $tables = $singleTable ? [(string) $singleTable] : $this->getSyncableTables();

        $totalPushed = 0;
        $totalPulled = 0;

        // ── 3. EJECUTAR SINCRONIZACIÓN DE BASE DE DATOS ───────────────────────
        if (!$onlyFiles) {
            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    $this->warn("   [!] Tabla '{$table}' no existe localmente. Omitiendo.");
                    continue;
                }

                // PULL: Nube -> Local
                if (in_array($direction, ['pull', 'both'], true)) {
                    $pulled = $this->syncPull($table, $cloudUrl, $token, $force, $dryRun);
                    $totalPulled += $pulled;
                }

                // PUSH: Local -> Nube
                if (in_array($direction, ['push', 'both'], true)) {
                    $pushed = $this->syncPush($table, $cloudUrl, $token, $force, $dryRun);
                    $totalPushed += $pushed;
                }
            }
        }

        // ── 4. EJECUTAR SINCRONIZACIÓN DE ARCHIVOS FÍSICOS (DOCUMENTACION_GIS) ─
        $filesPushed = 0;
        $filesPulled = 0;

        if (!$noFiles && config('sync.sync_files_enabled', true)) {
            $fileDirection = $isDevMode ? 'pull' : $direction;
            $fileResults = $this->syncPhysicalFiles($cloudUrl, $token, $fileDirection, $dryRun);
            $filesPulled = $fileResults['pulled'];
            $filesPushed = $fileResults['pushed'];
        }

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info("================================================================================");
        $this->info("🏁 SINCRONIZACIÓN FINALIZADA EN {$elapsed}s");
        $this->info("   📤 Registros subidos a la nube (PUSH): {$totalPushed}");
        $this->info("   📥 Registros descargados de la nube (PULL): {$totalPulled}");
        $this->info("   📁 Archivos físicos subidos (PUSH): {$filesPushed}");
        $this->info("   📁 Archivos físicos descargados (PULL): {$filesPulled}");
        $this->info("================================================================================");

        $this->logSync("[LOCAL-SYNC-OK] Sincronización completada en {$elapsed}s. DB PUSH: {$totalPushed}, DB PULL: {$totalPulled}, FILES PUSH: {$filesPushed}, FILES PULL: {$filesPulled}");

        return self::SUCCESS;
    }

    /**
     * Cliente HTTP preconfigurado con timeouts, reintentos y verificación SSL adaptable para Windows.
     */
    protected function httpClient(int $timeout = 60): PendingRequest
    {
        $options = [
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            ],
        ];

        $client = Http::timeout($timeout)
            ->retry(2, 200, throw: false)
            ->withOptions($options);

        if (!config('sync.ssl_verify', false)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Comprueba si la nube está disponible en tiempo récord.
     */
    protected function checkConnectivity(string $cloudUrl, string $token): bool
    {
        $pingTimeout = (int) config('sync.ping_timeout', 4);

        try {
            $response = $this->httpClient($pingTimeout)
                ->withHeaders(['X-Sync-Token' => $token])
                ->get("{$cloudUrl}/api/sync/ping");

            return $response->successful() && ($response->json('success') === true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Sube registros modificados localmente hacia la nube (PUSH).
     */
    protected function syncPush(string $table, string $cloudUrl, string $token, bool $force, bool $dryRun): int
    {
        $checkpoint = $this->getCheckpoint('push', $table);
        $lastSyncedAt = (!$force && $checkpoint) ? $checkpoint->last_synced_at : null;
        $lastSyncedId = (!$force && $checkpoint) ? $checkpoint->last_synced_id : null;
        $batchSize = (int) config('sync.batch_size', 250);
        $primaryKey = $this->getPrimaryKey($table);
        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');
        $hasCreatedAt = Schema::hasColumn($table, 'created_at');
        $timeout = (int) config('sync.timeout', 90);
        $totalPushed = 0;
        $page = 0;

        do {
            $page++;
            $isSnapshot = false;
            $query = DB::table($table);

            if ($hasUpdatedAt) {
                if (!empty($lastSyncedAt)) {
                    $query->where(function ($q) use ($lastSyncedAt, $lastSyncedId, $primaryKey) {
                        $q->where('updated_at', '>', $lastSyncedAt);
                        if (!empty($lastSyncedId)) {
                            $q->orWhere(function ($q2) use ($lastSyncedAt, $lastSyncedId, $primaryKey) {
                                $q2->where('updated_at', '=', $lastSyncedAt)
                                   ->where($primaryKey, '>', $lastSyncedId);
                            });
                        }
                    });
                }
                $query->orderBy('updated_at', 'asc')
                      ->orderBy($primaryKey, 'asc');
            } elseif ($hasCreatedAt) {
                if (!empty($lastSyncedAt)) {
                    $query->where(function ($q) use ($lastSyncedAt, $lastSyncedId, $primaryKey) {
                        $q->where('created_at', '>', $lastSyncedAt);
                        if (!empty($lastSyncedId)) {
                            $q->orWhere(function ($q2) use ($lastSyncedAt, $lastSyncedId, $primaryKey) {
                                $q2->where('created_at', '=', $lastSyncedAt)
                                   ->where($primaryKey, '>', $lastSyncedId);
                            });
                        }
                    });
                }
                $query->orderBy('created_at', 'asc')
                      ->orderBy($primaryKey, 'asc');
            } else {
                $totalInTable = DB::table($table)->count();
                if ($totalInTable <= 3000) {
                    $isSnapshot = true;
                    $query->orderBy($primaryKey, 'asc');
                } elseif (!empty($lastSyncedId)) {
                    $query->where($primaryKey, '>', $lastSyncedId)
                          ->orderBy($primaryKey, 'asc');
                } else {
                    $query->orderBy($primaryKey, 'asc');
                }
            }

            $records = $isSnapshot ? $query->get() : $query->limit($batchSize)->get();
            $count = $records->count();

            if ($count === 0) {
                break;
            }

            if ($page === 1) {
                if (!$isSnapshot && $count >= $batchSize) {
                    $this->line("   📤 [PUSH] Subiendo registros de '{$table}' (por lotes)...");
                } else {
                    $this->line("   📤 [PUSH] Subiendo {$count} registros de '{$table}'...");
                }
            }

            if ($dryRun) {
                $this->comment("      [DRY-RUN] Simulado push de {$count} registros (página {$page}).");
                $totalPushed += $count;
                if ($isSnapshot || $count < $batchSize) {
                    break;
                }
                $lastRecord = $records->last();
                if ($hasUpdatedAt && isset($lastRecord->updated_at)) {
                    $lastSyncedAt = $lastRecord->updated_at;
                } elseif ($hasCreatedAt && isset($lastRecord->created_at)) {
                    $lastSyncedAt = $lastRecord->created_at;
                }
                if (isset($lastRecord->{$primaryKey})) {
                    $lastSyncedId = $lastRecord->{$primaryKey};
                }
                continue;
            }

            try {
                $response = $this->httpClient($timeout)
                    ->withHeaders([
                        'X-Sync-Token' => $token,
                        'Accept' => 'application/json',
                    ])
                    ->post("{$cloudUrl}/api/sync/push", [
                        'table' => $table,
                        'records' => $records->toArray(),
                    ]);

                if ($response->successful() && $response->json('success') === true) {
                    $lastRecord = $records->last();

                    if ($hasUpdatedAt && isset($lastRecord->updated_at)) {
                        $lastSyncedAt = $lastRecord->updated_at;
                    } elseif ($hasCreatedAt && isset($lastRecord->created_at)) {
                        $lastSyncedAt = $lastRecord->created_at;
                    }
                    if (isset($lastRecord->{$primaryKey})) {
                        $lastSyncedId = $lastRecord->{$primaryKey};
                    }

                    $totalPushed += $count;
                    $this->updateCheckpoint('push', $table, $lastSyncedAt, $lastSyncedId, $count, 'success');

                    if ($isSnapshot || $count < $batchSize) {
                        $this->info("      ✅ {$totalPushed} registros subidos a '{$table}'.");
                        break;
                    } else {
                        $this->line("      ... {$totalPushed} registros subidos acumulados a '{$table}'...");
                    }
                } else {
                    $errorMsg = $response->json('message') ?? 'Error HTTP ' . $response->status();
                    $this->error("      ❌ Fallo al subir '{$table}': {$errorMsg}");
                    $this->updateCheckpoint('push', $table, $lastSyncedAt, $lastSyncedId, 0, 'error', $errorMsg);
                    break;
                }
            } catch (\Throwable $e) {
                $this->error("      ❌ Excepción en PUSH de '{$table}': " . $e->getMessage());
                $this->updateCheckpoint('push', $table, $lastSyncedAt, $lastSyncedId, 0, 'error', $e->getMessage());
                break;
            }
        } while (!$isSnapshot && $count >= $batchSize);

        return $totalPushed;
    }

    /**
     * Descarga registros nuevos o modificados desde la nube (PULL).
     */
    protected function syncPull(string $table, string $cloudUrl, string $token, bool $force, bool $dryRun): int
    {
        $checkpoint = $this->getCheckpoint('pull', $table);
        $since = (!$force && $checkpoint) ? $checkpoint->last_synced_at : null;
        $lastId = (!$force && $checkpoint) ? $checkpoint->last_synced_id : null;
        $batchSize = (int) config('sync.batch_size', 250);
        $timeout = (int) config('sync.timeout', 90);
        $totalApplied = 0;
        $hasMore = false;
        $page = 0;

        do {
            $page++;
            try {
                $response = $this->httpClient($timeout)
                    ->withHeaders([
                        'X-Sync-Token' => $token,
                        'Accept' => 'application/json',
                    ])
                    ->get("{$cloudUrl}/api/sync/pull", [
                        'table' => $table,
                        'since' => $since,
                        'last_id' => $lastId,
                        'limit' => $batchSize,
                    ]);

                if (!$response->successful() || $response->json('success') !== true) {
                    $errorMsg = $response->json('message') ?? ('Error HTTP ' . $response->status());
                    if ($totalApplied === 0) {
                        $this->error("      ❌ Fallo al descargar '{$table}': {$errorMsg}");
                    } else {
                        $this->error("      ❌ Fallo en página {$page} de '{$table}': {$errorMsg}");
                    }
                    break;
                }

                $records = $response->json('records', []);
                $count = count($records);

                if ($count === 0) {
                    break;
                }

                $hasMore = (bool) $response->json('has_more', false);

                if ($page === 1) {
                    if ($hasMore) {
                        $this->line("   📥 [PULL] Descargando registros de '{$table}' (por lotes)...");
                    } else {
                        $this->line("   📥 [PULL] Descargando {$count} registros de '{$table}'...");
                    }
                }

                if ($dryRun) {
                    $this->comment("      [DRY-RUN] Simulado pull de {$count} registros (página {$page}).");
                    $totalApplied += $count;
                    $since = $response->json('next_since');
                    $lastId = $response->json('next_id');
                    continue;
                }

                $primaryKey = $response->json('primary_key', 'id');
                $validColumns = array_flip(Schema::getColumnListing($table));

                DB::beginTransaction();
                try {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    try {
                        DB::statement("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");
                    } catch (\Throwable $e) {}

                    $cleanRecords = [];
                    foreach ($records as $row) {
                        $row = (array) $row;
                        $cleanRow = array_intersect_key($row, $validColumns);
                        if (!empty($cleanRow)) {
                            $cleanRecords[] = $cleanRow;
                        }
                    }

                    // Aplicar en micro-lotes de 100 con upsert de alta velocidad
                    foreach (array_chunk($cleanRecords, 100) as $chunk) {
                        $chunkHandled = false;
                        try {
                            $firstKeys = array_keys($chunk[0]);
                            $sameKeys = true;
                            foreach ($chunk as $item) {
                                if (array_keys($item) !== $firstKeys) {
                                    $sameKeys = false;
                                    break;
                                }
                            }

                            if ($sameKeys && in_array($primaryKey, $firstKeys, true)) {
                                DB::table($table)->upsert($chunk, [$primaryKey]);
                                $chunkHandled = true;
                            }
                        } catch (\Throwable $e) {
                            $chunkHandled = false;
                        }

                        if (!$chunkHandled) {
                            foreach ($chunk as $cleanRow) {
                                try {
                                    if (isset($cleanRow[$primaryKey]) && !empty($cleanRow[$primaryKey])) {
                                        $keyValue = $cleanRow[$primaryKey];
                                        DB::table($table)->updateOrInsert([$primaryKey => $keyValue], $cleanRow);
                                    } else {
                                        DB::table($table)->insert($cleanRow);
                                    }
                                } catch (\Throwable $rowEx) {
                                    // Auto-sanación ante colisión de índice único secundario (Error 1062)
                                    if (str_contains($rowEx->getMessage(), '1062 Duplicate entry')) {
                                        try {
                                            if (preg_match("/Duplicate entry '([^']+)' for key/i", $rowEx->getMessage(), $matches)) {
                                                $dupVal = $matches[1];
                                                foreach ($cleanRow as $col => $val) {
                                                    if (is_string($val) && (str_starts_with($val, $dupVal) || str_starts_with($dupVal, substr($val, 0, 50)))) {
                                                        DB::table($table)->where($col, '=', $val)->where($primaryKey, '!=', $cleanRow[$primaryKey] ?? 0)->delete();
                                                        break;
                                                    }
                                                }
                                            }
                                            if (isset($cleanRow[$primaryKey])) {
                                                DB::table($table)->updateOrInsert([$primaryKey => $cleanRow[$primaryKey]], $cleanRow);
                                            } else {
                                                DB::table($table)->insert($cleanRow);
                                            }
                                        } catch (\Throwable $retryEx) {
                                            $this->logSync("[PULL-SKIP] Registro omitido en '{$table}': " . $retryEx->getMessage(), 'warning');
                                        }
                                    } else {
                                        $this->logSync("[PULL-SKIP] Registro omitido en '{$table}': " . $rowEx->getMessage(), 'warning');
                                    }
                                }
                            }
                        }
                    }

                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    DB::commit();

                    $totalApplied += $count;
                    $since = $response->json('next_since');
                    $lastId = $response->json('next_id');

                    $this->updateCheckpoint('pull', $table, $since, $lastId, $count, 'success');

                    if ($hasMore) {
                        $this->line("      ... {$totalApplied} registros aplicados acumulados en '{$table}'...");
                    } else {
                        $this->info("      ✅ {$totalApplied} registros aplicados localmente en '{$table}'.");
                    }
                } catch (\Throwable $e) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    DB::rollBack();
                    $this->error("      ❌ Error aplicando PULL en '{$table}': " . $e->getMessage());
                    $this->updateCheckpoint('pull', $table, $since, $lastId, 0, 'error', $e->getMessage());
                    break;
                }
            } catch (\Throwable $e) {
                $this->error("      ❌ Excepción en PULL de '{$table}': " . $e->getMessage());
                break;
            }
        } while ($hasMore);

        return $totalApplied;
    }

    /**
     * Obtiene el checkpoint de sincronización para un canal y tabla.
     */
    protected function getCheckpoint(string $channel, string $table): ?object
    {
        if (!Schema::hasTable('sync_checkpoints')) {
            return null;
        }

        return DB::table('sync_checkpoints')
            ->where('channel', '=', $channel, 'and')
            ->where('table_name', '=', $table, 'and')
            ->first();
    }

    /**
     * Actualiza o crea el checkpoint en la base de datos local.
     */
    protected function updateCheckpoint(
        string $channel,
        string $table,
        ?string $lastSyncedAt,
        ?int $lastSyncedId,
        int $recordsCount,
        string $status,
        ?string $lastError = null
    ): void {
        if (!Schema::hasTable('sync_checkpoints')) {
            return;
        }

        $existing = DB::table('sync_checkpoints')
            ->where('channel', '=', $channel, 'and')
            ->where('table_name', '=', $table, 'and')
            ->first();

        $newCount = ($existing ? (int) $existing->records_count : 0) + $recordsCount;

        DB::table('sync_checkpoints')->updateOrInsert(
            [
                'channel' => $channel,
                'table_name' => $table,
            ],
            [
                'last_synced_at' => $lastSyncedAt,
                'last_synced_id' => $lastSyncedId,
                'records_count' => $newCount,
                'status' => $status,
                'last_error' => $lastError,
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Determina la clave primaria de la tabla.
     */
    protected function getPrimaryKey(string $table): string
    {
        try {
            $keys = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
            if (!empty($keys) && isset($keys[0]->Column_name)) {
                return $keys[0]->Column_name;
            }
        } catch (\Throwable $e) {
        }

        return 'id';
    }

    /**
     * Retorna todas las tablas sincronizables ordenadas.
     */
    protected function getSyncableTables(): array
    {
        $ignored = config('sync.ignore_tables', []);
        $priority = config('sync.priority_tables', []);

        $dbTables = [];
        try {
            $rawTables = DB::select('SHOW TABLES');
            $dbName = DB::getDatabaseName();
            $property = 'Tables_in_' . $dbName;

            foreach ($rawTables as $row) {
                $tableName = $row->{$property} ?? array_values((array) $row)[0] ?? null;
                if ($tableName && !in_array($tableName, $ignored, true)) {
                    $dbTables[] = $tableName;
                }
            }
        } catch (\Throwable $e) {
            $dbTables = $priority;
        }

        $ordered = [];
        foreach ($priority as $pTable) {
            if (in_array($pTable, $dbTables, true)) {
                $ordered[] = $pTable;
            }
        }

        $remaining = array_diff($dbTables, $ordered);
        sort($remaining);

        return array_merge($ordered, $remaining);
    }

    /**
     * Sincroniza bidireccionalmente los archivos físicos de DOCUMENTACION_GIS.
     */
    protected function syncPhysicalFiles(string $cloudUrl, string $token, string $direction, bool $dryRun): array
    {
        $this->info("\n📁 INICIANDO SINCRONIZACIÓN DE ARCHIVOS FÍSICOS (DOCUMENTACION_GIS)...");

        $baseRelative = (string) config('sync.files_relative_dir', 'DOCUMENTACION_GIS');
        $baseDir = storage_path('app/' . $baseRelative);
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        $pulled = 0;
        $pushed = 0;
        $timeout = (int) config('sync.timeout', 90);

        try {
            // 1. Obtener manifiesto de la nube
            $response = $this->httpClient($timeout)
                ->withHeaders(['X-Sync-Token' => $token])
                ->get("{$cloudUrl}/api/sync/files/manifest");

            if (!$response->successful() || $response->json('success') !== true) {
                $this->warn("   ⚠️  No se pudo obtener el manifiesto de archivos de la nube.");
                return ['pulled' => 0, 'pushed' => 0];
            }

            $rawCloudFiles = $response->json('files', []);
            $cloudFiles = [];
            foreach ($rawCloudFiles as $cf) {
                $cloudFiles[$cf['path']] = $cf;
            }

            // 2. Escanear manifiesto local
            $localFiles = $this->getLocalFilesManifest($baseDir);

            $this->line("   📊 Archivos detectados — Nube: " . count($cloudFiles) . " | Local: " . count($localFiles));

            // 3. PULL: Archivos en la nube que faltan localmente o están más nuevos en la nube
            if (in_array($direction, ['pull', 'both'], true)) {
                foreach ($cloudFiles as $path => $cloudInfo) {
                    $needsDownload = false;

                    if (!isset($localFiles[$path])) {
                        $needsDownload = true;
                    } elseif ($cloudInfo['mtime'] > $localFiles[$path]['mtime'] && $cloudInfo['size'] !== $localFiles[$path]['size']) {
                        $needsDownload = true;
                    }

                    if ($needsDownload) {
                        $sizeKb = round($cloudInfo['size'] / 1024, 1);
                        $this->line("   📥 [PULL-FILE] Descargando '{$path}' ({$sizeKb} KB)...");

                        if ($dryRun) {
                            $pulled++;
                            continue;
                        }

                        $downRes = $this->httpClient($timeout)
                            ->withHeaders(['X-Sync-Token' => $token])
                            ->get("{$cloudUrl}/api/sync/files/download", ['path' => $path]);

                        if ($downRes->successful()) {
                            $destPath = "{$baseDir}/{$path}";
                            $destDir = dirname($destPath);
                            if (!is_dir($destDir)) {
                                @mkdir($destDir, 0755, true);
                            }

                            file_put_contents($destPath, $downRes->body());
                            if (isset($cloudInfo['mtime'])) {
                                @touch($destPath, (int) $cloudInfo['mtime']);
                            }
                            $pulled++;
                        } else {
                            $this->error("      ❌ Fallo al descargar '{$path}'");
                        }
                    }
                }
            }

            // 4. PUSH: Archivos locales que faltan en la nube o están más nuevos en local
            if (in_array($direction, ['push', 'both'], true)) {
                foreach ($localFiles as $path => $localInfo) {
                    $needsUpload = false;

                    if (!isset($cloudFiles[$path])) {
                        $needsUpload = true;
                    } elseif ($localInfo['mtime'] > $cloudFiles[$path]['mtime'] && $localInfo['size'] !== $cloudFiles[$path]['size']) {
                        $needsUpload = true;
                    }

                    if ($needsUpload) {
                        $sizeKb = round($localInfo['size'] / 1024, 1);
                        $this->line("   📤 [PUSH-FILE] Subiendo '{$path}' ({$sizeKb} KB)...");

                        if ($dryRun) {
                            $pushed++;
                            continue;
                        }

                        $sourcePath = "{$baseDir}/{$path}";
                        if (file_exists($sourcePath)) {
                            $upRes = $this->httpClient($timeout)
                                ->withHeaders(['X-Sync-Token' => $token])
                                ->attach('file', file_get_contents($sourcePath), basename($sourcePath))
                                ->post("{$cloudUrl}/api/sync/files/upload", [
                                    'path' => $path,
                                    'mtime' => $localInfo['mtime'],
                                ]);

                            if ($upRes->successful() && $upRes->json('success') === true) {
                                $pushed++;
                            } else {
                                $this->error("      ❌ Fallo al subir '{$path}'");
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->error("   ❌ Excepción sincronizando archivos: " . $e->getMessage());
        }

        return ['pulled' => $pulled, 'pushed' => $pushed];
    }

    /**
     * Escanea el directorio local de almacenamiento y genera el manifiesto.
     */
    protected function getLocalFilesManifest(string $baseDir): array
    {
        $manifest = [];
        if (!is_dir($baseDir)) {
            return $manifest;
        }

        $allowedExtensions = config('sync.allowed_file_extensions', ['pdf', 'png', 'jpg', 'jpeg', 'dwg', 'dxf']);

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS)
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $ext = strtolower($fileInfo->getExtension());
                    if (!in_array($ext, $allowedExtensions, true)) {
                        continue;
                    }

                    $absolutePath = str_replace('\\', '/', $fileInfo->getRealPath());
                    $normalizedBase = str_replace('\\', '/', $baseDir);
                    $relative = ltrim(substr($absolutePath, strlen($normalizedBase)), '/');

                    $manifest[$relative] = [
                        'path' => $relative,
                        'size' => $fileInfo->getSize(),
                        'mtime' => $fileInfo->getMTime(),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        return $manifest;
    }

    /**
     * Escribe en el archivo de logs dedicado (storage/logs/sync.log).
     */
    protected function logSync(string $message, string $level = 'info'): void
    {
        try {
            Log::channel('sync')->{$level}($message);
        } catch (\Throwable $e) {
            try {
                $logPath = storage_path('logs/sync.log');
                $time = now()->format('Y-m-d H:i:s');
                file_put_contents($logPath, "[{$time}] [{$level}] {$message}\n", FILE_APPEND | LOCK_EX);
            } catch (\Throwable $e2) {
                // Silencioso
            }
        }
    }
}

