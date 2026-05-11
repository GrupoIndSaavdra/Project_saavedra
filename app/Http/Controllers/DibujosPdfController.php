<?php

namespace App\Http\Controllers;

use App\Models\DibujoFileLog;
use App\Models\DibujoOtHistory;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DibujosPdfController extends Controller
{
    private const BASE_DIR = 'DOCUMENTACION_GIS/DIBUJOS_MAQUINADOS';

    /**
     * Directorio legado para compatibilidad con archivos anteriores.
     */
    private const OLD_BASE_DIR = 'DIBUJOS_GIS';

    // =========================================================================
    // VISTAS
    // =========================================================================

    /**
     * Vista de administración del módulo de dibujos.
     * Solo accesible para administradores (perfil 1).
     */
    public function showManage(Request $request)
    {
        // ── Estructura del filesystem (para la tabla de carpetas existentes) ──
        $estructura = $this->buildStructure();

        // Cargamos SOLO las OTs que tienen al menos una Clase NO finalizada (activas)
        $todasLasOTs = Orden_trabajo::query()->with([
            'moldura',
            'clases' => fn($q) => $q->where('finalizada', 0)->orderBy('nombre'),
        ])
        ->whereHas('clases', fn($q) => $q->where('finalizada', 0))
        ->orderBy('id', 'asc')
        ->get();

        // ── Selección activa (via query string: ?ot_id=X&clase_id=Y) ──
        $otSeleccionadaId    = $request->query('ot_id');
        $claseSeleccionadaId = $request->query('clase_id');

        // Si hay OT seleccionada, obtener la OT y Clase activa
        $otActiva    = $otSeleccionadaId    ? $todasLasOTs->firstWhere('id', $otSeleccionadaId)    : null;
        $claseActiva = $claseSeleccionadaId ? optional($otActiva?->clases)->firstWhere('id', $claseSeleccionadaId) : null;

        return view('wo_views.manage_documentation', array_merge(compact(
            'estructura',
            'todasLasOTs',
            'otSeleccionadaId',
            'claseSeleccionadaId',
            'otActiva',
            'claseActiva'
        ), [
            'moduleType' => 'dibujos',
            'modulePrefix' => 'dibujos',
            'pageTitle' => 'Dibujos de Maquinados',
            'directoryName' => 'DOCUMENTACION_GIS / DIBUJOS_MAQUINADOS',
            'moduleMetadata' => [
                'description' => 'Selecciona la OT y Clase existentes en el sistema.'
            ]
        ]));
    }

    /**
     * Devuelve las últimas 50 entradas del log de auditoría como JSON.
     *
     * GET /dibujos/log
     */
    public function getLog()
    {
        $logs = DibujoFileLog::query()
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
    // API DE LECTURA (para operadores y administradores)
    // =========================================================================

    /**
     * Devuelve la estructura completa de carpetas OT → [Clase, ...] leyendo
     * directamente el sistema de archivos. La BD NO es la fuente de verdad.
     *
     * GET /dibujos/estructura
     * Response: { "OT001": ["ClaseA", "ClaseB"], "OT002": ["ClaseC"] }
     */
    public function getStructure()
    {
        $estructura = $this->buildStructure();
        return response()->json($estructura);
    }

    /**
     * Devuelve los archivos PDF dentro de una carpeta OT/Clase específica.
     *
     * GET /dibujos/archivos?ot=OT001&clase=ClaseA
     * Response: { "archivos": [{ "nombre": "plano.pdf", "url": "..." }], "ot": "OT001", "clase": "ClaseA" }
     */
    public function getFiles(Request $request)
    {
        $ot    = $this->sanitizePath($request->query('ot', ''));
        $clase = $this->sanitizePath($request->query('clase', ''));

        if (empty($ot) || empty($clase)) {
            return response()->json(['error' => 'Parámetros OT y Clase son requeridos.'], 422);
        }

        $newDirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;
        $oldDirPath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase;

        $newFiles = Storage::disk('local')->exists($newDirPath) ? Storage::disk('local')->files($newDirPath) : [];
        $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];

        $allFiles = collect(array_merge($newFiles, $oldFiles))
            ->filter(fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'pdf')
            ->map(function($f) use ($ot, $clase) {
                return [
                    'nombre' => basename($f),
                    'url'    => route('dibujos.serve', [
                        'ot'     => $ot,
                        'clase'  => $clase,
                        'archivo'=> basename($f),
                    ]),
                ];
            })
            ->unique('nombre') // Evitar duplicados si existen en ambas carpetas
            ->values();

        return response()->json([
            'archivos' => $allFiles,
            'ot'       => $ot,
            'clase'    => $clase,
            'existe'   => (count($allFiles) > 0),
        ]);
    }

    /**
     * Sirve el contenido binario de un PDF al navegador (nueva pestaña).
     * El navegador mostrará el PDF inline sin descargarlo.
     *
     * GET /dibujos/serve?ot=OT001&clase=ClaseA&archivo=plano.pdf
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $ot      = $this->sanitizePath($request->query('ot', ''));
        $clase   = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($ot) || empty($clase) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        $filePath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;
        
        // Fallback al directorio viejo si no existe en el nuevo
        if (!Storage::disk('local')->exists($filePath)) {
            $filePath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;
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
     * Crea la carpeta OT/Clase en el sistema de archivos.
     * Si ya existe, retorna un mensaje informativo (no error).
     *
     * POST /dibujos/createFolder
     * Body: { ot, clase }
     */
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'ot_id' => 'required|exists:orden_trabajo,id',
                'clase' => 'required|string|max:100',
            ]);

            $otId  = $request->input('ot_id');
            $clase = $this->sanitizePath($request->input('clase'));
            
            $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
            $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $otFolderName = $this->sanitizePath($otFolderName);

            $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $clase;

            if (Storage::disk('local')->exists($dirPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La carpeta ya existe.',
                ], 409);
            }

            Storage::disk('local')->makeDirectory($dirPath);

            DibujoOtHistory::firstOrCreate(['ot' => $otFolderName, 'clase' => $clase]);
            $this->logAction('crear_carpeta', $otFolderName . '/' . $clase, null);

            return response()->json([
                'success' => true,
                'message' => "Carpeta {$otFolderName}/{$clase} creada correctamente.",
                'ot'      => $otFolderName,
                'clase'   => $clase,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en DibujosPdfController@createFolder: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la carpeta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sube un PDF a la carpeta OT/Clase. Si la carpeta no existe, la crea automáticamente.
     *
     * POST /dibujos/upload
     * Body (multipart): { ot, clase, pdf (file) }
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'ot_id' => 'required|exists:orden_trabajo,id',
            'clase' => 'required|string|max:100',
            'pdf'   => 'required|file|mimes:pdf',
        ]);

        $otId  = $request->input('ot_id');
        $clase = $this->sanitizePath($request->input('clase'));
        
        $otModel = Orden_trabajo::query()->with('moldura')->findOrFail($otId);
        $otFolderName = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
        $otFolderName = $this->sanitizePath($otFolderName);

        $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            Storage::disk('local')->makeDirectory($dirPath);
            DibujoOtHistory::firstOrCreate(['ot' => $otFolderName, 'clase' => $clase]);
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

        $this->logAction('subir_pdf', $otFolderName . '/' . $clase, $originalName);

        return response()->json([
            'success'  => true,
            'message'  => "PDF '{$originalName}' subido correctamente.",
            'nombre'   => $originalName,
            'url'      => route('dibujos.serve', [
                'ot'     => $otFolderName,
                'clase'  => $clase,
                'archivo'=> $originalName,
            ]),
        ]);
    }

    /**
     * Elimina un PDF del sistema de archivos.
     *
     * POST /dibujos/delete
     * Body: { ot, clase, archivo }
     */
    public function deletePdf(Request $request)
    {
        $request->validate([
            'ot'     => 'required|string|max:200',
            'clase'  => 'required|string|max:100',
            'archivo'=> 'required|string|max:300',
        ]);

        $ot      = $this->sanitizePath($request->input('ot'));
        $clase   = $this->sanitizePath($request->input('clase'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));
        $filePath = self::BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;

        if (!Storage::disk('local')->exists($filePath)) {
            // Si no existe en el nuevo, verificamos si existe en el viejo para dar error de solo lectura
            $oldPath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase . '/' . $archivo;
            if (Storage::disk('local')->exists($oldPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los archivos del directorio antiguo son de solo lectura y no pueden ser eliminados.',
                ], 403);
            }

            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($filePath);
        $this->logAction('eliminar_pdf', $ot . '/' . $clase, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
        ]);
    }

    /**
     * Elimina una carpeta completa (en Dibujos seria la carpeta de la Clase).
     *
     * POST /dibujos/deleteFolder
     * Body: { ot, clase }
     */
    public function deleteFolder(Request $request)
    {
        $request->validate([
            'ot'    => 'required|string|max:200',
            'clase' => 'required|string|max:100',
        ]);

        $ot    = $this->sanitizePath($request->input('ot'));
        $clase = $this->sanitizePath($request->input('clase'));
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta no existe.',
            ], 404);
        }

        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            Storage::disk('local')->delete($files);
            $this->logAction('vaciar_carpeta', $ot . '/' . $clase, null);
            return response()->json([
                'success' => true,
                'message' => "Se eliminaron " . count($files) . " archivos de la clase '{$clase}' (OT: {$ot}).",
            ]);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $ot . '/' . $clase, null);

        return response()->json([
            'success' => true,
            'message' => "La subcarpeta de la clase '{$clase}' (OT: {$ot}) fue eliminada correctamente.",
        ]);
    }

    /**
     * Elimina la carpeta principal de una OT si está vacía.
     *
     * POST /dibujos/deleteParent
     * Body: { ot }
     */
    public function deleteParent(Request $request)
    {
        $request->validate([
            'ot' => 'required|string|max:200',
        ]);

        $ot      = $this->sanitizePath($request->input('ot'));
        $dirPath = self::BASE_DIR . '/' . $ot;

        if (!Storage::disk('local')->exists($dirPath)) {
            return response()->json([
                'success' => false,
                'message' => 'La carpeta de la OT no existe.',
            ], 404);
        }

        // Seguridad: Verificar que no tenga subcarpetas (clases)
        $subDirs = Storage::disk('local')->directories($dirPath);
        if (count($subDirs) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la OT todavía tiene subcarpetas (clases).',
            ], 400);
        }

        // Seguridad: Verificar que no tenga archivos sueltos
        $files = Storage::disk('local')->files($dirPath);
        if (count($files) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: la carpeta todavía contiene archivos.',
            ], 400);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $ot, null);

        return response()->json([
            'success' => true,
            'message' => "Carpeta principal '{$ot}' eliminada correctamente.",
        ]);
    }

    /**
     * Reemplaza un PDF existente por el nuevo (mismo nombre o nuevo nombre).
     *
     * POST /dibujos/replace
     * Body (multipart): { ot, clase, archivo_anterior, pdf (file) }
     */
    public function replacePdf(Request $request)
    {
        $request->validate([
            'ot'              => 'required|string|max:100',
            'clase'           => 'required|string|max:100',
            'archivo_anterior'=> 'required|string|max:300',
            'pdf'             => 'required|file|mimes:pdf',
        ]);

        $ot              = $this->sanitizePath($request->input('ot'));
        $clase           = $this->sanitizePath($request->input('clase'));
        $archivoAnterior = $this->sanitizeFileName($request->input('archivo_anterior'));
        $dirPath         = self::BASE_DIR . '/' . $ot . '/' . $clase;
        $oldPath         = $dirPath . '/' . $archivoAnterior;

        // Eliminar el archivo anterior si existe
        if (Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $ot . '/' . $clase, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre'   => $originalName,
            'url'      => route('dibujos.serve', [
                'ot'     => $ot,
                'clase'  => $clase,
                'archivo'=> $originalName,
            ]),
        ]);
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    /**
     * Construye la estructura completa de OTs y Clases leyendo el filesystem.
     * Retorna: [ "OT001" => ["ClaseA", "ClaseB"], ... ]
     */
    private function buildStructure(): array
    {
        $estructura = [];

        // 1. Escanear directorio nuevo
        if (Storage::disk('local')->exists(self::BASE_DIR)) {
            $otDirs = Storage::disk('local')->directories(self::BASE_DIR);
            foreach ($otDirs as $otDir) {
                $otName = basename($otDir);
                $claseDirs = Storage::disk('local')->directories($otDir);
                $estructura[$otName] = array_map('basename', $claseDirs);
            }
        }

        // 2. Escanear directorio viejo (Fallback)
        if (Storage::disk('local')->exists(self::OLD_BASE_DIR)) {
            $oldOtDirs = Storage::disk('local')->directories(self::OLD_BASE_DIR);
            foreach ($oldOtDirs as $otDir) {
                $otName = basename($otDir);
                $claseDirs = array_map('basename', Storage::disk('local')->directories($otDir));
                
                if (isset($estructura[$otName])) {
                    $estructura[$otName] = array_unique(array_merge($estructura[$otName], $claseDirs));
                } else {
                    $estructura[$otName] = $claseDirs;
                }
            }
        }

        ksort($estructura, SORT_NATURAL);
        return $estructura;
    }

    /**
     * Registra una acción en la tabla de auditoría dibujos_file_log.
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

        DibujoFileLog::create([
            'user_id'   => $user?->id,
            'user_name' => $userName,
            'action'    => $action,
            'ruta'      => $ruta,
            'archivo'   => $archivo,
        ]);
    }

    /**
     * Sanitiza un segmento de ruta para evitar path traversal.
     * Elimina '..' y caracteres peligrosos.
     */
    private function sanitizePath(string $path): string
    {
        // Eliminar cualquier intento de traversal
        $path = preg_replace('/\.\.+/', '', $path);
        $path = preg_replace('/[\/\\\\]/', '', $path);
        $path = trim($path);
        return $path;
    }

    /**
     * Sanitiza el nombre de un archivo PDF.
     */
    private function sanitizeFileName(string $name): string
    {
        // Solo permitir caracteres seguros en nombres de archivo
        $name = preg_replace('/[^a-zA-Z0-9_\-\.\s]/', '_', $name);
        $name = preg_replace('/\s+/', '_', $name);
        $name = trim($name, '_.');
        return $name ?: 'archivo.pdf';
    }
}
