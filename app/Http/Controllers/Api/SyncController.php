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
     * Valida el token secreto compartido entre local y nube.
     */
    protected function validateToken(Request $request): ?JsonResponse
    {
        $configuredToken = config('sync.token');
        $receivedToken = $request->header('X-Sync-Token') ?? $request->query('sync_token');

        if (empty($configuredToken) || empty($receivedToken) || !hash_equals((string) $configuredToken, (string) $receivedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Token de sincronización inválido o no proporcionado.',
            ], 401);
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

        return response()->json([
            'success' => true,
            'tables' => $allTables,
            'count' => count($allTables),
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
        $limit = min((int) ($request->query('limit', config('sync.batch_size', 200))), 500);

        if (!$this->isTableAllowed($table)) {
            return response()->json([
                'success' => false,
                'message' => "La tabla '{$table}' no está permitida o no existe.",
            ], 400);
        }

        $primaryKey = $this->getPrimaryKey($table);
        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');
        $hasCreatedAt = Schema::hasColumn($table, 'created_at');
        $isSnapshot = false;

        $query = DB::table($table);

        if ($hasUpdatedAt && !empty($since)) {
            $query->where('updated_at', '>', $since)
                  ->orderBy('updated_at', 'asc')
                  ->orderBy($primaryKey, 'asc');
        } elseif ($hasCreatedAt && !empty($since)) {
            $query->where('created_at', '>', $since)
                  ->orderBy('created_at', 'asc')
                  ->orderBy($primaryKey, 'asc');
        } elseif (!$hasUpdatedAt && !$hasCreatedAt) {
            // Tablas de catálogo sin timestamps (ej. clases, procesos, maquinas)
            // Si tienen <= 3000 registros, entregamos snapshot completo para garantizar sincronía total
            $totalInTable = DB::table($table)->count();
            if ($totalInTable <= 3000) {
                $isSnapshot = true;
                $query->orderBy($primaryKey, 'asc');
            } elseif (!empty($lastId)) {
                $query->where($primaryKey, '>', $lastId)->orderBy($primaryKey, 'asc');
            } else {
                $query->orderBy($primaryKey, 'asc');
            }
        } elseif (!empty($lastId)) {
            $query->where($primaryKey, '>', $lastId)
                  ->orderBy($primaryKey, 'asc');
        } else {
            $query->orderBy($primaryKey, 'asc');
        }

        $records = $isSnapshot ? $query->get() : $query->limit($limit)->get();
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

        return response()->json([
            'success' => true,
            'table' => $table,
            'primary_key' => $primaryKey,
            'records' => $records,
            'count' => $count,
            'has_more' => $isSnapshot ? false : ($count >= $limit),
            'is_snapshot' => $isSnapshot,
            'next_since' => $nextSince,
            'next_id' => $nextId,
        ]);
    }

    /**
     * Recibe y aplica un lote de registros (PUSH).
     */
    public function push(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
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

        $primaryKey = $this->getPrimaryKey($table);
        $validColumns = array_flip(Schema::getColumnListing($table));

        DB::beginTransaction();
        try {
            // Desactivar temporalmente FK checks para evitar bloqueos por orden de inserción
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

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
                        if (isset($cleanRow[$primaryKey]) && !empty($cleanRow[$primaryKey])) {
                            $keyValue = $cleanRow[$primaryKey];
                            DB::table($table)->updateOrInsert([$primaryKey => $keyValue], $cleanRow);
                        } else {
                            DB::table($table)->insert($cleanRow);
                        }
                        $processed++;
                    }
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            return response()->json([
                'success' => true,
                'table' => $table,
                'processed' => $processed,
            ]);
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::rollBack();

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
        if (empty($table) || !Schema::hasTable($table)) {
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

        return response()->download($absolutePath, basename($absolutePath));
    }

    /**
     * Recibe y guarda un archivo físico individual (PUSH).
     */
    public function filesUpload(Request $request): JsonResponse
    {
        if ($authError = $this->validateToken($request)) {
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
}

