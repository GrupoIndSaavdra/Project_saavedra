<?php

namespace App\Http\Controllers;

use App\Models\DibujoFileLog;
use App\Models\DibujoOtHistory;
use App\Models\Orden_trabajo;
use App\Models\Clase;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

        return view('wo_views.manage_dibujos', array_merge(compact(
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
        try {
            $rawOt = $request->query('ot', '');
            $clase = $this->sanitizePath($request->query('clase', ''));

            if (empty($rawOt)) {
                return response()->json(['error' => 'Parámetro OT es requerido.'], 422);
            }

            if ($clase === 'null' || $clase === '--') $clase = '';

            $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
            if ($otModel) {
                $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
                $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
            } else {
                $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
            }

            $newClasePath = Storage::disk('local')->path(self::BASE_DIR . '/' . $ot . '/' . $clase);
            $oldClasePath = Storage::disk('local')->path(self::OLD_BASE_DIR . '/' . $ot . '/' . $clase);
            $newRootPath = Storage::disk('local')->path(self::BASE_DIR . '/' . $ot);
            $oldRootPath = Storage::disk('local')->path(self::OLD_BASE_DIR . '/' . $ot);

            $files = [];

            if (!empty($clase)) {
                $files = array_merge($files, glob($newClasePath . '/*.{pdf,PDF}', GLOB_BRACE) ?: []);
                $files = array_merge($files, glob($oldClasePath . '/*.{pdf,PDF}', GLOB_BRACE) ?: []);
            }

            $rootFilesNew = glob($newRootPath . '/*.{pdf,PDF}', GLOB_BRACE) ?: [];
            $rootFilesOld = glob($oldRootPath . '/*.{pdf,PDF}', GLOB_BRACE) ?: [];

            $files = array_merge($files, $rootFilesNew, $rootFilesOld);

            $allFiles = collect($files)
                ->map(function($f) use ($ot, $clase, $newRootPath, $oldRootPath) {
                    $rawName = basename($f);
                    $utf8Name = $this->toUtf8($rawName);
                    $fullPath = $f;
                    
                    $dir = dirname($fullPath);
                    $esRaiz = ($dir === $newRootPath || $dir === $oldRootPath);

                    return [
                        'nombre'  => $utf8Name,
                        'url'     => url('/dibujos/serve') . '?ot=' . urlencode($ot) . '&clase=' . urlencode($esRaiz ? '--' : $clase) . '&archivo=' . urlencode($utf8Name),
                        'es_raiz' => $esRaiz
                    ];
                })
                ->unique('nombre')
                ->values();

            return response()->json([
                'archivos' => $allFiles,
                'ot'       => $ot,
                'clase'    => $clase,
                'existe'   => (count($allFiles) > 0),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en DibujosPdfController@getFiles: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $errorMsg = $e->getMessage();
            if (function_exists('mb_convert_encoding')) {
                $errorMsg = @mb_convert_encoding($errorMsg, 'UTF-8', 'UTF-8');
            }
            return response()->json([
                'success' => false,
                'error' => $errorMsg,
                'archivos' => [],
                'existe' => false
            ], 200);
        }
    }

    /**
     * Sirve el contenido binario de un PDF al navegador (nueva pestaña).
     * El navegador mostrará el PDF inline sin descargarlo.
     *
     * GET /dibujos/serve?ot=OT001&clase=ClaseA&archivo=plano.pdf
     */
    public function serveFile(Request $request): BinaryFileResponse
    {
        $rawOt = $request->query('ot', '');
        $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }
        $clase   = $this->sanitizePath($request->query('clase', ''));
        $archivo = $this->sanitizeFileName($request->query('archivo', ''));

        if (empty($ot) || empty($clase) || empty($archivo)) {
            abort(422, 'Parámetros inválidos.');
        }

        if ($clase === '--') {
            $dirPath = self::BASE_DIR . '/' . $ot;
            if (!Storage::disk('local')->exists($dirPath)) {
                $dirPath = self::OLD_BASE_DIR . '/' . $ot;
            }
        } else {
            $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;
            if (!Storage::disk('local')->exists($dirPath)) {
                $dirPath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase;
            }
        }

        if (!Storage::disk('local')->exists($dirPath)) {
            abort(404, 'Archivo no encontrado.');
        }

        $files = Storage::disk('local')->files($dirPath);
        $foundFile = null;
        $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8NameNorm === $archivoNorm) {
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
            $otFolderName = $this->normalizeOTName($this->sanitizePath($otFolderName));

            if ($clase === '--') {
                $dirPath = self::BASE_DIR . '/' . $otFolderName;
            } else {
                $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $clase;
            }

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
            Log::error("Error en DibujosPdfController@createFolder: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear la carpeta.',
                'error'   => $e->getMessage(),
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
        $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
        $otFolderName = $this->normalizeOTName($this->sanitizePath($otLabel));

        if ($clase === '--') {
            $dirPath = self::BASE_DIR . '/' . $otFolderName;
        } else {
            $dirPath = self::BASE_DIR . '/' . $otFolderName . '/' . $clase;
        }

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
            'url'      => url('/dibujos/serve') . '?ot=' . urlencode($otFolderName) . '&clase=' . urlencode($clase) . '&archivo=' . urlencode($originalName),
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

        $rawOt = $request->input('ot');
        $otModel = Orden_trabajo::query()->with('moldura')->find($rawOt);
        if ($otModel) {
            $otLabel = "OT " . $otModel->id . ($otModel->moldura ? " - " . $otModel->moldura->nombre : "");
            $ot = $this->normalizeOTName($this->sanitizePath($otLabel));
        } else {
            $ot = $this->normalizeOTName($this->sanitizePath($rawOt));
        }
        $clase   = $this->sanitizePath($request->input('clase'));
        $archivo = $this->sanitizeFileName($request->input('archivo'));
        
        $dirPath = self::BASE_DIR . '/' . $ot . '/' . $clase;
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        
        $foundFile = null;
        $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8NameNorm === $archivoNorm) {
                $foundFile = $f;
                break;
            }
        }

        if (!$foundFile) {
            // Si no existe en el nuevo, verificamos si existe en el viejo para dar error de solo lectura
            $oldDirPath = self::OLD_BASE_DIR . '/' . $ot . '/' . $clase;
            $oldFiles = Storage::disk('local')->exists($oldDirPath) ? Storage::disk('local')->files($oldDirPath) : [];
            $archivoNorm = \Normalizer::normalize(mb_strtolower($archivo, 'UTF-8'), \Normalizer::FORM_C);
            foreach ($oldFiles as $f) {
                $rawName = basename($f);
                $utf8Name = $this->toUtf8($rawName);
                $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
                if ($utf8NameNorm === $archivoNorm) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Los archivos del directorio antiguo son de solo lectura y no pueden ser eliminados.',
                    ], 403);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe.',
            ], 404);
        }

        Storage::disk('local')->delete($foundFile);
        $this->logAction('eliminar_pdf', $ot . '/' . $clase, $archivo);

        return response()->json([
            'success' => true,
            'message' => "Archivo '{$archivo}' eliminado correctamente.",
            'ot'      => $ot,
            'clase'   => $clase,
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
                'ot'      => $ot,
                'clase'   => $clase,
                'vaciada' => true,
            ]);
        }

        Storage::disk('local')->deleteDirectory($dirPath);
        $this->logAction('eliminar_carpeta', $ot . '/' . $clase, null);

        return response()->json([
            'success' => true,
            'message' => "La subcarpeta de la clase '{$clase}' (OT: {$ot}) fue eliminada correctamente.",
            'ot'      => $ot,
            'clase'   => $clase,
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
            'ot'      => $ot,
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
        
        $files = Storage::disk('local')->exists($dirPath) ? Storage::disk('local')->files($dirPath) : [];
        $foundFile = null;
        $archivoAnteriorNorm = \Normalizer::normalize(mb_strtolower($archivoAnterior, 'UTF-8'), \Normalizer::FORM_C);
        foreach ($files as $f) {
            $rawName = basename($f);
            $utf8Name = $this->toUtf8($rawName);
            $utf8NameNorm = \Normalizer::normalize(mb_strtolower($utf8Name, 'UTF-8'), \Normalizer::FORM_C);
            if ($utf8NameNorm === $archivoAnteriorNorm) {
                $foundFile = $f;
                break;
            }
        }

        // Eliminar el archivo anterior si existe
        if ($foundFile) {
            Storage::disk('local')->delete($foundFile);
        }

        $file         = $request->file('pdf');
        $originalName = $this->sanitizeFileName($file->getClientOriginalName());
        $file->storeAs($dirPath, $originalName, 'local');

        $this->logAction('reemplazar_pdf', $ot . '/' . $clase, "{$archivoAnterior} → {$originalName}");

        return response()->json([
            'success'  => true,
            'message'  => "Archivo reemplazado: '{$archivoAnterior}' → '{$originalName}'.",
            'nombre'   => $originalName,
            'url'      => url('/dibujos/serve') . '?ot=' . urlencode($ot) . '&clase=' . urlencode($clase) . '&archivo=' . urlencode($originalName),
            'ot'       => $ot,
            'clase'    => $clase,
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
        $bases = [self::BASE_DIR, self::OLD_BASE_DIR];

        foreach ($bases as $baseDir) {
            $basePath = Storage::disk('local')->path($baseDir);
            if (is_dir($basePath)) {
                $otDirs = glob($basePath . '/*', GLOB_ONLYDIR);
                if ($otDirs) {
                    foreach ($otDirs as $otDir) {
                        $otName = $this->toUtf8($this->normalizeOTName(basename($otDir)));
                        
                        $clases = [];
                        
                        $claseDirs = glob($otDir . '/*', GLOB_ONLYDIR);
                        if ($claseDirs) {
                            foreach ($claseDirs as $claseDir) {
                                $clases[] = $this->toUtf8(basename($claseDir));
                            }
                        }
                        
                        $pdfs = glob($otDir . '/*.{pdf,PDF}', GLOB_BRACE);
                        if ($pdfs && count($pdfs) > 0) {
                            $clases[] = '--';
                        }

                        if (isset($estructura[$otName])) {
                            $estructura[$otName] = array_unique(array_merge($estructura[$otName], $clases));
                        } else {
                            $estructura[$otName] = $clases;
                        }
                    }
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

        // Mapeo de acciones técnicas a acciones legibles para system_logs
        $actionMap = [
            'subir_pdf'        => 'Subida de Dibujo',
            'eliminar_pdf'     => 'Eliminación de Dibujo',
            'reemplazar_pdf'   => 'Reemplazo de Dibujo',
            'crear_carpeta'    => 'Creación de Carpeta',
            'vaciar_carpeta'   => 'Eliminación de Dibujo',
            'eliminar_carpeta' => 'Eliminación de Dibujo',
        ];
        $systemAction = $actionMap[$action] ?? null;
        if ($systemAction && $user) {
            $detailsMap = [
                'subir_pdf'        => "El administrador subió el dibujo '{$archivo}' en la ruta {$ruta}.",
                'eliminar_pdf'     => "El administrador eliminó el archivo '{$archivo}' de la ruta {$ruta}.",
                'reemplazar_pdf'   => "El administrador reemplazó el dibujo en {$ruta}: {$archivo}.",
                'crear_carpeta'    => "El administrador creó la carpeta de dibujos: {$ruta}.",
                'vaciar_carpeta'   => "El administrador vació la carpeta de dibujos: {$ruta}.",
                'eliminar_carpeta' => "El administrador eliminó la carpeta de dibujos: {$ruta}.",
            ];
            SystemLog::create([
                'user_matricula' => $user->matricula,
                'action'         => $systemAction,
                'details'        => $detailsMap[$action] ?? "Administrador realizó la acción '{$action}' en {$ruta}.",
            ]);
        }
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

    private function sanitizeFileName(string $name): string
    {
        // Evitar path traversal pero permitir acentos, paréntesis, espacios y otros caracteres seguros
        $name = preg_replace('/[\/\\\\]/', '', $name);
        $name = preg_replace('/\.\.+/', '', $name);
        return trim($name) ?: 'archivo.pdf';
    }

    /**
     * Convierte una cadena a UTF-8 si no lo está (e.g. nombres de archivos en Windows CP1252)
     */
    private function toUtf8(string $string): string
    {
        if (!mb_check_encoding($string, 'UTF-8')) {
            return mb_convert_encoding($string, 'UTF-8', 'Windows-1252');
        }
        return $string;
    }

    private function normalizeOTName(?string $name): string
    {
        if (!$name) return '';
        // Reemplazar guiones especiales y espacios de no ruptura
        $name = str_replace(['—', '–', "\xc2\xa0"], '-', $name);
        // Todo a mayúsculas para evitar problemas de case-sensitivity
        $name = mb_strtoupper($name, 'UTF-8');
        // Eliminar espacios múltiples
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }
}
