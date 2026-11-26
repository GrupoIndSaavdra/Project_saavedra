<?php

namespace App\Console\Commands;

use GuzzleHttp\Handler\CurlFactory;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
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
     * Handler Guzzle compartido para reutilizar conexiones TCP/TLS Keep-Alive entre lotes.
     */
    protected ?HandlerStack $sharedHandler = null;

    /**
     * Esquema local precargado en memoria (evita cientos de queries SHOW KEYS / information_schema).
     *
     * @var array<string, array{columns: array<string, int>, primary_key: string, has_updated_at: bool, has_created_at: bool}>
     */
    protected array $localSchema = [];

    /**
     * Caché en memoria de sync_checkpoints indexado por "canal:tabla".
     *
     * @var array<string, object>
     */
    protected array $checkpointsCache = [];

    protected bool $hasCheckpointsTable = false;

    /**
     * Huellas MD5 locales para tablas catálogo sin timestamps (evita re-subir y re-bajar snapshots idénticos).
     *
     * @var array{pull: array<string, string>, push: array<string, string>}
     */
    protected array $snapshotHashes = ['pull' => [], 'push' => []];

    protected bool $snapshotHashesDirty = false;

    protected bool $interactiveUi = false;

    protected int $currentTableIndex = 0;

    protected int $totalTablesCount = 0;

    protected int $spinnerTick = 0;

    protected int $terminalWidth = 100;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        $this->initInteractiveTerminal();

        $cloudUrl = rtrim((string) config('sync.cloud_url', 'https://api.grupoindsaavedra.com'), '/');
        $appUrl = rtrim((string) config('app.url', ''), '/');

        // Si este comando se ejecuta dentro del servidor de la nube (cPanel), no debe sincronizarse consigo mismo
        if (!empty($appUrl) && $cloudUrl === $appUrl) {
            return self::SUCCESS;
        }

        $devToken = (string) config('sync.dev_token', '');
        $prodToken = (string) config('sync.token', '');
        $isDevMode = (bool) config('sync.is_dev', false);

        $token = $isDevMode ? (!empty($devToken) ? $devToken : $prodToken) : $prodToken;

        $rawDirection = $this->option('direction');
        $devForcedPullWarning = false;
        if ($isDevMode) {
            $direction = strtolower((string) ($rawDirection ?: 'pull'));
            if (in_array($direction, ['push', 'both'], true)) {
                $devForcedPullWarning = true;
                $direction = 'pull';
            }
        } else {
            $direction = strtolower((string) ($rawDirection ?: 'both'));
        }

        $singleTable = $this->option('table');
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $noFiles = (bool) $this->option('no-files');
        $onlyFiles = (bool) $this->option('only-files');

        $dirCode = strtoupper($direction);
        $authProfile = $isDevMode ? 'DEV_TOKEN (READ_ONLY)' : 'PROD_TOKEN (READ_WRITE)';
        $strategyCode = $force ? 'FULL_SNAPSHOT (--force)' : 'INCREMENTAL_DELTA';
        $pullBatchCfg = (int) config('sync.pull_batch_size', 500);
        $pushBatchCfg = (int) config('sync.batch_size', 40);
        $pid = function_exists('getmypid') ? (int) getmypid() : 0;

        $flags = [];
        if ($dryRun) {
            $flags[] = 'DRY_RUN';
        }
        if ($singleTable) {
            $flags[] = "TARGET_TABLE={$singleTable}";
        }
        if ($noFiles) {
            $flags[] = 'SKIP_FS';
        }
        if ($onlyFiles) {
            $flags[] = 'FS_ONLY';
        }
        $flagsStr = !empty($flags) ? ('  flags=' . implode(',', $flags)) : '';

        $this->newLine();
        $this->line('<fg=cyan>--------------------------------------------------------------------------------</>');
        $this->line('<fg=white;options=bold>SAAVEDRA DB/FS REPLICATION ENGINE v2.1</>  <fg=gray>|  pid=' . $pid . '  |  ' . now()->format('Y-m-d H:i:s') . '</>');
        $this->line('<fg=cyan>--------------------------------------------------------------------------------</>');
        $this->line("<fg=gray>[CONF]</> endpoint=<fg=cyan>{$cloudUrl}</>  dir=<fg=yellow>{$dirCode}</>  auth={$authProfile}");
        $this->line("<fg=gray>[CONF]</> strategy={$strategyCode}  batch_pull={$pullBatchCfg}  batch_push={$pushBatchCfg} (max_post=15KB){$flagsStr}");

        if ($devForcedPullWarning) {
            $this->line('<fg=yellow>[WARN]</> SYNC_IS_DEV=true active: write operations (PUSH) suppressed.');
        }

        $this->logSync(
            "[INIT] endpoint={$cloudUrl} dir={$dirCode} auth={$authProfile} strategy={$strategyCode}{$flagsStr}"
        );

        // ── 1. COMPROBAR CONECTIVIDAD (PING / HEARTBEAT) ─────────────────────
        $pingStart = microtime(true);
        $this->renderLiveLine('[NET ] Probing TLS/HTTP endpoint ' . $cloudUrl . '/api/sync/ping ...');
        $connected = $this->checkConnectivity($cloudUrl, $token);
        $pingMs = (int) round((microtime(true) - $pingStart) * 1000);
        $this->clearLiveLine();

        if (!$connected) {
            $this->line("<fg=yellow>[WARN]</> endpoint_unreachable  rtt={$pingMs}ms  fallback=LOCAL_DB_STANDALONE");
            $this->logSync("[NET-ERR] Handshake failed against {$cloudUrl} after {$pingMs}ms. Standalone mode active.", 'warning');
            return self::SUCCESS;
        }

        $this->line("<fg=green>[NET ]</> status=CONNECTED  rtt=<fg=green>{$pingMs}ms</>  transport=HTTP/1.1 Keep-Alive  encoding=gzip");

        $totalPushed = 0;
        $totalPulled = 0;
        $tablesPulledCount = 0;
        $tablesPushedCount = 0;
        $tablesCleanCount = 0;
        $excludedCount = 0;

        // ── 2. PRECARGAR METADATOS LOCALES Y EJECUTAR SINCRONIZACIÓN DE BD ────
        if (!$onlyFiles) {
            $this->renderLiveLine('[META] Loading information_schema.COLUMNS and sync_checkpoints into memory...');
            $this->preloadLocalMetadata();
            $this->loadSnapshotHashes();
            $this->clearLiveLine();

            $totalSchemaTables = count($this->localSchema);
            $tables = $singleTable ? [(string) $singleTable] : $this->getSyncableTables();
            $excludedCount = $singleTable ? 0 : max(0, $totalSchemaTables - count($tables));

            // Filtrar solo tablas que existen localmente usando el mapa en memoria
            $validTables = [];
            foreach ($tables as $table) {
                if (!isset($this->localSchema[$table])) {
                    $this->line("<fg=yellow>[WARN]</> table_missing_locally='{$table}' action=SKIP");
                    continue;
                }
                $validTables[] = $table;
            }

            $this->totalTablesCount = count($validTables);
            $loadedCpCount = count($this->checkpointsCache);
            $this->line(
                "<fg=green>[META]</> db_tables={$totalSchemaTables}  syncable=<fg=cyan>{$this->totalTablesCount}</>"
                . "  excluded={$excludedCount} (ignore_tables)  checkpoints={$loadedCpCount}"
            );

            // PREFLIGHT REMOTO EN BLOQUE PARA PULL:
            $remoteChangedMap = null;
            if (in_array($direction, ['pull', 'both'], true) && !$force && count($validTables) > 1) {
                $remoteChangedMap = $this->detectRemoteChangedTables($validTables, $cloudUrl, $token);
            }

            $this->newLine();
            $scanStartedAt = microtime(true);

            foreach ($validTables as $idx => $table) {
                $this->currentTableIndex = $idx + 1;
                $pulled = 0;
                $pushed = 0;

                // PULL: Nube -> Local (solo si hubo cambios detectados o barrido directo Keep-Alive)
                if (in_array($direction, ['pull', 'both'], true)) {
                    if ($remoteChangedMap === null || isset($remoteChangedMap[$table])) {
                        $pulled = $this->syncPull($table, $cloudUrl, $token, $force, $dryRun, $scanStartedAt);
                        $totalPulled += $pulled;
                        if ($pulled > 0) {
                            $tablesPulledCount++;
                        }
                    }
                }

                // PUSH: Local -> Nube
                if (in_array($direction, ['push', 'both'], true)) {
                    $pushed = $this->syncPush($table, $cloudUrl, $token, $force, $dryRun, $scanStartedAt);
                    $totalPushed += $pushed;
                    if ($pushed > 0) {
                        $tablesPushedCount++;
                    }
                }

                if ($pulled === 0 && $pushed === 0) {
                    $tablesCleanCount++;
                }
            }

            $this->clearLiveLine();

            if ($totalPulled === 0 && $totalPushed === 0) {
                $this->line("<fg=green>[SYNC]</> delta_check=CLEAN  inspected={$this->totalTablesCount}/{$this->totalTablesCount}  pending_rows=0");
            }

            if (!$dryRun) {
                $this->saveSnapshotHashes();
            }
        }
        $dbElapsed = microtime(true) - $startTime;

        // ── 4. EJECUTAR SINCRONIZACIÓN DE ARCHIVOS FÍSICOS (DOCUMENTACION_GIS) ─
        $filesPushed = 0;
        $filesPulled = 0;
        $bytesTransferred = 0;
        $fsStart = microtime(true);

        if (!$noFiles && config('sync.sync_files_enabled', true)) {
            $fileDirection = $isDevMode ? 'pull' : $direction;
            $fileResults = $this->syncPhysicalFiles($cloudUrl, $token, $fileDirection, $dryRun);
            $filesPulled = $fileResults['pulled'];
            $filesPushed = $fileResults['pushed'];
            $bytesTransferred = $fileResults['bytes'] ?? 0;
        }
        $fsElapsed = microtime(true) - $fsStart;

        $elapsed = microtime(true) - $startTime;
        $elapsedFormatted = $this->formatDuration($elapsed);
        $dbElapsedFormatted = $this->formatDuration($dbElapsed);
        $totalRecordsMoved = $totalPushed + $totalPulled;
        $dbRate = ($dbElapsed > 0 && $totalRecordsMoved > 0)
            ? number_format((int) round($totalRecordsMoved / $dbElapsed)) . ' rows/s'
            : '0 rows/s';

        $this->newLine();
        $this->line('<fg=cyan>--------------------------------------------------------------------------------</>');
        $this->line('<fg=white;options=bold>[SUMMARY]</> status=<fg=green;options=bold>EXIT_SUCCESS (0)</>  total_elapsed=<fg=green>' . $elapsedFormatted . '</>  db_elapsed=' . $dbElapsedFormatted . '  db_rate=' . $dbRate);
        if (!$onlyFiles) {
            $this->line(
                "<fg=gray>[DB_STAT]</> inspected={$this->totalTablesCount}  pulled_tbl=<fg=cyan>{$tablesPulledCount}</>"
                . "  pushed_tbl=<fg=cyan>{$tablesPushedCount}</>  unchanged={$tablesCleanCount}  excluded={$excludedCount}"
            );
            $this->line(
                '<fg=gray>[DB_ROWS]</> pull_upserted=<fg=green;options=bold>' . number_format($totalPulled) . '</>'
                . '  push_committed=<fg=green;options=bold>' . number_format($totalPushed) . '</>'
            );
        }
        if (!$noFiles && config('sync.sync_files_enabled', true)) {
            $fsRateStr = ($fsElapsed > 0.2 && $bytesTransferred > 0)
                ? '  fs_rate=' . $this->formatBytes((int) round($bytesTransferred / $fsElapsed)) . '/s'
                : '';
            $this->line(
                '<fg=gray>[FS_SYNC]</> pull_files=<fg=cyan>' . number_format($filesPulled) . '</>'
                . '  push_files=<fg=cyan>' . number_format($filesPushed) . '</>'
                . '  payload=' . $this->formatBytes($bytesTransferred) . $fsRateStr
            );
        }
        $this->line('<fg=cyan>--------------------------------------------------------------------------------</>');

        $this->logSync(
            "[SUMMARY] status=OK elapsed={$elapsedFormatted} "
            . "pull_rows={$totalPulled} (tbl={$tablesPulledCount}) "
            . "push_rows={$totalPushed} (tbl={$tablesPushedCount}) "
            . "unchanged_tbl={$tablesCleanCount} excluded_tbl={$excludedCount} "
            . "fs_pull={$filesPulled} fs_push={$filesPushed} fs_bytes={$bytesTransferred}"
        );

        return self::SUCCESS;
    }

    /**
     * Retorna el HandlerStack persistente con CurlHandler para reutilizar UN MISMO socket TCP/TLS Keep-Alive
     * durante toda la sincronización y evitar bloqueos PORTFLOOD / Imunify360 en cPanel/Neubox.
     */
    protected function getSharedHandler(): HandlerStack
    {
        if ($this->sharedHandler === null) {
            $this->sharedHandler = HandlerStack::create(new CurlHandler([
                'handle_factory' => new CurlFactory(5),
            ]));
        }

        return $this->sharedHandler;
    }

    /**
     * Reinicia el pool de sockets cURL tras una caída o corte de firewall remoto.
     */
    protected function resetSharedHandler(): void
    {
        $this->sharedHandler = null;
    }

    /**
     * Cliente HTTP preconfigurado con Keep-Alive persistente, connectTimeout amplio (25s) y compresión GZIP.
     */
    protected function httpClient(int $timeout = 90, int $connectTimeout = 25): PendingRequest
    {
        $options = [
            'handler' => $this->getSharedHandler(),
            'decode_content' => true,
            'headers' => [
                'Connection' => 'keep-alive',
                'Keep-Alive' => 'timeout=60, max=1000',
                'Accept-Encoding' => 'gzip, deflate',
            ],
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 30,
                CURLOPT_TCP_KEEPINTVL => 15,
                CURLOPT_FORBID_REUSE => false,
                CURLOPT_FRESH_CONNECT => false,
            ],
        ];

        $client = Http::connectTimeout($connectTimeout)
            ->timeout($timeout)
            ->withOptions($options);

        if (!config('sync.ssl_verify', false)) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Ejecuta una petición HTTP con auto-recuperación y enfriamiento progresivo (3s, 6s, 12s, 20s)
     * ante bloqueos temporales del firewall de Neubox (cURL 28 / cURL 7 / 429 / 502 / 503 / 504).
     *
     * @param  callable(): \Illuminate\Http\Client\Response  $requestCallback
     */
    protected function sendWithResilience(callable $requestCallback, string $contextLabel): \Illuminate\Http\Client\Response
    {
        $backoffSeconds = [3, 6, 12, 20];
        $maxAttempts = count($backoffSeconds) + 1;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $response = $requestCallback();

                if (!in_array($response->status(), [429, 500, 502, 503, 504], true) || $attempt === $maxAttempts) {
                    return $response;
                }

                $wait = $backoffSeconds[$attempt - 1];
                $this->resetSharedHandler();
                $this->waitWithCooldownNotice($contextLabel, $attempt, $maxAttempts - 1, $wait, 'HTTP ' . $response->status());
            } catch (\Throwable $e) {
                $lastException = $e;
                if ($attempt === $maxAttempts) {
                    throw $e;
                }

                $wait = $backoffSeconds[$attempt - 1];
                $this->resetSharedHandler();
                $shortReason = str_contains($e->getMessage(), 'cURL error 28')
                    ? 'Timeout de conexión'
                    : (str_contains($e->getMessage(), 'cURL error 7') ? 'Enfriando puerto 443' : 'Corte temporal de red');

                $this->waitWithCooldownNotice($contextLabel, $attempt, $maxAttempts - 1, $wait, $shortReason);
            }
        }

        throw $lastException ?? new \RuntimeException("Fallo de red tras {$maxAttempts} intentos en {$contextLabel}");
    }

    /**
     * Muestra cuenta regresiva en vivo durante pausas de reconexión sin ensuciar la consola.
     */
    protected function waitWithCooldownNotice(string $contextLabel, int $retryNum, int $maxRetries, int $seconds, string $reason): void
    {
        $this->logSync("[NET-BACKOFF] {$contextLabel}: reason='{$reason}' attempt={$retryNum}/{$maxRetries} backoff={$seconds}s", 'warning');

        for ($remaining = $seconds; $remaining > 0; $remaining--) {
            $this->renderLiveLine(
                "\033[33m[BACKOFF {$retryNum}/{$maxRetries}]\033[0m {$contextLabel} | reason={$reason} | retry_in=\033[1;33m{$remaining}s\033[0m"
            );
            sleep(1);
        }
    }

    /**
     * Precarga en 2 únicas consultas SQL todo el esquema local y todos los checkpoints.
     */
    protected function preloadLocalMetadata(): void
    {
        $this->localSchema = [];
        $this->checkpointsCache = [];

        try {
            $rows = DB::select(
                'SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                 ORDER BY TABLE_NAME, ORDINAL_POSITION'
            );

            foreach ($rows as $row) {
                $t = (string) $row->TABLE_NAME;
                $col = (string) $row->COLUMN_NAME;
                $key = (string) ($row->COLUMN_KEY ?? '');

                if (!isset($this->localSchema[$t])) {
                    $this->localSchema[$t] = [
                        'columns' => [],
                        'primary_key' => 'id',
                        'has_updated_at' => false,
                        'has_created_at' => false,
                    ];
                }

                $this->localSchema[$t]['columns'][$col] = 1;

                if ($key === 'PRI' && $this->localSchema[$t]['primary_key'] === 'id') {
                    $this->localSchema[$t]['primary_key'] = $col;
                }
                if ($col === 'updated_at') {
                    $this->localSchema[$t]['has_updated_at'] = true;
                } elseif ($col === 'created_at') {
                    $this->localSchema[$t]['has_created_at'] = true;
                }
            }
        } catch (\Throwable $e) {
            // Fallback por si information_schema estuviera restringido
            foreach ($this->getSyncableTablesFallback() as $t) {
                if (Schema::hasTable($t)) {
                    $cols = array_flip(Schema::getColumnListing($t));
                    $this->localSchema[$t] = [
                        'columns' => $cols,
                        'primary_key' => $this->queryPrimaryKey($t),
                        'has_updated_at' => isset($cols['updated_at']),
                        'has_created_at' => isset($cols['created_at']),
                    ];
                }
            }
        }

        $this->hasCheckpointsTable = isset($this->localSchema['sync_checkpoints']);

        if ($this->hasCheckpointsTable) {
            try {
                $allCheckpoints = DB::table('sync_checkpoints')->get();
                foreach ($allCheckpoints as $cp) {
                    $this->checkpointsCache["{$cp->channel}:{$cp->table_name}"] = $cp;
                }
            } catch (\Throwable $e) {
                $this->checkpointsCache = [];
            }
        }
    }

    /**
     * Carga huellas MD5 locales para tablas sin timestamps desde storage/app/sync_snapshot_hashes.json.
     */
    protected function loadSnapshotHashes(): void
    {
        $path = storage_path('app/sync_snapshot_hashes.json');
        if (is_file($path)) {
            $decoded = json_decode((string) @file_get_contents($path), true);
            if (is_array($decoded)) {
                $this->snapshotHashes = [
                    'pull' => is_array($decoded['pull'] ?? null) ? $decoded['pull'] : [],
                    'push' => is_array($decoded['push'] ?? null) ? $decoded['push'] : [],
                ];
            }
        }
        $this->snapshotHashesDirty = false;
    }

    /**
     * Persiste las huellas MD5 actualizadas de tablas sin timestamps.
     */
    protected function saveSnapshotHashes(): void
    {
        if (!$this->snapshotHashesDirty) {
            return;
        }

        $path = storage_path('app/sync_snapshot_hashes.json');
        @file_put_contents($path, json_encode($this->snapshotHashes, JSON_PRETTY_PRINT));
        $this->snapshotHashesDirty = false;
    }

    /**
     * Consulta en lote a la nube qué tablas tienen novedades respecto a nuestros checkpoints.
     * Si el servidor aún no tiene /api/sync/changes (404/405), hace preflight paralelo con Http::pool().
     *
     * @param  array<int, string>  $tables
     * @return array<string, bool>|null  Mapa [tabla => true] de tablas con cambios, o null para revisar todas.
     */
    protected function detectRemoteChangedTables(array $tables, string $cloudUrl, string $token): ?array
    {
        $timeout = (int) config('sync.timeout', 90);
        $changedMap = [];

        // Construir mapa de estados locales para enviar en bloques < 12 KB (límite Nginx Neubox 15 KB)
        $allStates = [];
        foreach ($tables as $table) {
            $cp = $this->getCheckpoint('pull', $table);
            $meta = $this->localSchema[$table] ?? null;
            $isNoTimestamp = $meta && !$meta['has_updated_at'] && !$meta['has_created_at'];

            $state = [
                's' => $cp ? $cp->last_synced_at : null,
                'i' => $cp ? $cp->last_synced_id : null,
            ];
            if ($isNoTimestamp && !empty($this->snapshotHashes['pull'][$table])) {
                $state['h'] = $this->snapshotHashes['pull'][$table];
            }
            $allStates[$table] = $state;
        }

        // Dividir en lotes de 55 tablas (~5-7 KB por petición)
        $chunks = array_chunk($allStates, 55, true);
        $checkedSoFar = 0;
        $totalToCheck = count($tables);
        $preflightStart = microtime(true);

        foreach ($chunks as $stateChunk) {
            $this->renderProgressLine(
                'PREFLIGHT',
                'POST /api/sync/changes',
                'Evaluating remote delta checkpoints',
                $checkedSoFar,
                $totalToCheck,
                $preflightStart
            );

            try {
                $result = $this->postChangesChunk($stateChunk, $cloudUrl, $token, $timeout);

                if ($result['unsupported']) {
                    $this->clearLiveLine();
                    // Si el servidor remoto aún no expone /api/sync/changes (HTTP 404), es mucho más rápido
                    // escanear directamente sobre el socket Keep-Alive con limit=500 (evita doble round-trip TLS).
                    $this->line('<fg=yellow>[PRE ]</> endpoint=/api/sync/changes status=404_NOT_DEPLOYED fallback=DIRECT_KEEPALIVE_CURSOR');
                    return null;
                }

                if (!$result['success']) {
                    $this->clearLiveLine();
                    return null;
                }

                foreach ($result['changed_tables'] as $changedTable) {
                    $changedMap[(string) $changedTable] = true;
                }
                $checkedSoFar += count($stateChunk);
            } catch (\Throwable $e) {
                $this->clearLiveLine();
                return null;
            }
        }

        $this->clearLiveLine();
        $preMs = (int) round((microtime(true) - $preflightStart) * 1000);
        $changedCount = count($changedMap);
        $cleanCount = max(0, $totalToCheck - $changedCount);
        $this->line(
            "<fg=green>[PRE ]</> endpoint=/api/sync/changes  status=200_OK  time={$preMs}ms"
            . "  dirty_tables=<fg=yellow>{$changedCount}</>  clean_skipped=<fg=gray>{$cleanCount}</>"
        );

        return $changedMap;
    }

    /**
     * Envía un bloque de estados a /api/sync/changes garantizando tamaño < 14 KB.
     */
    protected function postChangesChunk(array $stateChunk, string $cloudUrl, string $token, int $timeout): array
    {
        $payload = ['states' => $stateChunk];
        $json = json_encode($payload);

        if (strlen((string) $json) > 14000 && count($stateChunk) > 1) {
            $mid = (int) ceil(count($stateChunk) / 2);
            $part1 = array_slice($stateChunk, 0, $mid, true);
            $part2 = array_slice($stateChunk, $mid, null, true);

            $res1 = $this->postChangesChunk($part1, $cloudUrl, $token, $timeout);
            if ($res1['unsupported'] || !$res1['success']) {
                return $res1;
            }
            $res2 = $this->postChangesChunk($part2, $cloudUrl, $token, $timeout);
            if ($res2['unsupported'] || !$res2['success']) {
                return $res2;
            }

            return [
                'success' => true,
                'unsupported' => false,
                'changed_tables' => array_merge($res1['changed_tables'], $res2['changed_tables']),
            ];
        }

        $response = $this->httpClient($timeout)
            ->withHeaders([
                'X-Sync-Token' => $token,
                'Accept' => 'application/json',
            ])
            ->post("{$cloudUrl}/api/sync/changes", $payload);

        if (in_array($response->status(), [404, 405], true)) {
            return ['success' => false, 'unsupported' => true, 'changed_tables' => []];
        }

        if ($response->successful() && $response->json('success') === true) {
            return [
                'success' => true,
                'unsupported' => false,
                'changed_tables' => $response->json('changed_tables', []),
            ];
        }

        return ['success' => false, 'unsupported' => false, 'changed_tables' => []];
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
     * Envía un lote de registros asegurando que el tamaño del payload HTTP
     * nunca supere 15 KB (15,000 bytes) para evitar bloqueos del proxy Nginx en Neubox.
     */
    protected function postPushBatch(string $table, array $records, string $cloudUrl, string $token, int $timeout): array
    {
        $payload = [
            'table' => $table,
            'records' => $records,
        ];
        $json = json_encode($payload);

        // Si el payload excede 15 KB y hay más de 1 registro, dividir recursivamente a la mitad
        if (strlen($json) > 15000 && count($records) > 1) {
            $mid = (int) ceil(count($records) / 2);
            $part1 = array_slice($records, 0, $mid);
            $part2 = array_slice($records, $mid);

            $res1 = $this->postPushBatch($table, $part1, $cloudUrl, $token, $timeout);
            if (!$res1['success']) {
                return $res1;
            }
            $res2 = $this->postPushBatch($table, $part2, $cloudUrl, $token, $timeout);
            return [
                'success' => $res2['success'],
                'processed' => $res1['processed'] + $res2['processed'],
                'error' => $res2['error'] ?? null,
            ];
        }

        $response = $this->sendWithResilience(function () use ($timeout, $token, $cloudUrl, $payload) {
            return $this->httpClient($timeout)
                ->withHeaders([
                    'X-Sync-Token' => $token,
                    'Accept' => 'application/json',
                ])
                ->post("{$cloudUrl}/api/sync/push", $payload);
        }, "PUSH {$table}");

        if ($response->successful() && $response->json('success') === true) {
            return [
                'success' => true,
                'processed' => (int) ($response->json('processed') ?? count($records)),
            ];
        }

        $errorMsg = $response->json('message') ?? ('Error HTTP ' . $response->status());
        return [
            'success' => false,
            'processed' => 0,
            'error' => $errorMsg,
        ];
    }

    /**
     * Sube registros modificados localmente hacia la nube (PUSH).
     */
    protected function syncPush(string $table, string $cloudUrl, string $token, bool $force, bool $dryRun, ?float $scanStartedAt = null): int
    {
        $checkpoint = $this->getCheckpoint('push', $table);
        $lastSyncedAt = (!$force && $checkpoint) ? $checkpoint->last_synced_at : null;
        $lastSyncedId = (!$force && $checkpoint) ? $checkpoint->last_synced_id : null;
        $batchSize = (int) config('sync.batch_size', 40);

        $meta = $this->localSchema[$table] ?? null;
        $primaryKey = $meta ? $meta['primary_key'] : $this->getPrimaryKey($table);
        $hasUpdatedAt = $meta ? $meta['has_updated_at'] : Schema::hasColumn($table, 'updated_at');
        $hasCreatedAt = $meta ? $meta['has_created_at'] : Schema::hasColumn($table, 'created_at');
        $timeout = (int) config('sync.timeout', 90);
        $totalPushed = 0;
        $count = -1;
        $page = 0;
        $tableStart = microtime(true);
        $tableDesc = $this->describeTable($table);
        $totalToPush = null;

        // Optimización para tablas catálogo sin timestamps (ej. clases, procesos, maquinas):
        // Si tienen <= 3000 filas y su huella MD5 no cambió desde el último ciclo, omitir re-subida completa
        $currentLocalSnapshotHash = null;
        if (!$hasUpdatedAt && !$hasCreatedAt && !$force) {
            try {
                $totalLocal = DB::table($table)->count();
                if ($totalLocal === 0) {
                    return 0;
                }
                if ($totalLocal <= 3000) {
                    $allLocalRows = DB::table($table)->orderBy($primaryKey, 'asc')->get();
                    $currentLocalSnapshotHash = md5(json_encode($allLocalRows));
                    if (($this->snapshotHashes['push'][$table] ?? null) === $currentLocalSnapshotHash) {
                        return 0;
                    }
                    // Si sí cambió el catálogo, reiniciar recorrido desde 0 para sincronizar cualquier fila editada
                    $lastSyncedId = null;
                }
            } catch (\Throwable $e) {
            }
        }

        do {
            $page++;
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
                // Para tablas sin timestamps (ej. clases, procesos, maquinas)
                if (!empty($lastSyncedId)) {
                    $query->where($primaryKey, '>', $lastSyncedId);
                }
                $query->orderBy($primaryKey, 'asc');
            }

            if ($page === 1) {
                $this->renderScanningLine('PUSH', $table, $tableDesc, $scanStartedAt);
            }

            $records = (clone $query)->limit($batchSize)->get();
            $count = $records->count();

            if ($count === 0) {
                if (!$hasUpdatedAt && !$hasCreatedAt) {
                    if ($currentLocalSnapshotHash !== null) {
                        $this->snapshotHashes['push'][$table] = $currentLocalSnapshotHash;
                        $this->snapshotHashesDirty = true;
                        if (!empty($lastSyncedId)) {
                            $this->updateCheckpoint('push', $table, null, null, 0, 'success');
                        }
                    }
                }
                break;
            }

            if ($page === 1) {
                if ($count >= $batchSize) {
                    try {
                        $totalToPush = (clone $query)->count();
                    } catch (\Throwable $e) {
                        $totalToPush = null;
                    }
                } else {
                    $totalToPush = $count;
                }
            }

            $this->renderProgressLine('PUSH', $table, $tableDesc, $totalPushed, $totalToPush, $tableStart, $page);

            if ($dryRun) {
                $totalPushed += $count;
                $this->renderProgressLine('PUSH', $table, $tableDesc . ' [DRY_RUN]', $totalPushed, $totalToPush, $tableStart, $page);
                if ($count < $batchSize) {
                    $this->renderCompletedTableLine('PUSH', $table, $tableDesc, $totalPushed, microtime(true) - $tableStart, true, $page);
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
                $batchResult = $this->postPushBatch($table, $records->toArray(), $cloudUrl, $token, $timeout);

                if ($batchResult['success']) {
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
                    $this->renderProgressLine('PUSH', $table, $tableDesc, $totalPushed, $totalToPush, $tableStart, $page);

                    if ($count < $batchSize) {
                        $tableElapsed = microtime(true) - $tableStart;
                        $this->renderCompletedTableLine('PUSH', $table, $tableDesc, $totalPushed, $tableElapsed, false, $page);
                        $this->logSync(
                            "[PUSH-OK] {$this->formatTableCounter()} table={$table} module='{$tableDesc}' rows={$totalPushed} batches={$page} elapsed=" . $this->formatDuration($tableElapsed)
                        );
                        if (!$hasUpdatedAt && !$hasCreatedAt && $currentLocalSnapshotHash !== null) {
                            $this->snapshotHashes['push'][$table] = $currentLocalSnapshotHash;
                            $this->snapshotHashesDirty = true;
                            $this->updateCheckpoint('push', $table, null, null, 0, 'success');
                        }
                        break;
                    }
                } else {
                    $this->clearLiveLine();
                    $errorMsg = $batchResult['error'] ?? 'Unknown HTTP error';
                    $this->line("<fg=red>[ERR ]</> {$this->formatTableCounter()} op=PUSH table={$table} batch={$page} error=\"{$errorMsg}\"");
                    $this->logSync("[PUSH-ERR] table={$table} batch={$page} error={$errorMsg}", 'error');
                    $this->updateCheckpoint('push', $table, $lastSyncedAt, $lastSyncedId, 0, 'error', $errorMsg);
                    break;
                }
            } catch (\Throwable $e) {
                $this->clearLiveLine();
                $this->line("<fg=red>[EXCP]</> {$this->formatTableCounter()} op=PUSH table={$table} batch={$page} exception=\"" . $e->getMessage() . '"');
                $this->logSync("[PUSH-EXCP] table={$table} batch={$page} exception=" . $e->getMessage(), 'error');
                $this->updateCheckpoint('push', $table, $lastSyncedAt, $lastSyncedId, 0, 'error', $e->getMessage());
                break;
            }
        } while ($count >= $batchSize);

        if ($totalPushed > 0 && $count === 0) {
            $tableElapsed = microtime(true) - $tableStart;
            $this->renderCompletedTableLine('PUSH', $table, $tableDesc, $totalPushed, $tableElapsed, $dryRun, $page);
            $this->logSync(
                "[PUSH-OK] {$this->formatTableCounter()} table={$table} module='{$tableDesc}' rows={$totalPushed} batches={$page} elapsed=" . $this->formatDuration($tableElapsed)
            );
        }

        return $totalPushed;
    }

    /**
     * Descarga registros nuevos o modificados desde la nube (PULL).
     */
    protected function syncPull(string $table, string $cloudUrl, string $token, bool $force, bool $dryRun, ?float $scanStartedAt = null): int
    {
        $checkpoint = $this->getCheckpoint('pull', $table);
        $since = (!$force && $checkpoint) ? $checkpoint->last_synced_at : null;
        $lastId = (!$force && $checkpoint) ? $checkpoint->last_synced_id : null;
        $clientSnapshotHash = (!$force) ? ($this->snapshotHashes['pull'][$table] ?? null) : null;
        $batchSize = (int) config('sync.pull_batch_size', 500);
        $timeout = (int) config('sync.timeout', 90);
        $totalApplied = 0;
        $hasMore = false;
        $count = -1;
        $page = 0;
        $tableStart = microtime(true);
        $tableDesc = $this->describeTable($table);
        $totalRemotePending = null;

        $meta = $this->localSchema[$table] ?? null;
        $validColumns = $meta ? $meta['columns'] : array_flip(Schema::getColumnListing($table));

        do {
            $page++;
            $count = -1;
            if ($page === 1) {
                $this->renderScanningLine('PULL', $table, $tableDesc, $scanStartedAt);
            } else {
                $this->renderProgressLine('PULL', $table, $tableDesc, $totalApplied, $totalRemotePending, $tableStart, $page);
            }

            // Micro-respiro suave en descargas masivas (> 5 lotes) para no disparar anti-DDoS / Imunify360 en Neubox
            if ($page > 5) {
                usleep(60000);
            }

            try {
                $response = $this->sendWithResilience(function () use ($timeout, $token, $cloudUrl, $table, $since, $lastId, $clientSnapshotHash, $page, $batchSize) {
                    return $this->httpClient($timeout)
                        ->withHeaders([
                            'X-Sync-Token' => $token,
                            'Accept' => 'application/json',
                        ])
                        ->get("{$cloudUrl}/api/sync/pull", [
                            'table' => $table,
                            'since' => $since,
                            'last_id' => $lastId,
                            'snapshot_hash' => $clientSnapshotHash,
                            'with_total' => ($page === 1) ? 1 : 0,
                            'limit' => $batchSize,
                        ]);
                }, "PULL {$table} (batch #{$page})");

                if (!$response->successful() || $response->json('success') !== true) {
                    $errorMsg = (string) ($response->json('message') ?? ('HTTP ' . $response->status()));
                    if ($response->status() === 400 && str_contains($errorMsg, 'no está permitida o no existe')) {
                        // Tabla local temporal que no existe en la nube: omitir sin error ruidoso
                        break;
                    }
                    $this->clearLiveLine();
                    $this->line("<fg=red>[ERR ]</> {$this->formatTableCounter()} op=PULL table={$table} batch={$page} error=\"{$errorMsg}\"");
                    $this->logSync("[PULL-ERR] table={$table} batch={$page} error={$errorMsg}", 'error');
                    break;
                }

                $records = $response->json('records', []);
                $count = count($records);
                $isSnapshot = (bool) $response->json('is_snapshot', false);
                $remoteSnapshotHash = $response->json('snapshot_hash');

                if ($page === 1) {
                    $rawTotal = $response->json('total_pending');
                    if (is_numeric($rawTotal) && (int) $rawTotal > 0) {
                        $totalRemotePending = (int) $rawTotal;
                    }
                }

                if ($count === 0) {
                    if ($isSnapshot && !empty($remoteSnapshotHash) && $clientSnapshotHash !== $remoteSnapshotHash) {
                        $this->snapshotHashes['pull'][$table] = (string) $remoteSnapshotHash;
                        $this->snapshotHashesDirty = true;
                    }
                    break;
                }

                // Si el servidor remoto (versión previa) devolvió snapshot completo idéntico a nuestra huella local, omitir re-escritura en BD
                if ($isSnapshot && !$force) {
                    $computedRemoteHash = !empty($remoteSnapshotHash) ? (string) $remoteSnapshotHash : md5(json_encode($records));
                    if ($clientSnapshotHash !== null && hash_equals($clientSnapshotHash, $computedRemoteHash)) {
                        break;
                    }
                    $remoteSnapshotHash = $computedRemoteHash;
                }

                $hasMore = (bool) $response->json('has_more', false);
                if ($page === 1 && !$hasMore && $totalRemotePending === null) {
                    $totalRemotePending = $count;
                }

                $this->renderProgressLine('PULL', $table, $tableDesc, $totalApplied, $totalRemotePending, $tableStart, $page);

                if ($dryRun) {
                    $totalApplied += $count;
                    $since = $response->json('next_since');
                    $lastId = $response->json('next_id');
                    $this->renderProgressLine('PULL', $table, $tableDesc . ' [DRY_RUN]', $totalApplied, $totalRemotePending, $tableStart, $page);
                    if (!$hasMore) {
                        $this->renderCompletedTableLine('PULL', $table, $tableDesc, $totalApplied, microtime(true) - $tableStart, true, $page);
                    }
                    continue;
                }

                $primaryKey = $response->json('primary_key', $meta['primary_key'] ?? 'id');

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

                    if ($isSnapshot && !empty($remoteSnapshotHash)) {
                        $this->snapshotHashes['pull'][$table] = (string) $remoteSnapshotHash;
                        // Sincronizar huella local post-pull para evitar eco innecesario en el siguiente PUSH
                        try {
                            $localRowsAfterPull = DB::table($table)->orderBy($primaryKey, 'asc')->get();
                            $this->snapshotHashes['push'][$table] = md5(json_encode($localRowsAfterPull));
                        } catch (\Throwable $e) {
                        }
                        $this->snapshotHashesDirty = true;
                    }

                    if ($hasMore) {
                        $this->renderProgressLine('PULL', $table, $tableDesc, $totalApplied, $totalRemotePending, $tableStart, $page);
                    } else {
                        $tableElapsed = microtime(true) - $tableStart;
                        $this->renderCompletedTableLine('PULL', $table, $tableDesc, $totalApplied, $tableElapsed, false, $page);
                        $this->logSync(
                            "[PULL-OK] {$this->formatTableCounter()} table={$table} module='{$tableDesc}' rows={$totalApplied} batches={$page} elapsed=" . $this->formatDuration($tableElapsed)
                        );
                    }
                } catch (\Throwable $e) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    DB::rollBack();
                    $this->clearLiveLine();
                    $this->line("<fg=red>[ERR ]</> {$this->formatTableCounter()} op=PULL_UPSERT table={$table} batch={$page} error=\"" . $e->getMessage() . '"');
                    $this->logSync("[PULL-DB-ERR] table={$table} batch={$page} error=" . $e->getMessage(), 'error');
                    $this->updateCheckpoint('pull', $table, $since, $lastId, 0, 'error', $e->getMessage());
                    break;
                }
            } catch (\Throwable $e) {
                $this->clearLiveLine();
                $this->line("<fg=red>[EXCP]</> {$this->formatTableCounter()} op=PULL_NET table={$table} batch={$page} exception=\"" . $e->getMessage() . '"');
                $this->logSync("[PULL-EXCP] table={$table} batch={$page} exception=" . $e->getMessage(), 'error');
                break;
            }
        } while ($hasMore);

        if ($totalApplied > 0 && $count === 0) {
            $tableElapsed = microtime(true) - $tableStart;
            $this->renderCompletedTableLine('PULL', $table, $tableDesc, $totalApplied, $tableElapsed, $dryRun, $page);
            $this->logSync(
                "[PULL-OK] {$this->formatTableCounter()} table={$table} module='{$tableDesc}' rows={$totalApplied} batches={$page} elapsed=" . $this->formatDuration($tableElapsed)
            );
        }

        return $totalApplied;
    }

    /**
     * Obtiene el checkpoint de sincronización desde la caché precargada en memoria.
     */
    protected function getCheckpoint(string $channel, string $table): ?object
    {
        if (!$this->hasCheckpointsTable) {
            return null;
        }

        return $this->checkpointsCache["{$channel}:{$table}"] ?? null;
    }

    /**
     * Actualiza o crea el checkpoint en la base de datos local y sincroniza la caché en memoria.
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
        if (!$this->hasCheckpointsTable) {
            return;
        }

        $cacheKey = "{$channel}:{$table}";
        $existing = $this->checkpointsCache[$cacheKey] ?? null;
        $newCount = ($existing ? (int) $existing->records_count : 0) + $recordsCount;
        $now = now();

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
                'updated_at' => $now,
            ]
        );

        $this->checkpointsCache[$cacheKey] = (object) [
            'channel' => $channel,
            'table_name' => $table,
            'last_synced_at' => $lastSyncedAt,
            'last_synced_id' => $lastSyncedId,
            'records_count' => $newCount,
            'status' => $status,
            'last_error' => $lastError,
            'updated_at' => $now,
        ];
    }

    /**
     * Determina la clave primaria de la tabla usando el esquema en memoria o consulta directa.
     */
    protected function getPrimaryKey(string $table): string
    {
        if (isset($this->localSchema[$table]['primary_key'])) {
            return $this->localSchema[$table]['primary_key'];
        }

        return $this->queryPrimaryKey($table);
    }

    protected function queryPrimaryKey(string $table): string
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
     * Retorna todas las tablas sincronizables ordenadas aprovechando el esquema en memoria.
     */
    protected function getSyncableTables(): array
    {
        if (!empty($this->localSchema)) {
            $ignored = config('sync.ignore_tables', []);
            $priority = config('sync.priority_tables', []);

            $dbTables = [];
            foreach (array_keys($this->localSchema) as $tableName) {
                if (!in_array($tableName, $ignored, true)) {
                    $dbTables[] = $tableName;
                }
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

        return $this->getSyncableTablesFallback();
    }

    /**
     * Fallback clásico mediante SHOW TABLES si information_schema no estuvo disponible.
     */
    protected function getSyncableTablesFallback(): array
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
     * Sincroniza bidireccionalmente los archivos físicos de DOCUMENTACION_GIS con barra de progreso técnica.
     */
    protected function syncPhysicalFiles(string $cloudUrl, string $token, string $direction, bool $dryRun): array
    {
        $baseRelative = (string) config('sync.files_relative_dir', 'DOCUMENTACION_GIS');
        $baseDir = storage_path('app/' . $baseRelative);
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        $pulled = 0;
        $pushed = 0;
        $bytesMoved = 0;
        $timeout = (int) config('sync.timeout', 90);

        try {
            $this->renderLiveLine('[FS  ] GET /api/sync/files/manifest (fetching remote file index)...');
            $response = $this->httpClient($timeout)
                ->withHeaders(['X-Sync-Token' => $token])
                ->get("{$cloudUrl}/api/sync/files/manifest");
            $this->clearLiveLine();

            if (!$response->successful() || $response->json('success') !== true) {
                $this->line('<fg=yellow>[WARN]</> fs_manifest_unavailable status=HTTP_' . $response->status());
                return ['pulled' => 0, 'pushed' => 0, 'bytes' => 0];
            }

            $rawCloudFiles = $response->json('files', []);
            $cloudFiles = [];
            foreach ($rawCloudFiles as $cf) {
                $cloudFiles[$cf['path']] = $cf;
            }

            $this->renderLiveLine('[FS  ] Scanning local filesystem tree storage/app/' . $baseRelative . ' ...');
            $localFiles = $this->getLocalFilesManifest($baseDir);
            $this->clearLiveLine();

            // Mapas case-insensitive para evitar falsos positivos entre Linux (cPanel) y Windows (NTFS) por mayúsculas/minúsculas
            $localByLower = [];
            foreach ($localFiles as $lPath => $lInfo) {
                $localByLower[mb_strtolower($lPath)] = $lInfo;
            }
            $cloudByLower = [];
            foreach ($cloudFiles as $cPath => $cInfo) {
                $cloudByLower[mb_strtolower($cPath)] = $cInfo;
            }

            // Pre-calcular colas de descarga y subida para mostrar porcentaje real
            $pullQueue = [];
            if (in_array($direction, ['pull', 'both'], true)) {
                foreach ($cloudFiles as $path => $cloudInfo) {
                    $localMatch = $localFiles[$path] ?? $localByLower[mb_strtolower($path)] ?? null;
                    if ($localMatch === null || ($cloudInfo['mtime'] > $localMatch['mtime'] && $cloudInfo['size'] !== $localMatch['size'])) {
                        $pullQueue[$path] = $cloudInfo;
                    }
                }
            }

            $pushQueue = [];
            if (in_array($direction, ['push', 'both'], true)) {
                foreach ($localFiles as $path => $localInfo) {
                    $cloudMatch = $cloudFiles[$path] ?? $cloudByLower[mb_strtolower($path)] ?? null;
                    if ($cloudMatch === null || ($localInfo['mtime'] > $cloudMatch['mtime'] && $localInfo['size'] !== $cloudMatch['size'])) {
                        $pushQueue[$path] = $localInfo;
                    }
                }
            }

            $this->line(
                '<fg=green>[FS  ]</> root=' . $baseRelative
                . '  cloud_files=' . number_format(count($cloudFiles))
                . '  local_files=' . number_format(count($localFiles))
                . '  pull_queue=' . count($pullQueue)
                . '  push_queue=' . count($pushQueue)
            );

            if (empty($pullQueue) && empty($pushQueue)) {
                return ['pulled' => 0, 'pushed' => 0, 'bytes' => 0];
            }

            // 3. PULL: Descargar cola de archivos nuevos/actualizados
            if (!empty($pullQueue)) {
                $totalPullFiles = count($pullQueue);
                $pullStart = microtime(true);
                $idx = 0;

                foreach ($pullQueue as $path => $cloudInfo) {
                    $idx++;
                    $shortFile = basename($path);
                    $this->renderProgressLine('FS_PULL', $shortFile, 'Downloading binary stream', $idx - 1, $totalPullFiles, $pullStart, $idx);

                    if ($dryRun) {
                        $pulled++;
                        $bytesMoved += (int) ($cloudInfo['size'] ?? 0);
                        continue;
                    }

                    $downRes = $this->sendWithResilience(function () use ($timeout, $token, $cloudUrl, $path) {
                        return $this->httpClient($timeout)
                            ->withHeaders(['X-Sync-Token' => $token])
                            ->get("{$cloudUrl}/api/sync/files/download", ['path' => $path]);
                    }, "FS_PULL {$shortFile}");

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

                        $relStorage = "{$baseRelative}/{$path}";
                        if (str_starts_with($path, 'DIBUJOS_MAQUINADOS/')) {
                            \App\Console\Commands\SyncMaquinadosDocs::syncSingleFile($relStorage, 'dibujo', 'upsert');
                        } elseif (str_starts_with($path, 'AYUDAS_MAQUINADOS/')) {
                            \App\Console\Commands\SyncMaquinadosDocs::syncSingleFile($relStorage, 'ayuda', 'upsert');
                        }

                        $pulled++;
                        $bytesMoved += (int) ($cloudInfo['size'] ?? 0);
                    } else {
                        $this->clearLiveLine();
                        $this->line("<fg=red>[ERR ]</> op=FS_PULL path=\"{$path}\" status=HTTP_" . $downRes->status());
                    }
                }

                $this->clearLiveLine();
                $this->line(
                    "<fg=green>[OK  ]</> op=FS_PULL  files={$pulled}/{$totalPullFiles}  elapsed="
                    . $this->formatDuration(microtime(true) - $pullStart)
                );
            }

            // 4. PUSH: Subir cola de archivos locales nuevos/actualizados
            if (!empty($pushQueue)) {
                $totalPushFiles = count($pushQueue);
                $pushStart = microtime(true);
                $idx = 0;

                foreach ($pushQueue as $path => $localInfo) {
                    $idx++;
                    $shortFile = basename($path);
                    $this->renderProgressLine('FS_PUSH', $shortFile, 'Uploading multipart stream', $idx - 1, $totalPushFiles, $pushStart, $idx);

                    if ($dryRun) {
                        $pushed++;
                        $bytesMoved += (int) ($localInfo['size'] ?? 0);
                        continue;
                    }

                    $sourcePath = "{$baseDir}/{$path}";
                    if (file_exists($sourcePath)) {
                        $upRes = $this->sendWithResilience(function () use ($timeout, $token, $cloudUrl, $sourcePath, $path, $localInfo) {
                            return $this->httpClient($timeout)
                                ->withHeaders(['X-Sync-Token' => $token])
                                ->attach('file', file_get_contents($sourcePath), basename($sourcePath))
                                ->post("{$cloudUrl}/api/sync/files/upload", [
                                    'path' => $path,
                                    'mtime' => $localInfo['mtime'],
                                ]);
                        }, "FS_PUSH {$shortFile}");

                        if ($upRes->successful() && $upRes->json('success') === true) {
                            $pushed++;
                            $bytesMoved += (int) ($localInfo['size'] ?? 0);
                        } else {
                            $this->clearLiveLine();
                            $this->line("<fg=red>[ERR ]</> op=FS_PUSH path=\"{$path}\" status=HTTP_" . $upRes->status());
                        }
                    }
                }

                $this->clearLiveLine();
                $this->line(
                    "<fg=green>[OK  ]</> op=FS_PUSH  files={$pushed}/{$totalPushFiles}  elapsed="
                    . $this->formatDuration(microtime(true) - $pushStart)
                );
            }
        } catch (\Throwable $e) {
            $this->clearLiveLine();
            $this->line('<fg=red>[EXCP]</> op=FS_SYNC exception="' . $e->getMessage() . '"');
            $this->logSync('[FS-EXCP] exception=' . $e->getMessage(), 'error');
        }

        return ['pulled' => $pulled, 'pushed' => $pushed, 'bytes' => $bytesMoved];
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
     * Inicializa el soporte ANSI/VT100 en consolas interactivas (Windows PowerShell / Terminal)
     * y lo desactiva automáticamente cuando el comando corre en segundo plano vía Cron hacia sync.log.
     */
    protected function initInteractiveTerminal(): void
    {
        $isTty = false;
        if (defined('STDOUT')) {
            if (function_exists('stream_isatty')) {
                $isTty = @stream_isatty(STDOUT);
            }
            if ($isTty && function_exists('sapi_windows_vt100_support')) {
                @sapi_windows_vt100_support(STDOUT, true);
            }
        }

        $this->interactiveUi = $isTty && $this->output->isDecorated();

        try {
            $term = new \Symfony\Component\Console\Terminal();
            $w = $term->getWidth();
            $this->terminalWidth = ($w >= 60) ? $w : 96;
        } catch (\Throwable $e) {
            $this->terminalWidth = 96;
        }
    }

    /**
     * Formatea el indicador de tabla actual ej: [018/135].
     */
    protected function formatTableCounter(): string
    {
        if ($this->totalTablesCount <= 0 || $this->currentTableIndex <= 0) {
            return '';
        }

        $pad = max(2, strlen((string) $this->totalTablesCount));
        $cur = str_pad((string) $this->currentTableIndex, $pad, '0', STR_PAD_LEFT);
        $tot = str_pad((string) $this->totalTablesCount, $pad, '0', STR_PAD_LEFT);

        return "[{$cur}/{$tot}]";
    }

    /**
     * Muestra una línea efímera en la misma fila de la terminal sin ensuciar logs de Cron.
     */
    protected function renderLiveLine(string $text): void
    {
        if (!$this->interactiveUi) {
            return;
        }

        $frames = ['|', '/', '-', '\\'];
        $frame = $frames[$this->spinnerTick % count($frames)];
        $this->spinnerTick++;

        $this->output->write("\r\033[2K\033[36m[{$frame}]\033[0m {$text}");
    }

    /**
     * Muestra progreso global [SCAN 088/139] [#########...] 63% ajustado al ancho real de la consola para evitar saltos de línea.
     */
    protected function renderScanningLine(string $dir, string $table, string $desc, ?float $scanStartedAt = null): void
    {
        if (!$this->interactiveUi) {
            return;
        }

        $counter = $this->formatTableCounter();
        $barWidth = 12;

        if ($this->totalTablesCount > 1 && $this->currentTableIndex > 0) {
            $ratio = max(0.0, min(1.0, $this->currentTableIndex / $this->totalTablesCount));
            $pct = str_pad((int) round($ratio * 100) . '%', 4, ' ', STR_PAD_LEFT);
            $filled = (int) round($ratio * $barWidth);
            $empty = max(0, $barWidth - $filled);
            $bar = "\033[36m" . str_repeat('#', $filled) . "\033[90m" . str_repeat('.', $empty) . "\033[0m";

            $etaInfo = '';
            if ($scanStartedAt !== null && $this->currentTableIndex > 2) {
                $elapsed = max(0.01, microtime(true) - $scanStartedAt);
                $tblPerSec = $this->currentTableIndex / $elapsed;
                $remTbl = max(0, $this->totalTablesCount - $this->currentTableIndex);
                if ($tblPerSec > 0.05 && $remTbl > 0) {
                    $etaSec = (int) round($remTbl / $tblPerSec);
                    $etaInfo = ' | ' . number_format($tblPerSec, 1) . 't/s | eta=' . $this->formatDuration((float) $etaSec);
                }
            }

            $maxTblLen = max(12, min(24, $this->terminalWidth - 56));
            $shortTbl = mb_strlen($table) > $maxTblLen ? mb_substr($table, 0, $maxTblLen - 2) . '..' : $table;
            $this->output->write(
                "\r\033[2K\033[90m[SCAN]\033[0m {$counter} [{$bar}] \033[33m{$pct}\033[0m {$dir} \033[37m{$shortTbl}\033[0m\033[90m{$etaInfo}\033[0m"
            );
            return;
        }

        $this->renderLiveLine("\033[90m{$counter} {$dir}_CHECK\033[0m \033[37m{$table}\033[0m");
    }

    /**
     * Renderiza telemetría compacta que nunca excede el ancho de la ventana (evita que \r deje renglones repetidos):
     * [PULL] [025/138] piezas           [######......]  46% 52,000/114,142 | 924r/s | eta=1m 7s
     */
    protected function renderProgressLine(
        string $actionLabel,
        string $subject,
        string $description,
        int $current,
        ?int $total,
        float $startedAt,
        int $batchPage = 1
    ): void {
        if (!$this->interactiveUi) {
            return;
        }

        $this->spinnerTick++;
        $elapsed = max(0.001, microtime(true) - $startedAt);
        $rate = ($current > 0 && $elapsed >= 0.15) ? (int) round($current / $elapsed) : 0;
        $rateStr = $rate > 0 ? number_format($rate) . 'r/s' : 'init';
        $counter = $this->formatTableCounter();
        $counterPart = $counter !== '' ? " {$counter}" : '';
        $barWidth = 12;

        $tag = str_pad(substr($actionLabel, 0, 4), 4, ' ', STR_PAD_RIGHT);
        $subjMax = $this->terminalWidth < 95 ? 14 : 18;
        $shortSubj = str_pad(
            mb_strlen($subject) > $subjMax ? mb_substr($subject, 0, $subjMax - 2) . '..' : $subject,
            $subjMax,
            ' ',
            STR_PAD_RIGHT
        );

        if ($total !== null && $total > 0) {
            $effectiveCurrent = min($current, $total);
            $ratio = max(0.0, min(1.0, $effectiveCurrent / $total));
            $pct = (int) round($ratio * 100);
            $filled = (int) round($ratio * $barWidth);
            $empty = max(0, $barWidth - $filled);

            $bar = "\033[32m" . str_repeat('#', $filled) . "\033[90m" . str_repeat('.', $empty) . "\033[0m";
            $pctPad = str_pad($pct . '%', 4, ' ', STR_PAD_LEFT);
            $progressNums = number_format($current) . '/' . number_format($total);

            $etaStr = '';
            if ($rate > 0 && $current < $total && $this->terminalWidth >= 88) {
                $etaSec = max(1, (int) round(($total - $current) / $rate));
                $etaStr = ' | eta=' . $this->formatDuration((float) $etaSec);
            }

            $line = "\r\033[2K\033[36m[{$tag}]\033[0m\033[90m{$counterPart}\033[0m \033[1;37m{$shortSubj}\033[0m [{$bar}] \033[33m{$pctPad}\033[0m {$progressNums} | \033[36m{$rateStr}\033[0m{$etaStr}";
        } else {
            $pulseWidth = 4;
            $maxPos = $barWidth - $pulseWidth;
            $cycle = $maxPos * 2;
            $step = $cycle > 0 ? ($this->spinnerTick % $cycle) : 0;
            $pos = $step <= $maxPos ? $step : ($cycle - $step);

            $left = str_repeat('.', $pos);
            $mid = str_repeat('#', $pulseWidth);
            $right = str_repeat('.', max(0, $barWidth - $pos - $pulseWidth));
            $bar = "\033[90m{$left}\033[36m{$mid}\033[90m{$right}\033[0m";

            $curFormatted = number_format($current) . 'r';
            $elapsedStr = $this->formatDuration($elapsed);
            $line = "\r\033[2K\033[36m[{$tag}]\033[0m\033[90m{$counterPart}\033[0m \033[1;37m{$shortSubj}\033[0m [{$bar}] \033[33m{$curFormatted}\033[0m | p={$batchPage} | \033[36m{$rateStr}\033[0m | {$elapsedStr}";
        }

        $this->output->write($line);
    }

    /**
     * Limpia la línea temporal actual en consola interactiva.
     */
    protected function clearLiveLine(): void
    {
        if ($this->interactiveUi) {
            $this->output->write("\r\033[2K");
        }
    }

    /**
     * Imprime una fila tabular técnica compacta (<= 94 cols) al concluir la transferencia de una tabla.
     */
    protected function renderCompletedTableLine(
        string $dir,
        string $table,
        string $desc,
        int $totalRecords,
        float $elapsed,
        bool $dryRun,
        int $batches = 1
    ): void {
        $this->clearLiveLine();

        $counter = $this->formatTableCounter();
        $opTag = str_pad($dir, 4, ' ', STR_PAD_RIGHT);
        $statusTag = $dryRun ? '<fg=yellow>[SIM ]</>' : '<fg=green>[OK  ]</>';

        $tblCol = str_pad(
            mb_strlen($table) > 24 ? mb_substr($table, 0, 22) . '..' : $table,
            24,
            ' ',
            STR_PAD_RIGHT
        );

        $descClean = str_replace(' · ', '/', $desc);
        $descCol = str_pad(
            mb_strlen($descClean) > 22 ? mb_substr($descClean, 0, 20) . '..' : $descClean,
            22,
            ' ',
            STR_PAD_RIGHT
        );

        $rowsCol = str_pad(number_format($totalRecords), 8, ' ', STR_PAD_LEFT);
        $batchCol = str_pad('p=' . $batches, 5, ' ', STR_PAD_LEFT);
        $timeCol = str_pad($this->formatDuration($elapsed), 6, ' ', STR_PAD_LEFT);
        $rate = ($elapsed >= 0.05 && $totalRecords > 0) ? (int) round($totalRecords / $elapsed) : 0;
        $rateCol = $rate > 0 ? str_pad(number_format($rate) . 'r/s', 8, ' ', STR_PAD_LEFT) : '     n/a';

        $this->line(
            "{$statusTag} <fg=gray>{$counter}</> <fg=cyan>{$opTag}</> <fg=white;options=bold>{$tblCol}</> <fg=gray>| {$descCol} |</> rows=<fg=green;options=bold>{$rowsCol}</> <fg=gray>{$batchCol} {$timeCol} {$rateCol}</>"
        );
    }

    /**
     * Diccionario descriptivo industrial de tablas de Project Saavedra.
     */
    protected function describeTable(string $table): string
    {
        $catalog = [
            'perfiles' => 'Seguridad · Perfiles de Acceso',
            'puestos' => 'RH · Catálogo de Puestos',
            'users' => 'Usuarios · Personal y Operadores',
            'clases' => 'Catálogo · Clases de Moldura',
            'procesos' => 'Catálogo · Procesos de Planta',
            'maquinas' => 'Planta · Máquinas y Centros CNC',
            'molduras' => 'Ingeniería · Catálogo de Molduras',
            'orden_trabajo' => 'Producción · Órdenes de Trabajo (O.T.)',
            'metas' => 'Producción · Metas por Turno/Máquina',
            'parcialidades_ot' => 'Almacén · Parcialidades de O.T.',
            'remisiones_ot' => 'Almacén · Remisiones de Entrega',
            'herramientas_tecamac' => 'Planta Tecámac · Inventario Herramientas',
            'herramienta_imagenes' => 'Planta Tecámac · Galería de Herramientas',
            'pre_ordenes_fundicion' => 'Fundición · Pre-Órdenes de Entrada',
            'liberacion_modelos_fundicion' => 'Fundición · Liberación de Modelos',
            'fundicion_history' => 'Fundición · Historial de O.T.',
            'fundicion_file_logs' => 'Fundición · Bitácora de Documentos',
            'fundicion_state_logs' => 'Fundición · Bitácora de Estados',
            'preorden_logs' => 'Fundición · Seguimiento Pre-Órdenes',
            'soldadura' => 'Soldadura · Órdenes de Soldadura',
            'soldadurapta' => 'Soldadura PTA · Procedimientos Activos',
            'pta_resultados' => 'Soldadura PTA · Resultados de Inspección',
            'soldadurapta_pza' => 'Soldadura PTA · Piezas Recubiertas',
            'soldadura_pza' => 'Soldadura · Piezas Soldadas',
            'pta_reporte_logs' => 'Soldadura PTA · Bitácora de Reportes',
            'piezas' => 'Producción · Maestro General de Piezas',
            'piezas_historial' => 'Producción · Trazabilidad Histórica',
            'piezas_ocupadas' => 'Producción · Piezas en Máquina Activa',
            'pzas_generales' => 'Producción · Concentrado de Piezas',
            'pzas_liberadas' => 'Calidad · Piezas Liberadas',
            'fechas_procesos' => 'Producción · Cronología de Procesos',
            'tiempos_produccion' => 'Productividad · Tiempos Estándar y Reales',
            'tratamiento_termicos' => 'Producción · Tratamientos Térmicos',
            'scar_modelos' => 'Calidad · Reportes SCAR / No Conformidad',
            'scar_logs' => 'Calidad · Historial de SCARs',
            'rechazo_logs' => 'Calidad · Bitácora de Rechazos',
            'r_visuales' => 'Calidad · Inspecciones Visuales',
            'r_visual_medidas' => 'Calidad · Criterios Visuales',
            'r_visuales_pdfs' => 'Calidad · Evidencias PDF Visuales',
            'r_dimensionales' => 'Calidad · Inspecciones Dimensionales',
            'r_dimensional_medidas' => 'Calidad · Lecturas Dimensionales',
            'r_dimensionales_pdfs' => 'Calidad · Reportes PDF Dimensionales',
            'calidad_maquinados_docs' => 'Calidad · Índice Documental Maquinados',
            'dibujos_file_log' => 'Ingeniería · Bitácora Dibujos Maquinados',
            'dibujos_ot_history' => 'Ingeniería · Historial Dibujos por O.T.',
            'ayudas_visuales_file_log' => 'Ingeniería · Bitácora Ayudas Visuales',
            'ayudas_visuales_history' => 'Ingeniería · Historial Ayudas Visuales',
            'ayudas_visuales_fundicion_file_log' => 'Fundición · Bitácora Ayudas Visuales',
            'ayudas_visuales_fundicion_history' => 'Fundición · Historial Ayudas Visuales',
            'manuales_file_log' => 'Ingeniería · Bitácora de Manuales',
            'manuales_history' => 'Ingeniería · Historial de Manuales',
            'r_interno_salidas' => 'Calidad · Salidas Internas de Moldura',
            'r_interno_salidas_log' => 'Calidad · Bitácora Salidas de Moldura',
            'r_interno_salidas_pdfs' => 'Calidad · Formatos PDF de Salida',
            'r_interno_salidas_piezas' => 'Calidad · Piezas en Salida de Moldura',
            'system_logs' => 'Auditoría · Bitácora General del Sistema',
        ];

        if (isset($catalog[$table])) {
            return $catalog[$table];
        }

        // Traducción automática de las 19 familias de procesos de maquinado
        $processNames = [
            'cepillado' => 'Cepillado',
            'desbasteexterior' => 'Desbaste Exterior',
            'desbaste' => 'Desbaste Exterior',
            'rectificado' => 'Rectificado',
            'asentado' => 'Asentado',
            'barrenomaniobra' => 'Barreno Maniobra',
            'barrenoprofundidad' => 'Barreno Profundidad',
            'acabadobombillo' => 'Acabado Bombillo',
            'acabadomolde' => 'Acabado Molde',
            'cavidades' => 'Cavidades',
            'candadoobturador' => 'Candado Obturador',
            'copiado' => 'Copiado',
            'embudocm' => 'Embudo CM',
            'offset' => 'Offset',
            'primeraopesoldadura' => '1ra Op. Soldadura',
            'segundaopesoldadura' => '2da Op. Soldadura',
            'pysopesoldadura' => '1ra y 2da Op. Soldadura',
            'primeraoperacioncabezasoplo' => '1ra Op. Cabeza Soplo',
            'segundaoperacioncabezasoplo' => '2da Op. Cabeza Soplo',
            'revcalificado' => 'Rev. Calificado',
            'revlaterales' => 'Rev. Laterales',
        ];

        if (isset($processNames[$table])) {
            return "Maquinados · Cabecera {$processNames[$table]}";
        }

        foreach (['_cnominal' => 'Cotas Nominales', '_tolerancia' => 'Tolerancias', '_pza' => 'Piezas Procesadas'] as $suffix => $label) {
            if (str_ends_with($table, $suffix)) {
                $base = substr($table, 0, -strlen($suffix));
                $procLabel = $processNames[$base] ?? ucfirst(str_replace('_', ' ', $base));
                return "Maquinados · {$label} ({$procLabel})";
            }
        }

        return 'Datos · ' . ucwords(str_replace('_', ' ', $table));
    }

    /**
     * Formatea segundos en una cadena legible (ej. "0.4s", "14.2s", "2m 18s").
     */
    protected function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return number_format($seconds, 1) . 's';
        }

        $mins = (int) floor($seconds / 60);
        $secs = (int) round(fmod($seconds, 60));

        return "{$mins}m {$secs}s";
    }

    /**
     * Formatea bytes a KB / MB legibles.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        return round($bytes / 1024, 1) . ' KB';
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

