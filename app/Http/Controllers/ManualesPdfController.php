<?php

namespace App\Http\Controllers;

use App\Models\ManualFileLog;
use App\Models\ManualHistory;
use App\Models\Procesos;
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

        return view('wo_views.manage_documentation', array_merge(compact(
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
     * @param \Illuminate\Http\Request Request $request
     */
    public function getFiles(Request $request)
    {
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
                return [
                    'nombre' => basename($f),
                    'url'    => route('manuales.serve', [
                        'proceso' => $proceso,
                        'archivo' => basename($f),
                    ]),
                ];
            })
            ->unique('nombre')
            ->values();

        return response()->json([
            'archivos' => $allFiles,
            'proceso'  => $proceso,
            'existe'   => (count($allFiles) > 0),
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $proceso = $this->sanitizePath($request->query('proceso', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($proceso) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $filePath = self::BASE_DIR . '/' . $proceso . '/' . $archivo;

        // Fallback
        if (!Storage::disk('local')->exists($filePath)) {
            $filePath = self::OLD_BASE_DIR . '/' . $proceso . '/' . $archivo;
        }

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'Archivo no encontrado.');
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk     = Storage::disk('local');
        $fullPath = $disk->path($filePath);

        return response()->file($fullPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $archivo . '"',
        ]);
    }

    // =========================================================================
    // CRUD ADMINISTRADOR
    // =========================================================================

        /**
     * @param \Illuminate\Http\Request Request $request
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
     * @param \Illuminate\Http\Request Request $request
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
            'url'      => route('manuales.serve', [
                'proceso' => $proceso,
                'archivo' => $originalName,
            ]),
        ]);
    }

        /**
     * @param \Illuminate\Http\Request Request $request
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'proceso' => 'required|string|max:100',
            'archivo' => 'required|string|max:300',
        ]);

        $proceso  = $this->sanitizePath($request->input('proceso'));
        $archivo  = $this->sanitizeFileName($request->input('archivo'));
        $filePath = self::BASE_DIR . '/' . $proceso . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            // Check fallback for read-only error
            $oldPath = self::OLD_BASE_DIR . '/' . $proceso . '/' . $archivo;
            if (Storage::disk('local')->exists($oldPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los manuales antiguos son de solo lectura.',
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($filePath);
        $this->logAction('eliminar_pdf', $proceso, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
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
     * @param \Illuminate\Http\Request Request $request
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
        $oldPath         = $dirPath . '/' . $archivoAnterior;

        if (Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $proceso, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre'   => $originalName,
            'url'      => route('manuales.serve', [
                'proceso' => $proceso,
                'archivo' => $originalName,
            ]),
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
                $estructura[basename($dir)] = true;
            }
        }

        // 2. Viejo
        if (Storage::disk('local')->exists(self::OLD_BASE_DIR)) {
            $oldDirs = Storage::disk('local')->directories(self::OLD_BASE_DIR);
            foreach ($oldDirs as $dir) {
                $estructura[basename($dir)] = true;
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

        /**
     * @param mixed string $name
     */
    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', $name);
        $name = trim($name, '_.');
        return $name ?: 'archivo.pdf';
    }
}
