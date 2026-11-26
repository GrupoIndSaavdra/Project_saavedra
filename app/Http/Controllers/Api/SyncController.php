<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SyncController extends Controller
{
    /**
     * Caché en memoria del esquema de tablas para evitar cientos de consultas a information_schema.
     *
     * @var array<string, array{columns: array<string, int>, primary_key: string, has_updated_at: bool, has_created_at: bool}>|null
     */
    protected ?array $schemaCache = null;

    /**
     * Carga en una sola consulta SQL todas las tablas, columnas y llaves primarias de la BD actual.
     */
    protected function ensureSchemaCache(): array
    {
        if ($this->schemaCache !== null) {
            return $this->schemaCache;
        }

        $this->schemaCache = [];

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

                if (!isset($this->schemaCache[$t])) {
                    $this->schemaCache[$t] = [
                        'columns' => [],
                        'primary_key' => 'id',
                        'has_updated_at' => false,
                        'has_created_at' => false,
                    ];
                }

                $this->schemaCache[$t]['columns'][$col] = 1;

                if ($key === 'PRI' && $this->schemaCache[$t]['primary_key'] === 'id') {
                    $this->schemaCache[$t]['primary_key'] = $col;
                }
                if ($col === 'updated_at') {
                    $this->schemaCache[$t]['has_updated_at'] = true;
                } elseif ($col === 'created_at') {
                    $this->schemaCache[$t]['has_created_at'] = true;
                }
            }
        } catch (\Throwable $e) {
            $this->schemaCache = [];
        }

        return $this->schemaCache;
    }

    /**
     * Obtiene metadatos de una tabla usando el caché precargado o fallback directo.
     */
    protected function getTableMeta(string $table): ?array
    {
        $cache = $this->ensureSchemaCache();
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        if (!Schema::hasTable($table)) {
            return null;
        }

        $cols = array_flip(Schema::getColumnListing($table));
        return [
            'columns' => $cols,
            'primary_key' => $this->getPrimaryKey($table),
            'has_updated_at' => isset($cols['updated_at']),
            'has_created_at' => isset($cols['created_at']),
        ];
    }

    /**
     * Calcula una huella MD5 determinística para tablas pequeñas sin timestamps (catálogos).
     */
    protected function computeSnapshotHash(string $table, string $primaryKey): string
    {
        $rows = DB::table($table)->orderBy($primaryKey, 'asc')->get();
        return md5(json_encode($rows));
    }
    /**
     * Valida el token secreto recibido.
     * Soporta dos llaves:
     * - Token de Producción (SYNC_API_TOKEN): Permisos completos (PUSH y PULL).
     * - Token de Desarrollador (SYNC_DEV_TOKEN): Solo lectura (PULL), PUSH denegado.
     */
    protected function authenticateToken(Request $request): array|JsonResponse
    {
        $prodToken = config('sync.token');
        $devToken = config('sync.dev_token');
        $receivedToken = (string) ($request->header('X-Sync-Token') ?? $request->query('sync_token'));

        if (empty($receivedToken)) {
            $this->logSync("[CPANEL-AUTH-FAIL] Token ausente desde IP: {$request->ip()}", 'warning');
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Token de sincronización no proporcionado.',
            ], 401);
        }

        if (!empty($prodToken) && hash_equals((string) $prodToken, $receivedToken)) {
            return ['role' => 'prod'];
        }

        if (!empty($devToken) && hash_equals((string) $devToken, $receivedToken)) {
            return ['role' => 'dev'];
        }

        $this->logSync("[CPANEL-AUTH-FAIL] Token inválido desde IP: {$request->ip()}", 'warning');
        return response()->json([
            'success' => false,
            'message' => 'No autorizado. Token de sincronización inválido.',
        ], 401);
    }

    /**
     * Valida el token y verifica permisos de rol.
     */
    protected function validateToken(Request $request, bool $allowDev = true): ?JsonResponse
    {
        $auth = $this->authenticateToken($request);
        if ($auth instanceof JsonResponse) {
            return $auth;
        }

        if ($auth['role'] === 'dev' && !$allowDev) {
            $this->logSync("[CPANEL-AUTH-BLOCKED] Intento de PUSH bloqueado con token de desarrollador desde IP: {$request->ip()}", 'warning');
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado: El token de desarrollador solo tiene permisos de descarga (PULL). No se permite modificar producción.',
            ], 403);
        }

        return null;
    }

    /**
     * Comprobación rápida de salud y conexión (Heartbeat / Ping).
     */
    public function ping(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
            return $authError;
        }

        $this->logSync("[CPANEL-PING] Heartbeat recibido con éxito desde IP: {$request->ip()}");

        return response()->json([
            'success' => true,
            'message' => 'Pong! Servicio de sincronización activo',
            'server_time' => now()->toIso8601String(),
            'app' => config('app.name'),
            'version' => '2.0.0',
        ]);
    }

    /**
     * Retorna el listado de tablas elegibles para sincronizar.
     */
    public function tables(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
            return $authError;
        }

        $allTables = $this->getAllSyncableTables();

        $this->logSync("[CPANEL-TABLES] Listado de tablas solicitado desde IP: {$request->ip()} (" . count($allTables) . " tablas)");

        return response()->json([
            'success' => true,
            'tables' => $allTables,
            'count' => count($allTables),
        ]);
    }

    /**
     * Evalúa en bloque qué tablas tienen registros nuevos o modificados respecto al checkpoint del cliente.
     * Reduce ~135 peticiones HTTP secuenciales a 1-2 peticiones rápidas (< 15 KB por paquete).
     */
    public function changes(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
            return $authError;
        }

        $states = $request->input('states', []);
        if (!is_array($states) || empty($states)) {
            return response()->json([
                'success' => true,
                'changed_tables' => [],
                'checked' => 0,
            ]);
        }

        $this->ensureSchemaCache();
        $changedTables = [];

        foreach ($states as $table => $state) {
            $table = (string) $table;
            if (!$this->isTableAllowed($table)) {
                continue;
            }

            $meta = $this->getTableMeta($table);
            if (!$meta) {
                continue;
            }

            $since = is_array($state) ? ($state['s'] ?? null) : null;
            $lastId = is_array($state) ? ($state['i'] ?? null) : null;
            $clientHash = is_array($state) ? ($state['h'] ?? null) : null;

            $primaryKey = $meta['primary_key'];
            $hasUpdatedAt = $meta['has_updated_at'];
            $hasCreatedAt = $meta['has_created_at'];

            try {
                if ($hasUpdatedAt) {
                    $query = DB::table($table);
                    if (!empty($since)) {
                        $query->where(function ($q) use ($since, $lastId, $primaryKey) {
                            $q->where('updated_at', '>', $since);
                            if (!empty($lastId)) {
                                $q->orWhere(function ($q2) use ($since, $lastId, $primaryKey) {
                                    $q2->where('updated_at', '=', $since)
                                       ->where($primaryKey, '>', $lastId);
                                });
                            }
                        });
                    }
                    if ($query->exists()) {
                        $changedTables[] = $table;
                    }
                } elseif ($hasCreatedAt) {
                    $query = DB::table($table);
                    if (!empty($since)) {
                        $query->where(function ($q) use ($since, $lastId, $primaryKey) {
                            $q->where('created_at', '>', $since);
                            if (!empty($lastId)) {
                                $q->orWhere(function ($q2) use ($since, $lastId, $primaryKey) {
                                    $q2->where('created_at', '=', $since)
                                       ->where($primaryKey, '>', $lastId);
                                });
                            }
                        });
                    }
                    if ($query->exists()) {
                        $changedTables[] = $table;
                    }
                } else {
                    $totalInTable = DB::table($table)->count();
                    if ($totalInTable === 0) {
                        continue;
                    }
                    if ($totalInTable <= 3000) {
                        $serverHash = $this->computeSnapshotHash($table, $primaryKey);
                        if (empty($clientHash) || !hash_equals($serverHash, (string) $clientHash)) {
                            $changedTables[] = $table;
                        }
                    } else {
                        $query = DB::table($table);
                        if (!empty($lastId)) {
                            $query->where($primaryKey, '>', $lastId);
                        }
                        if ($query->exists()) {
                            $changedTables[] = $table;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // En caso de duda sobre una tabla, marcarla para revisión individual segura
                $changedTables[] = $table;
            }
        }

        $this->logSync("[CPANEL-CHANGES] Revisadas " . count($states) . " tablas para IP: {$request->ip()} -> " . count($changedTables) . " con cambios");

        return response()->json([
            'success' => true,
            'changed_tables' => $changedTables,
            'checked' => count($states),
        ]);
    }

    /**
     * Entrega un lote de registros modificados (PULL).
     */
    public function pull(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
            return $authError;
        }

        $table = (string) $request->query('table');
        $since = $request->query('since');
        $lastId = $request->query('last_id');
        $clientSnapshotHash = $request->query('snapshot_hash');
        $withTotal = filter_var($request->query('with_total', false), FILTER_VALIDATE_BOOLEAN);
        $limit = min((int) ($request->query('limit', config('sync.pull_batch_size', 500))), 1000);

        if (!$this->isTableAllowed($table)) {
            return response()->json([
                'success' => false,
                'message' => "La tabla '{$table}' no está permitida o no existe.",
            ], 400);
        }

        $meta = $this->getTableMeta($table);
        $primaryKey = $meta['primary_key'] ?? $this->getPrimaryKey($table);
        $hasUpdatedAt = $meta['has_updated_at'] ?? Schema::hasColumn($table, 'updated_at');
        $hasCreatedAt = $meta['has_created_at'] ?? Schema::hasColumn($table, 'created_at');
        $isSnapshot = false;
        $snapshotHash = null;

        $query = DB::table($table);

        if ($hasUpdatedAt) {
            if (!empty($since)) {
                $query->where(function ($q) use ($since, $lastId, $primaryKey) {
                    $q->where('updated_at', '>', $since);
                    if (!empty($lastId)) {
                        $q->orWhere(function ($q2) use ($since, $lastId, $primaryKey) {
                            $q2->where('updated_at', '=', $since)
                               ->where($primaryKey, '>', $lastId);
                        });
                    }
                });
            }
            $query->orderBy('updated_at', 'asc')
                  ->orderBy($primaryKey, 'asc');
        } elseif ($hasCreatedAt) {
            if (!empty($since)) {
                $query->where(function ($q) use ($since, $lastId, $primaryKey) {
                    $q->where('created_at', '>', $since);
                    if (!empty($lastId)) {
                        $q->orWhere(function ($q2) use ($since, $lastId, $primaryKey) {
                            $q2->where('created_at', '=', $since)
                               ->where($primaryKey, '>', $lastId);
                        });
                    }
                });
            }
            $query->orderBy('created_at', 'asc')
                  ->orderBy($primaryKey, 'asc');
        } else {
            // Tablas de catálogo sin timestamps (ej. clases, procesos, maquinas)
            // Si tienen <= 3000 registros, entregamos snapshot completo solo cuando su hash cambió
            $totalInTable = DB::table($table)->count();
            if ($totalInTable <= 3000) {
                $isSnapshot = true;
                $records = DB::table($table)->orderBy($primaryKey, 'asc')->get();
                $snapshotHash = md5(json_encode($records));

                if (!empty($clientSnapshotHash) && hash_equals($snapshotHash, (string) $clientSnapshotHash)) {
                    return response()->json([
                        'success' => true,
                        'table' => $table,
                        'primary_key' => $primaryKey,
                        'records' => [],
                        'count' => 0,
                        'has_more' => false,
                        'is_snapshot' => true,
                        'snapshot_hash' => $snapshotHash,
                        'next_since' => null,
                        'next_id' => null,
                    ]);
                }

                $count = $records->count();
                $lastRecord = $records->last();
                $nextId = ($lastRecord && isset($lastRecord->{$primaryKey})) ? $lastRecord->{$primaryKey} : null;

                $this->logSync("[CPANEL-PULL] Tabla '{$table}': entregados {$count} registros hacia IP: {$request->ip()} (snapshot)");

                return response()->json([
                    'success' => true,
                    'table' => $table,
                    'primary_key' => $primaryKey,
                    'records' => $records,
                    'count' => $count,
                    'has_more' => false,
                    'is_snapshot' => true,
                    'snapshot_hash' => $snapshotHash,
                    'next_since' => null,
                    'next_id' => $nextId,
                ]);
            } elseif (!empty($lastId)) {
                $query->where($primaryKey, '>', $lastId)->orderBy($primaryKey, 'asc');
            } else {
                $query->orderBy($primaryKey, 'asc');
            }
        }

        $totalPending = null;
        if ($withTotal) {
            try {
                $totalPending = (clone $query)->count();
            } catch (\Throwable $e) {
                $totalPending = null;
            }
        }

        $records = $query->limit($limit)->get();
        $count = $records->count();

        // Determinar siguiente punto de corte
        $lastRecord = $records->last();
        $nextSince = null;
        $nextId = null;

        if ($lastRecord) {
            if ($hasUpdatedAt && isset($lastRecord->updated_at)) {
                $nextSince = $lastRecord->updated_at;
            } elseif ($hasCreatedAt && isset($lastRecord->created_at)) {
                $nextSince = $lastRecord->created_at;
            }
            if (isset($lastRecord->{$primaryKey})) {
                $nextId = $lastRecord->{$primaryKey};
            }
        }

        $this->logSync("[CPANEL-PULL] Tabla '{$table}': entregados {$count} registros hacia IP: {$request->ip()}");

        return response()->json([
            'success' => true,
            'table' => $table,
            'primary_key' => $primaryKey,
            'records' => $records,
            'count' => $count,
            'total_pending' => $totalPending,
            'has_more' => ($count >= $limit),
            'is_snapshot' => false,
            'snapshot_hash' => null,
            'next_since' => $nextSince,
            'next_id' => $nextId,
        ]);
    }

    /**
     * Recibe y aplica un lote de registros (PUSH).
     */
    public function push(Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            return response()->json([
                'success' => false,
                'message' => 'El endpoint /push solo acepta peticiones POST con el cuerpo de registros a sincronizar.',
            ], 405);
        }

        if ($authError = $this->validateToken($request, false)) {
            return $authError;
        }

        $table = (string) $request->input('table');
        $records = $request->input('records', []);

        if (!$this->isTableAllowed($table)) {
            return response()->json([
                'success' => false,
                'message' => "La tabla '{$table}' no está permitida o no existe.",
            ], 400);
        }

        if (empty($records) || !is_array($records)) {
            return response()->json([
                'success' => true,
                'table' => $table,
                'processed' => 0,
            ]);
        }

        $meta = $this->getTableMeta($table);
        $primaryKey = $meta['primary_key'] ?? $this->getPrimaryKey($table);
        $validColumns = $meta['columns'] ?? array_flip(Schema::getColumnListing($table));

        DB::beginTransaction();
        try {
            // Desactivar temporalmente FK checks para evitar bloqueos por orden de inserción
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            try {
                DB::statement("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION';");
            } catch (\Throwable $e) {}

            // Sanitizar todas las filas
            $cleanRecords = [];
            foreach ($records as $row) {
                $row = (array) $row;
                $cleanRow = array_intersect_key($row, $validColumns);
                if (!empty($cleanRow)) {
                    $cleanRecords[] = $cleanRow;
                }
            }

            $processed = 0;
            // Procesamiento en micro-lotes de 100 con upsert de alta velocidad y fallback seguro
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
                        $processed += count($chunk);
                        $chunkHandled = true;
                    }
                } catch (\Throwable $e) {
                    $chunkHandled = false;
                }

                // Fallback seguro fila por fila si upsert no aplicó
                if (!$chunkHandled) {
                    foreach ($chunk as $cleanRow) {
                        try {
                            if (isset($cleanRow[$primaryKey]) && !empty($cleanRow[$primaryKey])) {
                                $keyValue = $cleanRow[$primaryKey];
                                DB::table($table)->updateOrInsert([$primaryKey => $keyValue], $cleanRow);
                            } else {
                                DB::table($table)->insert($cleanRow);
                            }
                            $processed++;
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
                                    $processed++;
                                } catch (\Throwable $retryEx) {
                                    $this->logSync("[PUSH-SKIP] Registro omitido en '{$table}': " . $retryEx->getMessage(), 'warning');
                                }
                            } else {
                                $this->logSync("[PUSH-SKIP] Registro omitido en '{$table}': " . $rowEx->getMessage(), 'warning');
                            }
                        }
                    }
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            $this->logSync("[CPANEL-PUSH] Tabla '{$table}': recibidos y procesados {$processed} registros desde IP: {$request->ip()}");

            return response()->json([
                'success' => true,
                'table' => $table,
                'processed' => $processed,
            ]);
        } catch (\Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $fkEx) {}

            try {
                DB::rollBack();
            } catch (\Throwable $rbEx) {}

            Log::error("Fallo en sincronización PUSH de tabla '{$table}'", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error al aplicar registros en '{$table}': " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene la clave primaria de una tabla dada.
     */
    protected function getPrimaryKey(string $table): string
    {
        try {
            $keys = DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'");
            if (!empty($keys) && isset($keys[0]->Column_name)) {
                return $keys[0]->Column_name;
            }
        } catch (\Throwable $e) {
            // Si falla la consulta de llaves, asumimos 'id'
        }

        return 'id';
    }

    /**
     * Verifica si una tabla está permitida para sincronizar.
     */
    protected function isTableAllowed(string $table): bool
    {
        if (empty($table)) {
            return false;
        }

        $cache = $this->ensureSchemaCache();
        $exists = !empty($cache) ? isset($cache[$table]) : Schema::hasTable($table);
        if (!$exists) {
            return false;
        }

        $ignored = config('sync.ignore_tables', []);
        return !in_array($table, $ignored, true);
    }

    /**
     * Retorna el manifiesto de archivos físicos en storage/app/DOCUMENTACION_GIS.
     */
    public function filesManifest(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
            return $authError;
        }

        $baseRelative = (string) config('sync.files_relative_dir', 'DOCUMENTACION_GIS');
        $baseDir = storage_path('app/' . $baseRelative);

        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }

        $manifest = [];
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

                    $manifest[] = [
                        'path' => $relative,
                        'size' => $fileInfo->getSize(),
                        'mtime' => $fileInfo->getMTime(),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Error generando manifiesto de archivos en sync: ' . $e->getMessage());
        }

        $this->logSync("[CPANEL-FILES-MANIFEST] Manifiesto solicitado desde IP: {$request->ip()} (" . count($manifest) . " archivos)");

        return response()->json([
            'success' => true,
            'base_dir' => $baseRelative,
            'total_files' => count($manifest),
            'files' => $manifest,
        ]);
    }

    /**
     * Descarga un archivo físico individual (PULL).
     */
    public function filesDownload(Request $request)
    {
        if ($authError = $this->validateToken($request)) {
            return $authError;
        }

        $rawPath = (string) ($request->query('path') ?? $request->input('path'));
        $safePath = $this->sanitizeRelativeFilePath($rawPath);

        if (!$safePath) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta de archivo inválida o extensión no permitida.',
            ], 400);
        }

        $baseRelative = (string) config('sync.files_relative_dir', 'DOCUMENTACION_GIS');
        $absolutePath = storage_path("app/{$baseRelative}/{$safePath}");

        if (!file_exists($absolutePath) || !is_file($absolutePath)) {
            return response()->json([
                'success' => false,
                'message' => "El archivo '{$safePath}' no existe en el almacenamiento.",
            ], 404);
        }

        $this->logSync("[CPANEL-FILE-DOWNLOAD] Archivo '{$safePath}' descargado por IP: {$request->ip()}");

        return response()->download($absolutePath, basename($absolutePath));
    }

    /**
     * Recibe y guarda un archivo físico individual (PUSH).
     */
    public function filesUpload(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request, false)) {
            return $authError;
        }

        $rawPath = (string) $request->input('path');
        $safePath = $this->sanitizeRelativeFilePath($rawPath);

        if (!$safePath) {
            return response()->json([
                'success' => false,
                'message' => 'Ruta de archivo inválida o extensión no permitida.',
            ], 400);
        }

        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibió un archivo válido para transferir.',
            ], 400);
        }

        $uploadedFile = $request->file('file');
        $maxBytes = (int) config('sync.max_file_size_bytes', 104857600);

        if ($uploadedFile->getSize() > $maxBytes) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo excede el tamaño máximo permitido.',
            ], 413);
        }

        $baseRelative = (string) config('sync.files_relative_dir', 'DOCUMENTACION_GIS');
        $targetAbsolute = storage_path("app/{$baseRelative}/{$safePath}");
        $targetDir = dirname($targetAbsolute);

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        try {
            $uploadedFile->move($targetDir, basename($targetAbsolute));

            // Si se envió el mtime original, preservarlo
            $originalMtime = $request->input('mtime');
            if ($originalMtime && is_numeric($originalMtime)) {
                @touch($targetAbsolute, (int) $originalMtime);
            }

            $this->logSync("[CPANEL-FILE-UPLOAD] Archivo '{$safePath}' (" . round(filesize($targetAbsolute) / 1024, 1) . " KB) recibido y guardado desde IP: {$request->ip()}");

            // Sincronización inmediata hacia Calidad Maquinados si corresponde
            $relativeStoragePath = "{$baseRelative}/{$safePath}";
            if (str_starts_with($safePath, 'DIBUJOS_MAQUINADOS/')) {
                \App\Console\Commands\SyncMaquinadosDocs::syncSingleFile($relativeStoragePath, 'dibujo', 'upsert');
            } elseif (str_starts_with($safePath, 'AYUDAS_MAQUINADOS/')) {
                \App\Console\Commands\SyncMaquinadosDocs::syncSingleFile($relativeStoragePath, 'ayuda', 'upsert');
            }

            return response()->json([
                'success' => true,
                'path' => $safePath,
                'size' => filesize($targetAbsolute),
            ]);
        } catch (\Throwable $e) {
            Log::error("Fallo al guardar archivo subido en sync: {$safePath}", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al escribir el archivo en disco: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sanitiza y valida rutas relativas evitando directory traversal.
     */
    protected function sanitizeRelativeFilePath(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));

        // Eliminar prefijo de carpeta base si viene incluido
        $baseRelative = (string) config('sync.files_relative_dir', 'DOCUMENTACION_GIS');
        if (str_starts_with($path, $baseRelative . '/')) {
            $path = substr($path, strlen($baseRelative) + 1);
        }

        // Prohibir traversal, caracteres nulos o rutas absolutas
        if (str_contains($path, '..') || str_contains($path, "\0") || str_starts_with($path, '/')) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed = config('sync.allowed_file_extensions', ['pdf', 'png', 'jpg', 'jpeg', 'dwg', 'dxf']);

        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        return $path;
    }

    /**
     * Retorna todas las tablas sincronizables ordenadas por prioridad.
     */
    protected function getAllSyncableTables(): array
    {
        $ignored = config('sync.ignore_tables', []);
        $priority = config('sync.priority_tables', []);

        // Obtener todas las tablas de la base de datos
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

        // Ordenar primero las de prioridad y luego las restantes
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
     * Escribe en el archivo de log dedicado de sincronización (storage/logs/sync.log).
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

