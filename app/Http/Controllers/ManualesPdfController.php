<?php

namespace App\Http\Controllers;

use App\Models\ManualFileLog;
use App\Models\ManualHistory;
use App\Models\Procesos;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ManualesPdfController extends Controller
{
    private const BASE_DIR = 'DOCUMENTACION_GIS/MANUALES_PROCESOS';

    /**
     * Directorio legado para compatibilidad con archivos anteriores.
     */
    private const OLD_BASE_DIR = 'MANUALES_GIS';

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * Vista de administración del módulo de manuales.
     */
    public function showManage(Request $request)
    {
        $estructura = $this->buildStructure();

        // Lista de procesos estándar del sistema (estos no están en una tabla de catálogo, son nombres de columnas/procesos)
        $nombresProcesos = [
            'Cepillado', 'Desbaste Exterior', 'Revision Laterales', 'Primera Operacion',
            'Barreno Maniobra', 'Segunda Operacion', 'Soldadura', 'Soldadura PTA',
            'Rectificado', 'Asentado', 'Calificado', 'Acabado Bombillo', 'Acabado Molde',
            'Barreno Profundidad', 'Cavidades', 'Copiado', 'Off Set', 'Palomas',
            'Rebajes', 'Grabado', 'Operacion Equipo', 'Embudo CM',
            'Primera Operacion Cabeza Soplo', 'Segunda Operacion Cabeza Soplo'
        ];

        $todosLosProcesos = collect($nombresProcesos)->map(function($nombre) {
            return (object)[ 'id' => $nombre, 'nombre' => $nombre ];
        });

        $procesoSeleccionadoId = $request->query('proceso_id'); // Ahora es el nombre directamente
        $procesoActivo = $procesoSeleccionadoId ? $todosLosProcesos->firstWhere('id', $procesoSeleccionadoId) : null;

        return view('wo_views.manage_manuales', array_merge(compact(
            'estructura',
            'todosLosProcesos',
            'procesoSeleccionadoId',
            'procesoActivo'
        ), [
            'moduleType' => 'manuales',
            'modulePrefix' => 'manuales',
            'pageTitle' => 'Manuales de Procesos',
            'directoryName' => 'DOCUMENTACION_GIS / MANUALES_PROCESOS',
            'moduleMetadata' => [
                'description' => 'Selecciona el proceso existente en el sistema.'
            ]
        ]));
    }

    public function getLog()
    {
        $logs = ManualFileLog::query()
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'user_name', 'action', 'ruta', 'archivo', 'created_at'])
            ->map(function ($log) {
                return [
                    'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                    'user_name'  => $log->user_name,
                    'action'     => $log->action,
                    'ruta'       => $log->ruta,
                    'archivo'    => $log->archivo,
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    // =========================================================================
    // API DE LECTURA
    // =========================================================================

    public function getStructure()
    {
        $estructura = $this->buildStructure();
        return response()->json($estructura);
    }

        /**
     * @param Request $request
     */
    public function getFiles(Request $request)
    {
        try {
            $proceso = $this->sanitizePath($request->query('proceso', ''));

            if (empty($proceso)) {
                return response()->json(['error' => 'Parámetro Proceso es requerido.'], 422);
            }

            $newDirPath = self::BASE_DIR . '/' . $proceso;
            $oldDirPath = self::OLD_BASE_DIR . '/' . $proceso;

            $newFiles = Storage::disk('local')->exists($newDirPath) ? Storage::disk('local')->files($newDirPath) : [];
            $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];

            $allFiles = collect(array_merge($newFiles, $oldFiles))
                ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
                ->map(function($f) use ($proceso) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    return [
                        'nombre' => $utf8Name,
                        'url'    => url('/manuales/serve') . '?proceso=' . urlencode($proceso) . '&archivo=' . urlencode($utf8Name),
                    ];
                })
                ->unique('nombre')
                ->values();

            return response()->json([
                'archivos' => $allFiles,
                'proceso'  => $proceso,
                'existe'   => (count($allFiles) > 0),
            ]);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            if (!mb_check_encoding($errorMsg, 'UTF-8')) {
                $errorMsg = mb_convert_encoding($errorMsg, 'UTF-8', 'Windows-1252');
            }
            return response()->json([
                'success' => false,
                'error' => $errorMsg,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

        /**
     * @param Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $proceso = $this->sanitizePath($request->query('proceso', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($proceso) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $dirPath = self::BASE_DIR . '/' . $proceso;

        // Fallback
        if (!Storage::disk('local')->exists($dirPath)) {
            $dirPath = self::OLD_BASE_DIR . '/' . $proceso;
        }

        if (!Storage::disk('local')->exists($dirPath)) {
            abort(404, 'Archivo no encontrado.');
        }

        $files = Storage::disk('local')->files($dirPath);
        $foundFile = null;
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            if ($utf8Name === $archivo) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('local');
        $fullPath = $disk->path($foundFile);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $archivo . '"',
        ]);
    }

    // =========================================================================
    // CRUD ADMINISTRADOR
    // =========================================================================

        /**
     * @param Request $request
     */
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'proceso' => 'required|string|max:100',
            ]);

            $proceso = $this->sanitizePath($request->input('proceso'));
            $dirPath = self::BASE_DIR . '/' . $proceso;

            if (Storage::disk('local')->exists($dirPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe.',
                ], 409);
            }

            Storage::disk('local')->makeDirectory($dirPath);

            ManualHistory::firstOrCreate(['proceso' => $proceso]);
            $this->logAction('crear_carpeta', $proceso, null);

            return response()->json([
                'success' => true,
                'message' => "Carpeta {$proceso} creada correctamente.",
                'proceso' => $proceso,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en ManualesPdfController@createFolder: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la carpeta: ' . $e->getMessage(),
            ], 500);
        }
    }

        /**
     * @param Request $request
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            'pdf'     => 'required|file|mimes:pdf',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        $dirPath = self::BASE_DIR . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            ManualHistory::firstOrCreate(['proceso' => $proceso]);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());

        if (Storage::disk('local')->exists($dirPath . '/' . $originalName)) {
            return response()->json([
                'success' => false,
                'message' => "Ya existe un archivo con el nombre '{$originalName}'. Use la función de Reemplazar.",
            ], 409);
        }

        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('subir_pdf', $proceso, $originalName);

        return response()->json([
            'success'  => true,
            'message'  => "PDF '{$originalName}' subido correctamente.",
            'nombre'   => $originalName,
            'url'      => url('/manuales/serve') . '?proceso=' . urlencode($proceso) . '&archivo=' . urlencode($originalName),
        ]);
    }

        /**
     * @param Request $request
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $proceso  = $this->sanitizePath($request->input('proceso'));
        $archivo  = $this->sanitizeFileName($request->input('archivo'));
        
        $dirPath = self::BASE_DIR . '/' . $proceso;
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        $foundFile = null;
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            if ($utf8Name === $archivo) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            // Check fallback for read-only error
            $oldDirPath = self::OLD_BASE_DIR . '/' . $proceso;
            $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];
            foreach ($oldFiles as $f) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                if ($utf8Name === $archivo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los manuales antiguos son de solo lectura.',
                    ], 403);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($foundFile);
        $this->logAction('eliminar_pdf', $proceso, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
            'proceso' => $proceso,
        ]);
    }

    /**
     * Elimina una carpeta completa (el Proceso).
     *
     * POST /manuales/deleteFolder
     * Body: { proceso }
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        $dirPath = self::BASE_DIR . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            Storage::disk('local')->delete($files);
            $this->logAction('vaciar_carpeta', $proceso, null);
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron " . count($files) . " archivos del proceso '{$proceso}'.",
                'proceso' => $proceso,
                'vaciada' => true,
            ]);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $proceso, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta del proceso '{$proceso}' eliminada correctamente.",
        ]);
    }

        /**
     * @param Request $request
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'proceso'         => 'required|string|max:100',
            'archivo_anterior'=> 'required|string|max:300',
            'pdf'             => 'required|file|mimes:pdf',
        ]);

        $proceso         = $this->sanitizePath($request->input('proceso'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath         = self::BASE_DIR . '/' . $proceso;
        
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        $foundFile = null;
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            if ($utf8Name === $archivoAnterior) {
                $foundFile = $f;
                break;
            }
        }

        if ($foundFile) {
            Storage::disk('local')->delete($foundFile);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $proceso, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre'   => $originalName,
            'url'      => url('/manuales/serve') . '?proceso=' . urlencode($proceso) . '&archivo=' . urlencode($originalName),
            'proceso'  => $proceso,
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function buildStructure(): array
    {
        $estructura = [];

        // 1. Nuevo
        if (Storage::disk('local')->exists(self::BASE_DIR)) {
            $dirs = Storage::disk('local')->directories(self::BASE_DIR);
            foreach ($dirs as $dir) {
                $estructura[$this->toUtf8(basename($dir))] = true;
            }
        }

        // 2. Viejo
        if (Storage::disk('local')->exists(self::OLD_BASE_DIR)) {
            $oldDirs = Storage::disk('local')->directories(self::OLD_BASE_DIR);
            foreach ($oldDirs as $dir) {
                $estructura[$this->toUtf8(basename($dir))] = true;
            }
        }

        $final = array_keys($estructura);
        sort($final, SORT_NATURAL);
        return $final;
    }

    public function deleteParent(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
        ]);

        $proceso = $this->sanitizePath($request->input('proceso'));
        $dirPath = self::BASE_DIR . '/' . $proceso;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta del proceso no existe.',
            ], 404);
        }

        // Seguridad: Verificar que no tenga archivos (Regla Dibujos)
        $files = Storage::disk('local')->files($dirPath);
        $dirs  = Storage::disk('local')->directories($dirPath);
        if (count($files) > 0 || count($dirs) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la carpeta todavía contiene archivos o subcarpetas.',
            ], 400);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $proceso, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta del proceso '{$proceso}' eliminada correctamente.",
            'proceso' => $proceso,
        ]);
    }
    /**
     * @param string $action
     * @param string $ruta
     * @param string|null $archivo
     */
    private function logAction(string $action, string $ruta, ?string $archivo): void
    {
        $user     = Auth::user();
        $userName = null;

        if ($user) {
            $userName = trim(
                ($user->matricula ?? '') . ' - ' .
                ($user->nombre ?? '') . ' ' .
                ($user->a_paterno ?? '') . ' ' .
                ($user->a_materno ?? '')
            );
        }

        ManualFileLog::create([
            'user_id'   => $user?->id,
            'user_name' => $userName,
            'action'    => $action,
            'ruta'      => $ruta,
            'archivo'   => $archivo,
        ]);

        // Mapeo de acciones técnicas a acciones legibles para system_logs
        $actionMap = [
            'subir_pdf'        => 'Subida de Manual',
            'eliminar_pdf'     => 'Eliminación de Manual',
            'reemplazar_pdf'   => 'Reemplazo de Manual',
            'crear_carpeta'    => 'Creación de Carpeta',
            'vaciar_carpeta'   => 'Eliminación de Manual',
            'eliminar_carpeta' => 'Eliminación de Manual',
        ];
        $systemAction = $actionMap[$action] ?? null;
        if ($systemAction && $user) {
            $detailsMap = [
                'subir_pdf'        => "El administrador subió el manual '{$archivo}' para el proceso {$ruta}.",
                'eliminar_pdf'     => "El administrador eliminó el manual '{$archivo}' del proceso {$ruta}.",
                'reemplazar_pdf'   => "El administrador reemplazó el manual en {$ruta}: {$archivo}.",
                'crear_carpeta'    => "El administrador creó la carpeta de manuales: {$ruta}.",
                'vaciar_carpeta'   => "El administrador vació la carpeta de manuales: {$ruta}.",
                'eliminar_carpeta' => "El administrador eliminó la carpeta de manuales: {$ruta}.",
            ];
            SystemLog::create([
                'user_matricula' => $user->matricula,
                'action'         => $systemAction,
                'details'        => $detailsMap[$action] ?? "Administrador realizó la acción '{$action}' en {$ruta}.",
            ]);
        }
    }


        /**
     * @param mixed string $path
     */
    private function sanitizePath(string $path): string
    {
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        $path = trim($path);
        return $path;
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[\/\\\\]/', '', $name);
        $name = preg_replace('/\.\.+/', '', $name);
        return trim($name) ?: 'archivo.pdf';
    }

    private function toUtf8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }
        return $string;
    }
}
